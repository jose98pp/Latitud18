<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
    }

    /** @test */
    public function admin_login_page_loads()
    {
        $response = $this->get(route('admin.login'));
        
        $response->assertStatus(200);
        $response->assertSee('UHTV Admin');
        $response->assertSee('Iniciar Sesión');
    }

    /** @test */
    public function admin_can_login_with_correct_credentials()
    {
        $response = $this->post(route('admin.login.store'), [
            'email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    /** @test */
    public function admin_cannot_login_with_incorrect_credentials()
    {
        $response = $this->post(route('admin.login.store'), [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /** @test */
    public function non_admin_user_cannot_access_admin_dashboard()
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now()
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        
        $response->assertRedirect(route('portada'));
    }

    /** @test */
    public function unauthenticated_user_redirected_to_login()
    {
        $response = $this->get(route('admin.dashboard'));
        
        $response->assertRedirect(route('admin.login'));
    }
}