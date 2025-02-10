<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectRevision;
use Illuminate\Auth\Access\AuthorizationException;
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
        $userDepartmentIds = $user->departments->pluck('id');
        // $this->authorize('viewAny', Project::class);
        // $this->authorize('viewAllProjects', Project::class);
        // $query = Project::where('company_id', $companyId);
        // if (!$user->companies()->wherePivot('company_id', $companyId)->exists()) {
        //     $query->whereHas('departments', function ($query) use ($userDepartmentIds) {
        //         $query->whereIn('department_id', $userDepartmentIds);
        //     });
        // }
        try {
            // 1) Try to authorize viewAllProjects
            $this->authorize('viewAllProjects', Project::class);
    
            // If it passes, get *all* projects in this company
            $query = Project::where('company_id', $companyId);
    
        } catch (AuthorizationException $e) {
    
            // 2) If we *don't* have viewAllProjects permission,
            //    check if user has the "viewAny" permission
            $this->authorize('viewAny', Project::class);
    
            // If user is authorized only for viewAny,
            // show only projects in that user's departments
            $query = Project::where('company_id', $companyId)
                ->whereHas('departments', function ($q) use ($userDepartmentIds) {
                    $q->whereIn('department_id', $userDepartmentIds);
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
                'company',
                'departments',
                'leader',
            ]);
        });
        return response()->json($projects);
    }

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
            'company',
            'departments',
            'leader',
        ]);
        return response()->json($project);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'desc' => 'nullable|string',
            'status' => 'required|boolean',
            'leader_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'start_date' => 'nullable|date',
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
        $project->leader_id = $request->leader_id;
        $project->save();
        if ($request->has('department_id')) {
            $project->departments()->attach($request->department_id);
        }
        return response()->json([
            'message' => 'Project created successfully'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);
        $this->authorize('update', $project);
        $original = $project->getOriginal();
        $request->validate([
            'name' => 'required|string',
            'desc' => 'nullable|string',
            'status' => 'required|boolean',
            'leader_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'start_date' => 'nullable|date',
        ]);
        if (!$project) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $project->name = $request->name;
        $project->desc = $request->desc;
        $project->status = $request->status;
        $project->deadline = $request->deadline;
        $project->start_date = $request->start_date;
        $project->leader_id = $request->leader_id;
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
            'message' => 'Project updated successfully'
        ]);
    }
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
        Auth::user();
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
