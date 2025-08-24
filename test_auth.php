<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illware\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

// Ambil username dari parameter URL, default ke user WAHYU
$username = $_GET['username'] ?? '085433889212';
$password = 'VendorJBM'; // Ganti dengan password yang sesuai

// Cari user
$user = DB::table('users')->where('username', $username)->first();

echo "<h2>Testing Authentication for User: " . htmlspecialchars($username) . "</h2>";

if (!$user) {
    die("<p style='color:red'>User not found!</p>");
}

echo "<p>User found: " . htmlspecialchars($user->name) . " (ID: $user->id)</p>";
echo "<p>Role: " . htmlspecialchars($user->role_name) . "</p>";
echo "<p>Stored password hash: " . substr($user->password, 0, 20) . "...</p>";

// Coba verifikasi password
$passwordMatches = Hash::check($password, $user->password);
$verifyResult = $passwordMatches ? "<span style='color:green'>SUCCESS</span>" : "<span style='color:red'>FAILED</span>";

echo "<p>Password verification: $verifyResult</p>";

// Coba autentikasi dengan Auth
$credentials = [
    'username' => $username,
    'password' => $password
];

if (Auth::attempt($credentials)) {
    echo "<p style='color:green'>Authentication SUCCESSFUL!</p>";
    echo "<p>Authenticated user ID: " . Auth::id() . "</p>";
    echo "<p>Authenticated user name: " . Auth::user()->name . "</p>";
} else {
    echo "<p style='color:red'>Authentication FAILED!</p>";
    
    // Cek alasan kegagalan
    $user = Auth::getProvider()->retrieveByCredentials($credentials);
    if (!$user) {
        echo "<p>Reason: User not found with provided credentials.</p>";
    } else if (!Auth::getProvider()->validateCredentials($user, $credentials)) {
        echo "<p>Reason: Invalid password.</p>";
        
        // Debug password
        echo "<h3>Password Debug Info:</h3>";
        echo "<p>Input password: " . htmlspecialchars($password) . "</p>";
        echo "<p>Stored hash: " . $user->password . "</p>";
        
        // Coba hash password yang dimasukkan untuk perbandingan
        $newHash = Hash::make($password);
        echo "<p>New hash of input password: $newHash</p>";
        
        // Bandingkan hash secara manual
        $manualVerify = password_verify($password, $user->password);
        echo "<p>Manual password_verify: " . ($manualVerify ? "SUCCESS" : "FAILED") . "</p>";
    }
}

// Tampilkan 5 user terakhir untuk referensi
echo "<h3>Last 5 users in database:</h3>";
$recentUsers = DB::table('users')->orderBy('id', 'desc')->take(5)->get();
echo "<ul>";
foreach ($recentUsers as $u) {
    echo "<li>" . htmlspecialchars("$u->name ($u->username) - $u->role_name - " . substr($u->password, 0, 10) . "...") . "</li>";
}
echo "</ul>";
?>
