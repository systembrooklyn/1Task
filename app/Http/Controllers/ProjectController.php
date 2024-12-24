<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectRevision;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;

class ProjectController extends Controller
{
    use HasRoles;
    // public function index()
    // {
    //     $haveAccess = false;
    //     $user = Auth::user();
    //     $companyId = $user->company_id;
    //     $departmentId = $user->dept_id;

    //     $isOwner = $user->companies()->wherePivot('company_id', $companyId)->exists();
    //     $permissions = $user->getAllPermissions();
    //     foreach($permissions as $permission){
    //         if($permission->name == "view-project") {$haveAccess = true;
    //         break;}
    //     };
    //     if($haveAccess || $isOwner){
    //         $query = Project::where('company_id', $companyId);

    //         if (!$isOwner) {
    //             $query->whereHas('departments', function ($query) use ($departmentId) {
    //                 $query->where('dept_id', $departmentId);
    //             });
    //         }

    //         $projects = $query->with([
    //             'company:id,name',
    //             'departments:id,name',
    //             'createdBy:id,name',
    //             'editedBy:id,name',
    //             'leader:id,name',
    //         ])
    //         ->get();

    //         // Modify the response
    //         $projects->each(function ($project) {
    //             $project->setAppends([
    //                 'company_name',
    //                 'department_name',
    //                 'created_by_name',
    //                 'edited_by_name',
    //                 'leader_name',
    //             ]);

    //             // If no department is assigned, set 'department_name' to 'empty'
    //             if ($project->departments->isEmpty()) {
    //                 $project->department_name = 'No Department Assigned';
    //             }

    //             $project->makeHidden([
    //                 'company_id',
    //                 'dept_id',
    //                 'created_by',
    //                 'edited_by',
    //                 'editedBy',
    //                 'createdBy',
    //                 'leader_id',
    //                 'company',
    //                 'departments',  // Hide the full relationship for cleaner response
    //                 'leader',
    //             ]);
    //         });

    //         return response()->json($projects);
    //     }else return response()->json(['message' => 'You do not have permission to view projects'], 401);
    // }


    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $departmentId = $user->dept_id;
        $this->authorize('viewAny', Project::class);
        $query = Project::where('company_id', $companyId);
        if (!$user->companies()->wherePivot('company_id', $companyId)->exists()) {
            $query->whereHas('departments', function ($query) use ($departmentId) {
                $query->where('dept_id', $departmentId);
            });
        }
        $projects = $query->with([
            'company:id,name',
            'departments:id,name',
            'createdBy:id,name',
            'editedBy:id,name',
            'leader:id,name',
        ])->get();
        $projects->each(function ($project) {
            $project->setAppends([
                'company_name',
                'department_name',
                'created_by_name',
                'edited_by_name',
                'leader_name',
            ]);
            if ($project->departments->isEmpty()) {
                $project->department_name = 'No Department Assigned';
            }
            $project->makeHidden([
                'company_id',
                'dept_id',
                'created_by',
                'edited_by',
                'editedBy',
                'createdBy',
                'leader_id',
                'company',
                'departments',
                'leader',
            ]);
        });
        return response()->json($projects);
    }



    // public function show($id)
    // {
    //     $project = Project::with([
    //         'company:id,name',
    //         'departments:id,name',
    //         'createdBy:id,name',
    //         'editedBy:id,name',
    //         'leader:id,name',
    //     ])->find($id);

    //     if (!$project) {
    //         return response()->json(['message' => 'Project not found'], 404);
    //     }

    //     $project->setAppends([
    //         'company_name',
    //         'department_name',
    //         'created_by_name',
    //         'edited_by_name',
    //         'leader_name',
    //     ]);

    //     // If no department is assigned, set 'department_name' to 'empty'
    //     if ($project->departments->isEmpty()) {
    //         $project->department_name = 'No Department Assigned';
    //     }

    //     $project->makeHidden([
    //         'company_id',
    //         'dept_id',
    //         'created_by',
    //         'edited_by',
    //         'editedBy',
    //         'createdBy',
    //         'leader_id',
    //         'company',
    //         'departments',
    //         'leader',
    //     ]);

    //     return response()->json($project);
    // }

    public function show($id)
    {
        $project = Project::with([
            'company:id,name',
            'departments:id,name',
            'createdBy:id,name',
            'editedBy:id,name',
            'leader:id,name',
        ])->find($id);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $this->authorize('view', $project);
        $project->setAppends([
            'company_name',
            'department_name',
            'created_by_name',
            'edited_by_name',
            'leader_name',
        ]);
        if ($project->departments->isEmpty()) {
            $project->department_name = 'No Department Assigned';
        }
        $project->makeHidden([
            'company_id',
            'dept_id',
            'created_by',
            'edited_by',
            'editedBy',
            'createdBy',
            'leader_id',
            'company',
            'departments',
            'leader',
        ]);
        return response()->json($project);
    }

    // public function store(Request $request)
    // {
    //     $haveAccess = false;
    //     $request->validate([
    //         'name' => 'required|string',
    //         'desc' => 'nullable|string',
    //         'status' => 'required|boolean',
    //         'deadline' => 'required|date',
    //         'department_id' => 'nullable|exists:departments,id',
    //     ]);

    //     $user = Auth::user();
    //     $companyId = $user->company_id;

    //     $isOwner = $user->companies()->wherePivot('company_id', $companyId)->exists();
    //     $permissions = $user->getAllPermissions();
    //     foreach($permissions as $permission){
    //         if($permission->name == "create-project") {$haveAccess = true;
    //         break;}
    //     };
    //     if($haveAccess || $isOwner){
    //         $project = new Project();
    //         $project->name = $request->name;
    //         $project->desc = $request->desc;
    //         $project->status = $request->status;
    //         $project->deadline = $request->deadline;
    //         $project->company_id = $companyId;
    //         $project->created_by = $user->id;

    //         $project->save();
    //         if ($request->has('department_id')) {
    //             $project->departments()->attach($request->department_id);
    //         }

    //         return response()->json([
    //             'message' => 'Project created successfully',
    //             'project' => $project,
    //         ], 201);
    //     }else return response()->json(['message' => 'You do not have permission to create project'], 401);
    // }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'desc' => 'nullable|string',
            'status' => 'required|boolean',
            'deadline' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'start_date' => 'required|date',
        ]);
        $user = Auth::user();
        $companyId = $user->company_id;
        $this->authorize('create', Project::class);
        $project = new Project();
        $project->name = $request->name;
        $project->desc = $request->desc;
        $project->status = $request->status;
        $project->deadline = $request->deadline;
        $project->company_id = $companyId;
        $project->created_by = $user->id;
        $project->start_date = $request->start_date;
        $project->save();
        if ($request->has('department_id')) {
            $project->departments()->attach($request->department_id);
        }
        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project,
        ], 201);
    }

    // public function update(Request $request, $id)
    // {
    //     $haveAccess = false;
    //     $request->validate([
    //         'name' => 'required|string',
    //         'desc' => 'nullable|string',
    //         'status' => 'required|boolean',
    //         'deadline' => 'required|date',
    //         'department_id' => 'nullable|exists:departments,id',  // Optional department_id
    //     ]);
    //     $user = Auth::user();
    //     $companyId = $user->company_id;

    //     $isOwner = $user->companies()->wherePivot('company_id', $companyId)->exists();
    //     $permissions = $user->getAllPermissions();
    //     foreach($permissions as $permission){
    //         if($permission->name == "edit-project") {$haveAccess = true;
    //         break;}
    //     };
    //     if($haveAccess || $isOwner){
    //         $project = Project::find($id);

    //         if (!$project) {
    //             return response()->json(['message' => 'Project not found'], 404);
    //         }

    //         $project->name = $request->name;
    //         $project->desc = $request->desc;
    //         $project->status = $request->status;
    //         $project->deadline = $request->deadline;

    //         $project->save();

    //         // Sync the department if provided
    //         if ($request->has('department_id')) {
    //             $project->departments()->sync([$request->department_id]);  // Sync to ensure only one department is assigned
    //         }

    //         return response()->json([
    //             'message' => 'Project updated successfully',
    //             'project' => $project,
    //         ]);
    //     }else return response()->json(['message' => 'You do not have permission to edit project'], 401);
    // }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);
        $this->authorize('update', $project);
        $original = $project->getOriginal();
        $request->validate([
            'name' => 'required|string',
            'desc' => 'nullable|string',
            'status' => 'required|boolean',
            'deadline' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'start_date' => 'required|date',
        ]);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $project->name = $request->name;
        $project->desc = $request->desc;
        $project->status = $request->status;
        $project->deadline = $request->deadline;
        $project->start_date = $request->start_date;
        if ($request->has('department_id')) {
            $project->departments()->sync([$request->department_id]);
        }
        
        $project->save();
        $changes = $project->getChanges();
        foreach ($changes as $field => $newValue) {
            if (in_array($field, ['name','status','desc','deadline','department_id','start_date'])) {
                
                    ProjectRevision::create([
                        'project_id' => $project->id,
                        'user_id' => Auth::id(),
                        'field' => $field,
                        'old_value' => $original[$field] ?? null,
                        'new_value' => $newValue,
                        'created_at' => now()
                    ]);
                
            }
        }
        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project,
        ]);
    }

    // public function destroy($id)
    // {
    //     $haveAccess = false;
    //     $user = Auth::user();
    //     $companyId = $user->company_id;
    //     $isOwner = $user->companies()->wherePivot('company_id', $companyId)->exists();
    //     $permissions = $user->getAllPermissions();
    //     foreach($permissions as $permission){
    //         if($permission->name == "delete-project") {$haveAccess = true;
    //         break;}
    //     };
    //     if($haveAccess || $isOwner){
        
    //         $project = Project::find($id);

    //         if (!$project) {
    //             return response()->json(['message' => 'Project not found'], 404);
    //         }

    //         if ($project->company_id != $companyId && !$isOwner) {
    //             return response()->json(['message' => 'You do not have access to delete this project'], 403);
    //         }

    //         $project->delete();
    //         return response()->json(['message' => 'Project deleted successfully']);
    //     }else return response()->json(['message' => 'You do not have permission to delete project'], 401);
    // }

    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(['message' => 'Project deleted successfully']);
    }


    public function updatestatus(Request $request, $id)
    {
        $project = Project::find($id);
    if (!$project) {
        return response()->json(['message' => 'Project not found'], 404);
    }
    $this->authorize('update', $project);
    $project->status = !$project->status;
    $project->save();
    if ($request->has('department_id')) {
        $project->departments()->sync([$request->department_id]);
    }
    return response()->json([
        'message' => 'Project status toggled'
    ]);
    }
    public function getRevisions($id)
    {
        $project = Project::find($id);
        if (! $project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $revisions = $project->revisions()->with('user')->get();
        $formatted = $revisions->map(function($revision) {
            return [
                'id'         => $revision->id,
                'field'      => $revision->field,
                'old_value'  => $revision->old_value,
                'new_value'  => $revision->new_value,
                'changed_at' => $revision->created_at,
                'changed_by' => $revision->user ? $revision->user->name : null,
            ];
        });
        return response()->json([
            'project_id' => $project->id,
            'revisions'  => $formatted,
        ], 200);
    }
}
