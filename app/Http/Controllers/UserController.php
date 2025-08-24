<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
// Role management without Spatie package

class UserController extends Controller
{
    /**
     * Get available roles
     *
     * @return array
     */
    protected function getRoles()
    {
        return [
            'vendor' => 'Vendor',
            'mandor' => 'Mandor',
            // 'finance' => 'Finance',
            // 'cdr' => 'CDR',
            // 'plantation' => 'Plantation',
            // 'gis' => 'GIS',
            // 'pt_pag' => 'PT PAG',
            // 'qa' => 'QA',
            'admin' => 'Admin'
        ];
    }
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        
        // Log untuk debugging
        Log::info('Filter parameters:', [
            'search' => $search,
            'role' => $role
        ]);
        
        $query = User::query();
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        
        // Apply role filter
        if ($role) {
            // Normalize role name to match database values
            $normalizedRole = strtolower(trim($role));
            $query->whereRaw('LOWER(role_name) = ?', [$normalizedRole]);
            
            // Log the query being executed
            Log::info('Role filter query:', [
                'role' => $role,
                'normalized_role' => $normalizedRole,
                'query' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
        }
        
        $users = $query->latest()->paginate(15)->withQueryString();
        
        // Get all existing roles from the database for the filter dropdown
        $existingRoles = User::select('role_name')
            ->distinct()
            ->pluck('role_name')
            ->filter()
            ->mapWithKeys(function($role) {
                return [$role => ucfirst($role)];
            })
            ->toArray();
            
        // Merge with default roles to ensure all options are available
        $allRoles = array_merge($this->getRoles(), $existingRoles);
        $roles = ['' => 'Semua Role'] + $allRoles;
        
        // Log available roles for debugging
        Log::info('Available roles:', [
            'default_roles' => $this->getRoles(),
            'existing_roles' => $existingRoles,
            'all_roles' => $allRoles
        ]);
            
        // Set breadcrumb data
        $breadcrumb = [
            ['title' => 'Dashboard', 'url' => url('/')],
            ['title' => 'User Account Registration']
        ];
        
        return view('users.index', [
            'users' => $users,
            'breadcrumb' => $breadcrumb,
            'roles' => $roles,
            'selectedRole' => $role
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = $this->getRoles();
        
        // Dapatkan daftar vendor yang belum memiliki akun
        $existingVendorPhones = User::where('role_name', 'vendor')
            ->pluck('username')
            ->map(function($username) {
                // Hapus semua karakter non-angka dari username
                return preg_replace('/[^0-9]/', '', $username);
            })
            ->toArray();

        // Get all vendors first, ordered by name
        $allVendors = \App\Models\Vendor::select('id', 'nama_vendor', 'no_hp', 'kode_vendor')
            ->orderBy('nama_vendor')
            ->get();
            
        // Filter out vendors that already have accounts and remove duplicates by vendor name
        $uniqueVendorNames = [];
        $vendors = $allVendors->reject(function ($vendor) use ($existingVendorPhones, &$uniqueVendorNames) {
            $phone = preg_replace('/[^0-9]/', '', $vendor->no_hp);
            $isDuplicate = in_array(strtolower(trim($vendor->nama_vendor)), $uniqueVendorNames);
            
            if (!$isDuplicate) {
                $uniqueVendorNames[] = strtolower(trim($vendor->nama_vendor));
            }
            
            return $isDuplicate || in_array($phone, $existingVendorPhones);
        })->values();
            
        // Dapatkan daftar foreman yang belum memiliki akun
        $existingForemanEmails = User::where('role_name', 'mandor')
            ->pluck('username')
            ->toArray();
            
        // Dapatkan daftar foreman yang belum memiliki akun
        $foremen = \App\Models\Foreman::whereNotIn('email', $existingForemanEmails)
            ->orderBy('nama_mandor')
            ->get();
        
        // Set breadcrumb data
        $breadcrumb = [
            ['title' => 'User Account Registration', 'url' => route('users.index')],
            ['title' => 'Tambah User', 'url' => route('users.create')],
        ];
        
        // Available roles
        $roles = [
            'vendor' => 'Vendor',
            'mandor' => 'Mandor',
            // 'gis_department' => 'GIS Department',
            'admin' => 'Admin',
            // 'finance' => 'Finance'
        ];
        
        return view('users.create', compact('breadcrumb', 'roles', 'vendors', 'foremen'));
    }
    
    /**
     * Show the form for editing the specified user's password.
     */
    public function editPassword($id)
    {
        $user = User::findOrFail($id);
        
        // Set breadcrumb data
        $breadcrumb = [
            ['title' => 'User Account Registration', 'url' => route('users.index')],
            ['title' => 'Ubah Password', 'url' => route('users.edit-password', $user->id)],
        ];
        
        return view('users.edit', compact('user', 'breadcrumb'));
    }
    
    /**
     * Update the specified user's password.
     */
    public function updatePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Password saat ini tidak sesuai.');
                }
            }],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini harus diisi',
            'new_password.required' => 'Password baru harus diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password baru tidak sesuai',
        ]);
        
        try {
            // Update the password
            $user->password = Hash::make($validated['new_password']);
            $user->save();
            
            return redirect()->route('users.index')
                ->with('success', 'Password berhasil diperbarui');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui password. Silakan coba lagi.');
        }
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'role_name' => 'required|string',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ];

        // Get available roles from the roles array
        $availableRoles = array_keys($this->getRoles());
        
        // Add validation for role name
        $rules['role_name'] = 'required|string|max:50';
        
        // Add role-specific validations
        if ($request->role_name === 'vendor') {
            $rules['vendor_id'] = 'required|exists:vendor_angkut,id';
        } elseif ($request->role_name === 'mandor') {
            $rules['foreman_id'] = 'required|exists:foreman,id';
        } else if (!in_array($request->role_name, $availableRoles)) {
            // If it's a new role, validate the role name format
            $rules['role_name'] = 'required|string|max:50|regex:/^[a-zA-Z0-9_\s]+$/|not_in:' . implode(',', $availableRoles);
        } else {
            $rules['email'] = 'required|email|max:255';
        }

        $validated = $request->validate($rules);

        // Siapkan username berdasarkan role
        $username = '';
        $roleName = $validated['role_name'];
        
        // Generate username based on role
        if (!in_array($roleName, array_keys($this->getRoles()))) {
            // For new roles, use email if available, otherwise generate from role name
            $username = $request->input('email', strtolower(str_replace(' ', '_', $roleName)) . '_' . time());
        } 
        // Jika role vendor
        elseif ($roleName === 'vendor') {
            $vendor = \App\Models\Vendor::find($validated['vendor_id']);
            // Normalisasi nomor HP: hapus semua karakter non-angka
            $username = preg_replace('/[^0-9]/', '', $vendor->no_hp);
            
            // Pastikan username tidak kosong
            if (empty($username)) {
                return back()->withInput()->with('error', 'Nomor HP vendor tidak valid.');
            }
        } 
        // Jika role mandor
        elseif ($roleName === 'mandor') {
            $foreman = \App\Models\Foreman::find($validated['foreman_id']);
            $username = $foreman->email;
            
            if (empty($username)) {
                return back()->withInput()->with('error', 'Email foreman tidak valid.');
            }
        } 
        // Role lain (admin, gis_department, dll)
        else {
            $username = $validated['email'] ?? ($roleName . '_' . time());
        }

        // Cek apakah username sudah ada
        if (User::where('username', $username)->exists()) {
            return back()->withInput()->with('error', 'Akun untuk vendor ini sudah ada.');
        }

        // Prepare user data
        $userData = [
            'role_name' => $validated['role_name'],
            'name' => $validated['name'],
            'username' => $username,
            'password' => Hash::make($validated['password']),
        ];

        $user = User::create($userData);

        return redirect()->route('users.index')
                         ->with('success', 'Data user berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = ['vendor' => 'Vendor', 'mandor' => 'Mandor', 'gis_department' => 'GIS Department', 'admin' => 'Admin', 'finance' => 'Finance'];
        
        // Get all vendors for dropdown
        $vendors = VendorAngkut::all();
        
        // Get all foremen for dropdown
        $foremen = \App\Models\Foreman::all();
        
        // Set breadcrumb data
        $breadcrumb = [
            ['title' => 'Home', 'url' => url('/')],
            ['title' => 'User Account Registration', 'url' => route('users.index')],
            ['title' => 'Edit User']
        ];
        
        return view('users.edit', [
            'user' => $user,
            'roles' => $roles,
            'vendors' => $vendors,
            'foremen' => $foremen,
            'breadcrumb' => $breadcrumb
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Prevent deleting own account
            if (auth()->id() === $user->id) {
                return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
            }
            
            $user->delete();
            
            return redirect()->route('users.index')
                             ->with('success', 'Data user berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
}
