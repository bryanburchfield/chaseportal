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
			    		@include('tools.shared.topnav', ['toolpage' => 'email_drip'])

			    		<div class="tab-content">
                            <div class="tab-pane active mt30">
                                <h2 class="bbnone">{{__('tools.email_drip_builder')}}</h2>
                                <ul class="nav nav-tabs tabs tools_subnav">
                                    <li class="active"><a href="#drip_campaigns" data-toggle="tab">{{__('tools.email_drip_campaigns')}}</a></li>
                                    <li><a href="#smtp_servers" data-toggle="tab">{{__('tools.smtp_servers')}}</a></li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane active mt30" id="drip_campaigns">
                                        <div class="col-sm-12 nopad">
											<a href="#" class="btn btn-primary create_new_drip" data-toggle="modal" data-target="#createCampaignModal">{{__('tools.create_campaign')}}</a>
                                        	<div class="table-responsive nobdr drip_campaigns">
                                        		<table class="table mt20">
                                        			<thead>
                                        				<tr>
                                        					<th>asdf</th>
                                        					<th>asdf</th>
                                        					<th>asdfasdf</th>
                                        					<th>asdf</th>
                                        					<th>sadfsadfds</th>
                                        				</tr>
                                        			</thead>

                                        			<tbody>
                                        				<tr>
                                        					<td></td>
                                        					<td></td>
                                        					<td></td>
                                        					<td></td>
                                        					<td></td>
                                        				</tr>
                                        			</tbody>
                                        		</table>
                                        	</div>
                                        </div>
                                    </div>

                                    <div class="tab-pane mt30" id="smtp_servers">
                                        <div class="col-sm-12 nopad">
                                            <a href="#" data-toggle="modal" data-target="#addServerModal" class="btn btn-primary add_server_modal">{{__('tools.add_server')}}</a>

                                            <div class="table-responsive nobdr smtp_servers">
                                                <table class="table mt20">
                                                    <thead>
                                                        <tr>
                                                            <th>{{__('tools.name')}}</th>
                                                            <th>{{__('tools.host')}}</th>
                                                            <th>{{__('tools.port')}}</th>
                                                            <th>{{__('tools.username')}}</th>
                                                            <th>{{__('tools.edit')}}</th>
                                                            <th>{{__('tools.delete')}}</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @if(count($smtp_servers))
                                                            @foreach($smtp_servers as $server)
                                                                <tr>
                                                                    <td>{{$server->name}}</td>
                                                                    <td>{{$server->host}}</td>
                                                                    <td>{{$server->port}}</td>
                                                                    <td>{{$server->username}}</td>
                                                                    <?php $mode='edit';?>
                                                                    <td><a href="#" data-toggle="modal" data-target="#editServerModal" class=" edit_server_modal" data-serverid="{{$server->id}}"><i class="fas fa-edit"></i></a></td>
                                                                    <td><a class="remove_smtp_server_modal" data-toggle="modal" data-target="#deleteSmtpServerModal" href="#" data-servername="{{$server->name}}" data-serverid="{{$server->id}}"><i class="fa fa-trash-alt"></i></a></td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <div class="alert alert-info">{{__('tools.no_servers_added')}}</div>
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
	</div>
</div>

@include('shared.reportmodal')

<!-- Add SMTP Server Modal -->
<div class="modal fade" id="addServerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.add_server')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form add_smtp_server">
                    @include('tools.email_drip.smtp_server')
                    <input type="submit" class="btn btn-primary add_smtp_server" value="{{__('tools.add_server')}}">
                    <button type="submit" class="btn btn-info test_connection btn_flt_rgt add_btn_loader">{{__('tools.test_connection')}}</button>
                </form>
                <input type="hidden" name="smtp_server_id" id="smtp_server_id" value="">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit SMTP Server Modal -->
<div class="modal fade" id="editServerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.edit_server')}}</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form edit_smtp_server">
                    @include('tools.email_drip.smtp_server')
                    <input type="hidden" name="id" class="id" value="">
                    <input type="submit" class="btn btn-primary edit_smtp_server" value="{{__('tools.save_changes')}}">
                    <button type="submit" class="btn btn-info test_connection btn_flt_rgt add_btn_loader">{{__('tools.test_connection')}}</button>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Campaign Modal -->
<div class="modal fade" id="createCampaignModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.create_drip_campaign')}}</h4>
            </div>

            <div class="modal-body">
            </div>

	        <div class="modal-footer">
	            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
	        </div>
	    </div>
    </div>
</div>

<!-- Delete SMTP Server Modal -->
<div class="modal fade" id="deleteSmtpServerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_server')}}</h4>
            </div>

            <div class="modal-body">
                <h3>{{__('tools.confirm_delete')}} <span></span></h3>
                <input type="hidden" name="smtp_server_id" id="smtp_server_id" value="">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
                <button type="button" class="btn btn-danger delete_smtp_server">{{__('tools.delete')}}</button>
            </div>
        </div>
    </div>
</div>


@endsection