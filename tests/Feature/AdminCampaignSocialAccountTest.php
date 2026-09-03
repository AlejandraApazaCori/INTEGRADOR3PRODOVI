<?php

namespace Tests\Feature;

use App\Models\Campania;
use App\Models\Empresa;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AdminCampaignSocialAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_start_facebook_connection_for_the_campaign_company(): void
    {
        [$admin, $client, $company, $campaign] = $this->context();
        config([
            'services.facebook.client_id' => 'facebook-client-id',
            'services.facebook.client_secret' => 'facebook-client-secret',
            'services.facebook.redirect' => 'https://prodovi.test/clientes/social/facebook/callback',
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('scopes')->once()->andReturnSelf();
        $provider->shouldReceive('with')->once()->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(redirect()->away('https://facebook.test/oauth'));
        Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);

        $this->actingAs($admin)
            ->get(route('clientes.social.redirect', [
                'provider' => 'facebook',
                'empresa_id' => $company->id,
                'return_to' => 'admin_campaign',
                'campania_id' => $campaign->id,
            ]))
            ->assertRedirect('https://facebook.test/oauth')
            ->assertSessionHas('social_accounts.user_id', $client->id)
            ->assertSessionHas('social_accounts.empresa_id', $company->id)
            ->assertSessionHas(
                'social_accounts.return_url',
                route('administrador.campañas.show', $campaign).'#analiticas'
            );
    }

    public function test_admin_facebook_callback_stores_accounts_for_the_campaign_company_and_client(): void
    {
        [$admin, $client, $company, $campaign] = $this->context();
        $returnUrl = route('administrador.campañas.show', $campaign).'#analiticas';
        $socialUser = (new SocialiteUser)->map([
            'id' => 'facebook-admin-10',
            'name' => 'Administrador Meta',
            'email' => 'admin.meta@example.com',
        ]);
        $socialUser->token = 'facebook-user-token';
        $socialUser->refreshToken = null;

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($socialUser);
        Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($provider);

        Http::fake(function ($request) {
            if (str_contains($request->url(), '/me/accounts')) {
                return Http::response(['data' => [[
                    'id' => 'facebook-page-20',
                    'name' => 'Página Empresa Campaña',
                    'access_token' => 'facebook-page-token',
                ]]]);
            }

            if (str_contains($request->url(), '/me/permissions')) {
                return Http::response(['data' => [
                    ['permission' => 'pages_read_engagement', 'status' => 'granted'],
                    ['permission' => 'read_insights', 'status' => 'granted'],
                ]]);
            }

            return Http::response(['id' => 'facebook-page-20', 'name' => 'Página Empresa Campaña']);
        });

        $this->actingAs($admin)
            ->withSession([
                'social_accounts.return_url' => $returnUrl,
                'social_accounts.empresa_id' => $company->id,
                'social_accounts.user_id' => $client->id,
            ])
            ->get(route('clientes.social.callback', 'facebook'))
            ->assertRedirect($returnUrl)
            ->assertSessionHas('social_accounts_success');

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $client->id,
            'empresa_id' => $company->id,
            'provider' => 'facebook',
            'provider_user_id' => 'facebook-admin-10',
        ]);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $client->id,
            'empresa_id' => $company->id,
            'provider' => 'facebook_page',
            'provider_user_id' => 'facebook-page-20',
        ]);
        $this->assertDatabaseMissing('social_accounts', [
            'user_id' => $admin->id,
            'empresa_id' => $company->id,
        ]);

        $this->get(route('administrador.campañas.show', $campaign).'#analiticas')
            ->assertOk()
            ->assertSee('Página Empresa Campaña')
            ->assertSee('Conectar con Instagram');
    }

    public function test_non_admin_cannot_connect_accounts_for_another_clients_company(): void
    {
        [, , $company, $campaign] = $this->context();
        $otherClient = User::factory()->create();

        $this->actingAs($otherClient)
            ->get(route('clientes.social.redirect', [
                'provider' => 'facebook',
                'empresa_id' => $company->id,
                'return_to' => 'admin_campaign',
                'campania_id' => $campaign->id,
            ]))
            ->assertNotFound();
    }

    private function context(): array
    {
        $adminRole = Role::create(['nombre_rol' => 'Administrador']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);
        $client = User::factory()->create();
        $company = Empresa::create([
            'usuario_id' => $client->id,
            'nombre_empresa' => 'Empresa de la campaña',
            'tipo_empresa' => 'Servicios',
        ]);
        $campaign = Campania::create([
            'nombre' => 'Campaña con conexión administrativa',
            'descripcion' => 'Campaña utilizada para probar la vinculación social.',
            'fecha_inicio' => now()->startOfMonth(),
            'fecha_fin' => now()->endOfMonth(),
            'estado' => 'activa',
            'usuario_creador_id' => $admin->id,
            'community_manager_id' => $admin->id,
            'usuario_cliente_id' => $client->id,
        ]);
        $campaign->empresas()->attach($company);

        return [$admin, $client, $company, $campaign];
    }
}
