<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="" required>
</div>

<div class="form-group">
    <label>{{__('tools.campaign')}}</label>
    {!! Form::select("campaign", [null=>__('general.select_one')] + $campaigns, null, ["class" => "form-control", 'id'=> 'campaign_select', 'required'=>true]) !!}
</div>

<div class="form-group">
    <label class="flt_lft">{{__('tools.subcampaign')}} </label>
    <a href="#" class="remove_subcamp flt_rgt mt0 hidetilloaded"><i class="fa fa-trash-alt"></i> Remove</a>
    <select name="subcampaign" class="form-control subcampaigns"></select>
</div>

<a href="#" class="btn add_subcampaign hidetilloaded pl0"><i class="fas fa-plus-circle"></i> Add Subcampaign</a>

<div class="alert alert-success hidetilloaded mb0 mt20"></div>
<div class="alert alert-danger hidetilloaded mb0 mt20"></div>
<div class="alert connection_msg hidetilloaded mb0 mt20"></div>

