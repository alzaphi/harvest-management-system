<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

Route::get('/test-auth', function () {
    // Ambil username dari parameter URL, default ke user WAHYU
    $username = request('username', '085433889212');
    $password = 'VendorJBM'; // Ganti dengan password yang sesuai

    // Cari user
    $user = DB::table('users')->where('username', $username)->first();

    $output = "<h2>Testing Authentication for User: " . htmlspecialchars($username) . "</h2>";

    if (!$user) {
        return "<p style='color:red'>User not found!</p>";
    }

    $output .= "<p>User found: " . htmlspecialchars($user->name) . " (ID: $user->id)</p>";
    $output .= "<p>Role: " . htmlspecialchars($user->role_name) . "</p>";
    $output .= "<p>Stored password hash: " . substr($user->password, 0, 20) . "...</p>";

    // Coba verifikasi password
    $passwordMatches = Hash::check($password, $user->password);
    $verifyResult = $passwordMatches ? "<span style='color:green'>SUCCESS</span>" : "<span style='color:red'>FAILED</span>";

    $output .= "<p>Password verification: $verifyResult</p>";

    // Coba autentikasi dengan Auth
    $credentials = [
        'username' => $username,
        'password' => $password
    ];

    if (Auth::attempt($credentials)) {
        $output .= "<p style='color:green'>Authentication SUCCESSFUL!</p>";
        $output .= "<p>Authenticated user ID: " . Auth::id() . "</p>";
        $output .= "<p>Authenticated user name: " . Auth::user()->name . "</p>";
    } else {
        $output .= "<p style='color:red'>Authentication FAILED!</p>";
        
        // Cek alasan kegagalan
        $user = Auth::getProvider()->retrieveByCredentials($credentials);
        if (!$user) {
            $output .= "<p>Reason: User not found with provided credentials.</p>";
        } else if (!Auth::getProvider()->validateCredentials($user, $credentials)) {
            $output .= "<p>Reason: Invalid password.</p>";
            
            // Debug password
            $output .= "<h3>Password Debug Info:</h3>";
            $output .= "<p>Input password: " . htmlspecialchars($password) . "</p>";
            $output .= "<p>Stored hash: " . $user->password . "</p>";
            
            // Coba hash password yang dimasukkan untuk perbandingan
            $newHash = Hash::make($password);
            $output .= "<p>New hash of input password: $newHash</p>";
            
            // Bandingkan hash secara manual
            $manualVerify = password_verify($password, $user->password);
            $output .= "<p>Manual password_verify: " . ($manualVerify ? "SUCCESS" : "FAILED") . "</p>";
        }
    }

    // Tampilkan 5 user terakhir untuk referensi
    $output .= "<h3>Last 5 users in database:</h3>";
    $recentUsers = DB::table('users')->orderBy('id', 'desc')->take(5)->get();
    $output .= "<ul>";
    foreach ($recentUsers as $u) {
        $output .= "<li>" . htmlspecialchars("$u->name ($u->username) - $u->role_name - " . substr($u->password, 0, 10) . "...") . "</li>";
    }
    $output .= "</ul>";

    return $output;
});
