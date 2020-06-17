<!-- Edit Playbook Modal -->
<div class="modal fade" id="editPlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.edit_playbook')}}</h4>
            </div>

            <form action="#" method="post" class="form edit_playbook">
                <div class="modal-body">
                    @include('tools.playbook.shared.playbook_form')
                    <input type="hidden" name="id" class="id" value="{{empty($contacts_playbook->id)}}">
                </div>

                <div class="modal-footer">
                    <a href="#" class="btn btn-danger flt_lft delete_playbook_modal fw600" data-id="{{empty($contacts_playbook->id)}}" data-toggle="modal" data-target="#deletePlaybookModal"><i class="fas fa-trash-alt"></i> {{__('tools.delete_playbook')}}</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button><button type="submit" class="btn btn-primary edit_playbook add_btn_loader">{{__('tools.save_changes')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE Playbook Modal -->
<div class="modal fade" id="deletePlaybookModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_playbook')}}</h4>
            </div>

            <form action="#" method="post" class="form delete_playbook">
                <div class="modal-body">
                    <h3>{{__('tools.confirm_delete')}} {{empty($contacts_playbook->name)}} ?</h3>
                    <input type="hidden" name="id" class="id" value="{{empty($contacts_playbook->id)}}">
                </div>

                <div class="modal-footer">
                    <a href="#" class="btn btn-danger flt_rgt delete_playbook fw600"><i class="fas fa-trash-alt"></i> {{__('tools.delete_playbook')}}</a>
                    <button type="button" class="btn btn-secondary flt_rgt mr10" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DELETE Touch Modal -->
<div class="modal fade" id="deleteTouchModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{__('tools.delete_playbook')}}</h4>
            </div>

            <form action="#" method="post" class="form delete_playbook">
                <div class="modal-body">
                    <h3>{{__('tools.confirm_delete')}} <span></span> ?</h3>
                    <input type="hidden" name="id" class="id" value="">
                </div>

                <div class="modal-footer">
                    <a href="#" class="btn btn-danger flt_rgt delete_touch fw600"><i class="fas fa-trash-alt"></i> {{__('tools.delete_touch')}}</a>
                    <button type="button" class="btn btn-secondary flt_rgt mr10" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>