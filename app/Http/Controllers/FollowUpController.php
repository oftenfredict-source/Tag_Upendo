<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Member;
use App\Services\SmsService;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index()
    {
        $followUps = FollowUp::with('member')->latest()->get();
        return view('follow_ups.index', compact('followUps'));
    }

    public function create(Member $member)
    {
        return view('follow_ups.create', compact('member'));
    }

    public function store(Request $request, SmsService $smsService)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'message' => 'required|string',
            'scheduled_at' => 'nullable|date',
        ]);

        $followUp = FollowUp::create($validated);

        $member = Member::find($validated['member_id']);
        if (!$validated['scheduled_at'] || now()->greaterThanOrEqualTo($validated['scheduled_at'])) {
            $smsService->sendSms($member->phone_number, $validated['message']);
            $followUp->update(['status' => 'sent']);
            return redirect()->route('follow-ups.index')->with('success', 'Follow up saved and SMS sent successfully!');
        }

        return redirect()->route('follow-ups.index')->with('success', 'Follow up scheduled successfully!');
    }
}
