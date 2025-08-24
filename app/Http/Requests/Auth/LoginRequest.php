<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->ensureIsNotRateLimited();

        $username = $this->input('username');
        $password = $this->input('password');
        
        // Enable query logging
        \DB::enableQueryLog();
        
        // Debug: Log the login attempt
        \Log::channel('single')->info('Login attempt:', [
            'username' => $username,
            'ip' => $this->ip(),
            'time' => now()->toDateTimeString()
        ]);

        // Try to find the user by username
        $user = \App\Models\User::where('username', $username)->first();
        
        // Log the executed query
        \Log::channel('single')->info('User query:', \DB::getQueryLog());
        \Log::channel('single')->info('Raw SQL:', [
            'sql' => \DB::getQueryLog()[0]['query'] ?? 'No query logged',
            'bindings' => \DB::getQueryLog()[0]['bindings'] ?? []
        ]);
        \DB::flushQueryLog();
        
        // Debug: Log user lookup result
        if ($user) {
            \Log::channel('single')->info('User found in database:', [
                'user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role_name,
                'password_set' => !empty($user->password),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]);
            
            // Debug: Manually verify the password
            $passwordMatches = false;
            if (!empty($user->password)) {
                $passwordMatches = \Hash::check($password, $user->password);
                \Log::channel('single')->info('Manual password check:', [
                    'matches' => $passwordMatches,
                    'input_password_length' => strlen($password),
                    'stored_hash_length' => strlen($user->password)
                ]);
                
                // Log the first few characters of the stored hash for verification
                if (!empty($user->password)) {
                    \Log::channel('single')->info('Password hash prefix:', [
                        'stored_hash_prefix' => substr($user->password, 0, 10) . '...',
                        'stored_hash_algorithm' => password_get_info($user->password)['algoName'] ?? 'unknown'
                    ]);
                }
            } else {
                \Log::channel('single')->warning('User has no password set');
            }
            
            // If manual check passes but Auth::attempt fails, there might be an issue with the auth system
            if ($passwordMatches) {
                \Log::channel('single')->info('Password matches but will still try Auth::attempt');
            }
        } else {
            \Log::channel('single')->warning('User not found with username: ' . $username);
        }

        $credentials = [
            'username' => $username,
            'password' => $password
        ];

        // Try to authenticate
        $authSuccess = Auth::attempt($credentials, $this->boolean('remember'));
        
        if (!$authSuccess) {
            RateLimiter::hit($this->throttleKey());
            
            // More detailed error logging
            if ($user) {
                // Check if the user is active if you have an 'active' column
                $inactive = isset($user->active) && !$user->active;
                
                \Log::channel('single')->error('Auth::attempt failed. Possible reasons:', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'user_inactive' => $inactive,
                    'has_password' => !empty($user->password),
                    'input_password_empty' => empty($password),
                    'time' => now()->toDateTimeString()
                ]);
                
                // If we have the password, log more details (be careful with this in production)
                if (!empty($user->password) && !empty($password)) {
                    $hashInfo = password_get_info($user->password);
                    \Log::channel('single')->debug('Password verification details:', [
                        'stored_hash_algo' => $hashInfo['algoName'] ?? 'unknown',
                        'stored_hash_options' => $hashInfo['options'] ?? [],
                        'input_password_hash' => \Hash::make($password) === $user->password ? 'SAME_HASH' : 'DIFFERENT_HASH',
                        'bcrypt_verify' => password_verify($password, $user->password) ? 'VERIFIED' : 'INVALID'
                    ]);
                }
            }

            throw ValidationException::withMessages([
                'username' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        \Log::info('User logged in successfully:', ['user_id' => Auth::id()]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('username')).'|'.$this->ip();
    }
}
