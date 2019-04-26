$(document).ready(function () {

	$('.pag').clone().insertAfter('div.table-responsive');

	$('.view_report_btn').on('click', function () {
		$('.alert').hide();
		var selected_report = $('input.report_option:checked').val();

		if (selected_report != '' && selected_report != undefined) {
			window.location = "reports.php?report=" + selected_report;
		} else {
			$('#reports_modal .modal-footer').append('<div class="alert alert-danger">Please select a report</div>');
		}
	});

	$('.add_user').on('submit', function (e) {
		e.preventDefault();

		var group_id = $('.group_id').val(),
			name = $('.name').val(),
			email = $('.email').val(),
			timezone = $('#timezone').val(),
			type = $('#type').val(),
			database = $('#database').val()
			;

		$('form.add_user .alert').remove();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});
		$.ajax({
			url: '/master/add_user',
			type: 'POST',
			dataType: 'json',
			data: {
				group_id: group_id,
				name: name,
				email: email,
				timezone: timezone,
				type: type,
				database: database
			},

			success: function (response) {

				if (response['add_user'] == false) {
					$('form.add_user').append('<div class="alert alert-danger">User alredy exists</div>');
				} else {
					$('form.add_user').append('<div class="alert alert-success">User successfully added</div>');
					$('.users').append('<p id="user' + response['add_user'][1] + '">' + response['add_user'][0] + ' - <span class="user_name">' + response['add_user'][2] + '</span><a data-toggle="modal" data-target="#deleteRecipModal" class="remove_user" href="#" data-user="' + response['add_user'][1] + '"><i class="glyphicon glyphicon-remove-sign"></i></a></p>');
					setTimeout(function () {
						$('.alert').remove();
					}, 4500);
				}
			}
		});
	});

	$('.remove_recip_glyph').on('click', this.pass_user_removemodal);
	$('#deleteRecipModal .remove_recip').on('click', this.remove_recipient);

	$('.users').on('click', '.remove_user', function () {
		var id = $(this).data('user');
		var username = $(this).prev('.user_name').text();
		$('#deleteRecipModal .user_id').val(id);
		$('#deleteRecipModal .name').val(username);
		$('#deleteRecipModal .username').text(username);
	});

	// DELETE USER
	$('#deleteRecipModal .remove_recip').on('click', function (e) {
		e.preventDefault();
		var id = $('.user_id').val();
		var fromall = $('.fromall').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});
		$.ajax({
			url: '/master/delete_user',
			type: 'POST',
			dataType: 'json',
			data: {
				id: id
			},
			success: function (response) {
				$('.users p#user' + id).remove();
				$('#deleteRecipModal').modal('toggle');
			}
		});
	});

	///////////////////////////////////////////////////////////
	//  AJAX FOR REPORTS //
	//////////////////////////////////////////////////////////

	var curpage,
		pagesize,
		pag_link,
		sort_direction
		;

	// filter form submission
	$('form.report_filter_form').on('submit', function (e) {
		e.preventDefault();
		update_report('', '', 1, '', '');
		$([document.documentElement, document.body]).animate({
			scrollTop: $(".pag").offset().top
		}, 1500);
	});

	// click a pagination button
	$('.page-content').on('click', '.pagination li a', function (e) {
		e.preventDefault();

		if (!$(this).parent().hasClass('disabled')) {
			curpage = $('.curpage').val(),
				pagesize = $('.pagesize').val(),
				pag_link = $(this).data('paglink'),
				sort_direction = ''
				;

			update_report('', pagesize, curpage, pag_link, sort_direction);
		}
	});

	// sort by clicking th
	$('body').on('click', '.reports_table thead th a span', function (e) {
		e.preventDefault();

		var thisparent = $(this).parent().parent();
		var th_sort = $(thisparent).text();
		$(thisparent).siblings().find('a span').show();
		$(thisparent).siblings().find('a span').removeClass('active');
		$(thisparent).siblings().removeClass('active_column');
		$(thisparent).addClass('active_column');
		$(this).siblings().hide();
		$('.curpage').val(1);

		var sort_direction = $(this).attr('class');

		if ($(this).hasClass('active')) {
			$(this).siblings().show();
			$(this).removeClass('active');
			$(this).siblings().addClass('active');
			$(this).hide();
			sort_direction = $(this).siblings().attr('class').split(' ')[0];
		} else {
			$(this).addClass('active');
		}

		update_report(th_sort, '', '', '', sort_direction);
	});

	// check if pag input values have changed
	$('.page-content').on('change', '.curpage, .pagesize', function () {

		var max_pages = parseInt($('.curpage').attr('max')),
			totrows = parseInt($('.totrows').val()),
			pagesize = parseInt($('.pagesize').data('prevval')),
			new_pagesize = parseInt($('.pagesize').val()),
			curpage = parseInt($('.curpage').val())
			;

		// check if page input is greater than max available pages
		if (parseInt($(this).val()) > max_pages && $(this).hasClass('curpage')) {
			var prevval = $(this).data('prevval');
			$('.curpage').val(prevval);
			$('div.errors').text('Attempted page number greater than available pages').show(0).delay(4500).hide(0);
			return false;
			// check if page input is greater than total rows
		} else {
			if ($(this).hasClass('curpage')) {
				curpage = $(this).val();
				$('.curpage').val(curpage);
			}

			// if users changes pagesize set curpage back to 1
			if (pagesize != new_pagesize) {
				curpage = 1;
			}

			if ($(this).hasClass('pagesize')) {
				pagesize = $(this).val();
				$('.pagesize').val(pagesize);
			}

			update_report('', pagesize, curpage, '', '');
		}
	});

	// reset table sorting
	$('.reset_sorting_btn').on('click', function (e) {
		e.preventDefault();
		$('.reset_sorting').show();
		curpage = $('.curpage').val();
		pagesize = $('.pagesize').val();
		$(this).prev('h3').text('Not sorted');
		update_report('', pagesize, curpage, '', '');
	});

	function update_report(th_sort = '', pagesize = '', curpage = '', pag_link = '', sort_direction = '') {
		if (!curpage) {
			curpage = $('.curpage').val()
		}
		pagesize = $('.pagesize').val();

		if ($('input#showonlyterm[type=checkbox]').is(':checked')) {
			$('#showonlyterm').val(true);
		} else {
			$('#showonlyterm').val(false);
		}

		var form_data = $('form.report_filter_form').serialize();
		var report = $('#report').val();
		if (curpage == '') { curpage = $('.curpage').val(); }
		if (report == '') { report = $('#report').val(); }
		if (curpage != pag_link && pag_link != '') { curpage = pag_link; }
		if (th_sort == pag_link) { th_sort = ''; }

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
			}
		});
		$.ajax({
			url: '/master/update_report',
			type: 'POST',
			dataType: 'json',
			data: {
				curpage: curpage,
				pagesize: pagesize,
				th_sort: th_sort,
				sort_direction: sort_direction,
				form_data: form_data,
				report: report
			},

			success: function (response) {

				$('.table-responsive').find('.alert').remove();

				// check for total page result
				if (response.params.totrows) {
					$('.no_results_error').hide();

					var curpage = response.params.curpage,
						totpages = response.params.totpages
						;

					// append table
					$('.table-responsive').empty().append(response.table);

					// set active class to the th that was sorted
					for (var i = 0; i < $('.reports_table thead th').length; i++) {
						if ($('.reports_table thead th:eq(' + i + ')').text() == th_sort) {
							$('.reports_table thead th:eq(' + i + ')').addClass('active_column');
							$('.reports_table thead th:eq(' + i + ')').find('span.' + sort_direction).addClass('active');
						}
					}

					// pagination
					$('div.pag').empty();
					$('div.pag').append(response.pag);
					$('.pagination').find('li').removeClass('active');
					$('.pagination li a[data-paglink="' + curpage + '"]').parent().addClass('active');
					$('.report_download').show();

					// show sort order and reset button if sorting is active
					if (th_sort) {
						$('.reset_sorting h3').text('Sorted in ' + sort_direction + ' order by ' + th_sort);
						$('div.reset_sorting').show();
					}

					/// remove disabled class from first page buttons if not first page
					if (curpage != 1) {
						$('.pagination').find('li:eq(1), li:eq(0)').removeClass('disabled');
					}

					/// add disabled class from last page buttons if not last page
					if (curpage == totpages) {
						$('.pagination').find('li').last().addClass('disabled');
						$('.pagination').find('li').last().prev().addClass('disabled');
						$('.pagination').find('li').last().prev().prev().addClass('active');
					}
				} else {
					$('.table-responsive').empty();
					$('.pag').empty();
					$('.report_download').hide();
					$('.reset_sorting').hide();

					$('.table-responsive').append('<div class="alert alert-danger">No results found</div>');
				}
			}
		}); /// end ajax
	} /// end update_report function

});