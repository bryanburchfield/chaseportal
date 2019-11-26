var lang = $('html').attr('lang');
$('.datetimepicker').datetimepicker({useCurrent:false,  autoclose: true, endDate:'today', locale:lang,language:lang});
$('.datepicker_only').datetimepicker({pickTime: false, locale:lang, language:lang});

$('.timepicker .btn')
	.removeClass('btn-primary')
;
