<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe un usuario admin
        $adminExists = \App\Models\User::where('email', 'admin@uhtv.com')->first();
        
        if (!$adminExists) {
            \App\Models\User::create([
                'name' => 'Administrador UHTV',
                'email' => 'admin@uhtv.com',
                'email_verified_at' => now(),
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role' => 'admin',
            ]);
            
            $this->command->info('✅ Usuario administrador creado exitosamente');
            $this->command->info('📧 Email: admin@uhtv.com');
            $this->command->info('🔑 Password: admin123');
        } else {
            $this->command->info('ℹ️  El usuario administrador ya existe');
        }
        
        // Crear usuario de prueba adicional si no existe
        $testUser = \App\Models\User::where('email', 'test@uhtv.com')->first();
        
        if (!$testUser) {
            \App\Models\User::create([
                'name' => 'Usuario de Prueba',
                'email' => 'test@uhtv.com',
                'email_verified_at' => now(),
                'password' => \Illuminate\Support\Facades\Hash::make('test123'),
                'role' => 'user',
            ]);
            
            $this->command->info('✅ Usuario de prueba creado exitosamente');
            $this->command->info('📧 Email: test@uhtv.com');
            $this->command->info('🔑 Password: test123');
        }
    }
}
