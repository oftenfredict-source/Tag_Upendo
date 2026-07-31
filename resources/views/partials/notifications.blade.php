<li class="dropdown app-nav__notify">
    <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="{{ __('Notifications') }}">
        <i class="fa fa-bell-o fa-lg"></i>
        @if(($headerNotifications['count'] ?? 0) > 0)
            <span class="app-nav__notify-badge">{{ $headerNotifications['count'] > 9 ? '9+' : $headerNotifications['count'] }}</span>
        @endif
    </a>
    <ul class="app-notification dropdown-menu dropdown-menu-right">
        <li class="app-notification__title">
            @if(($headerNotifications['count'] ?? 0) > 0)
                @if($headerNotifications['count'] === 1)
                    {{ __('You have :count notification', ['count' => $headerNotifications['count']]) }}
                @else
                    {{ __('You have :count notifications', ['count' => $headerNotifications['count']]) }}
                @endif
            @else
                {{ __('No new notifications') }}
            @endif
        </li>
        <div class="app-notification__content">
            @forelse($headerNotifications['items'] ?? [] as $notification)
                <li>
                    <a class="app-notification__item" href="{{ $notification['url'] }}">
                        <span class="app-notification__icon">
                            <span class="fa-stack fa-lg">
                                <i class="fa fa-circle fa-stack-2x text-{{ $notification['icon_color'] }}"></i>
                                <i class="fa fa-{{ $notification['icon'] }} fa-stack-1x fa-inverse"></i>
                            </span>
                        </span>
                        <div>
                            <p class="app-notification__message">{{ $notification['message'] }}</p>
                            <p class="app-notification__meta">
                                {{ $notification['meta'] }}
                                · {{ $notification['time']->diffForHumans() }}
                            </p>
                        </div>
                    </a>
                </li>
            @empty
                <li class="px-3 py-4 text-center text-muted small">{{ __('You are all caught up.') }}</li>
            @endforelse
        </div>
        @if(auth()->user()->canManageServiceRequests())
        <li class="app-notification__footer">
            <a href="{{ route('requests.index', ['status' => 'pending']) }}">{{ __('View service requests') }}</a>
        </li>
        @endif
    </ul>
</li>
