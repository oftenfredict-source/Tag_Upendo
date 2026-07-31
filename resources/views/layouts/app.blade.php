<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="description" content="{{ $appChurchName ?? 'TAG Upendo' }} Follow Up System">
    <title>@yield('title', 'Dashboard') - {{ $appChurchName ?? 'TAG Upendo' }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vali-master/docs/css/main.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/brand.css') }}">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css"
        href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @stack('styles')
    <style>
        .lang-toggle {
            display: inline-flex;
            align-items: center;
            margin: 0 8px;
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 4px;
            overflow: hidden;
            height: 28px;
        }
        .lang-toggle a {
            color: rgba(255,255,255,.75);
            padding: 0 10px;
            font-size: 12px;
            font-weight: 600;
            line-height: 26px;
            text-decoration: none;
        }
        .lang-toggle a:hover { color: #fff; background: rgba(255,255,255,.12); }
        .lang-toggle a.active {
            background: #fff;
            color: #940000;
        }
        .app-nav__notify { position: relative; }
        .app-nav__notify-badge {
            position: absolute;
            top: 10px;
            right: 2px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ffc107;
            color: #333;
            font-size: 10px;
            font-weight: 700;
            line-height: 18px;
            text-align: center;
            box-shadow: 0 0 0 2px #940000;
        }
        .app-notification__title {
            background-color: rgba(148, 0, 0, 0.12) !important;
        }
        .app-header__logo-img {
            max-height: 42px;
            max-width: 120px;
            object-fit: contain;
        }
    </style>
</head>

<body class="app sidebar-mini rtl">
    @php $authUser = auth()->user(); @endphp
    <!-- Navbar-->
    <header class="app-header">
        <a class="app-header__logo d-flex align-items-center justify-content-center" href="{{ url('/dashboard') }}" title="{{ $appChurchName ?? 'TAG Upendo' }}">
            @if(!empty($appChurchLogo))
                <img src="{{ $appChurchLogo }}" alt="{{ $appChurchName ?? 'TAG Upendo' }}" class="app-header__logo-img">
            @else
                TAG
            @endif
        </a>
        <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar"
            aria-label="Hide Sidebar"></a>
        <!-- Navbar Right Menu-->
        <ul class="app-nav">
            <li class="d-flex align-items-center">
                <div class="lang-toggle" title="Language / Lugha">
                    <a href="{{ route('locale.switch', 'en') }}"
                        class="{{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
                    <a href="{{ route('locale.switch', 'sw') }}"
                        class="{{ app()->getLocale() === 'sw' ? 'active' : '' }}">SW</a>
                </div>
            </li>
            @include('partials.notifications')
            <!-- User Menu-->
            <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown"
                    aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"></i></a>
                <ul class="dropdown-menu settings-menu dropdown-menu-right">
                    @if($authUser->canManageSettings())
                    <li>
                        <a class="dropdown-item" href="{{ route('settings.index') }}">
                            <i class="fa fa-cog fa-lg"></i> {{ __('System Settings') }}
                        </a>
                    </li>
                    @endif
                    <li>
                        <form action="{{ url('/logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item"
                                style="cursor:pointer;background:none;border:none;width:100%;text-align:left;"><i
                                    class="fa fa-sign-out fa-lg"></i> {{ __('Logout') }}</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </header>
    <!-- Sidebar menu-->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
        <div class="app-sidebar__user"><img class="app-sidebar__user-avatar"
                src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}" alt="User Image">
            <div>
                <p class="app-sidebar__user-name">{{ auth()->user()->name ?? 'Administrator' }}</p>
                <p class="app-sidebar__user-designation">{{ auth()->user()->roleLabel() }}</p>
            </div>
        </div>
        <ul class="app-menu">
            <li><a class="app-menu__item {{ request()->is('dashboard') && !request()->routeIs('my.portal') && (!$authUser->isMember() || request('tab', 'overview') === 'overview') ? 'active' : '' }}"
                    href="{{ $authUser->isMember() ? route('dashboard', ['tab' => 'overview']) : url('/dashboard') }}"><i class="app-menu__icon fa fa-dashboard"></i><span
                        class="app-menu__label">
                        @if($authUser->isMember())
                            {{ __('My Dashboard') }}
                        @else
                            {{ __('Dashboard') }}
                        @endif
                    </span></a></li>

            @if($authUser->isMember() && $authUser->member_id)
            <li><a class="app-menu__item {{ request()->routeIs('announcements.*') ? 'active' : '' }}"
                    href="{{ route('announcements.index') }}"><i class="app-menu__icon fa fa-bullhorn"></i><span
                        class="app-menu__label">{{ __('Announcements') }}</span></a></li>
            <li><a class="app-menu__item {{ request('tab') === 'contributions' ? 'active' : '' }}"
                    href="{{ route('dashboard', ['tab' => 'contributions']) }}"><i class="app-menu__icon fa fa-money"></i><span
                        class="app-menu__label">{{ __('My contributions') }}</span></a></li>
            <li><a class="app-menu__item {{ request('tab') === 'leaders' ? 'active' : '' }}"
                    href="{{ route('dashboard', ['tab' => 'leaders']) }}"><i class="app-menu__icon fa fa-id-badge"></i><span
                        class="app-menu__label">{{ __('Church leaders') }}</span></a></li>
            <li><a class="app-menu__item {{ request('tab') === 'requests' ? 'active' : '' }}"
                    href="{{ route('dashboard', ['tab' => 'requests']) }}"><i class="app-menu__icon fa fa-hand-paper-o"></i><span
                        class="app-menu__label">{{ __('Service requests') }}</span></a></li>
            <li><a class="app-menu__item {{ request('tab') === 'account' ? 'active' : '' }}"
                    href="{{ route('dashboard', ['tab' => 'account']) }}"><i class="app-menu__icon fa fa-cog"></i><span
                        class="app-menu__label">{{ __('My account') }}</span></a></li>
            @elseif($authUser->isStaff() && $authUser->member_id)
            <li class="treeview {{ request()->routeIs('my.portal') ? 'is-expanded' : '' }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-user"></i>
                    <span class="app-menu__label">{{ __('My Portal') }}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item {{ request()->routeIs('my.portal') && request('tab', 'overview') === 'overview' ? 'active' : '' }}"
                            href="{{ route('my.portal', ['tab' => 'overview']) }}"><i class="icon fa fa-circle-o"></i> {{ __('Overview') }}</a></li>
                    <li><a class="treeview-item {{ request()->routeIs('my.portal') && request('tab') === 'contributions' ? 'active' : '' }}"
                            href="{{ route('my.portal', ['tab' => 'contributions']) }}"><i class="icon fa fa-circle-o"></i> {{ __('My contributions') }}</a></li>
                    <li><a class="treeview-item {{ request()->routeIs('my.portal') && request('tab') === 'leaders' ? 'active' : '' }}"
                            href="{{ route('my.portal', ['tab' => 'leaders']) }}"><i class="icon fa fa-circle-o"></i> {{ __('Church leaders') }}</a></li>
                    @if($authUser->isSecretary())
                    <li><a class="treeview-item {{ request()->routeIs('my.portal') && request('tab') === 'requests' ? 'active' : '' }}"
                            href="{{ route('my.portal', ['tab' => 'requests']) }}"><i class="icon fa fa-circle-o"></i> {{ __('Service requests') }}</a></li>
                    @endif
                    <li><a class="treeview-item {{ request()->routeIs('my.portal') && request('tab') === 'account' ? 'active' : '' }}"
                            href="{{ route('my.portal', ['tab' => 'account']) }}"><i class="icon fa fa-circle-o"></i> {{ __('My account') }}</a></li>
                </ul>
            </li>
            @endif

            @if(!$authUser->isMember())
            @if($authUser->isFullStaff())
            <li><a class="app-menu__item {{ request()->routeIs('announcements.*') ? 'active' : '' }}"
                    href="{{ route('announcements.index') }}"><i class="app-menu__icon fa fa-bullhorn"></i><span
                        class="app-menu__label">{{ __('Announcements') }}</span></a></li>
            <li><a class="app-menu__item {{ request()->is('departments*') ? 'active' : '' }}"
                    href="{{ url('/departments') }}"><i class="app-menu__icon fa fa-building"></i><span
                        class="app-menu__label">{{ __('Departments') }}</span></a></li>
            @endif

            <li class="treeview {{ request()->is('members*') || request()->is('member-registrations*') ? 'is-expanded' : '' }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-users"></i>
                    <span class="app-menu__label">{{ __('Members') }}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item {{ request()->routeIs('members.index') && request('status') !== 'archived' ? 'active' : '' }}"
                            href="{{ route('members.index') }}"><i class="icon fa fa-circle-o"></i> {{ __('View Members') }}</a></li>
                    <li><a class="treeview-item {{ request()->routeIs('members.children') ? 'active' : '' }}"
                            href="{{ route('members.children') }}"><i class="icon fa fa-child"></i> {{ __('Children') }}</a></li>
                    @if($authUser->isFullStaff())
                    <li><a class="treeview-item {{ request()->routeIs('members.create') ? 'active' : '' }}"
                            href="{{ route('members.create') }}"><i class="icon fa fa-circle-o"></i> {{ __('Add Member') }}</a></li>
                    <li><a class="treeview-item {{ request()->routeIs('members.registration-link') ? 'active' : '' }}"
                            href="{{ route('members.registration-link') }}"><i class="icon fa fa-link"></i> {{ __('Registration link') }}</a></li>
                    @endif
                    @if($authUser->isAdmin())
                    <li><a class="treeview-item {{ request('status') === 'archived' ? 'active' : '' }}"
                            href="{{ route('members.index', ['status' => 'archived']) }}"><i class="icon fa fa-archive"></i> {{ __('Archived members') }}</a></li>
                    @endif
                    @if($authUser->canManageMemberRegistrations())
                    <li><a class="treeview-item {{ request()->is('member-registrations*') ? 'active' : '' }}"
                            href="{{ route('member-registrations.index') }}"><i class="icon fa fa-circle-o"></i> {{ __('Member registrations') }}</a></li>
                    @endif
                </ul>
            </li>

            <li class="treeview {{ request()->is('calendar*') || request()->is('services*') || request()->is('attendance*') || request()->is('guests*') ? 'is-expanded' : '' }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-calendar"></i>
                    <span class="app-menu__label">{{ __('Services & Attendance') }}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item {{ request()->is('calendar*') ? 'active' : '' }}"
                            href="{{ route('calendar.index') }}"><i class="icon fa fa-circle-o"></i> {{ __('Calendar') }}</a></li>
                    <li><a class="treeview-item {{ request()->is('services*') ? 'active' : '' }}"
                            href="{{ route('services.index') }}"><i class="icon fa fa-circle-o"></i> {{ __('Church Services') }}</a></li>
                    <li><a class="treeview-item {{ request()->is('guests*') ? 'active' : '' }}"
                            href="{{ route('guests.index') }}"><i class="icon fa fa-handshake-o"></i> {{ __('Guests') }}</a></li>
                    <li><a class="treeview-item {{ request()->is('attendance*') ? 'active' : '' }}"
                            href="{{ route('attendance.index') }}"><i class="icon fa fa-circle-o"></i> {{ __('Attendance') }}</a></li>
                </ul>
            </li>

            <li><a class="app-menu__item {{ request()->is('church-leaders*') ? 'active' : '' }}"
                    href="{{ route('church-leaders.index') }}"><i class="app-menu__icon fa fa-id-badge"></i><span
                        class="app-menu__label">{{ __('Church Leaders') }}</span></a></li>

            @if($authUser->canManageServiceRequests())
            <li><a class="app-menu__item {{ request()->is('requests*') ? 'active' : '' }}"
                    href="{{ route('requests.index') }}"><i class="app-menu__icon fa fa-inbox"></i><span
                        class="app-menu__label">{{ __('Service requests') }}</span></a></li>
            @endif

            @if($authUser->isFullStaff())
            <li class="treeview {{ request()->is('finance') || request()->is('offerings*') || request()->is('tithes*') || request()->is('expenses*') || request()->is('pledges*') ? 'is-expanded' : '' }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-money"></i>
                    <span class="app-menu__label">{{ __('Finance') }}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item {{ request()->is('finance') ? 'active' : '' }}"
                            href="{{ route('finance.dashboard') }}"><i class="icon fa fa-circle-o"></i> {{ __('Dashboard') }}</a></li>
                    <li><a class="treeview-item {{ request()->is('offerings*') ? 'active' : '' }}"
                            href="{{ route('offerings.index') }}"><i class="icon fa fa-circle-o"></i> {{ __('Offerings') }}</a></li>
                    <li><a class="treeview-item {{ request()->is('tithes*') ? 'active' : '' }}"
                            href="{{ route('tithes.index') }}"><i class="icon fa fa-circle-o"></i> {{ __('Tithes') }}</a></li>
                    <li><a class="treeview-item {{ request()->is('expenses*') ? 'active' : '' }}"
                            href="{{ route('expenses.index') }}"><i class="icon fa fa-circle-o"></i> {{ __('Expenses') }}</a></li>
                    <li><a class="treeview-item {{ request()->is('pledges*') ? 'active' : '' }}"
                            href="{{ route('pledges.index') }}"><i class="icon fa fa-circle-o"></i> {{ __('Pledges') }}</a></li>
                </ul>
            </li>

            <li class="treeview {{ request()->is('reports') || request()->is('reports/general') ? 'is-expanded' : '' }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-bar-chart"></i>
                    <span class="app-menu__label">{{ __('Reports') }}</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item {{ request()->is('reports') ? 'active' : '' }}"
                            href="{{ route('reports.index') }}"><i class="icon fa fa-circle-o"></i> {{ __('Monthly Report') }}</a></li>
                    <li><a class="treeview-item {{ request()->is('reports/general') ? 'active' : '' }}"
                            href="{{ route('reports.general') }}"><i class="icon fa fa-circle-o"></i> {{ __('General Report') }}</a></li>
                </ul>
            </li>
            @endif

            @if($authUser->isFullStaff())
            <li><a class="app-menu__item {{ request()->is('bulk-sms*') ? 'active' : '' }}"
                    href="{{ url('/bulk-sms/create') }}"><i class="app-menu__icon fa fa-paper-plane"></i><span
                        class="app-menu__label">{{ __('Send Bulk SMS') }}</span></a></li>
            <li><a class="app-menu__item {{ request()->is('assets*') ? 'active' : '' }}" href="{{ url('/assets') }}"><i
                        class="app-menu__icon fa fa-archive"></i><span class="app-menu__label">{{ __('Church Assets') }}</span></a>
            </li>
            @if($authUser->canManageSettings())
            <li><a class="app-menu__item {{ request()->is('settings*') ? 'active' : '' }}"
                    href="{{ route('settings.index') }}"><i class="app-menu__icon fa fa-cog"></i><span
                        class="app-menu__label">{{ __('System Settings') }}</span></a></li>
            @endif
            @endif
            @endif
        </ul>
    </aside>
    <main class="app-content">
        @yield('content')
    </main>
    <!-- Essential javascripts for application to work-->
    <script src="{{ asset('vali-master/docs/js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/popper.min.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vali-master/docs/js/main.js') }}"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="{{ asset('vali-master/docs/js/plugins/pace.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.sweetalert-init')
    @stack('scripts')
</body>

</html>
