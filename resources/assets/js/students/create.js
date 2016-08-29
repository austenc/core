$(document).ready(function(){

	$(document).on('click', '#populate', function() 
	{
		$.ajax({
			url: $(this).data('href'),
			dataType: "json",
			success: function(result){
				$('input[name="gender"][value="'+result.gender+'"]').prop('checked', true);
				$('input[name="ssn"]').val(result.ssn);
				$('input[name="rev_ssn"]').val(result.ssn.split("").reverse().join(""));
				$('input[name="email"]').val(result.email);
				$('input[name="phone"]').val(result.phone);
				$('input[name="birthdate"]').val(result.birthdate);
				$('input[name="first"]').val(result.first);
				$('input[name="last"]').val(result.last);
				$('input[name="address"]').val(result.address);
				$('input[name="city"]').val(result.city);
				$('input[name="state"]').val(result.state);
				$('input[name="zip"]').val(result.zip);
				$('input[name="password"]').val(result.password);
				$('input[name="password_confirmation"]').val(result.password);
				$('#ckbGenFakeSsn').prop('checked', true);
				$('#ckbIsUnlisted').prop('checked', result.unlisted);
				$('input[name="is_oral"][value="'+result.oral+'"]').prop('checked', true);

				// set initial training as attending
				$('#status option[value="attending"]').prop('selected', true);
				$('#ended').val('');
				$('#ended-date').hide();
				$('#expires').val('');
				$('#expires-date').hide();

				// set discipline
				$('#discipline_id option[value="'+ result.discipline_id +'"]').prop('selected', true);

				// populate available facilities
				$('#facility_id').empty();
				$('#facility_id').append(
					$('<option></option>').val(0).html('Select Training Program')
				);
				$.each(result.available_facilities, function(i, val){
					$('#facility_id').append(
						$('<option></option>').val(val).html(i)
					);
				});
				// select facility
				$('#facility_id option[value="'+ result.facility_id +'"]').prop('selected', true);

				// populate available trainings
				$('#training_id').empty();
				$('#training_id').append(
					$('<option></option>').val(0).html('Select Training')
				);
				$.each(result.available_trainings, function(i, val){
					$('#training_id').append(
						$('<option></option>').val(val).html(i)
					);
				});
				// select training
				$('#training_id option[value="'+ result.training_id +'"]').prop('selected', true);

				// populate available instructors
				$('#instructor_id').empty();
				$('#instructor_id').append(
					$('<option></option>').val(0).html('Select Instructor')
				);
				if(result.hasOwnProperty('available_instructors'))
				{
					$.each(result.available_instructors, function(i, val){
						$('#instructor_id').append(
							$('<option></option>').val(val).html(i)
						);
					});

					$('#instructor_id option[value="'+ result.instructor_id +'"]').prop('selected', true);
				}

				// set training start date
				$('#started').val(result.training_started);
			}
		});
	});
});