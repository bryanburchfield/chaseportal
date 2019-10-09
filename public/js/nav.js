$(document).ready(function () {

	$('.components a.dash').on('click', function (e) {
		e.preventDefault();
		var dashboard = $(this).attr('href');

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});
		$.ajax({
			url: '/dashboards/set_dashboard',
			type: 'POST',
			dataType: 'json',
			data: { 'dashboard': dashboard },
			success: function (response) {
				if (!$('.sidebar').hasClass('active')) {
					$('.sidebar').toggle();
					$('.preloader').show();
					window.location = "/dashboards";
				} else {
					window.location = "/dashboards";
				}
			}
		})
	});

});