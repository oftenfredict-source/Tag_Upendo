<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ServiceGuest;
use App\Services\ActivityLogger;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'all');

        $query = ServiceGuest::with(['event', 'recorder'])
            ->latest('service_date')
            ->latest('id');

        if ($type === ServiceGuest::TYPE_VISITED) {
            $query->where('guest_type', ServiceGuest::TYPE_VISITED);
        } elseif ($type === ServiceGuest::TYPE_PROMISED) {
            $query->where('guest_type', ServiceGuest::TYPE_PROMISED);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('coming_from', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service_date')) {
            $query->whereDate('service_date', $request->service_date);
        }

        $guests = $query->paginate(20)->withQueryString();

        $stats = [
            'visited' => ServiceGuest::where('guest_type', ServiceGuest::TYPE_VISITED)->count(),
            'promised_pending' => ServiceGuest::where('guest_type', ServiceGuest::TYPE_PROMISED)
                ->where('status', ServiceGuest::STATUS_PENDING)->count(),
            'promised_attended' => ServiceGuest::where('guest_type', ServiceGuest::TYPE_PROMISED)
                ->where('status', ServiceGuest::STATUS_ATTENDED)->count(),
            'upcoming_promised' => ServiceGuest::where('guest_type', ServiceGuest::TYPE_PROMISED)
                ->where('status', ServiceGuest::STATUS_PENDING)
                ->where(function ($q) {
                    $q->whereDate('service_date', '>=', now()->toDateString())
                        ->orWhereNull('service_date');
                })
                ->count(),
        ];

        return view('guests.index', [
            'guests' => $guests,
            'stats' => $stats,
            'type' => $type,
            'serviceEvents' => $this->serviceEventOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateGuest($request);

        $guest = ServiceGuest::create(array_merge($validated, [
            'recorded_by' => auth()->id(),
            'status' => $validated['guest_type'] === ServiceGuest::TYPE_VISITED
                ? ServiceGuest::STATUS_RECORDED
                : ServiceGuest::STATUS_PENDING,
        ]));

        ActivityLogger::log(
            'guest.create',
            __('Registered :type: :name', [
                'type' => ServiceGuest::typeLabel($guest->guest_type),
                'name' => $guest->name,
            ]),
            auth()->user()
        );

        return redirect()
            ->route('guests.index', ['type' => $guest->guest_type === ServiceGuest::TYPE_PROMISED ? 'promised' : 'visited'])
            ->with('success', __('Guest saved successfully.'));
    }

    public function update(Request $request, ServiceGuest $guest)
    {
        $validated = $this->validateGuest($request, $guest);

        if ($validated['guest_type'] === ServiceGuest::TYPE_VISITED) {
            $validated['status'] = ServiceGuest::STATUS_RECORDED;
        } elseif ($guest->status === ServiceGuest::STATUS_RECORDED) {
            $validated['status'] = ServiceGuest::STATUS_PENDING;
        }

        $guest->update($validated);

        return redirect()
            ->route('guests.index', ['type' => $request->input('return_type', 'all')])
            ->with('success', __('Guest updated successfully.'));
    }

    public function destroy(ServiceGuest $guest)
    {
        $name = $guest->name;
        $guest->delete();

        ActivityLogger::log('guest.delete', __('Deleted guest: :name', ['name' => $name]), auth()->user());

        return redirect()
            ->route('guests.index')
            ->with('success', __('Guest deleted successfully.'));
    }

    public function sendReminder(Request $request, ServiceGuest $guest, SmsService $smsService)
    {
        abort_unless($guest->isPromised(), 404);

        if (! $guest->canSendReminder()) {
            return back()->with('error', __('Cannot send reminder — guest has no phone number.'));
        }

        $message = trim($request->input('message', '')) ?: $guest->defaultReminderMessage();

        $result = $smsService->sendSingle($guest->phone, $message);

        if (! ($result['success'] ?? false)) {
            return back()->with('error', $result['message'] ?? __('Failed to send SMS.'));
        }

        $guest->update(['reminder_sent_at' => now()]);

        ActivityLogger::log(
            'guest.reminder',
            __('Sent service reminder to guest :name', ['name' => $guest->name]),
            auth()->user()
        );

        return back()->with('success', __('Reminder sent to :name.', ['name' => $guest->name]));
    }

    public function sendThankYou(Request $request, ServiceGuest $guest, SmsService $smsService)
    {
        if (! $guest->canSendThankYou()) {
            return back()->with('error', __('Cannot send thank you — guest has no phone number or is not marked as attended.'));
        }

        $message = trim($request->input('message', '')) ?: $guest->defaultThankYouMessage();

        $result = $smsService->sendSingle($guest->phone, $message);

        if (! ($result['success'] ?? false)) {
            return back()->with('error', $result['message'] ?? __('Failed to send SMS.'));
        }

        $guest->update(['thank_you_sent_at' => now()]);

        ActivityLogger::log(
            'guest.thank_you',
            __('Sent thank you SMS to guest :name', ['name' => $guest->name]),
            auth()->user()
        );

        return back()->with('success', __('Thank you message sent to :name.', ['name' => $guest->name]));
    }

    public function markAttended(ServiceGuest $guest)
    {
        abort_unless($guest->isPromised(), 404);

        $guest->update(['status' => ServiceGuest::STATUS_ATTENDED]);

        return back()->with('success', __(':name marked as attended.', ['name' => $guest->name]));
    }

    public function markMissed(ServiceGuest $guest)
    {
        abort_unless($guest->isPromised(), 404);

        $guest->update(['status' => ServiceGuest::STATUS_MISSED]);

        return back()->with('success', __(':name marked as did not attend.', ['name' => $guest->name]));
    }

    public function convertToVisited(ServiceGuest $guest)
    {
        abort_unless($guest->isPromised(), 404);

        $visited = ServiceGuest::create([
            'guest_type' => ServiceGuest::TYPE_VISITED,
            'name' => $guest->name,
            'phone' => $guest->phone,
            'email' => $guest->email,
            'coming_from' => $guest->coming_from,
            'event_id' => $guest->event_id,
            'service_date' => $guest->service_date ?? now()->toDateString(),
            'notes' => $guest->notes,
            'status' => ServiceGuest::STATUS_RECORDED,
            'recorded_by' => auth()->id(),
            'promised_guest_id' => $guest->id,
        ]);

        $guest->update(['status' => ServiceGuest::STATUS_ATTENDED]);

        return redirect()
            ->route('guests.index', ['type' => 'visited'])
            ->with('success', __(':name moved to visited guests.', ['name' => $visited->name]));
    }

    /** @return array<string, mixed> */
    protected function validateGuest(Request $request, ?ServiceGuest $guest = null): array
    {
        $validated = $request->validate([
            'guest_type' => ['required', Rule::in([ServiceGuest::TYPE_VISITED, ServiceGuest::TYPE_PROMISED])],
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'coming_from' => 'nullable|string|max:255',
            'event_id' => 'nullable|exists:events,id',
            'service_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (empty($validated['service_date']) && ! empty($validated['event_id'])) {
            $event = Event::find($validated['event_id']);
            if ($event?->start_at) {
                $validated['service_date'] = Carbon::parse($event->start_at)->toDateString();
            }
        }

        return $validated;
    }

    /** @return \Illuminate\Support\Collection<int, Event> */
    protected function serviceEventOptions()
    {
        return Event::where('event_type', 'service')
            ->whereNotNull('start_at')
            ->where('start_at', '>=', now()->subMonths(6))
            ->where('start_at', '<=', now()->addMonths(6))
            ->orderByDesc('start_at')
            ->get()
            ->unique(fn (Event $event) => $event->service_group_id ?? $event->id)
            ->values();
    }
}
