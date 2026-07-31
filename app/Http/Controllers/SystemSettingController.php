<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\Setting;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SystemSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! auth()->user()?->canManageSettings()) {
                abort(403, __('You do not have permission to access this page.'));
            }

            return $next($request);
        });
    }

    public function index(Request $request, SmsService $smsService)
    {
        $tab = $request->query('tab', 'church');
        $allowedTabs = ['church', 'sms', 'general', 'users', 'logs', 'sessions'];
        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'church';
        }

        $smsBalance = null;
        if (Setting::bool('sms_enabled') && Setting::get('sms_token')) {
            $smsBalance = $smsService->getBalance();
        }

        $users = User::with('member')->latest()->paginate(10, ['*'], 'users_page')->withQueryString();

        $pastorMembers = Member::pastors()->orderBy('name')->get(['id', 'name', 'phone_number']);
        $secretaryMembers = Member::secretaries()->orderBy('name')->get(['id', 'name', 'phone_number']);

        $logs = [];
        $logsResponse = $smsService->getLogs();
        if ($logsResponse['success'] ?? false) {
            $logs = $logsResponse['logs'] ?? [];
        }

        $systemLogs = ActivityLog::with('user')
            ->latest()
            ->paginate(25, ['*'], 'logs_page')
            ->withQueryString();

        $currentSessionId = $request->session()->getId();
        $sessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderByDesc('sessions.last_activity')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $session->last_activity_at = \Carbon\Carbon::createFromTimestamp($session->last_activity);
                $session->is_current = $session->id === $currentSessionId;

                return $session;
            });

        $sessionConfig = [
            'driver' => config('session.driver'),
            'lifetime' => config('session.lifetime'),
            'expire_on_close' => config('session.expire_on_close'),
        ];

        return view('settings.index', compact(
            'tab',
            'smsBalance',
            'users',
            'pastorMembers',
            'secretaryMembers',
            'logs',
            'systemLogs',
            'sessions',
            'sessionConfig',
            'currentSessionId'
        ));
    }

    public function update(Request $request)
    {
        $tab = $request->input('tab', 'church');

        if ($tab === 'church') {
            $validated = $request->validate([
                'church_name' => 'required|string|max:255',
                'church_tagline' => 'nullable|string|max:255',
                'church_phone' => 'nullable|string|max:50',
                'church_email' => 'nullable|email|max:255',
                'church_address' => 'nullable|string|max:500',
                'church_pastor' => 'nullable|string|max:255',
                'church_logo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
                'remove_church_logo' => 'nullable|boolean',
            ]);

            foreach (['church_name', 'church_tagline', 'church_phone', 'church_email', 'church_address', 'church_pastor'] as $key) {
                Setting::set($key, $validated[$key] ?? '', 'church');
            }

            if ($request->boolean('remove_church_logo')) {
                $this->deleteChurchLogo();
                Setting::set('church_logo', '', 'church');
            } elseif ($request->hasFile('church_logo')) {
                $this->deleteChurchLogo();
                $path = $request->file('church_logo')->store('church', 'public');
                Setting::set('church_logo', $path, 'church');
            }
        } elseif ($tab === 'sms') {
            $validated = $request->validate([
                'sms_sender_id' => 'nullable|string|max:11',
                'sms_token' => 'nullable|string|max:500',
                'sms_enabled' => 'nullable|boolean',
            ]);

            Setting::set('sms_sender_id', $validated['sms_sender_id'] ?? 'TAG UPENDO', 'sms');
            Setting::set('sms_enabled', $request->boolean('sms_enabled'), 'sms');

            if ($request->filled('sms_token')) {
                Setting::set('sms_token', $validated['sms_token'], 'sms');
            }
        } elseif ($tab === 'general') {
            $validated = $request->validate([
                'currency' => 'required|string|max:10',
                'timezone' => 'required|string|max:100',
                'date_format' => 'required|string|max:20',
            ]);

            Setting::set('currency', $validated['currency'], 'general');
            Setting::set('timezone', $validated['timezone'], 'general');
            Setting::set('date_format', $validated['date_format'], 'general');
        }

        return redirect()
            ->route('settings.index', ['tab' => $tab])
            ->with('success', __('Settings updated successfully.'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:admin,pastor,secretary',
            'member_id' => 'nullable|exists:members,id|required_if:role,pastor|required_if:role,secretary',
        ]);

        $plainPassword = Str::random(10);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($plainPassword),
            'role' => $validated['role'],
            'member_id' => in_array($validated['role'], ['pastor', 'secretary'], true) ? $validated['member_id'] : null,
        ]);

        return redirect()
            ->route('settings.index', ['tab' => 'users'])
            ->with('success', __('User created! Temporary password: :password — Please share with the user and ask them to change it.', [
                'password' => $plainPassword,
            ]));
    }

    public function destroySession(Request $request, string $session)
    {
        if ($session === $request->session()->getId()) {
            return redirect()
                ->route('settings.index', ['tab' => 'sessions'])
                ->with('error', __('You cannot revoke your current session.'));
        }

        DB::table('sessions')->where('id', $session)->delete();

        return redirect()
            ->route('settings.index', ['tab' => 'sessions'])
            ->with('success', __('Session revoked successfully.'));
    }

    protected function deleteChurchLogo(): void
    {
        $path = Setting::get('church_logo');

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
