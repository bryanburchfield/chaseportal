@csrf
<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="{{!empty($email_drip_campaign) ? $email_drip_campaign->name : ''}}" required>
</div>

<div class="form-group">
    <label>{{__('tools.description')}}</label>
    <input type="text" class="form-control description" name="description" value="{{!empty($email_drip_campaign) ? $email_drip_campaign->description : ''}}" required>
</div>

<div class="form-group">
    <label>{{__('tools.subject')}}</label>
    <input type="text" class="form-control subject" name="subject" value="{{!empty($email_drip_campaign) ? $email_drip_campaign->subject : ''}}" required>
</div>

<div class="form-group">
    <label>{{__('tools.from')}}</label>
    <input type="text" class="form-control from" name="from"  value="{{!empty($email_drip_campaign) ? $email_drip_campaign->from : ''}}" required>
</div>

<div class="form-group">
    <label>{{__('tools.campaign')}}</label>
    <select name="campaign" class="form-control campaign drip_campaigns_campaign_menu"  required>
        <option value="">{{__('tools.select_one')}}</option>
        @foreach($campaigns as $key => $value)
            <option {{$key==$email_drip_campaign->campaign ? 'selected' :'' }} value="{{$key}}">{{$value}}</option>
        @endforeach
    </select>
</div>

<div class="form-group">

    <label>{{__('tools.subcampaigns')}}</label>
    {!! Form::select("subcampaigns[]", $subcampaigns, $email_drip_campaign->subcampaigns, ["class" => "form-control multiselect drip_campaigns_subcampaign", 'multiple'=>true]) !!}
</div>

<div class="form-group">
    <label>{{__('tools.email_field')}}</label>
    <select name="email_field" class="form-control email">
        @foreach($email_fields as $key => $value)
            <option {{$key==$email_drip_campaign->email_field ? 'selected' :'' }} value="{{$key}}">{{$key}}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>{{__('tools.templates')}}</label>
    <select name="template_id" class="template_id form-control">
        <option value="">{{__('tools.select_one')}}</option>
        @foreach($templates as $template)
            <option {{$template->id==$email_drip_campaign->template_id ? 'selected' :'' }} value="{{$template->id}}">{{$template->Name}}</option>
        @endforeach
    </select>

</div>

<div class="form-group">
    <label>{{__('tools.provider')}}</label>
    <select name="email_service_provider_id" class="form-control email_service_provider_id" required>
        <option value="">{{__('tools.select_one')}}</option>
        @foreach($email_service_providers as $server)
            <option {{$server->id==$email_drip_campaign->email_service_provider_id ? 'selected' :'' }} value="{{$server->id}}">{{$server->name}}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>{{__('tools.numb_times_can_be_emailed')}}</label>
    <input type="number" class="form-control emails_per_lead" name="emails_per_lead" value="{{!empty($email_drip_campaign) ? $email_drip_campaign->emails_per_lead : ''}}" min="0" max="1000">
</div>

<div class="form-group">
    <label>{{__('tools.numb_days_between_emails')}}</label>
    <input type="number" class="form-control days_between_emails" name="days_between_emails" value="{{!empty($email_drip_campaign) ? $email_drip_campaign->days_between_emails : ''}}" min="0" max="1000">
</div>

<input type="hidden" name="id" class="id" value="{{!empty($email_drip_campaign) ? $email_drip_campaign->id : ''}}">