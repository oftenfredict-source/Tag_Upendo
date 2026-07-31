<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->isFullStaff()) {
            $announcements = Announcement::with('author')
                ->latest('published_at')
                ->latest('id')
                ->paginate(15);

            return view('announcements.index', compact('announcements'));
        }

        $announcements = Announcement::with('author')
            ->visibleTo($user)
            ->latest('published_at')
            ->latest('id')
            ->paginate(15);

        return view('announcements.feed', compact('announcements'));
    }

    public function store(Request $request)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'audience' => 'required|in:all,members,staff',
            'priority' => 'required|in:normal,important',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_published' => 'nullable|boolean',
        ]);

        Announcement::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
            'audience' => $validated['audience'],
            'priority' => $validated['priority'],
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_published' => $request->boolean('is_published', true),
            'published_at' => $request->boolean('is_published', true) ? now() : null,
        ]);

        return redirect()
            ->route('announcements.index')
            ->with('success', __('Announcement published successfully.'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorizeStaff();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'audience' => 'required|in:all,members,staff',
            'priority' => 'required|in:normal,important',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_published' => 'nullable|boolean',
        ]);

        $wasPublished = $announcement->is_published;
        $isPublished = $request->boolean('is_published');

        $announcement->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'audience' => $validated['audience'],
            'priority' => $validated['priority'],
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_published' => $isPublished,
            'published_at' => $isPublished && ! $wasPublished ? now() : $announcement->published_at,
        ]);

        return redirect()
            ->route('announcements.index')
            ->with('success', __('Announcement updated successfully.'));
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorizeStaff();

        $announcement->delete();

        return redirect()
            ->route('announcements.index')
            ->with('success', __('Announcement deleted successfully.'));
    }

    protected function authorizeStaff(): void
    {
        if (! auth()->user()?->isFullStaff()) {
            abort(403, __('You do not have permission to access this page.'));
        }
    }
}
