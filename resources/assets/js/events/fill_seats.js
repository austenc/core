$(document).ready(function(){

	$(document).on('click','#sel-students-table tbody:last tr', function(e)	{
		$(this).toggleClass("success");

		var target = $(e.target);
		var $checkbox = $(this).find(':checkbox');

		if ( ! target.is(":checkbox")) 
		{
	    	// Toggles Checkbox
	    	$checkbox.prop('checked', function (i, value) {
	        	return !value;
	    	});
		}

		update_seat_count($checkbox);
		restrict_seats();
	});
});
function update_seat_count(target) {
	var isCheckbox = target.is(':checkbox');
	var isChecked = target.is(':checked');
	var checkbox = target.find('input[type=checkbox]');
	var seatCounts = $('.seat-count');

	$.each(seatCounts, function(){
		var rem_seats = parseInt($(this).html());

		// checkbox click
		if(isCheckbox)
		{
			// checked or not?
			if(isChecked)
			{
				rem_seats -= 1;
				$(this).html(rem_seats);
			}
			else
			{
				rem_seats += 1;
				$(this).html(rem_seats);
			}
		}
		// row click
		else
		{
			if(checkbox.prop('checked'))
			{
				rem_seats -= 1;
				$(this).html(rem_seats);
			}
			else
			{
				rem_seats += 1;
				$(this).html(rem_seats);
			}
		}
	});
}

function restrict_seats() {
	// go through all .seat-count
	// if any are less than 1, hide all rows
	// (ie coreqs must be taken together)

	var foundFull = false;
	var seatCounts = $('.seat-count');
	$.each(seatCounts, function(){
		if(parseInt($(this).html()) < 1)
		{
			$(this).removeClass('label-success');
			$(this).addClass('label-danger');
			foundFull = true;
		}
		else
		{
			$(this).removeClass('label-danger');
			$(this).addClass('label-success');
		}
	});

	if(foundFull)
	{
		$('#sel-students-table').find('input:checkbox').not(':checked').closest('tr').fadeOut();
	}
	else
	{
		$('#sel-students-table tbody tr').show();
	}
}