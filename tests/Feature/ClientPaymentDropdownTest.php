<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPaymentDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_type_and_extension_use_custom_dropdowns_without_changing_native_values(): void
    {
        $client = User::factory()->create();
        Plan::create([
            'nombre' => 'marketing junior',
            'subtitulo' => 'Plan inicial',
            'precio' => 350,
            'moneda' => 'BS',
            'periodo_facturacion' => 'mes',
            'activo' => true,
        ]);

        $this->actingAs($client)
            ->get(route('clientes.pago', ['plan' => 'marketing-junior', 'origen' => 'comprar-plan']))
            ->assertOk()
            ->assertSee('id="document-type" class="payment-native-select"', false)
            ->assertSee('id="document-extension" class="payment-native-select"', false)
            ->assertSee('data-payment-select="document-type"', false)
            ->assertSee('data-payment-select="document-extension"', false)
            ->assertSee('class="payment-select-trigger"', false)
            ->assertSee('data-value="5" data-label="NIT"', false)
            ->assertSee('data-value="LP" data-label="LP" data-detail="La Paz"', false)
            ->assertSee("document_type_code: document.getElementById('document-type').value", false)
            ->assertSee("document_extension: document.getElementById('document-extension').value", false)
            ->assertSee('paymentDropdowns.forEach(initPaymentDropdown);', false);
    }
}
