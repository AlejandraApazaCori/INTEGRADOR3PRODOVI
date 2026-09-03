<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SolicitudContacto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_can_see_contact_requests_from_the_landing_page(): void
    {
        $admin = User::factory()->create();
        $role = Role::create(['nombre_rol' => 'Administrador']);
        $admin->roles()->attach($role);

        SolicitudContacto::create([
            'nombre' => 'María Pérez',
            'correo' => 'maria@example.com',
            'telefono' => '76543210',
            'servicio' => 'social',
            'mensaje' => 'Necesito una estrategia completa para las redes sociales de mi empresa.',
            'correo_enviado_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('administrador.solicitudes-contacto.index'))
            ->assertOk()
            ->assertSee('Hablemos de tu Proyecto')
            ->assertSee('María Pérez')
            ->assertSee('maria@example.com')
            ->assertSee('76543210')
            ->assertSee('Redes sociales')
            ->assertSee('Necesito una estrategia completa');
    }

    public function test_a_customer_cannot_see_admin_contact_requests(): void
    {
        $customer = User::factory()->create();
        $role = Role::create(['nombre_rol' => 'Cliente']);
        $customer->roles()->attach($role);

        $this->actingAs($customer)
            ->get(route('administrador.solicitudes-contacto.index'))
            ->assertForbidden();
    }

    public function test_contact_requests_can_be_filtered_by_service(): void
    {
        $admin = User::factory()->create();
        $role = Role::create(['nombre_rol' => 'Super Administrador']);
        $admin->roles()->attach($role);

        SolicitudContacto::create([
            'nombre' => 'Interesado en publicidad',
            'correo' => 'publicidad@example.com',
            'servicio' => 'publicidad',
            'mensaje' => 'Quiero lanzar una campaña publicitaria para mi nuevo producto.',
        ]);
        SolicitudContacto::create([
            'nombre' => 'Interesado en eventos',
            'correo' => 'eventos@example.com',
            'servicio' => 'eventos',
            'mensaje' => 'Quiero organizar un evento empresarial durante este trimestre.',
        ]);

        $this->actingAs($admin)
            ->get(route('administrador.solicitudes-contacto.index', ['servicio' => 'eventos']))
            ->assertOk()
            ->assertSee('eventos@example.com')
            ->assertDontSee('publicidad@example.com');
    }
}
