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
        <label>{{__('tools.provider')}}</label>
        <select name="email_service_provider_id" class="form-control email_service_provider_id">
            <option value="">{{__('tools.select_one')}}</option>
            @foreach($email_service_providers as $server)
                <option {{$server->id==old('email_service_provider_id') ? 'selected' :'' }} value="{{$server->id}}">{{$server->name}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>{{__('tools.email_template')}}</label>
        <select name="template_id" class="template_id form-control">
            <option value="">{{__('tools.select_one')}}</option>
            @foreach($email_templates as $template)
                <option {{$template->id==old('template_id') ? 'selected' :'' }} value="{{$template->id}}">{{$template->Name}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>{{__('tools.email_field')}}</label>
        <select name="email_field" class="form-control email_field"></select>
    </div>

    <div class="form-group">
        <label>{{__('tools.subject')}}</label>
        <input type="text" class="form-control subject" name="subject">
    </div>

    <div class="form-group">
        <label>{{__('tools.from')}}</label>
        <input type="text" class="form-control from" name="from">
    </div>

    <div class="form-group">
        <label>{{__('tools.days_between_emails')}}</label>
        <input type="text" class="form-control days_between_emails" name="days_between_emails">
    </div>

    <div class="form-group">
        <label>{{__('tools.emails_per_lead')}}</label>
        <input type="text" class="form-control emails_per_lead" name="emails_per_lead">
    </div>
</div>

<div class="sms hidetilloaded action_type_fields">
    <div class="form-group">
        <label>{{__('tools.from_number')}}</label>
        <input type="text" class="form-control from_number" name="from_number">
    </div>

    <div class="form-group">
        <label>{{__('tools.sms_template')}}</label>
        <select name="template_id" class="template_id form-control">
            <option value="">{{__('tools.select_one')}}</option>
            @foreach($sms_templates as $template)
                <option {{$template->id==old('template_id') ? 'selected' :'' }} value="{{$template->id}}">{{$template->Name}}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label>{{__('tools.sms_per_lead')}}</label>
        <input type="text" class="form-control sms_per_lead " name="sms_per_lead ">
    </div>

    <div class="form-group">
        <label>{{__('tools.days_between_sms')}}</label>
        <input type="text" class="form-control days_between_sms" name="days_between_sms">
    </div>
</div>

<div class="lead hidetilloaded action_type_fields">
    <div class="form-group">
        <label>{{__('tools.to_campaign')}}</label>
        <div class="form-group">
            {!! Form::select("to_campaign", [null=>__('general.select_one')] + $campaigns, old('campaign'), ["class" => "form-control to_campaign", 'required'=>false]) !!}
        </div>
    </div>

    <div class="form-group">
        <label>{{__('tools.to_subcampaign')}}</label>
        <select name="to_subcampaign" class="form-control to_subcampaign"></select>
    </div>

    <div class="form-group">
        <label>{{__('tools.to_callstatus')}}</label>
        <select name="to_callstatus" class="form-control call_status"></select>
    </div>
</div>

<div class="alert alert-success hidetilloaded cb"></div>
<div class="alert alert-danger hidetilloaded cb"></div>
<div class="alert connection_msg hidetilloaded cb"></div>