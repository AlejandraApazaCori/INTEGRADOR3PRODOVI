<?php

namespace Tests\Feature;

use Database\Seeders\CampaniasDemoSeeder;
use Database\Seeders\StaffUsersSeeder;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MantenimientoWebTest extends TestCase
{
    public function test_the_maintenance_page_is_available_with_noindex_headers(): void
    {
        $response = $this->get(route('mantenimiento.web.index'));

        $response
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('php artisan migrate')
            ->assertSee('php artisan storage:link')
            ->assertSee('Ejecutar seeder del equipo')
            ->assertSee('Ejecutar seeder de campañas');
    }

    public function test_migrate_can_be_executed_and_its_output_is_returned(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('migrate', ['--force' => true])
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('2026_08_15_000000_create_solicitudes_contacto_table DONE');

        $response = $this->post(route('mantenimiento.web.execute', 'migrate'));

        $response
            ->assertRedirect(route('mantenimiento.web.index'))
            ->assertSessionHas('maintenance_result', function (array $result): bool {
                return $result['success'] === true
                    && $result['command'] === 'php artisan migrate'
                    && str_contains($result['output'], 'solicitudes_contacto');
            });
    }

    public function test_an_unlisted_command_cannot_be_executed(): void
    {
        Artisan::shouldReceive('call')->never();

        $this->post('/ejecutar-migraciones-Ma73027456Lpz/config-clear')
            ->assertNotFound();
    }

    public function test_the_staff_seeder_can_be_executed_with_a_password(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('db:seed', [
                '--class' => StaffUsersSeeder::class,
                '--force' => true,
            ])
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('Equipo creado correctamente.');

        $response = $this->post(route('mantenimiento.web.seed-staff'), [
            'staff_password' => 'Temporal#Equipo2026',
            'staff_password_confirmation' => 'Temporal#Equipo2026',
        ]);

        $response
            ->assertRedirect(route('mantenimiento.web.index'))
            ->assertSessionHas('staff_seed_result', fn (array $result): bool => $result['success'] === true)
            ->assertSessionHas('staff_credentials', fn (array $credentials): bool => $credentials['password'] === 'Temporal#Equipo2026');
    }

    public function test_the_demo_campaign_seeder_can_be_executed(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('db:seed', [
                '--class' => CampaniasDemoSeeder::class,
                '--force' => true,
            ])
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('Datos demo creados correctamente.');

        $response = $this->post(route('mantenimiento.web.seed-demo-campaigns'));

        $response
            ->assertRedirect(route('mantenimiento.web.index'))
            ->assertSessionHas('demo_campaigns_result', fn (array $result): bool => $result['success'] === true);
    }
}
