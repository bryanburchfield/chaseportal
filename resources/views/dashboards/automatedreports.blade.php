@extends('layouts.dash')

@section('title', 'Reports')

@section('content')

<div class="preloader"></div>

<div class="wrapper">
    @include('shared.sidenav')

    <div id="content">
        @include('shared.navbar')

        <div class="container-fluid bg dashboard p20">
            <div class="container-full mt20">
                <div class="row">            
                    <div class="col-sm-12">
                        <h2 class="page_heading">Automated Reports</h2>
                        <h5 class="mb20">Here you can toggle on and off automated reports. Reports are emailed to the address you registered with and will be <b>sent daily at 6:00am EST</b>.</h5><br>
                        
                        @foreach($reports as $report)
                        
                        <div class="col-sm-12 opt pl0" data-report="{{$report['report']}}">
                            <h4 class="mb0">{{$report['name']}}</h4>

                            <div class="controls reports">
                                <label class="switch">
                                    <input type="checkbox" <?=($report['selected']) ? 'checked' : ''?> name="kpi_input">
                                    <span></span>
                                </label>
                            </div>
                        </div><!-- end col 12 -->
                        @endforeach
                        
                    </div>            
                </div>
            </div>
        </div>
    </div>
</div>

@include('shared.reportmodal')

@endsection