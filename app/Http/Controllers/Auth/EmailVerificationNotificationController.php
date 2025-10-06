<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        \Log::info('Enviando email de verificación', [
            'user_id' => $request->user()->id,
            'user_email' => $request->user()->email,
        ]);

        try {
            $request->user()->sendEmailVerificationNotification();
            \Log::info('Email de verificación enviado exitosamente');
        } catch (\Exception $e) {
            \Log::error('Error al enviar email de verificación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        return back()->with('status', 'verification-link-sent');
    }
}
