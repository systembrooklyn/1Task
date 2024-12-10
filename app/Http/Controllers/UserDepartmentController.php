<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserDepartmentController extends Controller
{
    public function assignManagerToDepartment(Request $request)
    {
        // Validate the request to ensure 'department_id' and 'user_id' are provided
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id', // Ensure department_id exists in the departments table
            'user_id' => 'required|exists:users,id', // Ensure user_id exists in the users table
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the department by ID
        $department = Department::find($request->department_id);

        // Check if the department exists
        if (!$department) {
            return response()->json([
                'message' => 'Department not found.'
            ], 404);
        }

        // Get the user (manager) by ID
        $manager = User::find($request->user_id);

        // Check if the manager exists
        if (!$manager) {
            return response()->json([
                'message' => 'Manager not found.'
            ], 404);
        }

        // Check if the manager belongs to the same company as the department
        if ($manager->company_id !== $department->company_id) {
            return response()->json([
                'message' => 'The manager and the department must be in the same company.'
            ], 400);
        }

        // Assign the manager to the department
        $department->user_id = $request->user_id;
        $department->save();

        // Return success message and the updated department with manager details
        return response()->json([
            'message' => 'Manager assigned successfully.',
            'department' => $department->load('manager') // Load manager details with department
        ]);
    }
    public function assignDepartments(Request $request, $userId)
{
    // Validate that department_ids is an array of integers
    $validator = Validator::make($request->all(), [
        'department_ids' => 'required|array',
        'department_ids.*' => 'integer|exists:departments,id',
    ]);

    // Return validation errors if validation fails
    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed.',
            'errors' => $validator->errors()
        ], 422);
    }

    // Find the user to whom departments will be assigned
    $user = User::find($userId);

    // Check if the user exists
    if (!$user) {
        return response()->json([
            'message' => 'User not found.'
        ], 404);
    }

    // Get the logged-in user (authenticated user)
    $loggedInUser = Auth::user();

    // Check if both the logged-in user and the target user are in the same company
    if ($user->company_id !== $loggedInUser->company_id) {
        return response()->json([
            'message' => 'The user and the logged-in user are not in the same company.',
            'errors' => [
                'user' => [
                    'The target user does not belong to the same company as the logged-in user.'
                ]
            ]
        ], 400);
    }

    // Get the target departments from the request
    $departments = Department::whereIn('id', $request->department_ids)->get();

    // Check if departments belong to the same company as the logged-in user
    foreach ($departments as $department) {
        if ($department->company_id != $loggedInUser->company_id) {
            return response()->json([
                'message' => 'User and department are not in the same company.',
                'errors' => [
                    'department_id' => [
                        'One or more departments do not belong to the user\'s company.'
                    ]
                ]
            ], 400);
        }
    }

    // Assign the user to the departments (sync the relationship)
    $user->departments()->sync($request->department_ids);

    return response()->json([
        'message' => 'Departments assigned successfully.',
        'user' => $user->load('departments')
    ]);
}

public function getUsersInDepartment(Request $request)
{
    // Get the logged-in user
    $user = Auth::user();

    // Check if the user belongs to any department
    $userDepartments = $user->departments;

    if ($userDepartments->isEmpty()) {
        return response()->json([
            'message' => 'User is not assigned to any department.'
        ], 400);
    }

    // Get the first department the user belongs to (assuming the user is assigned to only one department)
    $department = $userDepartments->first();

    // Get the company that the department belongs to
    $company = $department->company;

    // Retrieve all users assigned to this department and check if they are in the same company
    $usersInDepartment = User::whereHas('departments', function($query) use ($department) {
        $query->where('departments.id', $department->id);
    })->get();

    // Filter users who are in the same company as the department
    $filteredUsers = $usersInDepartment->filter(function($user) use ($company) {
        return $user->company_id == $company->id;
    });

    return response()->json([
        'users' => $filteredUsers
    ]);
}

public function unassignDepartment(Request $request, $userId)
{
    // Validate input
    $validator = Validator::make($request->all(), [
        'department_id' => 'required|integer|exists:departments,id',
    ]);

    // Return validation errors if validation fails
    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed.',
            'errors' => $validator->errors()
        ], 422);
    }

    // Find the user
    $user = User::find($userId);

    // Check if the user exists
    if (!$user) {
        return response()->json([
            'message' => 'User not found.'
        ], 404);
    }

    // Get the department to unassign
    $department = Department::find($request->department_id);

    // Check if the department exists
    if (!$department) {
        return response()->json([
            'message' => 'Department not found.'
        ], 404);
    }

    // Check if the user is assigned to the department
    if (!$user->departments->contains($department)) {
        return response()->json([
            'message' => 'User is not assigned to this department.'
        ], 400);
    }

    // Unassign the user from the department
    $user->departments()->detach($department);

    return response()->json([
        'message' => 'User successfully unassigned from the department.',
    ]);
}
}
