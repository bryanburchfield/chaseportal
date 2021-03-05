<input type="hidden" class="open_kpi_id" name="open_kpi_id" value="{{ old('kpi_id') }}">

<div class="container-full mt50">
    <div class="row">
        <div class="col-sm-12">
            <h2>{{__('kpi.kpi_notifications')}}</h2>
        </div>
    </div>

    <div class="row">
        @foreach(\App\Models\Kpi::getKpis() as $kpi)

        <div class="col-sm-12 opt" data-kpi="{{ $kpi->id }}">
            <a href="#" class="kpi_trigger float-left"> {{ __('kpi.' . $kpi->name) }}</a>

            <div class="controls flt_rgt">
                <a href="https://webdev.chasedatacorp.com/kpi/crons/cron_{{ $kpi->id}}.php?interval={{ $kpi->interval }}" class="run_kpi btn btn-default btn-sm float-left"><span class="glyphicon glyphicon-flash"></span> {{__('kpi.run_now')}}</a>
                <label class="switch flt_rgt">
                    <input type="checkbox" {{ ($kpi->active) ? 'checked' : '' }} name="kpi_input">
                    <span></span>
                </label>
            </div>

            <div class="kpi cb hidetilloaded">
                <p>{{ __('kpi.desc_' . $kpi->name) }}</p>
                <div class="row mt-3 options kpi_options_top">
                    <div class="col-sm-4">
                        <h4 class="expand_dets float-left"><i class="glyphicon glyphicon-wrench exp"></i> {{__('kpi.options')}}</h4>
                        <div class="expanded_options clear card">
                            <form data-kpi="{{ $kpi->id }}" action="#" method="post" class="form adjust_interval fc_style">
                                <div class="form-group">
                                    <label for="type">{{__('kpi.interval')}}</label>
                                    <select name="interval" class="form-control interval"  required>
                                        <option {!! ($kpi->interval == '1440') ? ' selected="selected"' : '' !!} value="1440">{{__('kpi.one_a_day')}}</option>
                                        <option {!! ($kpi->interval == '720') ? ' selected="selected"' : '' !!} value="720">{{__('kpi.twice_a_day')}}</option>
                                        <option {!! ($kpi->interval == '60') ? ' selected="selected"' : '' !!} value="60">{{__('kpi.hourly')}}</option>
                                        <option {!! ($kpi->interval == '30') ? ' selected="selected"' : '' !!} value="30">{{__('kpi.every_30')}}</option>
                                        <option {!! ($kpi->interval == '15') ? ' selected="selected"' : '' !!} value="15">{{__('kpi.every_15')}}</option>
                                    </select>
                                </div>
                                <input type="submit" class="btn btn-primary btn-md mb0" value="{{__('general.save')}}">
                            </form>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <h4 class="expand_dets float-left"><i class="glyphicon glyphicon-envelope"></i> {{__('kpi.recipients')}}</h4>
                        <div class="expanded_emails clear">

                        @forelse($kpi->recipients as $r)
                            <div class="user clear" id="{{ $r->id }}">
                                <p class="name"><span class="name">{{ $r->name }}</span>
                                    @if($r->email)
                                    <i class="fas fa-envelope"></i>
                                    @endif
                                    @if($r->phone)
                                    <i class="fas fa-sms"></i>
                                    @endif
                                </p>
                                @if(!Auth::user()->isType('demo') || $r->user_id == Auth::user()->id)
                                    <a class="edit_recip_glyph float-left" data-toggle="modal" data-target="#editRecipModal" href="#" data-recip="{{ $r->recipient_id }}" data-userid="{{$r->id}}" data-username="{{$r->name}}"><i class="fas fa-user-edit"></i></a>
                                    <a data-toggle="modal" data-username="{{$r->name}}" data-target="#deleteRecipModal" class="remove_recip_glyph float-left" href="#" data-kpi="{{ $kpi->id }}" data-recip="{{ $r->id }}"><i class="fas fa-trash-alt"></i></a>
                                @endif
                            </div>
                        @empty
                            <div class="alert alert-info"> {{__('kpi.no_recips')}}</div>
                        @endforelse
                        </div>
                    </div>
                </div><!-- end options row -->
            </div>
        </div><!-- end col 12 -->
        @endforeach
    </div><!-- end row -->
</div>

<!-- Delete Recipient Modal -->
<div class="modal fade" id="deleteRecipModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">{{__('kpi.confirm_recipient_removal')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" class="user_id" name="user_id" value="">
                <input type="hidden" class="name" name="name" value="">
                <input type="hidden" class="fromall" name="fromall" value="0">
                <input type="hidden" class="kpi_id" name="kpi_id" value="">
               <h3>{{__('kpi.are_you_sure')}} <span class="username"></span> {{__('kpi.from_this_kpi')}}?</h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{__('general.cancel')}}</button>
                <button type="button" class="btn btn-danger remove_recip">{{__('kpi.remove_recipient')}}</button>
            </div>
        </div>
    </div>
</div>


@include('shared.editrecipmodal')
