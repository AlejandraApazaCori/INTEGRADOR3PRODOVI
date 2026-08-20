<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
        $returnUrl = $empresa && $request->input('return_to') === 'empresa'
            ? route('empresas.show', $empresa->id)
            : ($empresa
                ? route('clientes.micuenta', ['redes_empresa' => $empresa->id])
                : url()->previous());

        if (! $user->socialAccountsTableExists()) {
            return redirect($returnUrl)->with('social_accounts_error', 'La integración de redes sociales aún no está disponible en este entorno porque falta la tabla social_accounts. Ejecuta las migraciones del sistema.');
        }

        if ($provider === 'instagram') {
            return $this->connectInstagram($user, $empresa, $returnUrl);
        }

        if (! $this->providerIsConfigured('facebook')) {
            return redirect($returnUrl)->with('social_accounts_error', 'La configuración OAuth de Facebook todavía no está completa.');
        }

        session([
            'social_accounts.return_url' => $returnUrl,
            'social_accounts.empresa_id' => $empresa?->id,
        ]);

        return Socialite::driver('facebook')
            ->scopes([
                'email',
                'public_profile',
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_posts',
                'instagram_basic',
                'instagram_content_publish',
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
            return redirect($returnUrl)->with('social_accounts_error', 'Instagram se sincroniza mediante la página de Facebook vinculada.');
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

            $facebookPageAccount = $user->socialAccounts()->updateOrCreate(
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

            $instagramAccount = null;

            try {
                $instagramAccount = $this->syncInstagramAccount($user, $empresa, $facebookPageAccount);
            } catch (Throwable $instagramException) {
                Log::warning('Facebook se vinculó, pero Instagram no pudo sincronizarse.', [
                    'user_id' => $user->id,
                    'empresa_id' => $empresa?->id,
                    'facebook_page_id' => $facebookPageAccount->provider_user_id,
                    'error' => $instagramException->getMessage(),
                ]);
            }

            $successMessage = 'Facebook fue vinculado correctamente y ya se guardó la página principal autorizada.';

            if ($instagramAccount) {
                $instagramName = $instagramAccount->username
                    ? '@'.ltrim($instagramAccount->username, '@')
                    : $instagramAccount->display_name;
                $successMessage .= " Instagram {$instagramName} también fue vinculado.";
            }

            return redirect($returnUrl)->with('social_accounts_success', $successMessage);
        } catch (Throwable $e) {
            return redirect($returnUrl)->with('social_accounts_error', 'No se pudo completar la vinculación con Facebook: '.$e->getMessage());
        }
    }

    private function connectInstagram(User $user, ?Empresa $empresa, string $returnUrl): RedirectResponse
    {
        $facebookPageQuery = $user->socialAccounts()->where('provider', 'facebook_page');

        $facebookPage = $empresa
            ? (clone $facebookPageQuery)->where('empresa_id', $empresa->id)->first()
            : (clone $facebookPageQuery)->whereNull('empresa_id')->first();

        // Compatibilidad con vinculaciones realizadas antes de asociar redes por empresa.
        if (! $facebookPage && $empresa) {
            $facebookPage = (clone $facebookPageQuery)->whereNull('empresa_id')->first();
        }

        if (! $facebookPage) {
            return redirect($returnUrl)->with(
                'social_accounts_error',
                'Primero debes vincular la página de Facebook de esta empresa antes de conectar Instagram.'
            );
        }

        try {
            $instagramAccount = $this->syncInstagramAccount($user, $empresa, $facebookPage);

            if (! $instagramAccount) {
                return redirect($returnUrl)->with(
                    'social_accounts_error',
                    'La página de Facebook vinculada no tiene una cuenta profesional de Instagram asociada. Vincúlala desde Meta Business Suite e inténtalo nuevamente.'
                );
            }

            $instagramName = $instagramAccount->username
                ? '@'.ltrim($instagramAccount->username, '@')
                : $instagramAccount->display_name;

            return redirect($returnUrl)->with(
                'social_accounts_success',
                "Instagram {$instagramName} fue vinculado correctamente con esta empresa."
            );
        } catch (Throwable $e) {
            Log::warning('No se pudo sincronizar la cuenta profesional de Instagram.', [
                'user_id' => $user->id,
                'empresa_id' => $empresa?->id,
                'facebook_page_id' => $facebookPage->provider_user_id,
                'error' => $e->getMessage(),
            ]);

            return redirect($returnUrl)->with(
                'social_accounts_error',
                'No se pudo conectar Instagram: '.$e->getMessage()
            );
        }
    }

    private function syncInstagramAccount(User $user, ?Empresa $empresa, SocialAccount $facebookPage): ?SocialAccount
    {
        $pageId = $facebookPage->provider_user_id ?: data_get($facebookPage->metadata, 'page_id');
        $pageAccessToken = $facebookPage->access_token;

        if (! filled($pageId) || ! filled($pageAccessToken)) {
            throw new \RuntimeException('La página de Facebook guardada no tiene credenciales válidas. Vuelve a conectar Facebook.');
        }

        $instagram = $this->fetchInstagramAccount($pageId, $pageAccessToken);

        if (! $instagram) {
            return null;
        }

        $profilePictureUrl = $instagram['profile_picture_url'] ?? null;

        return $user->socialAccounts()->updateOrCreate(
            ['empresa_id' => $empresa?->id, 'provider' => 'instagram'],
            [
                'provider_user_id' => $instagram['id'],
                'username' => $instagram['username'] ?? null,
                'display_name' => $instagram['name'] ?? $instagram['username'] ?? null,
                'email' => null,
                'avatar' => is_string($profilePictureUrl) && strlen($profilePictureUrl) <= 255
                    ? $profilePictureUrl
                    : null,
                'access_token' => $pageAccessToken,
                'refresh_token' => null,
                'token_expires_at' => $facebookPage->token_expires_at,
                'metadata' => [
                    'source' => 'instagram_business_account',
                    'facebook_page_id' => $pageId,
                    'facebook_page_name' => $facebookPage->display_name,
                    'profile_picture_url' => $profilePictureUrl,
                    'raw' => $instagram,
                ],
            ]
        );
    }

    private function fetchInstagramAccount(string $pageId, string $pageAccessToken): ?array
    {
        $response = Http::timeout(20)->get(
            'https://graph.facebook.com/'.config('facebook.api_version', 'v25.0').'/'.$pageId,
            [
                'fields' => 'instagram_business_account{id,username,name,profile_picture_url}',
                'access_token' => $pageAccessToken,
            ]
        );

        if (! $response->successful()) {
            $error = $response->json('error.message') ?? 'Meta no permitió consultar la cuenta de Instagram asociada.';

            Log::warning('Meta rechazó la consulta de Instagram asociada a Facebook.', [
                'facebook_page_id' => $pageId,
                'status' => $response->status(),
                'token' => $this->maskToken($pageAccessToken),
                'error' => $error,
            ]);

            throw new \RuntimeException($error);
        }

        $instagram = $response->json('instagram_business_account');

        return is_array($instagram) && filled($instagram['id'] ?? null)
            ? $instagram
            : null;
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

            return redirect($returnUrl)->with('social_accounts_error', 'No se pudo ejecutar la migración automática: '.$e->getMessage());
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
            'https://graph.facebook.com/'.config('facebook.api_version', 'v25.0').'/me/accounts',
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

        return substr($token, 0, 4).str_repeat('*', max(strlen($token) - 8, 4)).substr($token, -4);
    }
}
