@if ($message = Session::get('flash'))
    <div class="alert alert-info alert-block">
        <button type="button" class="close" aria-label="Close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        <strong>{{ $message }}</strong>
    </div>
@endif
                            
<div class="row">
Dash cards go here
<p>
<a href="{{ action('ComplianceDashController@settingsIndex') }}">Go To Settings</a>
</p>
</div>