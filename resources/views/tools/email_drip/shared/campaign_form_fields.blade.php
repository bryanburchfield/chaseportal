<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="{{!empty($campaign) ? $campaign->name : ''}}" required>
</div>

<div class="form-group">
    <label>{{__('tools.description')}}</label>
    <input type="text" class="form-control description" name="description" value="{{!empty($campaign) ? $campaign->description : ''}}" required>
</div>

<div class="form-group">
    <label>{{__('tools.subject')}}</label>
    <input type="text" class="form-control subject" name="subject" value="{{!empty($campaign) ? $campaign->subject : ''}}" required>
</div>

<div class="form-group">
    <label>{{__('tools.from')}}</label>
    <input type="text" class="form-control from" name="from"  value="{{!empty($campaign) ? $campaign->from : ''}}" required>
</div>

<div class="form-group">
    <label>{{__('tools.campaign')}}</label>
    <select name="campaign" class="form-control campaign drip_campaigns_campaign_menu"  required>
        <option value="">{{__('tools.select_one')}}</option>
        @foreach($campaigns as $key => $value)
            <option {{$key==$campaign->campaign ? 'selected' :'' }} value="{{$key}}">{{$value}}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>{{__('tools.subcampaign')}}</label>
    {!! Form::select("subcampaign[]", $campaign['subcampaign'], null, ["class" => "form-control multiselect drip_campaigns_subcampaign", 'multiple'=>true]) !!}
</div>

<div class="form-group">
    <label>{{__('tools.email_field')}}</label>
    <select name="email_field" class="form-control email" required>
        {{-- <option {{$emails_fields->id==$campaign->email_field ? 'selected' :'' }} value="">{{__('tools.select_one')}}</option> --}}
    </select>
</div>

<div class="form-group">
    <label>{{__('tools.templates')}}</label>
    <select name="template_id" class="template_id form-control">
        <option value="">{{__('tools.select_one')}}</option>
        @foreach($templates as $template)
            <option {{$template->id==$campaign->template_id ? 'selected' :'' }} value="{{$template->id}}">{{$template->Name}}</option>
        @endforeach
    </select>

</div>

<div class="form-group">
    <label>{{__('tools.provider')}}</label>
    <select name="email_service_provider_id" class="form-control email_service_provider_id" required>
        <option value="">{{__('tools.select_one')}}</option>
        @foreach($email_service_providers as $server)
            <option {{$server->id==$campaign->email_service_provider_id ? 'selected' :'' }} value="{{$server->id}}">{{$server->name}}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>{{__('tools.numb_times_can_be_emailed')}}</label>
    <input type="number" class="form-control emails_per_lead" name="emails_per_lead" value="{{!empty($campaign) ? $campaign->emails_per_lead : ''}}" min="0" max="1000">
</div>

<div class="form-group">
    <label>{{__('tools.numb_days_between_emails')}}</label>
    <input type="number" class="form-control days_between_emails" name="days_between_emails" value="{{!empty($campaign) ? $campaign->days_between_emails : ''}}" min="0" max="1000">
</div>

<input type="hidden" name="id" class="id" value="{{!empty($campaign) ? $campaign->id : ''}}">