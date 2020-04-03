<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="" required>
</div>

<div class="campaign"></div>

<div class="field"></div>

<div class="col-sm-3 filter_operators_div">
    <label>Operator</label>
    <div class="form-group">
        <select class="form-control filter_operators" name="filter_operators[]" data-type="operator">
        </select>
    </div>
</div>

<div class="col-sm-3 filter_values_div">
    <label>Value</label>
    <input type="text" class="form-control filter_value" name="filter_values[]" data-type="value" value="">
</div>

<div class="alert alert-success hidetilloaded"></div>
<div class="alert alert-danger hidetilloaded"></div>
<div class="alert connection_msg hidetilloaded"></div>