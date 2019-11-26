@extends('layouts.dash')

@section('title', __('general.auto_reports'))

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
                        <h2 class="page_heading">{{__('general.auto_reports')}}</h2>
                        <h5 class="mb20">{{__('reports.auto_report_text')}}</h5><br>

                        @foreach($reports as $report)
                        <div class="col-sm-12 opt pl0" data-report="{{$report['report']}}">
                            <h4 class="mb0">{{__('reports.'.$report['report'])}}</h4>

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