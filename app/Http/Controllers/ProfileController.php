<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $profileData = [];
        
        if ($user->role_name === 'vendor') {
            $profileData = \App\Models\VendorAngkut::where('kode_vendor', $user->username)
                ->orWhere('nama_vendor', $user->name)
                ->get();
        } elseif ($user->role_name === 'mandor') {
            // Cari data mandor berdasarkan email atau username
            $mandor = \App\Models\Foreman::where('email', $user->email)
                ->orWhere('kode_mandor', $user->username)
                ->first();
            
            // Jika tidak ditemukan, coba cari berdasarkan nama
            if (!$mandor) {
                $mandor = \App\Models\Foreman::where('nama_mandor', $user->name)->first();
            }

            // Siapkan data profil
            if ($mandor) {
                $profileData = [
                    'kode_mandor' => $mandor->kode_mandor,
                    'no_hp' => $mandor->no_hp ?: '-',
                    'email' => $user->email
                ];
                
                // Update nama user jika berbeda dengan yang ada di database
                if ($user->name !== $mandor->nama_mandor) {
                    $user->name = $mandor->nama_mandor;
                    $user->save();
                }
            } else {
                // Fallback jika data mandor tidak ditemukan
                $profileData = [
                    'kode_mandor' => $user->username,
                    'no_hp' => '-',
                    'email' => $user->email
                ];
            }
        }
        
        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => route('dashboard')],
            ['title' => 'Profile', 'url' => route('profile')]
        ];
        
        return view('profile.profile', compact('user', 'breadcrumb', 'profileData'));
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak valid.',
            'new_password.required' => 'Password baru harus diisi.',
            'new_password.min' => 'Password minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('profile')
            ->with('status', 'Password berhasil diperbarui!');
    }
}
