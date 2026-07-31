<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        $query = ServiceRequest::with(['member.department'])
            ->latest();

        if ($status && in_array($status, $validStatuses, true)) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->withQueryString();

        $stats = [
            'pending' => ServiceRequest::where('status', 'pending')->count(),
            'in_progress' => ServiceRequest::where('status', 'in_progress')->count(),
            'completed' => ServiceRequest::where('status', 'completed')->count(),
            'total' => ServiceRequest::count(),
        ];

        return view('service-requests.index', compact('requests', 'stats', 'status'));
    }

    public function edit(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['member.department']);

        return view('service-requests.edit', compact('serviceRequest'));
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $serviceRequest->update($validated);

        return back()->with('success', __('Request updated successfully.'));
    }
}
