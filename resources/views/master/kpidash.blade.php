<div class="container mt20">
    <div class="row">
        <div class="col-sm-12">
            <h2>KPI Notifications</h2>
        </div>

        @foreach(\App\Kpi::getKpis() as $kpi)

        <div class="col-sm-12 opt" data-kpi="{{ $kpi->id }}">
            <a href="#" class="kpi_trigger"> {{ $kpi->name }}</a>

            <div class="controls">
                <a href="https://webdev.chasedatacorp.com/kpi/crons/cron_{{ $kpi->id}}.php?interval={{ $kpi->interval }}" class="run_kpi btn btn-default btn-sm"><span class="glyphicon glyphicon-flash"></span> Run KPI</a>
                <label class="switch">
                    <input type="checkbox" {{ ($kpi->active) ? 'checked' : '' }} name="kpi_input">
                    <span></span>
                </label>
            </div>

            <div class="kpi">
                <p>{{ $kpi->description }}</p>
                <div class="row mt30 options">
                    <div class="col-sm-6">
                        <a href="#" class="expand_dets"><i class="glyphicon glyphicon-wrench exp"></i> Options</a>
                        <div class="expanded_options clear">
                            <form data-kpi="{{ $kpi->id }}" action="#" method="post" class="form well adjust_interval">
                                <div class="form-group">
                                    <label for="type">Interval</label>
                                    <select name="interval" class="form-control interval"  required>
                                        <option >Choose One</option>
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

                    <div class="col-sm-6">
                        <a href="#" class="expand_dets"><i class="glyphicon glyphicon-envelope"></i> Recipients</a>
                        <div class="expanded_emails clear">
                            <a href="#" class="add_email"><i class="glyphicon glyphicon-plus-sign"></i> Add Recipient</a>

                            <form id="form{{ $kpi->id }}" data-kpi="{{ $kpi->id }}" action="#" method="post" class="form user_email_form well clear add_recipient" autocomplete="off">
                                <div class="form-group prel">
                                    <input type="text" class="form-control name" name="name" placeholder="Name" required onkeyup="searchRecips(this, this.value, '{{ $kpi->id }}')" autocomplete="new-password">

                                    <div class="search_results"></div>
                                </div>

                                <div class="form-group">
                                    <input type="email" class="form-control email" name="email" placeholder="Email Address" required>
                                </div>

                                <div class="form-group">
                                    <input type="tel" class="form-control phone" name="phone" placeholder="Phone Number">
                                </div>

                                <div class="checkbox mb20">
                                    <label><input type="checkbox" class="addtoall" name="addtoall" value="1">Add recipient to all KPI's?</label>
                                </div>

                                <input type="hidden" name="redirect_url" value="{{ url('/kpi') }}" class="redirect_url">

                                <input type="submit" class="btn btn-primary btn-md mb0" value="Submit">
                            </form>
                            @foreach($kpi->recipients as $r)
                            <div class="user clear" id="{{ $r->id }}">
                                <p class="name">{{ $r->name }}</p>
                                <p class="email">{{ $r->email }}</p>
                                <p class="phone">{{ $r->phone }}</p>
                                <a data-toggle="modal" data-target="#deleteRecipModal" class="remove_recip_glyph" href="#" data-recip="{{ $r->id }}"><i class="glyphicon glyphicon-remove-sign"></i></a>
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
               <h3>Are you sure you want to delete <span class="username"></span>?</h3>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger remove_recip">Delete User</button>
        </div>
    </div>
    </div>
</div>
