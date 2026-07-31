<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\BulkSmsController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\OfferingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\LeadershipController;
use App\Http\Controllers\ChurchLeaderController;
use App\Http\Controllers\PledgeController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TitheController;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\MemberRegistrationRequestController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\RegistrationLinkController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->where('locale', 'en|sw')
    ->name('locale.switch');

Route::get('register', [PublicRegistrationController::class, 'landing'])->name('register.landing');
Route::get('register/{code}', [PublicRegistrationController::class, 'create'])->name('register.show');
Route::post('register/{code}', [PublicRegistrationController::class, 'store'])->name('register.store');
Route::get('register/{code}/thanks', [PublicRegistrationController::class, 'thanks'])->name('register.thanks');

Route::middleware(['auth', 'activity.log'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

    Route::middleware('member.profile')->group(function () {
        Route::get('/my', [\App\Http\Controllers\MemberPortalController::class, 'index'])->name('my.portal');
        Route::put('my/profile', [\App\Http\Controllers\MemberPortalController::class, 'updateProfile'])->name('member.profile.update');
        Route::put('my/password', [\App\Http\Controllers\MemberPortalController::class, 'updatePassword'])->name('member.password.update');
        Route::post('my/requests', [\App\Http\Controllers\MemberPortalController::class, 'storeRequest'])->name('member.requests.store');
    });

    Route::middleware('staff')->group(function () {
        Route::get('members', [MemberController::class, 'index'])->name('members.index');
        Route::get('members/registration-link', [RegistrationLinkController::class, 'index'])->name('members.registration-link');
        Route::post('registration-links', [RegistrationLinkController::class, 'store'])->name('registration-links.store');
        Route::patch('registration-links/{registrationLink}/toggle', [RegistrationLinkController::class, 'toggle'])->name('registration-links.toggle');

        Route::middleware('manage.member.registrations')->group(function () {
            Route::get('member-registrations', [MemberRegistrationRequestController::class, 'index'])->name('member-registrations.index');
            Route::get('member-registrations/{member_registration_request}', [MemberRegistrationRequestController::class, 'show'])->name('member-registrations.show');
            Route::post('member-registrations/{member_registration_request}/approve', [MemberRegistrationRequestController::class, 'approve'])->name('member-registrations.approve');
            Route::post('member-registrations/{member_registration_request}/reject', [MemberRegistrationRequestController::class, 'reject'])->name('member-registrations.reject');
        });

        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/{service}', [AttendanceController::class, 'show'])->name('attendance.show');

        Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('calendar/feed', [CalendarController::class, 'feed'])->name('calendar.feed');
        Route::get('calendar/events/{event}', [CalendarController::class, 'show'])->name('calendar.events.show');

        Route::get('services', [\App\Http\Controllers\ServiceController::class, 'index'])->name('services.index');
        Route::get('services/{event}', [\App\Http\Controllers\ServiceController::class, 'show'])->name('services.show');

        Route::get('church-leaders', [ChurchLeaderController::class, 'index'])->name('church-leaders.index');

        Route::middleware('manage.service.requests')->group(function () {
            Route::get('requests', [\App\Http\Controllers\ServiceRequestController::class, 'index'])->name('requests.index');
            Route::get('requests/{serviceRequest}/edit', [\App\Http\Controllers\ServiceRequestController::class, 'edit'])->name('requests.edit');
            Route::put('requests/{serviceRequest}', [\App\Http\Controllers\ServiceRequestController::class, 'update'])->name('requests.update');
        });

        Route::middleware('admin')->group(function () {
            Route::get('members/create', [MemberController::class, 'create'])->name('members.create');
            Route::post('members', [MemberController::class, 'store'])->name('members.store');
            Route::get('members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
            Route::put('members/{member}', [MemberController::class, 'update'])->name('members.update');
            Route::post('members/{member}/archive', [MemberController::class, 'archive'])->name('members.archive');
            Route::post('members/{member}/restore', [MemberController::class, 'restore'])->name('members.restore');
            Route::post('members/{member}/generate-password', [MemberController::class, 'generatePassword'])->name('members.generate-password');
            Route::get('members-search', [MemberController::class, 'search'])->name('members.search');
            Route::post('members/{member}/link-spouse', [MemberController::class, 'linkSpouse'])->name('members.link-spouse');
            Route::post('members/{member}/unlink-spouse', [MemberController::class, 'unlinkSpouse'])->name('members.unlink-spouse');

            Route::get('follow-ups/create/{member}', [FollowUpController::class, 'create'])->name('follow-ups.create');
            Route::resource('follow-ups', FollowUpController::class)->only(['index', 'store']);

            Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
            Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
            Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

            Route::get('bulk-sms/create', [BulkSmsController::class, 'create'])->name('bulk-sms.create');
            Route::post('bulk-sms', [BulkSmsController::class, 'store'])->name('bulk-sms.store');

            Route::get('attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
            Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
            Route::get('attendance/{service}/collect', [AttendanceController::class, 'collect'])->name('attendance.collect');
            Route::post('attendance/{service}/collect', [AttendanceController::class, 'saveCollect'])->name('attendance.collect.save');

            Route::resource('departments', DepartmentController::class)->only(['index', 'store']);
            Route::post('departments/{department}/assign-member', [DepartmentController::class, 'assignMember'])->name('departments.assign-member');

            Route::get('api-sms-logs', fn () => redirect()->route('settings.index', ['tab' => 'sms']))->name('api-sms-logs.index');
            Route::delete('attendance/{service}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');

            Route::post('calendar/events', [CalendarController::class, 'store'])->name('calendar.events.store');
            Route::put('calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.events.update');
            Route::delete('calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.events.destroy');
            Route::get('calendar/events/{event}/attendance', [CalendarController::class, 'startAttendance'])->name('calendar.events.attendance');

            Route::get('services/{event}/edit', [\App\Http\Controllers\ServiceController::class, 'edit'])->name('services.edit');
            Route::put('services/{event}', [\App\Http\Controllers\ServiceController::class, 'update'])->name('services.update');
            Route::delete('services/{event}', [\App\Http\Controllers\ServiceController::class, 'destroy'])->name('services.destroy');

            Route::get('leadership', [LeadershipController::class, 'index'])->name('leadership.index');
            Route::post('leadership', [LeadershipController::class, 'store'])->name('leadership.store');
            Route::put('leadership', [LeadershipController::class, 'update'])->name('leadership.update');
            Route::delete('leadership/{event}', [LeadershipController::class, 'destroy'])->name('leadership.destroy');

            Route::post('church-leaders/roles', [ChurchLeaderController::class, 'storeRole'])->name('church-leaders.roles.store');
            Route::post('church-leaders/assign', [ChurchLeaderController::class, 'assign'])->name('church-leaders.assign');
            Route::delete('church-leaders/{member}/roles/{role}', [ChurchLeaderController::class, 'unassign'])->name('church-leaders.unassign');

            Route::get('finance', [FinanceDashboardController::class, 'index'])->name('finance.dashboard');
            Route::resource('offerings', OfferingController::class)->only(['index', 'store']);
            Route::resource('tithes', TitheController::class)->only(['index', 'store', 'destroy']);
            Route::resource('pledges', PledgeController::class)->only(['index', 'store']);
            Route::post('pledges/{pledge}/pay', [PledgeController::class, 'pay'])->name('pledges.pay');
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/general', [ReportController::class, 'general'])->name('reports.general');
            Route::resource('expenses', ExpenseController::class)->only(['index', 'store']);
            Route::resource('assets', AssetController::class)->only(['index', 'store']);

            Route::get('users', fn () => redirect()->route('settings.index', ['tab' => 'users']));
            Route::post('users', fn () => redirect()->route('settings.index', ['tab' => 'users']));

            Route::get('settings', [SystemSettingController::class, 'index'])->name('settings.index');
            Route::put('settings', [SystemSettingController::class, 'update'])->name('settings.update');
            Route::post('settings/users', [SystemSettingController::class, 'storeUser'])->name('settings.users.store');
            Route::delete('settings/sessions/{session}', [SystemSettingController::class, 'destroySession'])->name('settings.sessions.destroy');
        });
    });

    Route::get('members/{member}', [MemberController::class, 'show'])
        ->whereNumber('member')
        ->name('members.show');
});
