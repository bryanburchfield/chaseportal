@if ($message = Session::get('flash'))
    <div class="alert alert-info alert-block">
        <button type="button" class="close" aria-label="Close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        <strong>{{ $message }}</strong>
    </div>
@endif
                            
<div class="row">
    <p>
        <a class="btn btn-default" href="{{ action('ComplianceDashController@settingsIndex') }}">Go To Settings</a>
    </p>

    Dash cards go here

</div>