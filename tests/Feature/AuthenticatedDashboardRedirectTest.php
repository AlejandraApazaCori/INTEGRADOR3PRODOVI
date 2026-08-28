<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticatedDashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    public static function administrativeRoles(): array
    {
        return [
            'administrador' => ['Administrador'],
            'super administrador' => ['Super Administrador'],
        ];
    }

    #[DataProvider('administrativeRoles')]
    public function test_authenticated_administrators_are_always_sent_to_the_admin_dashboard(string $roleName): void
    {
        $user = User::factory()->create();
        $role = Role::create(['nombre_rol' => $roleName]);
        $user->roles()->attach($role);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('administrador.dashboard'));

        $this->actingAs($user)
            ->get(route('clientes.home'))
            ->assertRedirect(route('administrador.dashboard'));

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('administrador.dashboard'));

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee(route('dashboard'));
    }
}
