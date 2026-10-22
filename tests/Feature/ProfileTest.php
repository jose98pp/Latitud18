<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create(['role'=>'admin']);

        $response = $this
            ->actingAs($user)
            ->get('/admin/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create(['role'=>'admin']);

        $response = $this
            ->actingAs($user)
            ->patch('/admin/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create(['role'=>'admin']);

        $response = $this
            ->actingAs($user)
            ->patch('/admin/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_account_deletion_endpoint_is_not_exposed(): void
    {
        $user = User::factory()->create(['role'=>'admin']);
        $this->actingAs($user)->delete('/admin/profile', ['password'=>'password'])->assertStatus(405);
        $this->assertNotNull($user->fresh());
        $this->assertAuthenticatedAs($user);
    }

    public function test_non_admin_cannot_edit_an_administrators_profile(): void
    {
        $user = User::factory()->create(['role'=>'user']);
        $this->actingAs($user)->patch('/admin/profile', ['name'=>'Changed', 'email'=>$user->email])->assertRedirect(route('portada'));
        $this->assertNotEquals('Changed', $user->fresh()->name);
    }
}
