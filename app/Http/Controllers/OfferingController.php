<?php

namespace App\Http\Controllers;

use App\Models\Offering;
use Illuminate\Http\Request;

class OfferingController extends Controller
{
    public function index()
    {
        $offerings = Offering::latest('collection_date')->paginate(10);

        // Calculate totals by category (Zaka is tracked separately under Tithes)
        $totals = [
            'Sadaka' => Offering::where('category', 'Sadaka')->sum('amount'),
            'Ujenzi' => Offering::where('category', 'Ujenzi')->sum('amount'),
            'Shukran' => Offering::where('category', 'Shukran')->sum('amount'),
            'Other' => Offering::whereNotIn('category', ['Sadaka', 'Ujenzi', 'Shukran'])->sum('amount'),
        ];

        return view('offerings.index', compact('offerings', 'totals'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'service_type' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'collection_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Offering::create($validated);

        return back()->with('success', 'Offering recorded successfully.');
    }
}
