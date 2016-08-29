$(document).ready(function(){

	$(document).on('change', 'input[name="discipline_id[]"]', function()
	{
		var d = $(this).val();

		if($(this).is(':checked'))
		{
			// show
			$('#discipline-'+ d +'-header').removeClass('hide').show();
			$('#discipline-'+ d +'-content').removeClass('hide').show();
		}
		else
		{
			// hide
			$('#discipline-'+ d +'-header').hide();
			$('#discipline-'+ d +'-content').hide();

			// uncheck all training programs
			$('#discipline-'+ d +'-content input[name="training_site_id[]"]').prop('checked', false);
			// uncheck all trainings
			$('#discipline-'+ d +'-content input[name="training_id[]"]').prop('checked', false);
			// remove highlight 
			$('#discipline-'+ d +'-content table tr').removeClass('success');
		}
	});

	$(document).on('click', '#populate', function(){
		$.ajax({
			url: $(this).data('href'),
			dataType: "json",
			success: function(result){
				// uncheck all discipline
				$('.sel-disp').prop('checked', false);
				$('.info-div').hide();
				$('.empty-div').show();
				$('.disc').hide();

				$('input[name="gender"][value="'+result.gender+'"]').prop('checked', true);
				$('input[name="email"]').val(result.email);
				$('input[name="phone"]').val(result.phone);
				$('input[name="birthdate"]').val(result.birthdate);
				$('input[name="first"]').val(result.first);
				$('input[name="last"]').val(result.last);
				$('input[name="address"]').val(result.address);
				$('input[name="city"]').val(result.city);
				$('input[name="state"]').val(result.state);
				$('input[name="zip"]').val(result.zip);
				$('input[name="license"]').val(result.license);
				$('input[name="holdStatus"][value="true"]').prop('checked', true);
				$('#comments').text(result.comments);

				// trainings
				$('input[name="training_id[]"]').closest('tr').removeClass('success');
				$('input[name="training_id[]"]').prop('checked', false);
				$.each(result.trainings, function(i, val){
					var $target = $('input[name="training_id[]"][value="'+val+'"]');

					$target.closest('tr').addClass('success');
					$target.prop('checked', true);
				});

				// training sites
				$('input[name="training_site_id[]"]').prop('checked', false);
				$('input[name="training_site_id[]"]').closest('tr').removeClass('success');
				console.log(result.programs);
				$.each(result.programs, function(disciplineId, programIds){
					// each training site for this discipline
					$.each(programIds, function(i, programId)
					{
						var $trSiteChk = $('#discipline-'+ disciplineId +'-content input[name="training_site_id[]"][value="'+ disciplineId +'|'+ programId +'"]');

						$trSiteChk.closest('tr').addClass('success');
						$trSiteChk.prop('checked', true);
					});
				});

				// disciplines
				$('input[name="discipline_id[]"]').prop('checked', false);
				$('input[name="discipline_id[]"]').closest('tr').removeClass('success');
				$.each(result.disciplines, function(i, val){
					$('input[name="discipline_id[]"][value="'+val+'"]').prop('checked', true).change();
					$('input[name="discipline_id[]"][value="'+val+'"]').closest('tr').addClass('success');
				});
			}
		});
	});

});