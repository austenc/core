var conflict_dates;
autosize($('#upload'));

/**
 * Mark the duplicate form / skilltest rows 
 */
function markDuplicateRows(selector) 
{
	// mark duplicate testform rows
	var found = [];
	var duplicates = [];
	// Grab the duplicates
	$(selector).each(function(i, elem) {
		var alreadySeen = $.inArray($(elem).val(), found) > -1;
		if (alreadySeen) {
			duplicates[i] = $(elem).val();
		} else {
			found[i] = $(elem).val();
		}
	});

	// mark the rows	
	$(selector).each(function(i, elem) {
		if($.inArray($(elem).val(), duplicates) > -1) {
			$(elem).parents('tr').addClass('bg-warning');
		}
	});
}


$(document).ready(function(){

	// mark duplicate testform and skilltest rows
	markDuplicateRows('.student-testform-id');
	markDuplicateRows('.student-skilltest-id');

	// modify file upload
	$("#browse").click(function (e) {
		e.preventDefault();
    		$("#fileSelect").click();
	})
	$("#save").click(function (e) {
		// Change form action to submit form simply to process the file upload and not update
		// event information.
		var fileArr = $("#fileSelect").prop('files');
		var names = $.map(fileArr, function(val) { return val.name; });
		if(names.length > 0)
		{
			var url = '/events/' + $('#eventID').val() + "/uploadFiles";
			$('#frmEventEdit').attr('action', url);
			$('#frmEventEdit').submit();
		}
		else
		{
			alert("No files to save");
		}
	})
	$("#clear").click(function (e) {
		$('#upload').val('');
		// Reset files array to null. Not doing so will still allow a file to upload after one has been selected
		document.getElementById('fileSelect').value = document.getElementById('fileSelect').defaultValue;
		autosize.update($('#upload'));
	})
	$('#fileSelect').change(function (e) {
		var str = "";
		var fileArr = $("#fileSelect").prop('files');
		var names = $.map(fileArr, function(val) { return val.name; });
		for(var i = 0; i < fileArr.length; i++){
			str += fileArr[i].name + "\n";
		}
		$("#upload").val(str);
		autosize.update($('#upload'));
	})
	
	// add timepicker
	$('.timepicker').timepicker({
		minuteStep: 15,
		defaultTime: ''
	});

	// parse all conflict dates
	conflict_dates = $('.conflict-date').map(function(){
		return $(this).val();
	}).get();

	$('.test-date-picker').datepicker({ beforeShowDay: unavailable, startDate: new Date() });

	// Toggle yes/no label on regional event check
	var $isRegional = $('input[name="is_regional"]');
	$isRegional.click(function() {

		if($(this).prop('checked') == true)
		{
			$(this).parents('.checkbox').find('.label').removeClass('label-danger').addClass('label-success').html('YES');
		}
		else
		{
			$(this).parents('.checkbox').find('.label').removeClass('label-success').addClass('label-danger').html('NO');	
		}
	});

	// Prevent double-submission from buttons in modals
	$('body').on('click', '#dataConfirmOK', function() {
	    $('#main-ajax-load').show();
	    $('button, input[type="button"], input[type="submit"]').prop('disabled', true);
	});
	$('body').on('click', '#dataConfirmCancel', function() {
	   $('button, input[type="button"], input[type="submit"]').prop('disabled', false);
	});

}); // end document.ready

function unavailable(date) 
{
	dmy = date.getFullYear() + "-" + ('0' + (date.getMonth()+1)).slice(-2) + "-" + ('0' + date.getDate()).slice(-2);

	if($.inArray(dmy, conflict_dates) < 0) {
		return true;
	}
	
	return false;
}