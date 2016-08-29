$('input[type=checkbox]:checked').parents('tr').addClass('success');

$(document).ready(function(){
	var count = $('#rel-student-table tr.active').length;
	$('#curr-showing').html("current (" + count + ")");

	// toggle students
	$(document).on('click', '.training-toggle-btn', function(){
		if($(this).attr('id') == 'show-current')
		{
			var count = $('#rel-student-table').find('tr.active').length;
			$('#curr-showing').html("current (" + count + ")");

			$('#rel-student-table tr.inactive').hide();
			$('#rel-student-table tr.active').show();
		}
		else
		{
			var count = $('#rel-student-table').find('tr.active, tr.inactive').length;
			$('#curr-showing').html("all (" + count + ")");

			$('#rel-student-table tr.inactive').show();
			$('#rel-student-table tr.active').show();
		}

		$('.training-toggle-btn').removeClass('active');
		$(this).addClass('active');
	});
});