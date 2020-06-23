@extends('layouts.dash')
@section('title', 'Select Group')

@section('content')

<div class="wrapper">
	<div id="content">
		<div class="container-fluid bg dashboard p20">
			<div class="container mt20 p20">
				<div class="row">
					<div class="col-sm-6 card">
						<form method="POST" action="{{action('ReportController@setGroup')}}" class="form fc_style">
						    @csrf
						    <input type="hidden" name="report" value="{{$report}}">

						    <div class="form-group">
						    	<label for="group_id">Select Group</label>
						    	<select name="group_id" id="group_id" class="form-control">
						    	    @foreach ($groups as $group)
						    	        <option value="{{$group->GroupId}}">{{$group->GroupId}} : {{$group->GroupName}}</option>
						    	    @endforeach
						    	</select>
						    </div>
						    
						    <button class="btn btn-primary" type="submit">Submit</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection