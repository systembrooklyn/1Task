<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;

class ProjectController extends Controller
{
    use HasRoles;
    public function index()
    {
        $user = Auth::user();

        $companyId = $user->company_id;
        $departmentId = $user->dept_id;

        $isOwner = $user->companies()->wherePivot('company_id', $companyId)->exists();

        $query = Project::where('company_id', $companyId);

        if (!$isOwner) {
            $query->where('dept_id', $departmentId);
        }
        $projects = $query->with([
            'company:id,name',
            'department:id,name',
            'createdBy:id,name',
            'editedBy:id,name',
            'leader:id,name',
        ])
        ->get();
        $projects->each(function ($project) {
            $project->setAppends([
                'company_name',
                'department_name',
                'created_by_name',
                'edited_by_name',
                'leader_name',
            ]);
            $project->makeHidden([
                'company_id',
                'dept_id',
                'created_by',
                'edited_by',
                'editedBy',
                'createdBy',
                'leader_id',
                'company',
                'department',
                'leader',
            ]);
        });
        return response()->json($projects);
    }

    public function show($id)
    {
        $project = Project::with([
            'company:id,name',
            'department:id,name',
            'createdBy:id,name',
            'editedBy:id,name',
            'leader:id,name',
        ])->findOrFail($id);

        $project->makeHidden([
            'company_id',
            'dept_id',
            'created_by',
            'edited_by',
            'editedBy',
            'createdBy',
            'leader_id',
            'company',
            'department',
            'leader'
        ]);

        return response()->json($project);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $company_id = $user->company_id;
        $created_by = $user->id;
    
        $haveAccess = false;
        $permissions = $user->getAllPermissions();
        
        foreach ($permissions as $permission) {
            if ($permission->name == "create-project") {
                $haveAccess = true;
                break;
            }
        }
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
    
        if ($haveAccess == "create-project" || $isOwner) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'desc' => 'required|string',
                'status' => 'required|boolean',
                'deadline' => 'required|date',
                'leader_id' => 'required|exists:users,id',
                'dept_id' => 'required|exists:departments,id',
            ]);

            $existingProject = Project::where('name', $validated['name'])
                                                ->where('company_id', $company_id)
                                                ->first();
        
                if ($existingProject) {
                    return response()->json(['message' => 'Project already exists for this company.'], 400);
                }
        
                if (!$company_id) {
                    return response()->json(['message' => 'You must be associated with a company to create a Project.'], 403);
                }

            $project = Project::create([
                'name' => $validated['name'],
                'desc' => $validated['desc'],
                'status' => $validated['status'],
                'deadline' => $validated['deadline'],
                'company_id' => $company_id,
                'dept_id' => $validated['dept_id'],
                'created_by' => $created_by,
                'leader_id' => $validated['leader_id'],
            ]);
            $project->load([
                'company:id,name',
                'department:id,name',
                'createdBy:id,name',
                'leader:id,name',
            ]);
        
            $project->company_name = $project->company->name;
            $project->department_name = $project->department->name;
            $project->created_by_name = $project->createdBy->name;
            $project->leader_name = $project->leader->name;
        
            $project->makeHidden(['company', 'department', 'createdBy', 'leader', 'company_id', 'dept_id', 'created_by', 'leader_id']);
        

            return response()->json([
                'message' => 'Project created successfully',
                'project' => $project
            ], 201);
        }else {
            return response()->json(['message' => 'You do not have permission to create project'], 401);
        }
}

    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $company_id = $user->company_id;
    
        $haveAccess = false;
        $permissions = $user->getAllPermissions();
        
        foreach ($permissions as $permission) {
            if ($permission->name == "edit-project") {
                $haveAccess = true;
                break;
            }
        }
        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
    
        if ($haveAccess == "edit-project" || $isOwner) {
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'desc' => 'nullable|string',
                'status' => 'nullable|boolean',
                'deadline' => 'nullable|date',
                'leader_id' => 'nullable|exists:users,id',
            ]);

            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'message' => 'Project not found',
                ], 404);
            }

            $project->update([
                'name' => $validated['name'] ?? $project->name,
                'desc' => $validated['desc'] ?? $project->desc,
                'status' => $validated['status'] ?? $project->status,
                'deadline' => $validated['deadline'] ?? $project->deadline,
                'leader_id' => $validated['leader_id'] ?? $project->leader_id,
                'edited_by' => $user->id,
            ]);
            $project->load([
                'company:id,name',
                'department:id,name',
                'createdBy:id,name',
                'editedBy:id,name',
                'leader:id,name',
            ]);
            $response = [
                'message' => 'Project updated successfully',
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'desc' => $project->desc,
                    'status' => $project->status,
                    'deadline' => $project->deadline,
                    'created_at' => $project->created_at,
                    'updated_at' => $project->updated_at,
                    'company_name' => $project->company->name,
                    'department_name' => $project->department->name,
                    'created_by_name' => $project->createdBy->name,
                    'leader_name' => $project->leader ? $project->leader->name : null,
                    'edited_by_name' => $project->editedBy ? $project->editedBy->name : null,
                    'edited_by' => $project->editedBy->name,
                ],
            ];
        
            return response()->json($response);
        }else {
            return response()->json(['message' => 'You do not have permission to edit this project'], 401);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $company_id = $user->company_id;

        $haveAccess = false;
        $permissions = $user->getAllPermissions();

        foreach ($permissions as $permission) {
            if ($permission->name == "delete-project") {
                $haveAccess = true;
                break;
            }
        }
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        if ($project->company_id != $company_id) {
            return response()->json(['message' => 'You do not have access to delete this project'], 403);
        }

        $isOwner = $user->companies()->wherePivot('company_id', $company_id)->exists();
        $isInSameDepartment = $user->department_id == $project->dept_id;

        if ($haveAccess || $isOwner || $isInSameDepartment) {
            $project->delete();
            return response()->json(['message' => 'Project deleted successfully']);
        } else {
            return response()->json(['message' => 'You do not have permission to delete this project'], 403);
        }
    }
}
