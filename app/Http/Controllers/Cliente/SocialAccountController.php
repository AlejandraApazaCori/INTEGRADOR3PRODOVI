<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAccountController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['facebook', 'instagram'];

    public function redirect(Request $request, string $provider)
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), 404);

        $user = Auth::user();
        $empresa = $request->filled('empresa_id')
            ? $user->empresas()->findOrFail($request->integer('empresa_id'))
            : null;
        $returnUrl = $empresa
            ? route('clientes.micuenta', ['redes_empresa' => $empresa->id])
            : url()->previous();

        if (! $user->socialAccountsTableExists()) {
            return redirect($returnUrl)->with('social_accounts_error', 'La integración de redes sociales aún no está disponible en este entorno porque falta la tabla social_accounts. Ejecuta las migraciones del sistema.');
        }

        $facebookLinked = $empresa
            ? $empresa->socialAccounts()->where('provider', 'facebook')->whereNotNull('provider_user_id')->exists()
            : $user->hasLinkedSocialAccount('facebook');

        if ($provider === 'instagram' && ! $facebookLinked) {
            return redirect($returnUrl)->with('social_accounts_error', 'Primero debes vincular Facebook antes de continuar con Instagram.');
        }

        if (! $this->providerIsConfigured($provider)) {
            return redirect($returnUrl)->with('social_accounts_error', 'La configuración OAuth de ' . ucfirst($provider) . ' todavía no está completa.');
        }

        session([
            'social_accounts.return_url' => $returnUrl,
            'social_accounts.empresa_id' => $empresa?->id,
        ]);

        if ($provider === 'instagram') {
            return redirect($returnUrl)->with('social_accounts_error', 'Instagram OAuth quedó preparado, pero el callback específico se conectará en el siguiente paso.');
        }

        return Socialite::driver('facebook')
            ->scopes([
                'email',
                'public_profile',
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_posts',
            ])
            ->with([
                'config_id' => config('services.facebook.login_config_id'),
            ])
            ->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), 404);

        $returnUrl = session()->pull('social_accounts.return_url', route('clientes.home'));
        $empresaId = session()->pull('social_accounts.empresa_id');

        if ($provider !== 'facebook') {
            return redirect($returnUrl)->with('social_accounts_error', 'El callback OAuth de Instagram se conectará en el siguiente paso.');
        }

        if (! Auth::user()->socialAccountsTableExists()) {
            return redirect($returnUrl)->with('social_accounts_error', 'La integración de redes sociales aún no está disponible en este entorno porque falta la tabla social_accounts. Ejecuta las migraciones del sistema.');
        }

        try {
            $socialUser = Socialite::driver('facebook')->user();
            $user = Auth::user();
            $empresa = $empresaId ? $user->empresas()->find($empresaId) : null;

            if ($empresaId && ! $empresa) {
                return redirect($returnUrl)->with('social_accounts_error', 'La empresa seleccionada ya no está disponible.');
            }

            $facebookPages = $this->fetchFacebookPages($socialUser->token);
            $primaryPage = $facebookPages[0] ?? null;
            $rawUserData = method_exists($socialUser, 'user') ? ($socialUser->user ?? []) : [];

            $user->socialAccounts()->updateOrCreate(
                ['empresa_id' => $empresa?->id, 'provider' => 'facebook'],
                [
                    'provider_user_id' => $socialUser->getId(),
                    'username' => $socialUser->getNickname(),
                    'display_name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'avatar' => $socialUser->getAvatar(),
                    'access_token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken,
                    'token_expires_at' => null,
                    'metadata' => [
                        'provider' => 'facebook',
                        'user_access_token' => $socialUser->token,
                        'facebook_user_id' => $socialUser->getId(),
                        'granted_scopes' => $rawUserData['granted_scopes'] ?? [],
                        'pages' => $facebookPages,
                        'raw' => $rawUserData,
                    ],
                ]
            );

            if (! $primaryPage) {
                $user->socialAccounts()
                    ->where('empresa_id', $empresa?->id)
                    ->where('provider', 'facebook_page')
                    ->delete();

                return redirect($returnUrl)->with('social_accounts_error', 'Facebook fue vinculado, pero no se encontró ninguna página autorizada. Asegúrate de seleccionar una página y otorgar permisos de publicación.');
            }

            $user->socialAccounts()->updateOrCreate(
                ['empresa_id' => $empresa?->id, 'provider' => 'facebook_page'],
                [
                    'provider_user_id' => $primaryPage['id'],
                    'username' => $primaryPage['name'],
                    'display_name' => $primaryPage['name'],
                    'email' => null,
                    'avatar' => null,
                    'access_token' => $primaryPage['access_token'] ?? null,
                    'refresh_token' => null,
                    'token_expires_at' => null,
                    'metadata' => [
                        'page_id' => $primaryPage['id'],
                        'page_name' => $primaryPage['name'],
                        'source' => 'me/accounts',
                        'linked_facebook_user_id' => $socialUser->getId(),
                        'raw' => $primaryPage,
                        'all_pages' => $facebookPages,
                    ],
                ]
            );

            return redirect($returnUrl)->with('social_accounts_success', 'Facebook fue vinculado correctamente y ya se guardó la página principal autorizada.');
        } catch (Throwable $e) {
            return redirect($returnUrl)->with('social_accounts_error', 'No se pudo completar la vinculación con Facebook: ' . $e->getMessage());
        }
    }

    public function setupSocialAccountsTable()
    {
        $returnUrl = url()->previous();

        try {
            Schema::dropIfExists('social_accounts');
            Artisan::call('migrate', ['--force' => true]);

            if (! Schema::hasTable('social_accounts')) {
                return redirect($returnUrl)->with('social_accounts_error', 'No se pudo completar la creación de la tabla social_accounts.');
            }

            return redirect($returnUrl)->with('social_accounts_success', 'La tabla social_accounts fue creada correctamente. Ya puedes vincular Facebook.');
        } catch (Throwable $e) {
            Log::error('No se pudo ejecutar la configuración de social_accounts.', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect($returnUrl)->with('social_accounts_error', 'No se pudo ejecutar la migración automática: ' . $e->getMessage());
        }
    }
    private function providerIsConfigured(string $provider): bool
    {
        $config = config("services.{$provider}");

        return filled($config['client_id'] ?? null)
            && filled($config['client_secret'] ?? null)
            && filled($config['redirect'] ?? null);
    }

    private function fetchFacebookPages(string $userAccessToken): array
    {
        $response = Http::timeout(20)->get(
            'https://graph.facebook.com/' . config('facebook.api_version', 'v25.0') . '/me/accounts',
            [
                'fields' => 'id,name,access_token',
                'access_token' => $userAccessToken,
            ]
        );

        if (! $response->successful()) {
            $error = $response->json('error.message') ?? 'No se pudo obtener la lista de páginas autorizadas.';

            Log::warning('No se pudieron obtener p�ginas de Facebook autorizadas.', [
                'status' => $response->status(),
                'token' => $this->maskToken($userAccessToken),
                'error' => $error,
            ]);

            throw new \RuntimeException($error);
        }

        return collect($response->json('data', []))
            ->map(function (array $page): array {
                return [
                    'id' => $page['id'] ?? null,
                    'name' => $page['name'] ?? 'Página sin nombre',
                    'access_token' => $page['access_token'] ?? null,
                ];
            })
            ->filter(fn (array $page): bool => filled($page['id']) && filled($page['access_token']))
            ->values()
            ->all();
    }

    private function maskToken(?string $token): ?string
    {
        if (! filled($token)) {
            return null;
        }

        if (strlen($token) <= 8) {
            return str_repeat('*', strlen($token));
        }

        return substr($token, 0, 4) . str_repeat('*', max(strlen($token) - 8, 4)) . substr($token, -4);
    }
}
