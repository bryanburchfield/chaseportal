<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="" required>
</div>

<div class="col-sm-6 filter_campaigns_div pl0">
    <label>{{__('tools.campaign')}}</label>
    <div class="form-group">
        {!! Form::select("campaign", [null=>__('general.select_one')] + $campaigns, old('campaign'), ["class" => "form-control filter_campaigns", 'id'=> 'update_campaign_select', 'required'=>true]) !!}
    </div>
</div>

<div class="col-sm-6 filter_fields_div pr0">
    <label>{{__('tools.field')}}</label>
    <div class="form-group">
        <select class="form-control filter_fields" name="fields[]" data-type="field">
        </select>
    </div>
</div>

<div class="col-sm-6 filter_operators_div pl0">
    <label>{{__('tools.operator')}}</label>
    <div class="form-group">
        <select class="form-control filter_operators" name="operators[]" data-type="operator">
        </select>
    </div>
</div>

<div class="col-sm-6 filter_values_div pr0">
    <label>{{__('tools.value')}}</label>
    <input type="text" class="form-control filter_value" name="filter_values[]" data-type="value" value="">
</div>

<div class="alert alert-success hidetilloaded"></div>
<div class="alert alert-danger hidetilloaded"></div>
<div class="alert connection_msg hidetilloaded"></div>