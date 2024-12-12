<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Traits\HasRoles;

class DepartmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function index()
    {
        $user = Auth::user();
        $company_id = $user->company_id; 
        $haveAccess = 'you dont have access';
        $permissions = $user->getAllPermissions();
        foreach($permissions as $permission){
            if($permission->name == "view-department") $haveAccess = $permission->name;
        };
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        if($haveAccess == "view-department" || $isOwner){
            if (!$company_id) {
                return response()->json(['message' => 'You must be associated with a company to view departments.'], 403);
            }
        
            $departments = Department::where('company_id', $company_id)
                ->with('manager')
                ->get();
            $departmentsWithManagers = $departments->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'manager_name' => $department->manager ? $department->manager->name : 'No manager assigned',
                ];
            });
        
            return response()->json(['Departments' => $departmentsWithManagers], 200);
        }else return response()->json(['message' => $haveAccess], 401);

    
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|max:255',
        ]);
    
        $user = Auth::user();
        $company_id = $user->company_id;
        $user_id = $user->id;
    
        $haveAccess = 'you dont have access';
        $permissions = $user->getAllPermissions();
        
        // Check if the user has the "create-department" permission
        foreach ($permissions as $permission) {
            if ($permission->name == "create-department") {
                $haveAccess = $permission->name;
            }
        }
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
    
        if ($haveAccess == "create-department" || $isOwner) {
            // Check if the department already exists for this company
            $existingDepartment = Department::where('name', $fields['name'])
                                            ->where('company_id', $company_id)
                                            ->first();
    
            if ($existingDepartment) {
                return response()->json(['message' => 'Department already exists for this company.'], 400);
            }
    
            // If the department doesn't exist, create a new one
            if (!$company_id) {
                return response()->json(['message' => 'You must be associated with a company to create a department.'], 403);
            }
    
            $department = Department::create([
                'name' => $fields['name'],
                'company_id' => $company_id,
                'user_id' => $user_id,
            ]);
    
            $department->makeHidden(['created_at', 'updated_at']);
            return response()->json(['Department' => $department], 201);
        } else {
            return response()->json(['message' => $haveAccess], 401);
        }
    }
    /**
     * Display the specified resource.
     */
        public function show($id)
        {
            $user = Auth::user();
            $company_id = $user->company_id;

            if (!$company_id) {
                return response()->json(['message' => 'You must be associated with a company to view departments.'], 403);
            }

            $department = Department::where('company_id', $company_id)
                                    ->where('id', $id)
                                    ->first();
            if (!$department) {
                return response()->json(['message' => 'Department not found or does not belong to your company.'], 404);
            }

            return response()->json(['Department' => $department], 200);
        }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $fields = $request->validate([
            'name' => 'required|max:255',
        ]);

        $user = Auth::user();
        $company_id = $user->company_id;

        $haveAccess = 'you dont have access';
        $permissions = $user->getAllPermissions();
        foreach($permissions as $permission){
            if($permission->name == "edit-department") $haveAccess = $permission->name;
        };
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();

        if($haveAccess == "edit-department" || $isOwner){
            if (!$company_id) {
                return response()->json(['message' => 'You must be associated with a company to update a department.'], 403);
            }
    
            $department = Department::where('company_id', $company_id)
                                    ->where('id', $id)
                                    ->first();
    
            if (!$department) {
                return response()->json(['message' => 'Department not found or does not belong to your company.'], 404);
            }
    
            $department->update([
                'name' => $fields['name'],
            ]);
    
            return response()->json(['Department' => $department], 200);
        }else return response()->json(['message' => $haveAccess], 401);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $company_id = $user->company_id;


        $haveAccess = 'you dont have access';
        $permissions = $user->getAllPermissions();
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        foreach($permissions as $permission){
            if($permission->name == "delete-department" || $isOwner) $haveAccess = $permission->name;
        };
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        if($haveAccess == "delete-department" || $isOwner){
            if (!$company_id) {
                return response()->json(['message' => 'You must be associated with a company to delete a department.'], 403);
            }
    
            $department = Department::where('company_id', $company_id)
                                    ->where('id', $id)
                                    ->first();
    
            if (!$department) {
                return response()->json(['message' => 'Department not found or does not belong to your company.'], 404);
            }
    
            $department->delete();
    
            return response()->json(['message' => 'Department deleted successfully.'], 200);
        }   else return response()->json(['message' => $haveAccess], 401);
    }
}
