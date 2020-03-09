<div class="col-sm-6 settings mbp0">
	<a class="link" href="{{url('dashboards/automatedreports')}}"><i class="fas fa-external-link-alt"></i> {{__('general.auto_report_settings')}}</a>
	<a class="link" href="{{url('dashboards/kpi')}}"><i class="fas fa-external-link-alt"></i> {{__('general.kpi_settings')}}</a>
	<a class="link" href="{{url('dashboards/kpi/recipients')}}"><i class="fas fa-external-link-alt"></i> {{__('general.recipient_settings')}}</a>

	<div class="divider"></div>

	<form action="{{action('UserController@updateSettings')}}" method="POST" class="form hide_lang">
		@csrf
		<div>
			<label class="checkbox-inline">
			<input type="checkbox" value="1" name="language_displayed" {{Auth::user()->language_displayed ? 'checked' : ''}}> {{__('general.display_language')}}</label>
		</div>

		<div>
			<label class="checkbox-inline">
			<input type="checkbox" value="1" name="theme" {{Auth::user()->theme == 'dark' ? 'checked' : ''}}> {{__('general.enable_dark_theme')}}</label>
		</div>

			<button type="submit" class="btn btn-primary btn-sm mt30 update_lang_btn add_btn_loader">{{__('general.update')}}</button>
		</div>
	</form>
</div>