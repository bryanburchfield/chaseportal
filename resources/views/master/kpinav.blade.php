{{-- <div class="btn-group">
    <button type="button" onclick="window.location.href = {{ route('logout') }};" class="btn logout_btn"><span>Log Out</span></button>
</div> --}}

<div class="btn-group">
	<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" onclick="window.location.href = '{{ url('kpi/recipients') }}';">
        <span>Recipients</span>
    </button>
</div>
