<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InstagramSocialAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_instagram_is_synchronized_from_the_company_facebook_page_without_changing_facebook(): void
    {
        $user = User::factory()->create();
        $empresa = Empresa::create([
            'usuario_id' => $user->id,
            'nombre_empresa' => 'Lumina consultora',
            'tipo_empresa' => 'Consultora',
        ]);

        $facebookPage = $user->socialAccounts()->create([
            'empresa_id' => $empresa->id,
            'provider' => 'facebook_page',
            'provider_user_id' => '106610722014428',
            'username' => 'Lumina consultora',
            'display_name' => 'Lumina consultora',
            'access_token' => 'page-access-token',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'id' => '106610722014428',
                'name' => 'Lumina consultora',
                'instagram_business_account' => [
                    'id' => '17841458503920416',
                    'username' => 'blanca_peluche.boutique_online',
                    'name' => 'Blanca Boutique Online',
                    'profile_picture_url' => 'https://example.com/instagram-profile.jpg',
                ],
            ]),
        ]);

        $response = $this->actingAs($user)->get(route('clientes.social.redirect', [
            'provider' => 'instagram',
            'empresa_id' => $empresa->id,
        ]));

        $response
            ->assertRedirect(route('clientes.micuenta', ['redes_empresa' => $empresa->id]))
            ->assertSessionHas('social_accounts_success', fn (string $message): bool => str_contains($message, '@blanca_peluche.boutique_online'));

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'provider' => 'instagram',
            'provider_user_id' => '17841458503920416',
            'username' => 'blanca_peluche.boutique_online',
            'display_name' => 'Blanca Boutique Online',
            'access_token' => 'page-access-token',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'id' => $facebookPage->id,
            'provider' => 'facebook_page',
            'provider_user_id' => '106610722014428',
            'access_token' => 'page-access-token',
        ]);

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/106610722014428')
            && $request['fields'] === 'instagram_business_account{id,username,name,profile_picture_url}'
            && $request['access_token'] === 'page-access-token');
    }

    public function test_instagram_connection_requires_a_linked_facebook_page(): void
    {
        $user = User::factory()->create();
        $empresa = Empresa::create([
            'usuario_id' => $user->id,
            'nombre_empresa' => 'Empresa sin Facebook',
            'tipo_empresa' => 'Servicios',
        ]);

        Http::fake();

        $this->actingAs($user)
            ->get(route('clientes.social.redirect', [
                'provider' => 'instagram',
                'empresa_id' => $empresa->id,
            ]))
            ->assertRedirect(route('clientes.micuenta', ['redes_empresa' => $empresa->id]))
            ->assertSessionHas('social_accounts_error');

        Http::assertNothingSent();
    }
}
