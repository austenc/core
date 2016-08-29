$(document).ready(function(){
	$(document).on('click', '#populate', function(){
		$.ajax({
			url: $(this).data('href'),
			dataType: "json",
			success: function(result){
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
				$('input[name="password"]').val(result.password);
				$('input[name="password_confirmation"]').val(result.password);
				$('input[name="holdStatus"][value="true"]').prop('checked', true);
				$('#comments').text(result.comments);

				if(result.license)
				{
					$('#license').val(result.license);
				}

				// test sites
				$('.test-site-div').hide();
				$('.test-site-header').hide();
				$('input[name="testsite_id[]"]').prop('checked', false);
				$('input[name="testsite_id[]"]').closest('tr').removeClass('success');
				$.each(result.testSites, function(disciplineId, programIds){

					// each test site for this discipline
					$.each(programIds, function(i, programId){
						var p = $('#discipline-'+ disciplineId +'-test-site input[name="testsite_id[]"][value="'+ disciplineId +'|'+ programId +'"]');

						p.closest('tr').addClass('success');
						p.prop('checked', true);
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

	$(document).on('change', 'input[name="discipline_id[]"]', function(){
		var d = $(this).val();

		if($(this).is(':checked'))
		{
			$('#discipline-'+ d +'-test-site').removeClass('hide').show();
			$('#discipline-'+ d +'-test-site-title').removeClass('hide').show();
		}
		else
		{
			$('#discipline-'+ d +'-test-site').hide();
			$('#discipline-'+ d +'-test-site-title').hide();
			// uncheck all test sites that were checked under this discipline
			$('#discipline-'+ d +'-test-site input[name="testsite_id[]"]').prop('checked', false);
			$('#discipline-'+ d +'-test-site tr').removeClass('success');
		}
	});

});