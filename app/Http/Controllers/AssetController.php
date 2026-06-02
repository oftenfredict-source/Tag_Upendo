<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::latest()->paginate(10);
        $totalQuantity = Asset::sum('quantity');
        return view('assets.index', compact('assets', 'totalQuantity'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|string|max:255',
            'purchase_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        Asset::create($validated);

        return back()->with('success', 'Asset recorded successfully.');
    }
}
