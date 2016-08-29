$(document).ready(function(){

	var $status = $('#status');
	var $hours  = $('#hours-div');
	var $ended  = $('#ended').parents('.form-group');
	var $reason = $('#reason').parents('.form-group');
	var $expires = $('#expires').parents('.form-group');

	// set expiration date
	$('input', $ended).on('focusout', function(e){
		if ($(this).val().length === 0 || $status.val() != 'passed' || $('#training_id').val().length == 0) {
			return;
		}

		// only lookup expiration if passed
		$.ajax({
	        url: "/students/training/expires",
	        data: {'ended': $(this).val(), 'training': $('#training_id').val()},
	        success: function(result){                    
            	$('input[name="expires"]').val(result);
	        }
	    });	
	});

	// change status
	$(document).on('change', '#status', function(e) {
		handleStatus($(this).val());
	});
	
	function handleStatus(status) 
	{
		if (status == 'passed') {
		// passed
			$reason.hide();
			$ended.show();
			$expires.show();
		} else if (status == 'failed') {
		// failed
			$reason.show();
			$ended.show();
			$expires.hide();
		} else {
		// attending
			$reason.hide();
			$expires.hide();
			$ended.hide();
		}
	}

	// handle initial status
	handleStatus($status.val());
});