<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="" required>
</div>

<div class="form-group">
    <label>{{__('tools.provider_type')}}</label>
    <select name="provider_type" class="form-control provider_type" required>
    	<option value="">Select One</option>
    	@foreach($provider_types as $provider)
			<option value="{{$provider}}">{{ucwords($provider)}}</option>
    	@endforeach
    </select>
</div>

<div class="properties"></div>


<div class="alert alert-success hidetilloaded"></div>
<div class="alert alert-danger hidetilloaded"></div>
<div class="alert connection_msg hidetilloaded"></div>
