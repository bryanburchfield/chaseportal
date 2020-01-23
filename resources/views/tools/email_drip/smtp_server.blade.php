
<h2 class="page_heading"><i class="fas fa-server"></i> {{ ($mode == 'add') ? 'Add' : 'Edit' }} SMTP Server</h2>

<div class="card">
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
        <input type="submit" class="btn btn-primary" value="Add SMTP Server">
        <button type="submit" class="btn btn-info test_connection btn_flt_rgt add_btn_loader">Test Connection</button>
    </form>
</div>