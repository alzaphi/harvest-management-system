<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_name' => ['required', 'string', 'in:vendor,mandor,admin,gis_department,finance,Assistant Divisi Plantation'],
        ]);

        // Clean the username (remove any non-numeric characters if it's a phone number)
        $username = $request->username;
        if ($request->role_name === 'vendor') {
            $username = preg_replace('/[^0-9]/', '', $username);
        } else {
            $username = strtolower(trim($username));
        }
        
        // Buat user dengan password yang sudah di-hash
        $user = new User();
        $user->name = $request->name;
        $user->username = $username;
        $user->password = $request->password; // Akan di-hash oleh mutator
        $user->role_name = $request->role_name;
        $user->save();
        
        // Log the user creation for debugging
        \Log::info('New user registered:', [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role_name,
            'has_password' => !empty($user->password),
            'password_hash' => $user->password
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
