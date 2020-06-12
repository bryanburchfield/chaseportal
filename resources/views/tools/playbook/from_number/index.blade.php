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
                                @include('tools.playbook.shared.topnav', ['playbook_page' => 'sms_numbers'])

								<div class="tab-pane mt30" id="sms_numbers">
                                    <div class="col-sm-12 nopad">
                                        <div class="table-responsive nobdr sms_numbers mt20">
                                            <a href="#" data-toggle="modal" data-target="#addSMSModal" class="btn btn-primary add_sms_modal"><i class="fas fa-plus-circle"></i> Add Number</a>

                                            <table class="table mt20 table-striped" id="sms_numbers_datatable">
                                                <thead>
                                                    <tr>
                                                        <th>Group</th>
                                                        <th>Number</th>
                                                        <th>Edit</th>
                                                        <th>Delete</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                        <td>0</td>
                                                        <td>+15617258677</td>
                                                        <td>
                                                            <a href="#" data-id="2" data-toggle="modal" data-target="#editSMSModal" class="btn btn-sm btn-info edit_sms_modal fw600"><i class="fas fa-edit"></i> Edit</a>
                                                        </td>
                                                        <td>
                                                            <a href="#" data-number="+15617258677" data-id="2" data-toggle="modal" data-target="#deleteSMSModal" class="btn btn-danger btn-sm delete_sms_modal fw600"><i class="fa fa-trash-alt"></i> Delete</a>
                                                        </td>
                                                    </tr>
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

<!-- Add Playbook Modal -->
<div class="modal fade" id="addSMSModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Add SMS Number</h4>
            </div>
            
            <form action="#" method="post" class="form add_sms_number">
                <div class="modal-body">
                    @include('tools.playbook.from_number.shared.sms_form')
                </div>

                <div class="modal-footer">
                    <img src="/img/loader_hor.gif" alt="" class="img-responsive loader_hor hidetilloaded mt10">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                    <input type="submit" class="btn btn-primary add_sms" value="Add SMS Number">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Playbook Modal -->
<div class="modal fade" id="editSMSModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Edit SMS Number</h4>
            </div>
            
            <form action="#" method="post" class="form edit_sms_number">
                <div class="modal-body">
                    @include('tools.playbook.from_number.shared.sms_form')
                </div>

                <div class="modal-footer">
                    <img src="/img/loader_hor.gif" alt="" class="img-responsive loader_hor hidetilloaded mt10">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                    <input type="submit" class="btn btn-primary edit_sms" value="Edit SMS Number">
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Playbook Modal -->
<div class="modal fade" id="deleteSMSModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Delete SMS Number</h4>
            </div>
            
            <form action="#" method="post" class="form delete_sms_number">
                <div class="modal-body">
                    <h3>{{__('tools.confirm_delete')}} <span></span>?</h3>
                    <input type="hidden" name="id" class="id" value="">
                    <div class="alert alert-danger hidetilloaded mt20"></div>
                </div>

                <div class="modal-footer">
                    <img src="/img/loader_hor.gif" alt="" class="img-responsive loader_hor hidetilloaded mt10">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                    <input type="submit" class="btn btn-danger" value="Delete SMS Number">
                </div>
            </form>
        </div>
    </div>
</div>

@endsection