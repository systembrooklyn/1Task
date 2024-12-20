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
    use HasRoles;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $haveAccess = false;
        $user = Auth::user();
        $company_id = $user->company_id;
        $permissions = $user->getAllPermissions();
        foreach($permissions as $permission){
            if($permission->name == "view-department") {
                $haveAccess = true;
            break;
        }
        };
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        if($haveAccess || $isOwner){
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
        }else return response()->json(['message' => 'You do not have permission to view departments'], 401);

    
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $haveAccess = false;
        $fields = $request->validate([
            'name' => 'required|max:255',
        ]);
    
        $user = Auth::user();
        $company_id = $user->company_id;
        $user_id = $user->id;
        $permissions = $user->getAllPermissions();
        
        foreach ($permissions as $permission) {
            if ($permission->name == "create-department") {$haveAccess = true;
            break;}
        }
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
    
        if ($haveAccess || $isOwner) {
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
            return response()->json(['message' => 'You do not have permission to create department'], 401);
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
        $haveAccess = false;
        $fields = $request->validate([
            'name' => 'required|max:255',
        ]);

        $user = Auth::user();
        $company_id = $user->company_id;
        $permissions = $user->getAllPermissions();
        foreach($permissions as $permission){
            if($permission->name == "edit-department") {$haveAccess = true;
            break;}
        };
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();

        if($haveAccess || $isOwner){
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
        }else return response()->json(['message' => 'You do not have permission to edit this department'], 401);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $haveAccess = false;
        $user = Auth::user();
        $company_id = $user->company_id;
        $permissions = $user->getAllPermissions();
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        foreach($permissions as $permission){
            if($permission->name == "delete-department" || $isOwner) {$haveAccess = true;
            break;}
        };
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        if($haveAccess || $isOwner){
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
        }   else return response()->json(['message' => 'You do not have permission to delete this department'], 401);
    }
}
