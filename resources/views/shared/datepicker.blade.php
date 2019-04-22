<!-- Modal -->
<div class="modal fade" id="datefilter_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Custom Date Filter</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                <!-- Date Picker -->
                    <div class="input-group date " id="startDate">
                        <input type='text' class="form-control datepicker startdate" name="startdate" placeholder="Start Date" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>
                <!-- Time Picker -->
                    <div class="input-group date" id="endDate">
                            <input type='text' class="form-control datepicker enddate" name="enddate" placeholder="End Date" />
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                    </div>
                </div>
            </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary submit_date_filter">Submit</button>
        </div>
    </div>
    </div>
</div>