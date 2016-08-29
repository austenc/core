var conflict_dates;
$(document).ready(function(){
	// add timepicker
	$('.timepicker').timepicker({
		minuteStep: 15,
		defaultTime: '12:00 PM'
	});
	
	conflict_dates = $('.conflict-date').map(function(){
		return $(this).val();
	}).get();

	$('input[name="test_date[0]"]').datepicker({ 
		beforeShowDay: unavailable, 
		startDate: new Date()
	}).on('changeDate', function(e) {
		$('#pending-test-team-div select').prop('disabled', true);
		$("#observer_id option[value='0']").prop('selected', true);
		$("#proctor_id option[value='0']").prop('selected', true);
		$("#actor_id option[value='0']").prop('selected', true);
		$('#obs-is-mentor').hide();

        flash('Test Team has been reset. Click Update to reselect Test Team.', 'warning');
    });
});
function unavailable(date) 
{
	dmy = date.getFullYear() + "-" + ('0' + (date.getMonth()+1)).slice(-2) + "-" + ('0' + date.getDate()).slice(-2);

	if($.inArray(dmy, conflict_dates) < 0) {
		return true;
	}
	
	return false;
}