<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Member;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('members')->with('members')->latest()->get();
        $allMembers = Member::orderBy('name')->get();
        return view('departments.index', compact('departments', 'allMembers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create($validated);
        return back()->with('success', 'Department created successfully!');
    }

    public function assignMember(Request $request, Department $department)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
        ]);

        $member = Member::find($request->member_id);
        $member->update(['department_id' => $department->id]);

        return back()->with('success', "{$member->name} has been assigned to {$department->name} successfully!");
    }
}
