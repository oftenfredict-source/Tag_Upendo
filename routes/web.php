<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\MemberController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\BulkSmsController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ApiSmsLogController;
use App\Http\Controllers\OfferingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\LeadershipController;
use App\Http\Controllers\ChurchLeaderController;
use App\Http\Controllers\PledgeController;
use App\Http\Controllers\SystemSettingController;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $memberCount = \App\Models\Member::count();
        $followUpCount = \App\Models\FollowUp::count();
        $departmentCount = \App\Models\Department::count();

        $recentMembers = \App\Models\Member::with('department')->latest()->take(6)->get();
        $recentFollowUps = \App\Models\FollowUp::with('member')->latest()->take(6)->get();

        return view('dashboard', compact('memberCount', 'followUpCount', 'departmentCount', 'recentMembers', 'recentFollowUps'));
    })->name('dashboard');

    Route::resource('departments', DepartmentController::class)->only(['index', 'store']);
    Route::post('departments/{department}/assign-member', [DepartmentController::class, 'assignMember'])->name('departments.assign-member');

    Route::resource('members', MemberController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('members/{member}/link-spouse', [MemberController::class, 'linkSpouse'])->name('members.link-spouse');
    Route::post('members/{member}/unlink-spouse', [MemberController::class, 'unlinkSpouse'])->name('members.unlink-spouse');

    Route::get('follow-ups/create/{member}', [FollowUpController::class, 'create'])->name('follow-ups.create');
    Route::resource('follow-ups', FollowUpController::class)->only(['index', 'store']);

    Route::get('bulk-sms/create', [BulkSmsController::class, 'create'])->name('bulk-sms.create');
    Route::post('bulk-sms', [BulkSmsController::class, 'store'])->name('bulk-sms.store');

    Route::get('api-sms-logs', [ApiSmsLogController::class, 'index'])->name('api-sms-logs.index');
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/{service}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::get('attendance/{service}/collect', [AttendanceController::class, 'collect'])->name('attendance.collect');
    Route::post('attendance/{service}/collect', [AttendanceController::class, 'saveCollect'])->name('attendance.collect.save');
    Route::delete('attendance/{service}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendar/feed', [CalendarController::class, 'feed'])->name('calendar.feed');
    Route::post('calendar/events', [CalendarController::class, 'store'])->name('calendar.events.store');
    Route::get('calendar/events/{event}', [CalendarController::class, 'show'])->name('calendar.events.show');
    Route::put('calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.events.update');
    Route::delete('calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.events.destroy');
    Route::get('calendar/events/{event}/attendance', [CalendarController::class, 'startAttendance'])->name('calendar.events.attendance');

    Route::get('leadership', [LeadershipController::class, 'index'])->name('leadership.index');
    Route::post('leadership', [LeadershipController::class, 'store'])->name('leadership.store');
    Route::put('leadership', [LeadershipController::class, 'update'])->name('leadership.update');
    Route::delete('leadership/{event}', [LeadershipController::class, 'destroy'])->name('leadership.destroy');

    Route::get('church-leaders', [ChurchLeaderController::class, 'index'])->name('church-leaders.index');
    Route::post('church-leaders/assign', [ChurchLeaderController::class, 'assign'])->name('church-leaders.assign');
    Route::delete('church-leaders/{member}/roles/{role}', [ChurchLeaderController::class, 'unassign'])->name('church-leaders.unassign');

    Route::resource('offerings', OfferingController::class)->only(['index', 'store']);
    Route::resource('pledges', PledgeController::class)->only(['index', 'store']);
    Route::post('pledges/{pledge}/pay', [PledgeController::class, 'pay'])->name('pledges.pay');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/general', [ReportController::class, 'general'])->name('reports.general');
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store']);
    Route::resource('assets', AssetController::class)->only(['index', 'store']);
    Route::resource('users', UserController::class)->only(['index', 'store']);

    Route::get('settings', [SystemSettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SystemSettingController::class, 'update'])->name('settings.update');
});
