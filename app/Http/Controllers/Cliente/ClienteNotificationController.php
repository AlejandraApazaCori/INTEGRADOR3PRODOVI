<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ClienteNotificationController extends Controller
{
    public function show(string $notification): RedirectResponse
    {
        $item = Auth::user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return redirect()->route('clientes.dashboard');
    }
}
