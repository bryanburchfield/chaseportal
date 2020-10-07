<div class="form-group">
    <label>{{__('tools.name')}}</label>
    <input type="text" class="form-control name" name="name" value="" required>
</div>

<div class="col-sm-6 filter_campaigns_div pl0">
    <label>{{__('tools.campaign')}}</label>
    <div class="form-group">
        {!! Form::select("campaign", [null=>__('general.select_one')] + $campaigns, null, ["class" => "form-control filter_campaigns", 'required'=>false]) !!}
    </div>
</div>

<div class="col-sm-6 filter_fields_div pr0">
    <label>{{__('tools.field')}}</label>
    <div class="form-group">
        <select class="form-control filter_fields" name="field" data-type="field">
            <option value>{{__('general.select_one')}}</option>
            @foreach ($fields as $name => $type)
                <option data-type="{{$type}}" value="{{$name}}">{{$name}}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="col-sm-6 filter_operators_div pl0">
    <label>{{__('tools.operator')}}</label>
    <div class="form-group">
        <select class="form-control filter_operators" name="operator" data-type="operator">
        </select>
    </div>
</div>

<div class="col-sm-6 filter_values_div pr0">
    <label>{{__('tools.value')}}</label>
    <input type="text" class="form-control filter_value" name="value" data-type="value" value="">
</div>

<div class="alert alert-success hidetilloaded cb"></div>
<div class="alert alert-danger hidetilloaded cb"></div>