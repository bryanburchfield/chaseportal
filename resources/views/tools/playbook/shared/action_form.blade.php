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

<div class="col-sm-6 action_types_div">
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

<div class="email hidetilloaded action_type_fields">
    <div class="form-group">
        <label>Email Field</label>
        <input type="text" class="form-control email_field" name="email_field">
    </div>

    <div class="form-group">
        <label>Days Between Emails</label>
        <input type="text" class="form-control days_between_emails" name="days_between_emails">
    </div>

    <div class="form-group">
        <label>Emails Per Lead</label>
        <input type="text" class="form-control emails_per_lead" name="emails_per_lead">
    </div>

    <div class="form-group">
        <label>From</label>
        <input type="text" class="form-control from" name="from">
    </div>
</div>

<div class="sms hidetilloaded action_type_fields">
    <div class="form-group">
        <label>To Campaign</label>
        <input type="text" class="form-control from_number" name="from_number">
    </div>

    <div class="form-group">
        <label>Message</label>
        <textarea name="message" rows="8" class="form-control message"></textarea>
    </div>
</div>

<div class="lead hidetilloaded action_type_fields">
    <div class="form-group">
        <label>To Campaign</label>
        <select name="to_campaign" class="form-control to_campaign"></select>
    </div>

    <div class="form-group">
        <label>To SubCampaign</label>
        <select name="to_subcampaign" class="form-control to_subcampaign"></select>
    </div>

    <div class="form-group">
        <label>Call Status</label>
        <select name="call_status" class="form-control call_status"></select>
    </div>
</div>

<div class="alert alert-success hidetilloaded"></div>
<div class="alert alert-danger hidetilloaded"></div>
<div class="alert connection_msg hidetilloaded"></div>