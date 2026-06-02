<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\FollowUp;
use App\Services\SmsService;
use Illuminate\Http\Request;

class BulkSmsController extends Controller
{
    public function create()
    {
        $departments = \App\Models\Department::orderBy('name')->get();
        $members = \App\Models\Member::orderBy('name')->get();
        return view('bulk_sms.create', compact('departments', 'members'));
    }

    public function store(Request $request, SmsService $smsService)
    {
        $validated = $request->validate([
            'target' => 'required|string|in:all,department,member',
            'department_id' => 'required_if:target,department|nullable|exists:departments,id',
            'member_ids' => 'required_if:target,member|array',
            'member_ids.*' => 'exists:members,id',
            'message' => 'required|string',
        ]);

        $query = Member::query();

        if ($validated['target'] === 'department' && !empty($validated['department_id'])) {
            $query->where('department_id', $validated['department_id']);
        } elseif ($validated['target'] === 'member' && !empty($validated['member_ids'])) {
            $query->whereIn('id', $validated['member_ids']);
        }

        $members = $query->get();

        if ($members->isEmpty()) {
            return back()->with('error', 'No members found for the selected criteria.');
        }

        $count = 0;
        foreach ($members as $member) {
            $smsService->sendSms($member->phone_number, $validated['message']);

            FollowUp::create([
                'member_id' => $member->id,
                'message' => $validated['message'],
                'status' => 'sent',
                'scheduled_at' => null,
            ]);
            $count++;
        }

        return redirect()->route('follow-ups.index')->with('success', "Bulk SMS sent successfully to {$count} members!");
    }
}
