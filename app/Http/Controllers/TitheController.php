<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Tithe;
use Illuminate\Http\Request;

class TitheController extends Controller
{
    public function index(Request $request)
    {
        $tithes = Tithe::with('member')
            ->when($request->filled('member_id'), fn ($q) => $q->where('member_id', $request->member_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('payment_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('payment_date', '<=', $request->to))
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $members = Member::whereNull('parent_id')->active()->orderBy('name')->get(['id', 'name', 'phone_number']);

        $stats = [
            'this_month' => Tithe::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'total' => Tithe::sum('amount'),
            'count' => Tithe::count(),
        ];

        return view('tithes.index', compact('tithes', 'members', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $member = Member::findOrFail($validated['member_id']);

        Tithe::create([
            ...$validated,
            'member_name' => $member->name,
        ]);

        return back()->with('success', __('Tithe recorded successfully.'));
    }

    public function destroy(Tithe $tithe)
    {
        $tithe->delete();

        return back()->with('success', __('Tithe deleted successfully.'));
    }
}
