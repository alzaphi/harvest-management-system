<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Password;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
        ]);

        // Laravel expects 'email' field, so we'll trick it:
        $credentials = ['email' => $request->username];

        // Simulate a user model with 'email' = username
        $user = User::where('username', $request->username)->first();

        // Manually create a Password Broker instance using a custom user resolver
        $status = Password::broker()->sendResetLink($credentials, function ($user, $token) use ($request) {
            // Inject 'username' as the email placeholder
            $user->email = $user->username;
        });

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['username' => __($status)]);
    }
}
