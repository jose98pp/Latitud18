<?php

namespace Tests\Feature;

use App\Services\SiteBackupService;
use Tests\TestCase;
use ZipArchive;

class SiteBackupTest extends TestCase
{
    public function test_short_password_is_rejected_before_backup(): void
    {
        $this->expectExceptionMessage('al menos 16 caracteres');
        app(SiteBackupService::class)->create('/tmp/unused-backup', 'short');
    }

    public function test_web_directory_cannot_receive_sensitive_backup(): void
    {
        $this->expectExceptionMessage('fuera del proyecto');
        app(SiteBackupService::class)->create(public_path(), str_repeat('x', 32));
    }

    private function archive(array $entries, bool $tamper = false): string
    {
        $path = tempnam(sys_get_temp_dir(), 'backup-test-');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $files = [];
        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
            $zip->setEncryptionName($name, ZipArchive::EM_AES_256, str_repeat('x',32));
            $files[$name] = ['sha256'=>hash('sha256', $tamper ? 'different' : $content), 'bytes'=>strlen($content)];
        }
        $zip->addFromString('manifest.json', json_encode(['format'=>1, 'database'=>'source', 'table_counts'=>[], 'files'=>$files]));
        $zip->setEncryptionName('manifest.json', ZipArchive::EM_AES_256, str_repeat('x',32));
        $zip->close();
        return $path;
    }

    public function test_encrypted_archive_hashes_are_checked_and_wrong_password_fails(): void
    {
        $path = $this->archive(['database.sql'=>'SELECT 1;']);
        try {
            $service = app(SiteBackupService::class);
            $this->assertCount(1, $service->verify($path, str_repeat('x',32))['files']);
            $this->expectException(\RuntimeException::class);
            $service->verify($path, str_repeat('z',32));
        } finally { unlink($path); }
    }

    public function test_tampered_file_is_rejected(): void
    {
        $path = $this->archive(['database.sql'=>'SELECT 1;'], true);
        try {
            $this->expectExceptionMessage('integridad');
            app(SiteBackupService::class)->verify($path, str_repeat('x',32));
        } finally { unlink($path); }
    }

    public function test_parent_directory_entries_cannot_be_restored(): void
    {
        $path = $this->archive(['database.sql'=>'SELECT 1;', '../outside'=>'bad']);
        try {
            $this->expectExceptionMessage('ruta insegura');
            app(SiteBackupService::class)->verify($path, str_repeat('x',32));
        } finally { unlink($path); }
    }

    public function test_restoration_refuses_the_live_database(): void
    {
        $path = $this->archive(['database.sql'=>'SELECT 1;']);
        try {
            $this->expectExceptionMessage('distinta de la original');
            app(SiteBackupService::class)->restoreCheck($path, str_repeat('x',32), config('database.connections.mysql.database'), '/tmp/unused-restore');
        } finally { unlink($path); }
    }
}
