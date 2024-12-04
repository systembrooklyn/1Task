<?php

namespace App\Http\Controllers;

use App\Models\departments;
use Illuminate\Http\Request;

class DepartmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return departments::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'name'=>'required|max:255'
        ]);
        $department = departments::create($fields);
        return ['Departments' => $department];
    }

    /**
     * Display the specified resource.
     */
    public function show(departments $department)
    {
        return $department;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, departments $department)
    {
        $fields = $request->validate([
            'name'=>'required|max:255'
        ]);
        $department->update($fields);
        return $department;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(departments $department)
    {
        $dept_name = $department->name;
        $department->delete();
        return ["message" => "$dept_name Deleted Succecfully"];
    }
}
