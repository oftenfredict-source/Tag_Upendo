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
        if (! $smsService->isEnabled()) {
            return back()
                ->withInput()
                ->with('error', __('SMS is not configured. Add your API token in System Settings → SMS.'));
        }

        $validated = $request->validate([
            'target' => 'required|string|in:all,department,member',
            'department_id' => 'required_if:target,department|nullable|exists:departments,id',
            'member_ids' => 'required_if:target,member|array',
            'member_ids.*' => 'exists:members,id',
            'message' => 'required|string|max:1000',
        ]);

        $query = Member::query()->whereNotNull('phone_number')->where('phone_number', '!=', '');

        if ($validated['target'] === 'department' && ! empty($validated['department_id'])) {
            $query->where('department_id', $validated['department_id']);
        } elseif ($validated['target'] === 'member' && ! empty($validated['member_ids'])) {
            $query->whereIn('id', $validated['member_ids']);
        }

        $members = $query->get();

        if ($members->isEmpty()) {
            return back()->withInput()->with('error', __('No members with valid phone numbers were found.'));
        }

        $sent = 0;
        $failed = 0;
        $lastError = null;

        foreach ($members->chunk(50) as $chunk) {
            $phones = $chunk->pluck('phone_number')->all();
            $result = $smsService->sendMultiple($phones, $validated['message']);

            if ($result['success'] ?? false) {
                foreach ($chunk as $member) {
                    FollowUp::create([
                        'member_id' => $member->id,
                        'message' => $validated['message'],
                        'status' => 'sent',
                        'scheduled_at' => null,
                    ]);
                    $sent++;
                }
            } else {
                $lastError = $result['message'] ?? null;

                foreach ($chunk as $member) {
                    $single = $smsService->sendSingle($member->phone_number, $validated['message']);
                    if ($single['success'] ?? false) {
                        FollowUp::create([
                            'member_id' => $member->id,
                            'message' => $validated['message'],
                            'status' => 'sent',
                            'scheduled_at' => null,
                        ]);
                        $sent++;
                    } else {
                        $lastError = $single['message'] ?? $lastError;
                        $failed++;
                    }
                }
            }
        }

        if ($sent === 0) {
            $error = $lastError
                ? __('SMS could not be sent: :reason', ['reason' => $lastError])
                : __('SMS could not be sent. Check your token, sender ID, and phone numbers.');

            return back()->withInput()->with('error', $error);
        }

        $message = __('Bulk SMS sent successfully to :count members.', ['count' => $sent]);
        if ($failed > 0) {
            $message .= ' ' . __(':count failed.', ['count' => $failed]);
        }

        return redirect()->route('follow-ups.index')->with('success', $message);
    }
}
