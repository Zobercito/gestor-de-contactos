<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DemoLoginController extends Controller
{
    public function __invoke(Request $request, string $role): RedirectResponse
    {
        if (! config('demo.enabled')) {
            throw new NotFoundHttpException;
        }

        if (! in_array($role, ['admin', 'editor', 'viewer'], true)) {
            throw new NotFoundHttpException;
        }

        $email = config("demo.accounts.{$role}");

        if ($email === null) {
            throw new NotFoundHttpException;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            throw new NotFoundHttpException;
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
