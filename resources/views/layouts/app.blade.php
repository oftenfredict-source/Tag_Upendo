<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="description" content="{{ $appChurchName ?? 'TAG Upendo' }} Follow Up System">
    <title>@yield('title', 'Dashboard') - {{ $appChurchName ?? 'TAG Upendo' }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vali-master/docs/css/main.css') }}">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css"
        href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    @stack('styles')
</head>

<body class="app sidebar-mini rtl">
    <!-- Navbar-->
    <header class="app-header"><a class="app-header__logo" href="{{ url('/dashboard') }}" title="{{ $appChurchName ?? 'TAG Upendo' }}">TAG</a>
        <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar"
            aria-label="Hide Sidebar"></a>
        <!-- Navbar Right Menu-->
        <ul class="app-nav">
            <!-- User Menu-->
            <li class="dropdown"><a class="app-nav__item" href="#" data-toggle="dropdown"
                    aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"></i></a>
                <ul class="dropdown-menu settings-menu dropdown-menu-right">
                    <li>
                        <a class="dropdown-item" href="{{ route('settings.index') }}">
                            <i class="fa fa-cog fa-lg"></i> System Settings
                        </a>
                    </li>
                    <li>
                        <form action="{{ url('/logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item"
                                style="cursor:pointer;background:none;border:none;width:100%;text-align:left;"><i
                                    class="fa fa-sign-out fa-lg"></i> Logout</button>
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
            </div>
        </div>
        <ul class="app-menu">
            <li><a class="app-menu__item {{ request()->is('dashboard') ? 'active' : '' }}"
                    href="{{ url('/dashboard') }}"><i class="app-menu__icon fa fa-dashboard"></i><span
                        class="app-menu__label">Dashboard</span></a></li>

            <li><a class="app-menu__item {{ request()->is('departments*') ? 'active' : '' }}"
                    href="{{ url('/departments') }}"><i class="app-menu__icon fa fa-building"></i><span
                        class="app-menu__label">Departments</span></a></li>

            <li class="treeview {{ request()->is('members*') ? 'is-expanded' : '' }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-users"></i>
                    <span class="app-menu__label">Members</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item {{ request()->routeIs('members.index', 'members.show') ? 'active' : '' }}"
                            href="{{ route('members.index') }}"><i class="icon fa fa-circle-o"></i> View Members</a></li>
                    <li><a class="treeview-item {{ request()->routeIs('members.create') ? 'active' : '' }}"
                            href="{{ route('members.create') }}"><i class="icon fa fa-circle-o"></i> Add Member</a></li>
                </ul>
            </li>

            <li><a class="app-menu__item {{ request()->is('calendar*') ? 'active' : '' }}"
                    href="{{ route('calendar.index') }}"><i class="app-menu__icon fa fa-calendar"></i><span
                        class="app-menu__label">Calendar (Ibada & Events)</span></a></li>
            <li><a class="app-menu__item {{ request()->is('church-leaders*') ? 'active' : '' }}"
                    href="{{ route('church-leaders.index') }}"><i class="app-menu__icon fa fa-id-badge"></i><span
                        class="app-menu__label">Viongozi wa Kanisa</span></a></li>
            <li><a class="app-menu__item {{ request()->is('leadership*') ? 'active' : '' }}"
                    href="{{ route('leadership.index') }}"><i class="app-menu__icon fa fa-user-circle"></i><span
                        class="app-menu__label">Uongozi wa Ibada</span></a></li>
            <li><a class="app-menu__item {{ request()->is('attendance*') ? 'active' : '' }}"
                    href="{{ route('attendance.index') }}"><i class="app-menu__icon fa fa-check-square-o"></i><span
                        class="app-menu__label">Attendance (Mahudhurio)</span></a></li>

            <li class="treeview {{ request()->is('offerings*') || request()->is('expenses*') || request()->is('pledges*') ? 'is-expanded' : '' }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-money"></i>
                    <span class="app-menu__label">Finance</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item {{ request()->is('offerings*') ? 'active' : '' }}"
                            href="{{ route('offerings.index') }}"><i class="icon fa fa-circle-o"></i> Offerings</a></li>
                    <li><a class="treeview-item {{ request()->is('expenses*') ? 'active' : '' }}"
                            href="{{ route('expenses.index') }}"><i class="icon fa fa-circle-o"></i> Expenses</a></li>
                    <li><a class="treeview-item {{ request()->is('pledges*') ? 'active' : '' }}"
                            href="{{ route('pledges.index') }}"><i class="icon fa fa-circle-o"></i> Pledges</a></li>
                </ul>
            </li>

            <li class="treeview {{ request()->is('reports') || request()->is('reports/general') ? 'is-expanded' : '' }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-bar-chart"></i>
                    <span class="app-menu__label">Reports</span>
                    <i class="treeview-indicator fa fa-angle-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li><a class="treeview-item {{ request()->is('reports') ? 'active' : '' }}"
                            href="{{ route('reports.index') }}"><i class="icon fa fa-circle-o"></i> Monthly Report</a></li>
                    <li><a class="treeview-item {{ request()->is('reports/general') ? 'active' : '' }}"
                            href="{{ route('reports.general') }}"><i class="icon fa fa-circle-o"></i> General Report</a></li>
                </ul>
            </li>

            <li><a class="app-menu__item {{ request()->is('bulk-sms*') ? 'active' : '' }}"
                    href="{{ url('/bulk-sms/create') }}"><i class="app-menu__icon fa fa-paper-plane"></i><span
                        class="app-menu__label">Send Bulk SMS</span></a></li>
            <li><a class="app-menu__item {{ request()->is('api-sms-logs*') ? 'active' : '' }}"
                    href="{{ url('/api-sms-logs') }}"><i class="app-menu__icon fa fa-list"></i><span
                        class="app-menu__label">SMS Logs</span></a></li>
            <li><a class="app-menu__item {{ request()->is('assets*') ? 'active' : '' }}" href="{{ url('/assets') }}"><i
                        class="app-menu__icon fa fa-archive"></i><span class="app-menu__label">Church Assets</span></a>
            </li>
            <li><a class="app-menu__item {{ request()->is('users*') ? 'active' : '' }}" href="{{ url('/users') }}"><i
                        class="app-menu__icon fa fa-user-plus"></i><span class="app-menu__label">System Users</span></a>
            </li>
            <li><a class="app-menu__item {{ request()->is('settings*') ? 'active' : '' }}"
                    href="{{ route('settings.index') }}"><i class="app-menu__icon fa fa-cog"></i><span
                        class="app-menu__label">System Settings</span></a></li>
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
    @stack('scripts')
</body>

</html>