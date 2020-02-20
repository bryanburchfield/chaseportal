@php
    $controller = new App\Http\Controllers\ComplianceDashController();
@endphp
<div class="row">
    <p>
    <a href="{{ action('MasterDashController@complianceDashboard') }}">Back to Dashboard</a>
    </p>

    @foreach ($controller->getPauseCodes() as $code)
    {{ $code }} <br>
    @endforeach
</div>