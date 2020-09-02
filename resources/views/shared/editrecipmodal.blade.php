<!-- Edit Recipient Modal -->
<div class="modal fade" id="editRecipModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">{{__('general.edit_recip')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body">
                {!! Form::open(['method'=> 'POST', 'url' => '#', 'class' => 'form fc_style clear display update_recip']) !!}

                <div class="form-group">
                    {!! Form::label('name', __('general.full_name')) !!}
                    {!! Form::text('name', null, ['class'=>'form-control name', 'required'=> true, 'autocomplete' => 'new-password']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('email', __('general.email')) !!}
                    {!! Form::email('email', null, ['class'=>'form-control email']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('phone', __('general.phone')) !!}
                    {!! Form::tel('phone', null, ['class'=>'form-control phone']) !!}
                </div>

                <div class="kpi_list"></div>

                {!! Form::hidden('recipient_id', '', ['class'=>'recipient_id']) !!}
                {!! Form::hidden('from_page', url()->current(), ['class'=>'from_page']) !!}

                @if (old('recipient_id'))
                    {!! Form::hidden('edit_form_submitted', '1', ['class'=>'edit_form_submitted']) !!}
                @endif

                <button type="button" class="btn btn-default" data-dismiss="modal">{{__('general.cancel')}}</button>
                {!! Form::submit(__('general.save'), ['class'=>'btn btn-primary btn-md mb0']) !!}

                <div class="alert alert-danger mt20 hidetilloaded"></div>
                {!! Form::close() !!}

            </div>
        </div>
    </div>
</div>