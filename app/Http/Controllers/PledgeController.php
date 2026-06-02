<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Pledge;
use App\Models\PledgePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PledgeController extends Controller
{
    public function index(Request $request)
    {
        $pledges = Pledge::with(['member', 'payments'])
            ->when($request->filled('member_id'), fn ($q) => $q->where('member_id', $request->member_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('due_date')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $members = Member::whereNull('parent_id')->orderBy('name')->get(['id', 'name', 'phone_number']);

        $stats = [
            'total_pledged' => Pledge::sum('amount'),
            'total_paid' => Pledge::sum('amount_paid'),
            'total_remaining' => Pledge::selectRaw('SUM(amount - amount_paid) as remaining')->value('remaining') ?? 0,
        ];

        return view('pledges.index', compact('pledges', 'members', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'pledge_for' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'amount_paid' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $member = Member::findOrFail($validated['member_id']);
        $initialPaid = (float) ($validated['amount_paid'] ?? 0);

        if ($initialPaid > (float) $validated['amount']) {
            return back()->withInput()->with('error', 'Malipo ya awali hayawezi kuzidi kiasi cha ahadi.');
        }

        $pledge = Pledge::create([
            'member_id' => $member->id,
            'member_name' => $member->name,
            'pledge_for' => $validated['pledge_for'],
            'amount' => $validated['amount'],
            'amount_paid' => 0,
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        if ($initialPaid > 0) {
            $this->recordPayment($pledge, $initialPaid, $validated['due_date'], 'Malipo ya awali');
        } else {
            $pledge->syncStatus();
            $pledge->save();
        }

        return back()->with('success', 'Pledge imehifadhiwa.');
    }

    public function pay(Request $request, Pledge $pledge)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $remaining = $pledge->remainingAmount();

        if ((float) $validated['amount'] > $remaining) {
            return back()->with('error', 'Kiasi kinazidi kilichobaki (TSH ' . number_format($remaining, 0) . ').');
        }

        $this->recordPayment(
            $pledge,
            (float) $validated['amount'],
            $validated['payment_date'],
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Malipo yameandikwa. Kilichobaki: TSH ' . number_format($pledge->fresh()->remainingAmount(), 0));
    }

    protected function recordPayment(Pledge $pledge, float $amount, string $date, ?string $notes): void
    {
        DB::transaction(function () use ($pledge, $amount, $date, $notes) {
            PledgePayment::create([
                'pledge_id' => $pledge->id,
                'amount' => $amount,
                'payment_date' => $date,
                'notes' => $notes,
            ]);

            $pledge->amount_paid = (float) $pledge->amount_paid + $amount;
            $pledge->syncStatus();
            $pledge->save();
        });
    }
}
