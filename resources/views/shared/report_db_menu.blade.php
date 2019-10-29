
@if(Auth::user()->isMultiDb())
	@php $show_multi_db = 'show_multi_db'; @endphp
@else
	@php $show_multi_db = ''; @endphp
@endif

<div class="col-sm-4 multi_db {{ $show_multi_db }}">
	<div class="form-group">
		<label>{{__('general.database')}}</label>
        <select name="databases[]" id="database_select" multiple class="form-control multiselect" value="<?php if(isset($_POST['databases'])){echo $_POST['databases'];}?>">

			@foreach ($filters['db_list'] as $key => $value) {
                <option value="{{$value}}">{{$key}}</option>
            @endforeach

		</select>
    </div>
</div>