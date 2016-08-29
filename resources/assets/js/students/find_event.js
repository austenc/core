$(document).ready(function(){

	$(document).on('click','input[name="event_id"]',function(e)
	{
		$('#sel-event-table tbody:last tr').removeClass("success");
		$(this).closest('tr').addClass("success");
	});

});