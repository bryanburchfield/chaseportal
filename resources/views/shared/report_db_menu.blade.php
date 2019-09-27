
@if(Auth::user()->isMultiDb())
	@php $show_multi_db = 'show_multi_db'; @endphp
@else
	@php $show_multi_db = ''; @endphp
@endif

<div class="col-sm-4 multi_db {{ $show_multi_db }}">
	<div class="form-group">

		{!! Form::label('databases', 'Database') !!}
		{!! Form::select("databases[]", $filters['db_list'], null, ["class" => "form-control multiselect", 'id'=> 'subcampaign_select', 'multiple'=>true]) !!}
		
    </div>
</div>