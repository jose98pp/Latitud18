<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin {--name=} {--email=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un nuevo usuario administrador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Creando usuario administrador para UHTV...');
        
        // Obtener datos del usuario
        $name = $this->option('name') ?: $this->ask('Nombre del administrador');
        $email = $this->option('email') ?: $this->ask('Email del administrador');
        $password = $this->option('password') ?: $this->secret('Contraseña del administrador');
        
        // Validar datos
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);
        
        if ($validator->fails()) {
            $this->error('❌ Errores de validación:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("   • $error");
            }
            return Command::FAILURE;
        }
        
        // Verificar si el usuario ya existe
        if (User::where('email', $email)->exists()) {
            $this->error("❌ Ya existe un usuario con el email: $email");
            return Command::FAILURE;
        }
        
        // Crear usuario
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            $this->info('✅ Usuario administrador creado exitosamente!');
            $this->info("📧 Email: {$user->email}");
            $this->info("👤 Nombre: {$user->name}");
            $this->info("🔑 Rol: {$user->role}");
            $this->info('');
            $this->info('🌐 Puedes acceder al panel admin en: http://127.0.0.1:8000/admin/login');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error al crear el usuario: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
