<!-- Add Playbook Modal -->
<div class="modal fade" id="addPlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.add_playbook')}}</h4>
            </div>

            <form action="#" method="post" class="form add_playbook fc_style">
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{__('tools.name')}}</label>
                        <input type="text" class="form-control name" name="name" value="" required>
                    </div>

                    <div class="form-group">
                        <label>{{__('tools.campaign')}}</label>
                        {!! Form::select("campaign", [null=>__('general.select_one')] + $campaigns, null, ["class" => "form-control campaign_select", 'required'=>true]) !!}
                    </div>

                    <div class="dropdown mb20">
                        <label>{{__('tools.extra_campaigns')}}</label>
                        <button class="btn btn-default dropdown-toggle myselect" type="button" id="add_extra_campaigns_menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        {{__('tools.extra_campaigns')}}
                        <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu extra_campaigns stop-propagation" aria-labelledby="add_extra_campaigns_menu"></ul>
                    </div>

                    <div class="dropdown mb20">
                        <label>{{__('tools.subcampaigns')}}</label>
                        <button class="btn btn-default dropdown-toggle myselect" type="button" id="subcampaigns_menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        {{__('tools.subcampaigns')}}
                        <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu subcampaigns stop-propagation" aria-labelledby="subcampaigns_menu"></ul>
                    </div>

                    <a href="#" class="btn add_subcampaign hidetilloaded pl0"><i class="fas fa-plus-circle"></i> Add Subcampaign</a>

                    <div class="alert alert-success hidetilloaded mb0 mt20"></div>
                    <div class="alert alert-danger hidetilloaded mb0 mt20"></div>
                    <div class="alert connection_msg hidetilloaded mb0 mt20"></div>
                </div>

                <div class="modal-footer mb20">
                    <img src="/img/loader_hor.gif" alt="" class="img-responsive loader_hor hidetilloaded mt10">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                    <button type="submit" class="btn btn-primary add_playbook add_btn_loader">{{__('tools.add_playbook')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Playbook Modal -->
<div class="modal fade" id="editPlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.edit_playbook')}}</h4>
            </div>

            <form action="#" method="post" class="form edit_playbook fc_style">
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{__('tools.name')}}</label>
                        <input type="text" class="form-control name" name="name" value="" required>
                    </div>

                    <div class="form-group">
                        <label>{{__('tools.campaign')}}</label>
                        {!! Form::select("campaign", [null=>__('general.select_one')] + $campaigns, null, ["class" => "form-control campaign_select", 'required'=>true]) !!}
                    </div>

                    <div class="dropdown mb20">
                        <label>{{__('tools.extra_campaigns')}}</label>
                        <button class="btn btn-default dropdown-toggle myselect" type="button" id="edit_extra_campaigns_menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        {{__('tools.extra_campaigns')}}
                        <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu extra_campaigns stop-propagation" aria-labelledby="edit_extra_campaigns_menu"></ul>
                    </div>

                   <div class="dropdown mb20">
                        <label>{{__('tools.subcampaigns')}}</label>
                        <button class="btn btn-default dropdown-toggle myselect" type="button" id="subcampaigns_menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        {{__('tools.subcampaigns')}}
                        <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu subcampaigns stop-propagation" aria-labelledby="subcampaigns_menu"></ul>
                    </div>

                    <a href="#" class="btn add_subcampaign hidetilloaded pl0"><i class="fas fa-plus-circle"></i> Add Subcampaign</a>

                    <div class="alert alert-success hidetilloaded mb0 mt20"></div>
                    <div class="alert alert-danger hidetilloaded mb0 mt20"></div>
                    <div class="alert connection_msg hidetilloaded mb0 mt20"></div>
                    <input type="hidden" name="id" class="id" value="">
                </div>

                <div class="modal-footer mb20">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button><button type="submit" class="btn btn-primary edit_playbook add_btn_loader">{{__('tools.save_changes')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Playbook Modal -->
<div class="modal fade" id="deletePlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_playbook')}}</h4>
            </div>

            <div class="modal-body">
                <h3>{{__('tools.confirm_delete')}} <span></span>?</h3>
                <input type="hidden" name="id" class="id" value="">
                <div class="alert alert-danger hidetilloaded mt20"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                <button type="button" class="btn btn-danger delete_playbook"><i class="fa fa-trash-alt"></i> {{__('tools.delete')}}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Touch Modal -->
<div class="modal fade" id="deleteTouchModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_touch')}}</h4>
            </div>

            <form action="#" method="post" class="form delete_touch fc_style">
                <div class="modal-body">
                    <h3>{{__('tools.confirm_delete')}} <span></span> ?</h3>
                    <input type="hidden" name="id" class="id" value="">
                    <input type="hidden" name="playbook_id" class="playbook_id" value="">
                </div>

                <div class="modal-footer">
                    <a href="#" class="btn btn-danger flt_rgt delete_touch fw600"><i class="fas fa-trash-alt"></i> {{__('tools.delete_touch')}}</a>
                    <button type="button" class="btn btn-secondary flt_rgt mr10" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>