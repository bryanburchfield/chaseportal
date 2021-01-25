@extends('layouts.master')
@section('title', __('tools.spam_check'))

@section('content')

<div class="preloader"></div>

<div class="wrapper">

    @include('shared.sidenav')

    <div id="content">
        @include('shared.navbar')

        <div class="container-fluid bg dashboard p20">
            <div class="container-full mt50 tools">
                <div class="row">
                    <div class="col-sm-12">

                        <div class="tab-pane" id="spam_check">
                            @if ($message = Session::get('flash'))
                            <div class="alert alert-info alert-block">
                                <button type="button" class="close" aria-label="Close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                                <strong>{{ $message }}</strong>
                            </div>
                            @endif

                            <h2 class="bbnone mb20">{{__('tools.spam_check_files')}}</h2>

                            @if (count($files))
                            {{ $files->links() }}
                            <div class="table-responsive nobdr">
                                <form enctype="multipart/form-data" method="post">
                                    @csrf
                                    <table class="table dnc_table mt20">
                                        <thead>
                                            <tr>
                                                <th class="text-center">{{__('tools.view')}}</th>
                                                <th class="text-center">ID</th>
                                                <th>{{__('tools.description')}}</th>
                                                <th>{{__('tools.uploaded')}}</th>
                                                <th class="text-center">{{__('tools.records')}}</th>
                                                <th>{{__('tools.processed')}}</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($files as $file)
                                            <tr>
                                                <td><a class="btn btn-link" href="{{ action("SpamCheckController@showRecords", ["id" => $file['id']]) }}"><i class="far fa-eye"></i></a></td>
                                                <td class="text-center">{{$file['id']}}</td>
                                                <td>{{$file['description']}}</td>
                                                <td>{{$file['uploaded_at']}}</td>
                                                <td class="text-center">{{$file['recs']}}</td>
                                                <td>
                                                    @if (empty($file['process_started_at']))
                                                        <button class="btn btn-success" name="action" value="process:{{$file['id']}}">{{__('tools.process')}}</button>
                                                    @elseif (empty($file['processed_at']))
                                                        {{__('tools.in_process')}}
                                                    @else
                                                        {{$file['processed_at']}}
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                            {{ $files->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('shared.notifications_bar')
</div>

<!-- Delete DNC Modal -->
<div class="modal fade" id="deleteDNCModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.confirm_dnc_removal')}}</h4>
            </div>
            <div class="modal-body">
                <h3>{{__('tools.confirm_delete')}}</h3>
            </div>
            <div class="modal-footer">
                <form enctype="multipart/form-data" method="post">
                    @csrf
                    <button class="btn btn-danger" name="action" value=""><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('shared.reportmodal')

@endsection