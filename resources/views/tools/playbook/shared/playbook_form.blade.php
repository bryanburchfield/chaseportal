<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="" required>
</div>

<div class="form-group">
    <label>{{__('tools.campaign')}}</label>
    {!! Form::select("campaign", [null=>__('general.select_one')] + $campaigns, null, ["class" => "form-control", 'id'=> 'campaign_select', 'required'=>true]) !!}
</div>

<div class="form-group">
    <label>{{__('tools.subcampaign')}}</label>
    <select name="subcampaign" class="form-control subcampaign"></select>
</div>

<div class="alert alert-success hidetilloaded mb0"></div>
<div class="alert alert-danger hidetilloaded mb0"></div>
<div class="alert connection_msg hidetilloaded mb0"></div>

<img src="/img/loader_hor.gif" alt="" class="img-responsive loader_hor hidetilloaded mt20 flt_lft">