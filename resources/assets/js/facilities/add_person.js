$(document).ready(function(){

	$('#person_type').on('change', function()
	{
		var facilityId   = $('#facility_id').val();
		var disciplineId = $('#discipline_id').val();
		var personType   = $('#person_type').val();

		if(disciplineId == 0 || personType == 0)
		{
			$('#person-table').find('tbody').empty();
		}
		else 
		{
			$('#person-table').find('tbody').empty();

			$.ajax({
				url: '/facilities/' + facilityId + '/discipline/' + disciplineId + '/' + personType + '/get',
				dataType: 'json',
				success: function(data){

					$.each(data, function(i, m){
						$('#person-table').find('tbody')
							.append($('<tr data-clickable-row>')
								.append($('<td>')
									.html('<input type="checkbox" name="person_id[]" value="'+ m.id +'">')
								)
								.append($('<td>')
									.html(m.first+' '+m.last)
								)
								.append($('<td>')
									.html(m.city+', '+m.state)
								)
							);

					});
				}
			});
		}
	});

	$('#discipline_id').on('change', function()
	{
		var facilityId   = $('#facility_id').val();
		var disciplineId = $('#discipline_id').val();
		var personType   = $('#person_type').val();
		
		if(disciplineId == 0 || personType == 0)
		{
			$('#person-table').find('tbody').empty();
		} 
		else 
		{
			$('#person-table').find('tbody').empty();

			$.ajax({
				url: '/facilities/' + facilityId + '/discipline/' + disciplineId + '/' + personType + '/get',
				dataType: 'json',
				success: function(data){

					$.each(data, function(i, m){
						$('#person-table').find('tbody')
							.append($('<tr data-clickable-row>')
								.append($('<td>')
									.html('<input type="checkbox" name="person_id[]" value="'+ m.id +'">')
								)
								.append($('<td>')
									.html(m.first+' '+m.last)
								)
								.append($('<td>')
									.html(m.city+', '+m.state)
								)
							);

					});
				}
			});
		}
	});

});