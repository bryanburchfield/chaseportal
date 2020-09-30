
@if(Auth::user()->isMultiDb())
	@php $show_multi_db = 'show_multi_db'; @endphp
@else
	@php $show_multi_db = ''; @endphp
@endif

<div class="col-sm-4 multi_db {{ $show_multi_db }} mb-2">
	<div class="form-group">
		<label>{{__('general.database')}}</label>

		<select class="form-control selectpicker" value="<?php if(isset($_POST['databases'])){echo $_POST['databases'];}?>" id="database_select" multiple name="databases[]" data-live-search="true" data-actions-box="true">
			@foreach ($filters['db_list'] as $key => $value) {
                <option value="{{$value}}">{{$key}}</option>
            @endforeach
		</select>

    </div>
</div>