<!-- Modal -->
<div class="modal fade" id="datefilter_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">{{__('general.custom_date_filter')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                <!-- Date Picker -->
                    <div class="input-group date fc_style" id="startDate">
                        <input type='text' class="form-control datepicker_only startdate" name="startdate" placeholder="{{__('general.start_date')}}" />
                        <span class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </span>
                    </div>
                <!-- Time Picker -->
                    <div class="input-group date fc_style" id="endDate">
                            <input type='text' class="form-control datepicker_only enddate" name="enddate" placeholder="{{__('general.end_date')}}" />
                            <span class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </span>
                    </div>
                </div>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{__('general.cancel')}}</button>
            <button type="button" class="btn btn-primary submit_date_filter">{{__('general.submit')}}</button>
        </div>
    </div>
    </div>
</div>