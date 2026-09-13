<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUsers extends Command
{
    protected $signature = 'users:check';
    protected $description = 'Check existing users and their roles';

    public function handle()
    {
        $users = User::all();
        
        $this->info("Total users: " . $users->count());
        
        foreach ($users as $user) {
            $this->line("Email: {$user->email}");
            $this->line("Name: {$user->name}");
            $this->line("Role: {$user->role}");
            $this->line("Active: " . ($user->is_active ? 'Yes' : 'No'));
            $this->line("Email Verified: " . ($user->email_verified_at ? 'Yes' : 'No'));
            $this->line("---");
        }
        
        return 0;
    }
}