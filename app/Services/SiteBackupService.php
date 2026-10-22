<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

/** Encrypted, private backups; recovery drills never overwrite a live database. */
class SiteBackupService
{
    public function create(string $directory, string $password): array
    {
        $this->password($password);
        $directory = $this->privateDirectory($directory);
        $stage = $directory.'/.building-'.bin2hex(random_bytes(8));
        mkdir($stage, 0700);
        $archive = $directory.'/latitud18-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(3)).'.zip';
        $zip = new ZipArchive;
        try {
            $this->dump($stage.'/database.sql', $stage);
            $files = ['database.sql' => $stage.'/database.sql'];
            foreach (['storage/app/public', 'public/images', 'public/ediciones_pdf', 'public/periodico'] as $root) {
                $path = base_path($root);
                if (!is_dir($path)) continue;
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
                foreach ($iterator as $file) {
                    if ($file->isFile() && !$file->isLink()) {
                        $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($file->getPathname(), strlen(base_path()) + 1));
                        $files[$relative] = $file->getPathname();
                    }
                }
            }
            foreach (['.env', 'composer.json', 'composer.lock', 'storage/app/periodicos.json'] as $relative) {
                if (is_file(base_path($relative)) && !is_link(base_path($relative))) $files[$relative] = base_path($relative);
            }
            $manifest = ['format'=>1, 'created_at'=>now()->toIso8601String(), 'laravel'=>app()->version(),
                'database'=>config('database.connections.'.config('database.default').'.database'),
                'table_counts'=>$this->tableCounts(DB::connection()), 'files'=>[]];
            if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::EXCL) !== true) throw new \RuntimeException('No se pudo crear el respaldo.');
            foreach ($files as $name => $source) {
                $manifest['files'][$name] = ['sha256'=>hash_file('sha256', $source), 'bytes'=>filesize($source)];
                if (!$zip->addFile($source, $name) || !$zip->setEncryptionName($name, ZipArchive::EM_AES_256, $password)) {
                    throw new \RuntimeException('No se pudo cifrar un archivo del respaldo.');
                }
            }
            $zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            if (!$zip->setEncryptionName('manifest.json', ZipArchive::EM_AES_256, $password) || !$zip->close()) {
                throw new \RuntimeException('No se pudo cerrar el respaldo cifrado.');
            }
            chmod($archive, 0600);
            $this->verify($archive, $password);
            file_put_contents($archive.'.sha256', hash_file('sha256', $archive).'  '.basename($archive).PHP_EOL);
            chmod($archive.'.sha256', 0600);
            return ['archive'=>$archive, 'files'=>count($manifest['files']), 'tables'=>count($manifest['table_counts'])];
        } catch (\Throwable $e) {
            try { $zip->close(); } catch (\Throwable $ignored) {}
            @unlink($archive);
            throw $e;
        } finally {
            File::deleteDirectory($stage);
        }
    }

    public function verify(string $archive, string $password): array
    {
        $this->password($password);
        $zip = $this->open($archive, $password);
        try {
            $json = $zip->getFromName('manifest.json');
            if ($json === false) throw new \RuntimeException('Contraseña incorrecta o manifiesto ilegible.');
            $manifest = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (($manifest['format'] ?? null) !== 1 || empty($manifest['files']['database.sql']) || !isset($manifest['table_counts'])) {
                throw new \RuntimeException('El manifiesto no corresponde a un respaldo compatible.');
            }
            if ($zip->numFiles !== count($manifest['files']) + 1) throw new \RuntimeException('El archivo contiene entradas no declaradas.');
            foreach ($manifest['files'] as $name => $expected) {
                $this->safeEntry($name);
                $stream = $zip->getStream($name);
                if (!$stream) throw new \RuntimeException('No se pudo leer un archivo cifrado.');
                $hash = hash_init('sha256');
                $bytes = hash_update_stream($hash, $stream);
                fclose($stream);
                if (!hash_equals($expected['sha256'], hash_final($hash)) || $bytes !== $expected['bytes']) {
                    throw new \RuntimeException('Falló la integridad de un archivo del respaldo.');
                }
            }
            return $manifest;
        } finally { $zip->close(); }
    }

    public function restoreCheck(string $archive, string $password, string $database, string $directory): array
    {
        $manifest = $this->verify($archive, $password);
        $config = $this->mysqlConfig();
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $database) || strcasecmp($database, (string)$config['database']) === 0
            || strcasecmp($database, (string)$manifest['database']) === 0) {
            throw new \RuntimeException('La restauración de prueba requiere otra base de datos, distinta de la original.');
        }
        $config['database'] = $database;
        config(['database.connections.backup_restore_check'=>$config]);
        DB::purge('backup_restore_check');
        $target = DB::connection('backup_restore_check');
        if ($target->select('SHOW TABLES')) throw new \RuntimeException('La base de destino debe existir y estar completamente vacía.');
        $directory = $this->privateDirectory($directory);
        if (count(scandir($directory)) > 2) throw new \RuntimeException('La carpeta de restauración debe estar vacía.');
        $zip = $this->open($archive, $password);
        try {
            foreach ($manifest['files'] as $name => $metadata) {
                $this->safeEntry($name);
                $destination = $directory.'/'.$name;
                File::ensureDirectoryExists(dirname($destination), 0700);
                $input = $zip->getStream($name);
                $output = fopen($destination, 'xb');
                if (!$input || !$output) throw new \RuntimeException('No se pudo extraer el respaldo de prueba.');
                try { stream_copy_to_stream($input, $output); } finally { fclose($input); fclose($output); }
                chmod($destination, 0600);
                if (!hash_equals($metadata['sha256'], hash_file('sha256', $destination))) throw new \RuntimeException('Un archivo restaurado no coincide con el original.');
            }
            $credentials = $this->credentials($directory, $config);
            $input = fopen($directory.'/database.sql', 'rb');
            try {
                $process = new Process([config('backup.mysql', 'mysql'), '--defaults-extra-file='.$credentials, '--binary-mode=1', $database]);
                $process->setInput($input)->setTimeout(3600)->disableOutput();
                $process->run();
                if (!$process->isSuccessful()) throw new \RuntimeException('Falló la importación SQL en la base aislada (código '.$process->getExitCode().').');
            } finally { fclose($input); @unlink($credentials); }
            DB::purge('backup_restore_check');
            $counts = $this->tableCounts(DB::connection('backup_restore_check'));
            if ($counts !== $manifest['table_counts']) throw new \RuntimeException('Los recuentos de la base restaurada no coinciden.');
            return ['database'=>$database, 'directory'=>$directory, 'tables'=>count($counts), 'files'=>count($manifest['files'])];
        } finally { $zip->close(); DB::purge('backup_restore_check'); }
    }

    private function dump(string $path, string $stage): void
    {
        $config = $this->mysqlConfig();
        $credentials = $this->credentials($stage, $config);
        $output = fopen($path, 'xb');
        try {
            $process = new Process([config('backup.mysqldump', 'mysqldump'), '--defaults-extra-file='.$credentials,
                '--single-transaction', '--skip-lock-tables', '--quick', '--hex-blob', '--routines', '--events', '--triggers',
                '--no-tablespaces', '--skip-comments', $config['database']]);
            $error = '';
            $process->setTimeout(3600)->run(function ($type, $chunk) use ($output, $process, &$error) {
                if ($type === Process::OUT) {
                    $written = 0;
                    while ($written < strlen($chunk)) {
                        $count = fwrite($output, substr($chunk, $written));
                        if (!$count) throw new \RuntimeException('No se pudo escribir el SQL.');
                        $written += $count;
                    }
                    $process->clearOutput();
                } else { $error = substr($error.$chunk, -4000); $process->clearErrorOutput(); }
            });
            if (!$process->isSuccessful()) throw new \RuntimeException('Falló mysqldump (código '.$process->getExitCode().'): '.str_replace((string)$config['password'], '[oculta]', trim($error)));
        } finally { fclose($output); @unlink($credentials); }
        chmod($path, 0600);
    }

    private function credentials(string $directory, array $config): string
    {
        $path = $directory.'/.mysql-'.bin2hex(random_bytes(8)).'.cnf';
        $quote = fn ($value) => '"'.str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '\\n', '\\r'], (string)$value).'"';
        $text = "[client]\nuser=".$quote($config['username'])."\npassword=".$quote($config['password'])."\nhost=".$quote($config['host'] ?? 'localhost')."\nport=".(int)($config['port'] ?? 3306)."\n";
        if (config('backup.ssl_ca')) $text .= 'ssl-ca='.$quote(config('backup.ssl_ca'))."\nssl-verify-server-cert=1\n";
        if (!empty($config['unix_socket'])) $text .= 'socket='.$quote($config['unix_socket'])."\n";
        file_put_contents($path, $text);
        chmod($path, 0600);
        return $path;
    }

    private function tableCounts($connection): array
    {
        $counts = [];
        foreach ($connection->select('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'') as $row) {
            $table = array_values((array)$row)[0];
            $counts[$table] = (int)$connection->selectOne('SELECT COUNT(*) AS total FROM `'.str_replace('`','``',$table).'`')->total;
        }
        ksort($counts);
        return $counts;
    }

    private function mysqlConfig(): array
    {
        $config = DB::connection()->getConfig();
        if (!in_array($config['driver'], ['mysql', 'mariadb'], true) || !empty($config['url'])) {
            throw new \RuntimeException('Configura la conexión MySQL con DB_HOST, DB_DATABASE, DB_USERNAME y DB_PASSWORD para el respaldo.');
        }
        return $config;
    }

    private function privateDirectory(string $directory): string
    {
        if ($directory === '' || !str_starts_with($directory, '/')) throw new \RuntimeException('Indica una carpeta privada con ruta absoluta.');
        File::ensureDirectoryExists($directory, 0700);
        $path = realpath($directory);
        $project = realpath(base_path());
        if (!$path || in_array($path, ['/', '/tmp', '/home', '/var', '/usr', '/opt', '/srv', '/mnt', '/workspace'], true)
            || $path === $project || str_starts_with($path, $project.'/') || str_starts_with($project, $path.'/')
            || preg_match('#/(public|public_html|htdocs|www)(/|$)#i', $path)) {
            throw new \RuntimeException('El respaldo y la restauración deben estar fuera del proyecto y de las carpetas web públicas.');
        }
        if (!is_writable($path)) throw new \RuntimeException('La carpeta privada no tiene permisos de escritura.');
        chmod($path, 0700);
        return $path;
    }

    private function password(string $password): void
    {
        if (strlen($password) < 16) throw new \RuntimeException('SITE_BACKUP_PASSWORD debe contener al menos 16 caracteres.');
        if (!class_exists(ZipArchive::class) || !ZipArchive::isEncryptionMethodSupported(ZipArchive::EM_AES_256)) {
            throw new \RuntimeException('La extensión ZIP debe admitir cifrado AES-256.');
        }
    }

    private function open(string $path, string $password): ZipArchive
    {
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) throw new \RuntimeException('No se pudo abrir el respaldo ZIP.');
        $zip->setPassword($password);
        return $zip;
    }

    private function safeEntry(string $name): void
    {
        if ($name === '' || str_starts_with($name, '/') || str_contains($name, '\\') || str_contains($name, "\0")
            || in_array('..', explode('/', $name), true)) throw new \RuntimeException('El respaldo contiene una ruta insegura.');
    }
}
