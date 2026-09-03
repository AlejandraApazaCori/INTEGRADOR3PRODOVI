<?php

namespace Tests\Feature;

use App\Models\SolicitudContacto;
use Carbon\Carbon;
use Database\Seeders\SolicitudesContactoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolicitudesContactoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_creates_fifteen_varied_contact_requests_without_duplicates(): void
    {
        Carbon::setTestNow('2026-09-03 12:00:00');

        $this->seed(SolicitudesContactoSeeder::class);
        $this->seed(SolicitudesContactoSeeder::class);

        $solicitudes = SolicitudContacto::where('correo', 'like', '%@demo.prodovi.test')->get();

        $this->assertCount(15, $solicitudes);
        $this->assertGreaterThanOrEqual(6, $solicitudes->pluck('servicio')->unique()->count());
        $this->assertSame(4, $solicitudes->whereNull('correo_enviado_at')->count());
        $this->assertSame(11, $solicitudes->whereNotNull('correo_enviado_at')->count());
        $this->assertTrue($solicitudes->every(fn (SolicitudContacto $solicitud) => $solicitud->created_at->lte(now())));
    }
}
