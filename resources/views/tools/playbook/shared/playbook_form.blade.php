<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="" required>
</div>

<div class="form-group">
    <label>{{__('tools.campaign')}}</label>
    {!! Form::select("source_campaign", [null=>__('general.select_one')] + $campaigns, null, ["class" => "form-control", 'id'=> 'campaign_select', 'required'=>true]) !!}
</div>

<div class="form-group">
    <label>{{__('tools.subcampaign')}}</label>
    <select name="subcampaign" class="form-control subcampaign"></select>
</div>

<div class="form-group">
    <label class="checkbox-inline"><input type="checkbox" name="active" value="">Active</label>
</div>

<div class="alert alert-success hidetilloaded"></div>
<div class="alert alert-danger hidetilloaded"></div>
<div class="alert connection_msg hidetilloaded"></div>