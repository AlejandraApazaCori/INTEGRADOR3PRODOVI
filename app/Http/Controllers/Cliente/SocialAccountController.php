<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAccountController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['facebook', 'instagram'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), 404);

        $user = Auth::user();

        if (! $user->socialAccountsTableExists()) {
            return redirect()->back()->with('social_accounts_error', 'La integración de redes sociales aún no está disponible.');
        }

        if ($provider === 'instagram' && ! $user->hasLinkedSocialAccount('facebook')) {
            return redirect()->back()->with('social_accounts_error', 'Primero debes vincular Facebook antes de continuar con Instagram.');
        }

        if (! $this->providerIsConfigured($provider)) {
            return redirect()->back()->with('social_accounts_error', 'La configuración OAuth de ' . ucfirst($provider) . ' todavía no está completa.');
        }

        session(['social_accounts.return_url' => url()->previous()]);

        if ($provider === 'instagram') {
            return redirect()->back()->with('social_accounts_error', 'Instagram OAuth quedó preparado, pero el callback específico se conectará en el siguiente paso.');
        }

        return Socialite::driver('facebook')
            ->scopes(['email', 'public_profile'])
            ->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), 404);

        $returnUrl = session()->pull('social_accounts.return_url', route('clientes.home'));

        if ($provider !== 'facebook') {
            return redirect($returnUrl)->with('social_accounts_error', 'El callback OAuth de Instagram se conectará en el siguiente paso.');
        }

        if (! Auth::user()->socialAccountsTableExists()) {
            return redirect($returnUrl)->with('social_accounts_error', 'La integración de redes sociales aún no está disponible.');
        }

        try {
            $socialUser = Socialite::driver('facebook')->user();

            Auth::user()->socialAccounts()->updateOrCreate(
                ['provider' => 'facebook'],
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
                        'raw' => method_exists($socialUser, 'user') ? $socialUser->user : [],
                    ],
                ]
            );

            return redirect($returnUrl)->with('social_accounts_success', 'Facebook fue vinculado correctamente. Ya puedes continuar con Instagram.');
        } catch (Throwable $e) {
            return redirect($returnUrl)->with('social_accounts_error', 'No se pudo completar la vinculación con Facebook: ' . $e->getMessage());
        }
    }

    private function providerIsConfigured(string $provider): bool
    {
        $config = config("services.{$provider}");

        return filled($config['client_id'] ?? null)
            && filled($config['client_secret'] ?? null)
            && filled($config['redirect'] ?? null);
    }
}