<!-- start pagination -->
<div class="pag col-sm-5 col-xs-12 nopad">
	<input type="hidden" name="report" value="{{$report}}">
</div>
<!-- end pagination -->

<!-- start download options -->
<div class="col-sm-3 col-xs-6 report_download">
	<h3>Download Report:</h3>
	<a target="_blank" href="report_export/{{$report}}/csv" data-report="{{$report}}" data-format="csv" class="report_dl_option" title="Download CSV" data-dl_option="csv"><i class="fas fa-file-csv"></i></a>
	<a target="_blank" href="report_export/{{$report}}/xls" data-report="{{$report}}" data-format="xls" class="report_dl_option" title="Download Excel" data-dl_option="excel"><i class="fas fa-file-excel"></i></a>
	<a target="_blank" href="report_export/{{$report}}/pdf" data-report="{{$report}}" data-format="pdf" class="report_dl_option pdf" title="Download PDF" data-dl_option="pdf"><i class="fas fa-file-pdf"></i></a>
	<a target="_blank" href="report_export/{{$report}}/html" data-report="{{$report}}" data-format="html" class="report_dl_option" title="Download HTML" data-dl_option="html"><i class="fas fa-file-code"></i></a>
</div>
<!-- end download options -->

<!-- start sorting -->
<div class="col-sm-4 col-xs-6 reset_sorting">
	<h3></h3>
	<a href="#" class="reset_sorting_btn btn btn-default">Reset Sorting</a>
</div>
<!-- end sorting -->