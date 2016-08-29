$(document).ready(function(){

	// select discipline, show corresponding testsite and exam seats
	$(document).on('change', 'select[name="discipline_id"]', function(e){
		var id = $(this).val();

		// clear all seats
		$('.seats').val('');

		// reset other discipline testsite selections
		$('.sel-test-site').val(0);

		// hide all testsite divs except this one
		$('.disc').not('disc-'+id).hide();
		$('.disc-'+id).show();
	});

	// clone date
	$(document).on('click','#clone-date',function(e){
		var clone_div = $('.event-date-section:first').clone();
		var $btn = clone_div.find('.btn-sm');
		$btn.removeAttr('id');
		$btn.addClass('remove-clone-date');
		$btn.addClass('text-danger');
		$btn.attr('title', 'Remove this date');
		$btn.tooltip();
		$btn.find('.glyphicon').removeClass('glyphicon-plus').addClass('glyphicon-minus');
		clone_div.show();

		var clone_input = clone_div.find('input');
		var count = $('.event-date-section').length;

		// labels
		clone_div.find('label.test-date-label').html('Test Date #'+(count + 1));
		clone_div.find('label.start-time-label').html('Start Time #'+(count + 1));

		clone_input.each(function() {
			$(this).val('');
			var name = $(this).prop('name');
			$(this).prop('name', name.replace('0', count));
		});

		// add datepicker
		var timepick_input = clone_div.find('input.timepicker');
		timepick_input.timepicker({
			minuteStep: 15,
			defaultTime: '12:00 PM'
		});

		// show cloned date input
		$(clone_div).insertAfter('.event-date-section:last');
	});
	
	// remove cloned date
	$(document).on('click','.remove-clone-date',function(e){
		e.preventDefault();
		
		// remove the input field
		$(this).parents('.event-date-section').remove();
	});

	// add timepicker
	$('.timepicker').timepicker({
		minuteStep: 15,
		defaultTime: '12:00 PM'
	});

});