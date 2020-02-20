@php
    $controller = new App\Http\Controllers\ComplianceDashController();
@endphp
<div class="row">
Dash cards go here
<p>
<a href="{{ action('ComplianceDashController@settingsIndex') }}">Go To Settings</a>
</p>
</div>