<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        // Get all roles that are in use by users
        $query = Role::whereIn('name', User::distinct()->pluck('role_name'));
        
        // Apply search filter if search term exists
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        $roles = $query->paginate(5)->withQueryString();
    
        $permissions = Permission::all()->pluck('name');
    
        $rolePermissions = [];
        foreach ($roles as $role) {
            $rolePermissions[$role->name] = $role->permissions->pluck('name')->toArray();
        }
    
        return view('admin.permissions.index', compact('roles', 'permissions', 'rolePermissions', 'search'));
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'string|max:255'
        ]);

        try {
            DB::beginTransaction();

            // Create or get the role
            $role = Role::firstOrCreate(['name' => $request->role_name]);

            // Sync permissions
            if ($request->has('permissions')) {
                // Create any new permissions that don't exist
                foreach ($request->permissions as $permissionName) {
                    Permission::firstOrCreate(['name' => $permissionName]);
                }
                
                // Sync all permissions to the role
                $role->syncPermissions($request->permissions);
            } else {
                // If no permissions are selected, remove all permissions
                $role->syncPermissions([]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Permissions updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to update permissions: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
