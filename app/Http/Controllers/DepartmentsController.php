<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $user = Auth::user();
    $company_id = $user->company_id; 

    if (!$company_id) {
        return response()->json(['message' => 'You must be associated with a company to view departments.'], 403);
    }

    $departments = Department::where('company_id', $company_id)->get();

    return response()->json(['Departments' => $departments], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validate the department name
    $fields = $request->validate([
        'name' => 'required|max:255',
    ]);

    $user = Auth::user();
    $company_id = $user->company_id; 
    $user_id = $user->id; 

    if (!$company_id) {
        return response()->json(['message' => 'You must be associated with a company to create a department.'], 403);
    }

    $department = Department::create([
        'name' => $fields['name'],
        'company_id' => $company_id,
        'user_id' => $user_id,
    ]);

    return response()->json(['Department' => $department], 201);
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

    $department->delete();

    return response()->json(['message' => 'Department deleted successfully.'], 200);
}
}
