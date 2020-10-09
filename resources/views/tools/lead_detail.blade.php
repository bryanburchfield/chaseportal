@extends('layouts.master')
@section('title', __('tools.tools'))

@section('content')

<div class="preloader"></div>

<div class="wrapper">

	@include('shared.sidenav')

	<div id="content">
		@include('shared.navbar')

		<div class="container-fluid bg dashboard p20">
			<div class="container-full mt50 tools">
			    <div class="row">

                    {{-- Put lead# form field here --}}
                    {{-- GET http://chasedata.test/tools/lead_detail/143468 --}}

@if ($lead)
    {{ $lead->id }} <br>
    {{ $lead->ClientId }} <br>
    {{ $lead->FirstName }} <br>
    {{ $lead->LastName }} <br>
    {{ $lead->Address }} <br>
    {{ $lead->City }} <br>
    {{ $lead->State }} <br>
    {{ $lead->ZipCode }} <br>
    {{ $lead->PrimaryPhone }} <br>
    {{ $lead->SecondaryPhone }} <br>
    {{ $lead->Rep }} <br>
    {{ $lead->CallStatus }} <br>
    {{ $lead->Date }} <br>
    {{ $lead->Campaign }} <br>
    {{ $lead->Attempt }} <br>
    {{ $lead->GroupId }} <br>
    {{ $lead->WasDialed }} <br>
    {{ $lead->LastUpdated }} <br>
    {{ $lead->IdGuid }} <br>
    {{ $lead->ReloadDate }} <br>
    {{ $lead->ReloadAttempt }} <br>
    {{ $lead->Notes }} <br>
    {{ $lead->Subcampaign }} <br>
    {{ $lead->CallType }} <br>
    {{ $lead->DispositionId }} <br>
    {{ $lead->FullName }} <br>

    @foreach($lead->customFields() as $field)
        key = <b>{{ $field['key'] }}</b> description = <b>{{ $field['description'] }}</b> value = <b>{{ $field['value'] }}</b> <br>
    @endforeach
@endif
				</div>
			</div>
		</div>
	</div>

	@include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

@endsection