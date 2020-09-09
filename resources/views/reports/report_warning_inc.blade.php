<!-- Modal -->
<div class="modal fade" id="report_dl_warning" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="myModalLabel">{{__('general.download_warning')}}</h5>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<div class="alert dl_alert alert-danger">
					<p></p>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default cancel" data-dismiss="modal">{{__('general.cancel')}}</button>
				<button type="button" class="btn btn-primary dl_report">{{__('general.download')}}</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->