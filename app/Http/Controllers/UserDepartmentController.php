<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Traits\HasRoles;

class UserDepartmentController extends Controller
{
    use HasRoles;
    public function assignManagerToDepartment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $department = Department::find($request->department_id);

        if (!$department) {
            return response()->json([
                'message' => 'Department not found.'
            ], 404);
        }

        $manager = User::find($request->user_id);

        if (!$manager) {
            return response()->json([
                'message' => 'Manager not found.'
            ], 404);
        }

        if ($manager->company_id !== $department->company_id) {
            return response()->json([
                'message' => 'The manager and the department must be in the same company.'
            ], 400);
        }

        $department->user_id = $request->user_id;
        $department->save();

        return response()->json([
            'message' => 'Manager assigned successfully.',
            'department' => $department->load('manager')
        ]);
    }
    public function assignDepartments(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'department_ids' => 'required|array',
            'department_ids.*' => 'integer|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        $loggedInUser = Auth::user();

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
        $departments = Department::whereIn('id', $request->department_ids)->get();

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

        $user->departments()->sync($request->department_ids);

        return response()->json([
            'message' => 'Departments assigned successfully.',
            'user' => $user->load('departments')
        ]);
    }

    public function getUsersInDepartment(Request $request)
    {
        $user = Auth::user();

        $userDepartments = $user->departments;

        if ($userDepartments->isEmpty()) {
            return response()->json([
                'message' => 'User is not assigned to any department.'
            ], 400);
        }

        $department = $userDepartments->first();

        $company = $department->company;

        $usersInDepartment = User::whereHas('departments', function($query) use ($department) {
            $query->where('departments.id', $department->id);
        })->get();

        $filteredUsers = $usersInDepartment->filter(function($user) use ($company) {
            return $user->company_id == $company->id;
        });

        return response()->json([
            'users' => $filteredUsers
        ]);
    }

    public function unassignDepartment(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|integer|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        $department = Department::find($request->department_id);

        if (!$department) {
            return response()->json([
                'message' => 'Department not found.'
            ], 404);
        }

        if (!$user->departments->contains($department)) {
            return response()->json([
                'message' => 'User is not assigned to this department.'
            ], 400);
        }

        $user->departments()->detach($department);

        return response()->json([
            'message' => 'User successfully unassigned from the department.',
        ]);
    }
}
