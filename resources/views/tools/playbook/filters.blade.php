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
                                @include('tools.playbook.shared.topnav', ['playbook_page' => 'filters'])

								<div class="tab-pane mt30" id="playbook_filters">
                                    <div class="col-sm-12 nopad">
                                        <a href="#" data-toggle="modal" data-target="#addFilterModal" class="btn btn-primary add_playbook_filter_modal"><i class="fas fa-plus-circle"></i> {{__('tools.add_filter')}}</a>

                                        <div class="table-responsive nobdr filters_table">
                                            <table class="table mt20 table-striped" id="filters_dataTable">
                                                <thead>
                                                    <tr>
                                                        <th>{{__('tools.name')}}</th>
                                                        <th>{{__('tools.campaign')}}</th>
                                                        <th>{{__('tools.field')}}</th>
                                                        <th>{{__('tools.operator')}}</th>
                                                        <th>{{__('tools.filter_value')}}</th>
                                                        <th>{{__('tools.edit')}}</th>
                                                        <th>{{__('tools.delete')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(count($playbook_filters))
                                                        @foreach($playbook_filters as $playbook_filter)
                                                            <tr>
                                                                <td>{{$playbook_filter->name}}</td>
                                                                <td>{{$playbook_filter->campaign}}</td>
                                                                <td>{{$playbook_filter->field}}</td>
                                                                <td>{{$playbook_filter->operator_name}}</td>
                                                                <td>{{$playbook_filter->value}}</td>
                                                                <?php $mode='edit';?>
                                                                <td><a href="#" data-toggle="modal" data-target="#editFilterModal" class=" edit_playbook_filter_modal  btn btn-sm btn-info fw600 table_btns" data-id="{{$playbook_filter->id}}" data-name="{{$playbook_filter->name}}"><i class="fas fa-edit"></i> {{__('tools.edit')}}</a></td>
                                                                <td><a class="remove_playbook_filter_modal btn btn-sm btn-danger fw600 table_btns" data-toggle="modal" data-target="#deleteFilterModal" href="#" data-name="{{$playbook_filter->name}}" data-id="{{$playbook_filter->id}}"><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</a></td>
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

<!-- Add Filter Modal -->
<div class="modal fade" id="addFilterModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.add_filter')}}</h4>
            </div>
            <form method="post" class="form add_filter fc_style">
                <div class="modal-body">
                    @include('tools.playbook.shared.filter_form')
                </div>

                <div class="modal-footer">
                    <img src="/img/loader_hor.gif" alt="" class="img-responsive loader_hor hidetilloaded mt10">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                    <button type="submit" class="btn btn-primary add_filter add_btn_loader">{{__('tools.add_filter')}}</button>
                </div>
             </form>
        </div>
    </div>
</div>

<!-- Edit Filter Modal -->
<div class="modal fade" id="editFilterModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.edit_filter')}}</h4>
            </div>

            <div class="modal-body">
                <h3><span></span></h3><br>
                <form action="#" method="post" class="form edit_filter fc_style">
                    @method('PATCH')
                    @include('tools.playbook.shared.filter_form')
                    <input type="hidden" name="id" class="id" value="{{old('id')}}">
                </form>
            </div>

            <div class="modal-footer">
                <img src="/img/loader_hor.gif" alt="" class="img-responsive loader_hor hidetilloaded mt10">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                <button type="submit" class="btn btn-primary update_filter add_btn_loader">{{__('tools.save_changes')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Filter Modal -->
<div class="modal fade" id="deleteFilterModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_filter')}}</h4>
            </div>

            <div class="modal-body">
                <h3>{{__('tools.confirm_delete')}} <span></span>?</h3>
                <input type="hidden" name="id" class="id" value="{{old('id')}}">
                <div class="alert alert-danger hidetilloaded mt20"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                <button type="button" class="btn btn-danger delete_playbook_filter add_btn_loader"><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</button>
            </div>
        </div>
    </div>
</div>

@endsection