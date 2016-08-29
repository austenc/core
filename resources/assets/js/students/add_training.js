$(document).ready(function(){

	var $status = $('#status');
	var $hours  = $('#hours-div');
	var $ended  = $('#ended').parents('.form-group');
	var $reason = $('#reason').parents('.form-group');
	var $expires = $('#expires').parents('.form-group');


	// set expiration date
	$('input[name="ended"]').on('focusout', function(e){
		if($(this).val().length === 0 || $('#status').val() != 'passed' || $('#training_id').val().length == 0)
		{
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

	// on change discipline, repopulate facilities
	$(document).on('change', '#discipline_id', function(e) {
		resetFacilities();
		resetInstructors();
		resetTrainings();

		getFacilityList();
		getTrainingList();
	});

	// on change facility, repopulate instructors dropdown if needed
	$(document).on('change', '#facility_id', function(e) {
		getInstructorList();
	});

	// on change training type, repopulate instructors if needed
	$(document).on('change', '#training_id', function(e) {
		getInstructorList();
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

	// handle status on initial page load
	handleStatus(status);
});

/**
 * Called after Discipline select
 * Gets a list of all facilities doing this discipline
 * Instructor list is reset to empty
 */
function getFacilityList()
{
	var $disciplineId = $('#discipline_id');
	var isSelect      = $disciplineId.is('select');
	var discipline    = $('#discipline_id').val();

	if(isSelect)
	{
		if(discipline != 0)
		{
			$.ajax({
		        url: "/discipline/"+discipline+"/facilities/training?filter=true",
		        success: function(result){                
		            $.each(result, function(i, val){
						$('#facility_id').append(
							$('<option></option>').val(val.id).html(val.name)
						);
					});
		        }
		    });	
		}
	}
}

/**
 * Called after Discipline select
 * Gets all Trainings under a Discipline
 */
function getTrainingList()
{
	var isSelect     = $('#discipline_id').is('select');
	var disciplineId = $('#discipline_id').val();
	var studentId    = $('#student_id').length ? $('#student_id').val() : 0;

	if(isSelect)
	{
		if(disciplineId != 0)
		{
			$.ajax({
		        url: "/students/"+studentId+"/discipline/"+disciplineId+"/available/trainings",
		        success: function(result){
		        	console.log(result);

		            $.each(result, function(i, val){
						$('#training_id').append(
							$('<option></option>').val(val.id).html(val.name)
						);
					});
		        }
		    });	
		}
	}
}

/**
 * Ajax compatible instructors for training at facility
 */
function getInstructorList()
{
	var $instructorId = $('#instructor_id');
	var isSelect      = $instructorId.is('select');
	var discipline    = $('#discipline_id').val();
	var facility      = $('#facility_id').val();
	var training      = $('#training_id').val();

	// the field can be a hidden field sometimes (if instructor logged in)
	// if it's NOT a hidden field, it must be a dropdown, ajax update it
	if(isSelect)
	{
		resetInstructors();

		// need both training program AND training selected
		// Grab a matching list of instructors via ajax and build new options
		if(training != 0 && facility != 0 && discipline != 0)
		{
			$.ajax({
		        url: "/facilities/"+facility+"/discipline/"+discipline+"/training/"+training+"/instructors",

		        success: function(result){                 
	            	$.each(result, function(i, val){
						$('#instructor_id').append(
							$('<option></option>').val(val.id).html(val.first+' '+val.last)
						);
					});
		        }
		    });	
		}
	}
}

function resetInstructors()
{
	$('#instructor_id').empty().append(
		$('<option></option>').val(0).html("Select Instructor")
	);
}

function resetFacilities()
{
	$('#facility_id').empty().append(
		$('<option></option>').val(0).html("Select Training Program")
	);
}

function resetTrainings()
{
	$('#training_id').empty().append(
		$('<option></option>').val(0).html("Select Training")
	);
}