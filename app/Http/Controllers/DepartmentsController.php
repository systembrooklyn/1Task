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
        $user = Auth::user();
        $this->authorize('viewAny', Department::class);
        $company_id = $user->company_id;
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
        $this->authorize('create', Department::class);
        if (!$company_id) {
            return response()->json(['message' => 'You must be associated with a company to create a department.'], 403);
        }
        $existingDepartment = Department::where('name', $fields['name'])
            ->where('company_id', $company_id)
            ->first();

        if ($existingDepartment) {
            return response()->json(['message' => 'Department already exists for this company.'], 400);
        }
        $department = Department::create([
            'name' => $fields['name'],
            'company_id' => $company_id,
            'user_id' => $user->id,
        ]);

        $department->makeHidden(['created_at', 'updated_at']);
        return response()->json(['Department' => $department], 201);
    }
    /**
     * Display the specified resource.
     */
     public function show($id)
     {
         $department = Department::find($id);
     
         if (!$department) {
             return response()->json(['message' => 'Department not found.'], 404);
         }
         $this->authorize('view', $department);
     
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
        if (!$company_id) {
            return response()->json(['message' => 'You must be associated with a company to update a department.'], 403);
        }
        $department = Department::where('company_id', $company_id)
                                ->where('id', $id)
                                ->first();
        if (!$department) {
            return response()->json(['message' => 'Department not found or does not belong to your company.'], 404);
        }
        $this->authorize('update', $department);
        $department->update([
            'name' => $fields['name'],
        ]);
        return response()->json(['Department' => $department], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $company_id = $user->company_id;
        if (!$company_id) {
            return response()->json(['message' => 'You must be associated with a company to delete a department.'], 403);
        }
        $department = Department::where('company_id', $company_id)
                                ->where('id', $id)
                                ->first();
        if (!$department) {
            return response()->json(['message' => 'Department not found or does not belong to your company.'], 404);
        }
        $this->authorize('delete', $department);
        $department->delete();
        return response()->json(['message' => 'Department deleted successfully.'], 200);
    }
}
