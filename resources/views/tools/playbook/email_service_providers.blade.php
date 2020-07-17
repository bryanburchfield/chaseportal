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
			    	<div class="col-sm-12">                        
			    		<div class="tab-content">
                            <div class="tab-pane active">
                                <h2 class="bbnone">{{__('tools.contacts_playbook')}}</h2>
                                @include('tools.playbook.shared.topnav', ['playbook_page' => 'providers'])

                                <div class="tab-pane mt30" id="email_service_providers">
                                    <div class="col-sm-12 nopad">
                                        <a href="#" data-toggle="modal" data-target="#addESPModal" class="btn btn-primary add_provider_modal"><i class="fas fa-plus-circle"></i> {{__('tools.add_provider')}}</a>

                                        <div class="table-responsive nobdr email_service_providers">
                                            <table class="table mt20 table-striped" id="emails_dataTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('tools.name')}}</th>
                                                        <th>{{__('tools.provider_type')}}</th>
                                                        <th>{{__('tools.edit')}}</th>
                                                        <th>{{__('tools.delete')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(count($email_service_providers))
                                                        @foreach($email_service_providers as $provider)
                                                            <tr>
                                                                <td>{{$provider->name}}</td>
                                                                <td>{{Str::studly($provider->provider_type)}}</td>
                                                                <?php $mode='edit';?>
                                                                <td><a href="#" data-toggle="modal" data-target="#editESPModal" class=" edit_provider_modal btn btn-sm btn-info fw600 table_btns" data-providerid="{{$provider->id}}"><i class="fas fa-edit"></i> {{__('tools.edit')}}</a></td>
                                                                <td><a class="remove_email_service_provider_modal btn btn-sm btn-danger fw600 table_btns" data-toggle="modal" data-target="#deleteESPModal" href="#" data-name="{{$provider->name}}" data-id="{{$provider->id}}"><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</a></td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
			    	</div>
				</div>
			</div>
		</div>
	</div>

    @include('shared.notifications_bar')
</div>

@include('shared.reportmodal')

<!-- Add Provider Modal -->
<div class="modal fade" id="addESPModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.add_provider')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form add_esp fc_style">
                    @include('tools.playbook.shared.email_service_provider_form')
                    <button type="submit" class="btn btn-primary add_esp add_btn_loader">{{__('tools.add_provider')}}</button>
                    <button type="submit" class="btn btn-info test_connection btn_flt_rgt add_btn_loader">{{__('tools.test_connection')}}</button>
                </form>
                <input type="hidden" name="email_service_provider_id" id="email_service_provider_id" value="">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Provider Modal -->
<div class="modal fade" id="editESPModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.edit_provider')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form edit_esp fc_style">
                    @include('tools.playbook.shared.email_service_provider_form')
                    <input type="hidden" name="id" class="id" value="">
                    <button type="submit" class="btn btn-primary edit_esp add_btn_loader">{{__('tools.save_changes')}}</button>
                    <button type="submit" class="btn btn-info test_connection btn_flt_rgt add_btn_loader">{{__('tools.test_connection')}}</button>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Provider Modal -->
<div class="modal fade" id="deleteESPModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_provider')}}</h4>
            </div>

            <div class="modal-body">
                <h3>{{__('tools.confirm_delete')}} <span></span>?</h3>
                <input type="hidden" name="id" class="id" value="">
                <div class="alert alert-danger hidetilloaded mt20"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                <button type="button" class="btn btn-danger delete_email_service_provider add_btn_loader"><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</button>
            </div>
        </div>
    </div>
</div>

@endsection