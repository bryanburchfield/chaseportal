<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="" required>
</div>

<div class="col-sm-6 filter_campaigns_div pl0">
    <label>{{__('tools.campaign')}}</label>
    <div class="form-group">
        {!! Form::select("campaign", [null=>__('general.select_one')] + $campaigns, old('campaign'), ["class" => "form-control filter_campaigns", 'id'=> 'update_campaign_select', 'required'=>false]) !!}
    </div>
</div>

<div class="action_types_div">
    <label>{{__('tools.action_type')}}</label>
    <div class="form-group">
        <select class="form-control action_types" name="action_type" data-type="type">
            <option value>{{__('general.select_one')}}</option>
            <option value="email">{{__('tools.email')}}</option>
            <option value="sms">{{__('tools.sms')}}</option>
            <option value="lead">{{__('tools.lead_update')}}</option>
        </select>
    </div>
</div>

<div class="alert alert-success hidetilloaded"></div>
<div class="alert alert-danger hidetilloaded"></div>
<div class="alert connection_msg hidetilloaded"></div>