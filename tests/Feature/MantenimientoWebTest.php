<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class MantenimientoWebTest extends TestCase
{
    public function test_the_maintenance_page_is_available_with_noindex_headers(): void
    {
        $response = $this->get(route('mantenimiento.web.index'));

        $response
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('Ejecutar composer install')
            ->assertSee('php artisan migrate')
            ->assertSee('php artisan storage:link');
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

    public function test_composer_dependencies_can_be_installed_from_the_maintenance_page(): void
    {
        Process::fake([
            '*' => Process::result('Generating optimized autoload files'),
        ]);

        Artisan::shouldReceive('call')
            ->once()
            ->with('optimize:clear')
            ->andReturn(0);

        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('Caches cleared successfully.');

        $response = $this->post(route('mantenimiento.web.execute', 'composer-install'));

        $response
            ->assertRedirect(route('mantenimiento.web.index'))
            ->assertSessionHas('maintenance_result', function (array $result): bool {
                return $result['success'] === true
                    && str_starts_with($result['command'], 'composer install')
                    && str_contains($result['output'], 'optimized autoload');
            });

        Process::assertRan(function ($process): bool {
            return is_array($process->command)
                && in_array('install', $process->command, true)
                && in_array('--no-dev', $process->command, true)
                && in_array('--optimize-autoloader', $process->command, true);
        });
    }

    public function test_an_unlisted_command_cannot_be_executed(): void
    {
        Artisan::shouldReceive('call')->never();

        $this->post('/ejecutar-migraciones-Ma73027456Lpz/config-clear')
            ->assertNotFound();
    }
}
