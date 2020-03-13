@extends('layouts.report')

@section('title', __('general.compliance_dashboard'))

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card p0">
			<form method="post" class="form compliance_settings">
				@csrf
				<table class="table">
					<colgroup>
						<col style="width: 50%;">
						<col style="width: 25%;">
						<col style="width: 25%;">
					</colgroup>
					<thead>
						<th>{{__('general.code')}}</th>
						<th>{{__('general.minutes_per_day')}}</th>
						<th>{{__('general.times_per_day')}}</th>
					</thead>
					<tbody>
		    			@foreach ($pause_codes as $pause_code)
		    				<tr >
			    				<td>
				    				<div class="form-group">
				    					<h5><b>{{$pause_code['code']}}</b></h5>
				    	    			<input type="hidden" class="form-control code" readonly name="code[]" value="{{ $pause_code['code'] }}">
									</div>
			    				</td>
								<td>
									<div class="form-group">
										<input type="text" class="form-control minutes_per_day" name="minutes_per_day[]" value="{{$pause_code['minutes_per_day']}}">
									</div>
								</td>
								<td>
									<div class="form-group">
										<input type="text" class="form-control times_per_day" name="times_per_day[]"  value="{{$pause_code['times_per_day']}}">
									</div>
								</td>
							</tr>
		    			@endforeach

					</tbody>
				</table>
				<input type="submit" class="btn btn-primary mr10" value="{{__('general.save')}}" />
				<input type="submit" class="btn btn-default btn-cancel" name="cancel" value="{{__('general.cancel')}}" />
			</form>
	    </div>
    </div>
</div>

@endsection
