<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
class OtpResetPasswordController extends Controller
{
    public function showRequestForm()
    {
        return view('auth.reset-otp-request');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
        ]);

        $username = $request->input('username');
        $user = User::where('username', $username)->first();

        if (!$user) {
            return back()->withErrors(['username' => 'Username not found']);
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        // Cek apakah username adalah email
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            // ✅ Kirim ke email
            Mail::to($username)->send(new \App\Mail\OtpMail($otp));
            return redirect()->route('otp.reset.verify.form')
                             ->with('status', 'OTP has been sent to your email.');
        } else {
            // ⚠️ Kirim ke nomor HP, misal via WhatsApp (sementara: tampilkan saja dulu)
            // Nanti bisa pakai API WhatsApp / SMS Gateway
            return redirect()->route('otp.reset.verify.form')
                             ->with('status', "OTP sent to phone number: $username (simulasi)");
        }
    }

    public function showVerifyForm(Request $request)
    {
        return view('auth.reset-otp-verify', [
            'username' => session('username'),
            'info' => session('info'),
        ]);
    }

    public function verifyOtpAndReset(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || $user->reset_token !== $request->otp) {
            return back()->withErrors(['otp' => 'OTP salah.']);
        }

        if (Carbon::now()->gt($user->reset_token_expires_at)) {
            return back()->withErrors(['otp' => 'OTP sudah kedaluwarsa.']);
        }

        $user->password = Hash::make($request->password);
        $user->reset_token = null;
        $user->reset_token_expires_at = null;
        $user->save();

        return redirect()->route('login')->with('status', 'Password berhasil di-reset. Silakan login.');
    }


}
