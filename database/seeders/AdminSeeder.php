<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Verificar si el usuario admin ya existe
        $existingAdmin = User::where('email', 'bryan.costas@ultimahoratv.com')->first();
        
        if (!$existingAdmin) {
            User::create([
                'name' => 'Bryan Costas',
                'email' => 'bryan.costas@ultimahoratv.com',
                'password' => Hash::make('bryan.ultimahora2024'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->command->info('Usuario administrador creado exitosamente.');
        } else {
            // Si existe, actualizar los campos que puedan faltar
            $existingAdmin->update([
                'role' => 'admin',
                'is_active' => true,
                'updated_at' => now()
            ]);
            
            $this->command->info('Usuario administrador actualizado exitosamente.');
        }
    }
}
