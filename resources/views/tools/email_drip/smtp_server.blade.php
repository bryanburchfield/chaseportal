<!-- Add/Edit SMTP Server Modal -->
<div class="modal fade" id="{{$mode}}ServerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{ ($mode == 'add') ? 'Add' : 'Edit' }} SMTP Server</h4>
            </div>

            <div class="modal-body">
                <form action="#" method="post" class="form {{$mode}}_smtp_server">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Host</label>
                        <input type="text" class="form-control host" name="host" required>
                    </div>

                    <div class="form-group">
                        <label>Port</label>
                        <input type="text" class="form-control port" name="port" required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control password" name="password" required>
                    </div>

                    <div class="alert alert-success hidetilloaded"></div>
                    <div class="alert alert-danger hidetilloaded"></div>
                    <div class="alert connection_msg hidetilloaded"></div>
                    <input type="submit" class="btn btn-primary {{$mode}}_smtp_server" value="{{ ($mode == 'add') ? 'Add' : 'Edit' }} SMTP Server">
                    <button type="submit" class="btn btn-info test_connection btn_flt_rgt add_btn_loader">Test Connection</button>
                </form>
                <input type="hidden" name="smtp_server_id" id="smtp_server_id" value="">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('general.close')}}</button>
            </div>
        </div>
    </div>
</div>