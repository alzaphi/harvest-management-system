<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

// Data pengguna yang akan dicek
$username = '085433889212';
$password = 'VendorJBM';

echo "Checking authentication for user: $username\n";

// 1. Cari pengguna berdasarkan username
$user = DB::table('users')->where('username', $username)->first();

if (!$user) {
    die("Error: User not found!\n");
}

echo "User found in database.\n";
echo "User ID: " . $user->id . "\n";
echo "Name: " . $user->name . "\n";
echo "Role: " . $user->role_name . "\n";

// 2. Verifikasi password secara manual
if (Hash::check($password, $user->password)) {
    echo "Password verification: SUCCESS\n";
} else {
    echo "Password verification: FAILED\n";
}

// 3. Coba autentikasi menggunakan Auth::attempt
echo "\nTrying Auth::attempt...\n";

$credentials = [
    'username' => $username,
    'password' => $password
];

if (Auth::attempt($credentials)) {
    echo "Auth::attempt: SUCCESS\n";
    echo "Authenticated user ID: " . Auth::id() . "\n";
} else {
    echo "Auth::attempt: FAILED\n";
    
    // Cek alasan kegagalan
    $user = Auth::getProvider()->retrieveByCredentials($credentials);
    
    if (!$user) {
        echo "- User not found with given credentials\n";
    } else if (!Auth::getProvider()->validateCredentials($user, $credentials)) {
        echo "- Invalid password\n";
    } else {
        echo "- Unknown authentication failure\n";
    }
}

echo "\nPassword hash info:\n";
$hashInfo = password_get_info($user->password);
print_r($hashInfo);

// Coba verifikasi password langsung
echo "\nVerifying password directly...\n";
$directVerify = password_verify($password, $user->password);
echo "password_verify result: " . ($directVerify ? 'SUCCESS' : 'FAILED') . "\n";

// Coba buat hash baru dari password yang sama
$newHash = Hash::make($password);
echo "\nNew hash of the same password: " . $newHash . "\n";

// Bandingkan hash yang tersimpan dengan yang baru
echo "Stored hash: " . $user->password . "\n";
$compareHashes = Hash::check($password, $user->password) ? 'MATCH' : 'NO MATCH';
echo "Hash comparison: " . $compareHashes . "\n";
