<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="" required>
</div>

<div class="campaign"></div>

<div class="col-sm-3 action_operators_div">
    <label>{{__('tools.action_type')}}</label>
    <div class="form-group">
        <select class="form-control action_operators" name="action_operators[]" data-type="operator">
        </select>
    </div>
</div>

<div class="alert alert-success hidetilloaded"></div>
<div class="alert alert-danger hidetilloaded"></div>
<div class="alert connection_msg hidetilloaded"></div>