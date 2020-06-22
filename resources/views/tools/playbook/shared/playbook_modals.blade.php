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
                    @include('tools.playbook.shared.playbook_form')
                </div>

                <div class="modal-footer">
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
                    @include('tools.playbook.shared.playbook_form')
                    <input type="hidden" name="id" class="id" value="">
                </div>

                <div class="modal-footer">
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
                    <input type="hidden" name="playbook_id" class="playbook_id" value="{{$contacts_playbook->id}}">
                </div>

                <div class="modal-footer">
                    <a href="#" class="btn btn-danger flt_rgt delete_touch fw600"><i class="fas fa-trash-alt"></i> {{__('tools.delete_touch')}}</a>
                    <button type="button" class="btn btn-secondary flt_rgt mr10" data-dismiss="modal"><i class="fas fa-ban"></i>  {{__('general.cancel')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>