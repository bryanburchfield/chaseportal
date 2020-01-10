<div class="col-sm-6 settings mbp0">
	<a class="link" href="{{url('dashboards/automatedreports')}}"><i class="fas fa-external-link-alt"></i> {{__('general.auto_report_settings')}}</a>
	<a class="link" href="{{url('dashboards/kpi')}}"><i class="fas fa-external-link-alt"></i> {{__('general.kpi_settings')}}</a>
	<a class="link" href="{{url('dashboards/kpi/recipients')}}"><i class="fas fa-external-link-alt"></i> {{__('general.recipient_settings')}}</a>

	<div class="divider"></div>

	<form action="{{url('/dashboards/settings/update_lang_display')}}" method="POST" class="form hide_lang">
		@csrf
		<label class="checkbox-inline"><input type="checkbox" value="{{Auth::user()->language_displayed ? '1' : '0'}}" name="lang_displayed[]" {{Auth::user()->language_displayed ? 'checked' : ''}}> {{__('general.display_language')}}</label>
		<button type="submit" class="btn btn-primary btn-sm mt30 update_lang_btn add_btn_loader">{{__('general.update')}}</button>
	</form>

	<form action="{{url('/dashboards/settings/update_theme')}}" method="POST" class="form toggle_theme">
		@csrf
		<label class="checkbox-inline"><input type="checkbox" value="{{Auth::user()->theme == 'dark' ? '1' : '0'}}" name="theme[]" {{Auth::user()->theme == 'dark' ? 'checked' : ''}}> Enable Dark Theme</label>
		<button type="submit" class="btn btn-primary btn-sm mt30 update_lang_btn add_btn_loader">{{__('general.update')}}</button>
	</form>
</div>