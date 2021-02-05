@extends('layouts.master')
@section('title', 'Spam Check')

@section('content')

<div class="preloader"></div>

<div class="wrapper">

    @include('shared.sidenav')

    <div id="content">
        @include('shared.navbar')

        <div class="container-fluid bg dashboard p20">
            <div class="container-full mt50">
                <div class="row">
                    <div class="col-sm-12">

                        <div class="tab-pane" id="spam_check">
                            @if ($message = Session::get('flash'))
                            <div class="alert alert-info alert-block">
                                <button type="button" class="close" aria-label="Close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                                <strong>{{ $message }}</strong>
                            </div>
                            @endif

                            <h2 class="bbnone mb20">Spam Check Numbers</h2>

                            <div class="col-sm-4">
                                <form method="POST" action="{{action('SpamCheckController@submitNumber')}}" class="form">
                                    @csrf

                                    <div class="form-group">
                                        <label>Check a single number:</label>
                                       <input type="text" name="phone" class="form-control" value="{{old('phone')}}">
                                    </div>

                                    <input class="btn btn-primary btn-md mb20" type="submit" value="{{__('general.submit')}}" />

                                    @if($errors->any())
                                        <div class="alert alert-danger mt20">
                                            @foreach ($errors->all() as $error)
                                                <p>{{ $error }}</p>
                                            @endforeach
                                        </div>
                                    @endif
                                </form>
                            </div>

                            <div class="card instructions cb">
								<a href="#" class="toggle_instruc flt_rgt"><i class="fas fa-angle-up"></i></a>
								<h3><b>{{__('tools.instructions')}}</b></h3>
								<div class="mt20 instuc_div">
									<ul class="pl10 paditem5">
                                        <li>Upload a file to the portal. This only stages the file.</li>
                                        <li>Review the contents to ensure it has the number of records you were expecting, and view any errors.</li>
                                        <li>At this point, you will either Delete the file (so that you can correct and re-upload it) or Process the file.</li>
                                        <li>Processing starts the spam checking process. This will run in the background and may take some time to complete.</li>
									</ul>
								</div>
							</div>

                            <a class="btn btn-primary" href="{{ action('SpamCheckController@uploadIndex') }}">{{__('tools.upload_new_file')}}</a>

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
                                                <th class="text-center">{{__('tools.errors')}}</th>
                                                <th class="text-center">Flagged</th>
                                                <th>{{__('tools.processed')}}</th>
                                                <th class="text-center">Delete</th>
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
                                                @if ($file['errors'] > 0)
													<td><a class="btn btn-link danger text-center" href="{{ action("SpamCheckController@showErrors", ["id" => $file['id']]) }}">{{$file['errors']}}</a></td>
												@else
													<td class="text-center">{{$file['errors']}}</td>
                                                @endif
                                                @if ($file['flags'] > 0)
													<td><a class="btn btn-link danger text-center" href="{{ action("SpamCheckController@showFlags", ["id" => $file['id']]) }}">{{$file['flags']}}</a></td>
												@else
													<td class="text-center">{{$file['flags']}}</td>
												@endif
                                                <td>
                                                    @if (empty($file['process_started_at']))
                                                        <button class="btn btn-success" name="action" value="process:{{$file['id']}}">{{__('tools.process')}}</button>
                                                    @elseif (empty($file['processed_at']))
                                                        {{__('tools.in_process')}}
                                                    @else
                                                        {{$file['processed_at']}}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (empty($file['process_started_at']))
                                                    <a class="btn btn-danger delete_file" data-toggle="modal" data-target="#deleteFileModal" href="#" data-id="{{$file['id']}}"><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</a>
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

<!-- Delete File Modal -->
<div class="modal fade" id="deleteFileModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Confirm File Removal</h4>
            </div>
            <div class="modal-body">
                <h3>{{__('tools.confirm_delete')}} <span class="id"></span>?</h3>
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