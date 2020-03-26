<h4>Current Values</h4>
{{ __('general.full_name') }}: {{ $recipient->name}}<br>
{{ __('general.email') }}: {{ $recipient->email}}<br>
{{ __('general.phone') }}: {{ $recipient->phone}}<br>

@foreach ($audits as $audit)
    @php
        $modified = $audit->getModified();
        $fields = array_keys($modified);
        $old = array_column($modified, 'old');
        $new = array_column($modified, 'new');
    @endphp
    <hr>
    ==== {{ $audit->event }} ====<br>
    At: {{ $audit->created_at }}<br>
    From: {{ $audit->ip_address }}<br>
    By: {{ $audit->user->name }} ({{ $audit->user->email }})<br>

    <table border=1>
        <thead>
            <th>Field</th>
            <th>Old</th>
            <th>New</th>
        </thead>
        <tbody>
        @for ($i = 0; $i < count($modified); $i++)
            <tr>
                <td>{{ $fields[$i] }}</td>
                <td>{{ isset($old[$i]) ? $old[$i] : '' }}</td>
                <td>{{ isset($new[$i]) ? $new[$i] : '' }}</td>
            </tr>
        @endfor
        </tbody>
    </table>
@endforeach