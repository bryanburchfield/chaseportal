<input type="hidden" class="open_kpi_id" name="open_kpi_id" value="{{ old('kpi_id') }}">

<div class="container-full mt20">
    <div class="row">
        <div class="col-sm-12">
            <h2>KPI Notifications</h2>
        </div>

        @foreach(\App\Kpi::getKpis() as $kpi)

        <div class="col-sm-12 opt" data-kpi="{{ $kpi->id }}">
            <a href="#" class="kpi_trigger"> {{ $kpi->name }}</a>

            <div class="controls">
                <a href="https://webdev.chasedatacorp.com/kpi/crons/cron_{{ $kpi->id}}.php?interval={{ $kpi->interval }}" class="run_kpi btn btn-default btn-sm"><span class="glyphicon glyphicon-flash"></span> Run Now</a>
                <label class="switch">
                    <input type="checkbox" {{ ($kpi->active) ? 'checked' : '' }} name="kpi_input">
                    <span></span>
                </label>
            </div>

            <div class="kpi">
                <p>{{ $kpi->description }}</p>
                <div class="row mt30 options kpi_options_top">
                    <div class="col-sm-4">
                        <h4 class="expand_dets"><i class="glyphicon glyphicon-wrench exp"></i> Options</h4>
                        <div class="expanded_options clear card">
                            <form data-kpi="{{ $kpi->id }}" action="#" method="post" class="form adjust_interval">
                                <div class="form-group">
                                    <label for="type">Interval</label>
                                    <select name="interval" class="form-control interval"  required>
                                        <option {!! ($kpi->interval == '15') ? ' selected="selected"' : '' !!} value="15">Every 15 minutes</option>
                                        <option {!! ($kpi->interval == '30') ? ' selected="selected"' : '' !!} value="30">Every 30 minutes</option>
                                        <option {!! ($kpi->interval == '60') ? ' selected="selected"' : '' !!} value="60">Every Hour</option>
                                        <option {!! ($kpi->interval == '720') ? ' selected="selected"' : '' !!} value="720">Twice a Day</option>
                                        <option {!! ($kpi->interval == '1440') ? ' selected="selected"' : '' !!} value="1440">Once a Day</option>
                                    </select>
                                </div>
                                <input type="submit" class="btn btn-primary btn-md mb0" value="Save">
                            </form>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <h4 class="expand_dets"><i class="glyphicon glyphicon-envelope"></i> Recipients</h4>
                        <div class="expanded_emails clear">

                            @foreach($kpi->recipients as $r)

                            <div class="user clear" id="{{ $r->id }}">
                                <p class="name"><span class="name">{{ $r->name }}</span>
                                    @if($r->email)
                                    <i class="fas fa-envelope"></i>
                                    @endif
                                    @if($r->phone)
                                    <i class="fas fa-sms"></i>
                                    @endif
                                </p>

                                <a class="edit_recip_glyph" data-toggle="modal" data-target="#editRecipModal" href="#" data-recip="{{ $r->recipient_id }}" data-userid="{{$r->id}}" data-username="{{$r->name}}"><i class="fas fa-user-edit"></i></a>
                                <a data-toggle="modal" data-username="{{$r->name}}" data-target="#deleteRecipModal" class="remove_recip_glyph" href="#" data-kpi="{{ $kpi->id }}" data-recip="{{ $r->id }}"><i class="fas fa-trash-alt"></i></a>
                            </div>
                            @endforeach
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
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Confirm Recipient Removal</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" class="user_id" name="user_id" value="">
                <input type="hidden" class="name" name="name" value="">
                <input type="hidden" class="fromall" name="fromall" value="0">
                <input type="hidden" class="kpi_id" name="kpi_id" value="">
               <h3>Are you sure you want to remove <span class="username"></span> from this KPI?</h3>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger remove_recip">Remove Recipient</button>
        </div>
    </div>
    </div>
</div>


@include('shared.editrecipmodal');
