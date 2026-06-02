<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::with(['department', 'spouse'])
            ->select('members.*')
            ->selectRaw('(
                SELECT COUNT(*) FROM members AS family_children
                WHERE family_children.parent_id = members.id
                   OR (members.spouse_id IS NOT NULL AND family_children.parent_id = members.spouse_id)
            ) AS family_children_count');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
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

        return view('members.index', compact('members', 'departments'));
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

        return view('members.create', compact('departments', 'tzRegionNames'));
    }

    public function store(Request $request)
    {
        if ($request->filled('parent_id') && $request->boolean('is_child')) {
            return $this->storeChild($request);
        }

        $validated = $request->validate([
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
        ]);

        $validated['is_baptized'] = (bool) $validated['is_baptized'];
        if (! $validated['is_baptized']) {
            $validated['baptism_date'] = null;
        }

        $member = Member::create($validated);

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Member registered successfully.');
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
        $member->load(['department', 'spouse', 'parent.spouse', 'leadershipRoles']);
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
}
