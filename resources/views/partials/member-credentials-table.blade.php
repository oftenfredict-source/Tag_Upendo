<p class="text-left mb-2">{{ __('Share these details with the member(s). An SMS was also sent if a phone number was provided. Username is Member ID (e.g. TU001-2026); password is last name in CAPITAL letters.') }}</p>
<table class="table table-sm table-bordered mb-0">
    <thead>
        <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Member ID') }}</th>
            <th>{{ __('Password') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($accounts as $account)
            <tr>
                <td>{{ $account['name'] }}</td>
                <td><code>{{ $account['member_code'] }}</code></td>
                <td><code>{{ $account['password'] }}</code></td>
            </tr>
        @endforeach
    </tbody>
</table>
