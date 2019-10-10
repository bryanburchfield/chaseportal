<!-- Edit Recipient Modal -->
<div class="modal fade" id="editRecipModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Edit Recipient</h4>
            </div>

            <div class="modal-body">
                {!! Form::open(['method'=> 'POST', 'url' => '#', 'class' => 'form clear display update_recip']) !!}

                <div class="form-group">
                    {!! Form::label('name', 'Full Name') !!}
                    {!! Form::text('name', null, ['class'=>'form-control name', 'required'=> true, 'autocomplete' => 'new-password']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('email', 'Email Address') !!}
                    {!! Form::email('email', null, ['class'=>'form-control email']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('phone', 'Phone') !!}
                    {!! Form::tel('phone', null, ['class'=>'form-control phone']) !!}
                </div>

                <div class="kpi_list"></div>

                {!! Form::hidden('recipient_id', '', ['class'=>'recipient_id']) !!}
                {!! Form::hidden('from_page', $from_page, ['class'=>'from_page']) !!}

                @if (old('recipient_id'))
                    {!! Form::hidden('edit_form_submitted', '1', ['class'=>'edit_form_submitted']) !!}
                @endif

                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                {!! Form::submit('Submit', ['class'=>'btn btn-warning btn-md mb0 ']) !!}
                
                <div class="alert alert-danger mt20"></div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>