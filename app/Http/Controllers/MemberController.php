<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Member;
use App\Models\MemberRegistrationRequest;
use App\Services\ActivityLogger;
use App\Services\MemberAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $showArchived = $request->input('status') === 'archived';

        $query = Member::with(['department', 'spouse'])
            ->select('members.*')
            ->selectRaw('(
                SELECT COUNT(*) FROM members AS family_children
                WHERE family_children.parent_id = members.id
                   OR (members.spouse_id IS NOT NULL AND family_children.parent_id = members.spouse_id)
            ) AS family_children_count');

        if ($showArchived) {
            $query->archived()->latest('archived_at');
        } else {
            $query->active();
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('member_type')) {
            $query->where('member_type', $request->member_type);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        match ($request->input('family')) {
            'has_spouse' => $query->whereNotNull('spouse_id'),
            'has_children' => $query->whereRaw('(
                SELECT COUNT(*) FROM members AS fc
                WHERE fc.parent_id = members.id
                   OR (members.spouse_id IS NOT NULL AND fc.parent_id = members.spouse_id)
            ) > 0'),
            'is_child' => $query->whereNotNull('parent_id'),
            'adults' => $query->whereNull('parent_id'),
            default => null,
        };

        $members = $query->latest()->paginate(15)->withQueryString();

        $departments = Department::orderBy('name')->get();

        $stats = [
            'total' => Member::active()->whereNull('parent_id')->count(),
            'members' => Member::active()->whereNull('parent_id')->where('member_type', 'member')->count(),
            'visitors' => Member::active()->where('member_type', 'visitor')->count(),
            'new_converts' => Member::active()->where('member_type', 'new_convert')->count(),
            'children' => Member::active()->whereNotNull('parent_id')->count(),
            'archived' => Member::archived()->count(),
        ];

        $pendingRegistrations = auth()->user()->canManageMemberRegistrations()
            ? MemberRegistrationRequest::where('status', 'pending')->count()
            : 0;

        return view('members.index', compact('members', 'departments', 'stats', 'pendingRegistrations', 'showArchived'));
    }

    public function create(Request $request)
    {
        $departments = Department::orderBy('name')->get();
        $tzRegionNames = array_keys(config('tanzania_locations.regions'));
        sort($tzRegionNames);

        if ($request->filled('parent_id')) {
            $parent = Member::findOrFail($request->parent_id);

            return view('members.create-child', compact('parent'));
        }

        $eligibleSpouses = Member::whereNull('spouse_id')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name', 'phone_number', 'gender']);

        return view('members.create', compact('departments', 'tzRegionNames', 'eligibleSpouses'));
    }

    public function store(Request $request)
    {
        if ($request->filled('parent_id') && $request->boolean('is_child')) {
            return $this->storeChild($request);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'phone_number' => 'required_without:parent_id|nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'birth_mkoa' => 'nullable|string|max:255',
            'birth_wilaya' => 'nullable|string|max:255',
            'residence_mkoa' => 'nullable|string|max:255',
            'residence_wilaya' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:single,married,widowed,divorced',
            'date_joined' => 'nullable|date',
            'is_baptized' => 'required|in:0,1',
            'baptism_date' => 'nullable|date',
            'occupation' => 'nullable|string|max:255',
            'member_type' => 'required|in:member,visitor,new_convert',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'department_id' => 'nullable|exists:departments,id',
            'parent_id' => 'nullable|exists:members,id',
            'spouse_is_member' => 'nullable|in:0,1',
            'spouse_mode' => 'nullable|in:new,existing',
            'existing_spouse_id' => 'nullable|exists:members,id',
            'spouse_name' => 'nullable|string|max:255',
            'spouse_phone_number' => 'nullable|string|max:20',
            'spouse_email' => 'nullable|email|max:255',
            'spouse_gender' => 'nullable|in:male,female',
            'spouse_date_of_birth' => 'nullable|date|before:today',
            'spouse_occupation' => 'nullable|string|max:255',
            'spouse_member_type' => 'nullable|in:member,visitor,new_convert',
            'spouse_department_id' => 'nullable|exists:departments,id',
            'spouse_is_baptized' => 'nullable|in:0,1',
            'spouse_baptism_date' => 'nullable|date',
            'spouse_date_joined' => 'nullable|date',
            'spouse_birth_mkoa' => 'nullable|string|max:255',
            'spouse_birth_wilaya' => 'nullable|string|max:255',
        ];

        if ($request->input('marital_status') === 'married' && $request->input('spouse_is_member') === '1') {
            $rules['spouse_mode'] = 'required|in:new,existing';

            if ($request->input('spouse_mode') === 'existing') {
                $rules['existing_spouse_id'] = 'required|exists:members,id';
            } else {
                $rules['spouse_name'] = 'required|string|max:255';
                $rules['spouse_phone_number'] = 'required|string|max:20';
                $rules['spouse_member_type'] = 'required|in:member,visitor,new_convert';
                $rules['spouse_is_baptized'] = 'required|in:0,1';
            }
        }

        $validated = $request->validate($rules);

        $validated['is_baptized'] = (bool) $validated['is_baptized'];
        if (! $validated['is_baptized']) {
            $validated['baptism_date'] = null;
        }

        $memberData = collect($validated)->except([
            'spouse_is_member',
            'spouse_mode',
            'existing_spouse_id',
            'spouse_name',
            'spouse_phone_number',
            'spouse_email',
            'spouse_gender',
            'spouse_date_of_birth',
            'spouse_occupation',
            'spouse_member_type',
            'spouse_department_id',
            'spouse_is_baptized',
            'spouse_baptism_date',
            'spouse_date_joined',
            'spouse_birth_mkoa',
            'spouse_birth_wilaya',
        ])->all();

        $member = null;
        $newAccounts = [];

        DB::transaction(function () use (&$member, &$newAccounts, $memberData, $validated) {
            $member = Member::create($memberData);
            $member->update(['member_code' => MemberAccountService::generateMemberCode()]);

            $account = app(MemberAccountService::class)->provision($member->fresh());
            if ($account) {
                $newAccounts[] = $account;
            }

            if (
                ($validated['marital_status'] ?? null) === 'married'
                && ($validated['spouse_is_member'] ?? null) === '1'
            ) {
                $this->linkOrCreateSpouse($member, $validated);

                $spouse = $member->fresh()->spouse;
                if ($spouse && ! $spouse->member_code) {
                    $spouse->update(['member_code' => MemberAccountService::generateMemberCode()]);
                }
                if ($spouse) {
                    $spouseAccount = app(MemberAccountService::class)->provision($spouse->fresh());
                    if ($spouseAccount) {
                        $newAccounts[] = $spouseAccount;
                    }
                }
            }
        });

        $message = __('Member registered successfully.');
        if ($member->fresh()->spouse_id) {
            $message = __('Member and spouse registered successfully.');
        }

        return redirect()
            ->route('members.show', $member)
            ->with('success', $message)
            ->with('new_member_accounts', $newAccounts);
    }

    protected function linkOrCreateSpouse(Member $member, array $validated): void
    {
        if (($validated['spouse_mode'] ?? '') === 'existing') {
            $spouse = Member::findOrFail($validated['existing_spouse_id']);

            if ($spouse->id === $member->id) {
                return;
            }

            if ($spouse->spouse_id) {
                return;
            }

            $member->update([
                'spouse_id' => $spouse->id,
                'marital_status' => 'married',
            ]);
            $spouse->update([
                'spouse_id' => $member->id,
                'marital_status' => 'married',
            ]);

            if (! $spouse->member_code) {
                $spouse->update(['member_code' => MemberAccountService::generateMemberCode()]);
            }

            return;
        }

        $spouseGender = $member->gender === 'male' ? 'female' : ($member->gender === 'female' ? 'male' : null);

        $spouseBaptized = (bool) ($validated['spouse_is_baptized'] ?? false);

        $spouse = Member::create([
            'name' => $validated['spouse_name'],
            'phone_number' => $validated['spouse_phone_number'],
            'email' => $validated['spouse_email'] ?? null,
            'gender' => $spouseGender,
            'date_of_birth' => $validated['spouse_date_of_birth'] ?? null,
            'occupation' => $validated['spouse_occupation'] ?? null,
            'member_type' => $validated['spouse_member_type'] ?? 'member',
            'department_id' => $validated['spouse_department_id'] ?? $member->department_id,
            'is_baptized' => $spouseBaptized,
            'baptism_date' => $spouseBaptized ? ($validated['spouse_baptism_date'] ?? null) : null,
            'date_joined' => $validated['spouse_date_joined'] ?? $member->date_joined,
            'marital_status' => 'married',
            'birth_mkoa' => $validated['spouse_birth_mkoa'] ?? null,
            'birth_wilaya' => $validated['spouse_birth_wilaya'] ?? null,
            'residence_mkoa' => $member->residence_mkoa,
            'residence_wilaya' => $member->residence_wilaya,
            'address' => $member->address,
            'emergency_contact_name' => $member->name,
            'emergency_contact_phone' => $member->phone_number,
        ]);

        $member->update([
            'spouse_id' => $spouse->id,
            'marital_status' => 'married',
        ]);
        $spouse->update(['spouse_id' => $member->id]);
    }

    protected function storeChild(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|exists:members,id',
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
        ]);

        $parent = Member::findOrFail($validated['parent_id']);

        $member = Member::create([
            'name' => $validated['name'],
            'gender' => $validated['gender'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'parent_id' => $parent->id,
            'phone_number' => $parent->phone_number,
            'residence_mkoa' => $parent->residence_mkoa,
            'residence_wilaya' => $parent->residence_wilaya,
            'address' => $parent->address,
            'department_id' => $parent->department_id,
            'member_type' => 'member',
            'marital_status' => 'single',
            'is_baptized' => false,
        ]);

        $message = "Mtoto {$member->name} amesajiliwa.";
        if ($parent->spouse) {
            $message .= " Ataonekana kwa {$parent->name} na {$parent->spouse->name}.";
        }

        return redirect()
            ->route('members.show', $parent)
            ->with('success', $message);
    }

    public function show(Member $member)
    {
        if (auth()->user()->isMember()) {
            return redirect()->route('dashboard', ['tab' => 'overview']);
        }

        $member->load(['department', 'spouse', 'parent.spouse', 'leadershipRoles', 'user']);
        $familyChildren = $member->familyChildren()->get();

        $eligibleSpouses = Member::where('id', '!=', $member->id)
            ->whereNull('spouse_id')
            ->when($member->parent_id, fn ($q) => $q->where('id', '!=', $member->parent_id))
            ->where(fn ($q) => $q->whereNull('parent_id')->orWhere('parent_id', '!=', $member->id))
            ->orderBy('name')
            ->get();

        return view('members.show', compact('member', 'eligibleSpouses', 'familyChildren'));
    }

    public function linkSpouse(Request $request, Member $member)
    {
        $validated = $request->validate([
            'spouse_id' => 'required|exists:members,id|different:' . $member->id,
        ]);

        $spouse = Member::findOrFail($validated['spouse_id']);

        if ($member->spouse_id) {
            return back()->withErrors(['spouse_id' => 'Mwanachama huyu tayari ana mwenzi.']);
        }

        if ($spouse->spouse_id) {
            return back()->withErrors(['spouse_id' => 'Mwanachama uliyemchagua tayari ana mwenzi.']);
        }

        if ($spouse->id === $member->parent_id || $member->id === $spouse->parent_id) {
            return back()->withErrors(['spouse_id' => 'Huwezi kumuunganisha mwanachama na mzazi/mtoto wake.']);
        }

        $member->update([
            'spouse_id' => $spouse->id,
            'marital_status' => 'married',
        ]);

        $spouse->update([
            'spouse_id' => $member->id,
            'marital_status' => 'married',
        ]);

        return back()->with('success', "Wameunganishwa kama wanandoa: {$member->name} na {$spouse->name}.");
    }

    public function unlinkSpouse(Member $member)
    {
        $spouse = $member->spouse;

        $member->update(['spouse_id' => null]);
        if ($spouse) {
            $spouse->update(['spouse_id' => null]);
        }

        return back()->with('success', 'Uhusiano wa ndoa umeondolewa.');
    }

    public function edit(Member $member)
    {
        if ($member->isArchived()) {
            return redirect()
                ->route('members.index', ['status' => 'archived'])
                ->with('error', __('Archived members must be restored before editing.'));
        }

        $departments = Department::orderBy('name')->get();
        $tzRegionNames = array_keys(config('tanzania_locations.regions'));
        sort($tzRegionNames);

        return view('members.edit', compact('member', 'departments', 'tzRegionNames'));
    }

    public function update(Request $request, Member $member)
    {
        if ($member->isArchived()) {
            return back()->with('error', __('Archived members cannot be updated.'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date|before:today',
            'birth_mkoa' => 'nullable|string|max:255',
            'birth_wilaya' => 'nullable|string|max:255',
            'residence_mkoa' => 'nullable|string|max:255',
            'residence_wilaya' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:single,married,widowed,divorced',
            'date_joined' => 'nullable|date',
            'is_baptized' => 'required|in:0,1',
            'baptism_date' => 'nullable|date',
            'occupation' => 'nullable|string|max:255',
            'member_type' => 'required|in:member,visitor,new_convert',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $validated['is_baptized'] = (bool) $validated['is_baptized'];
        if (! $validated['is_baptized']) {
            $validated['baptism_date'] = null;
        }

        $member->update($validated);

        if ($member->user) {
            $member->user->update(['name' => $member->name]);
        }

        ActivityLogger::log('member.update', __('Updated member profile: :name', ['name' => $member->name]));

        return redirect()
            ->route('members.show', $member)
            ->with('success', __('Member updated successfully.'));
    }

    public function archive(Request $request, Member $member)
    {
        if ($member->isArchived()) {
            return back()->with('error', __('This member is already archived.'));
        }

        $validated = $request->validate([
            'archive_reason' => 'required|string|max:1000',
        ]);

        $member->update([
            'archived_at' => now(),
            'archive_reason' => $validated['archive_reason'],
            'archived_by' => auth()->id(),
        ]);

        ActivityLogger::log('member.archive', __('Archived member: :name', ['name' => $member->name]));

        return redirect()
            ->route('members.index')
            ->with('success', __('Member :name has been archived.', ['name' => $member->name]));
    }

    public function restore(Member $member)
    {
        if (! $member->isArchived()) {
            return back()->with('error', __('This member is not archived.'));
        }

        $name = $member->name;

        $member->update([
            'archived_at' => null,
            'archive_reason' => null,
            'archived_by' => null,
        ]);

        ActivityLogger::log('member.restore', __('Restored archived member: :name', ['name' => $name]));

        return redirect()
            ->route('members.index')
            ->with('success', __('Member :name has been restored.', ['name' => $name]));
    }

    public function generatePassword(Member $member, MemberAccountService $accountService)
    {
        if ($member->isArchived()) {
            return back()->with('error', __('Cannot generate a password for an archived member.'));
        }

        if ($member->parent_id) {
            return back()->with('error', __('Child profiles do not have login accounts.'));
        }

        $user = $member->user;
        $plainPassword = MemberAccountService::defaultPassword($member->name);

        if (! $member->member_code) {
            $member->update(['member_code' => MemberAccountService::generateMemberCode()]);
            $member->refresh();
        }

        if ($user) {
            $user->update(['password' => Hash::make($plainPassword)]);
        } else {
            $result = $accountService->provision($member->fresh());
            if (! $result) {
                return back()->with('error', __('Could not create a login account for this member.'));
            }

            return redirect()
                ->route('members.show', $member)
                ->with('success', __('Login account created and password generated.'))
                ->with('new_member_accounts', [$result]);
        }

        $account = [
            'name' => $member->name,
            'member_code' => $member->member_code,
            'password' => $plainPassword,
        ];

        $accountService->sendWelcomeSms($member->fresh(), $account);

        ActivityLogger::log('member.password_reset', __('Generated new password for member: :name', ['name' => $member->name]));

        return redirect()
            ->route('members.show', $member)
            ->with('success', __('New password generated successfully.'))
            ->with('new_member_accounts', [$account]);
    }

    public function destroy(Member $member)
    {
        $name = $member->name;

        if ($member->spouse) {
            $member->spouse->update(['spouse_id' => null]);
        }

        $member->children()->update(['parent_id' => null]);

        $member->delete();

        return redirect()
            ->route('members.index')
            ->with('success', "Mwanachama {$name} amefutwa kwenye mfumo.");
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $members = Member::adults()
            ->active()
            ->where('name', 'like', '%' . $q . '%')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json($members);
    }
}
