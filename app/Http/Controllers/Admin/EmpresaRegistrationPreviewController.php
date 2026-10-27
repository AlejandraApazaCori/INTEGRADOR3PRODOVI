<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EmpresaRegistrationPreviewController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        abort_unless($user->roles()->whereIn('nombre_rol', ['Super Administrador', 'Administrador'])->exists(), 403);

        return view('clientes.popupRedes', [
            'user' => $user,
            'facebookLinked' => false,
            'instagramLinked' => false,
            'anyAccountLinked' => false,
            'socialSetupSkipped' => false,
            'canContinueSocialSetup' => false,
            'empresa' => null,
            'onboardingSubscriptionId' => null,
            'isReturningSetup' => false,
            'suggestedCompanyName' => '',
            'initialStep' => 3,
            'isAdminPreview' => true,
        ]);
    }
}
