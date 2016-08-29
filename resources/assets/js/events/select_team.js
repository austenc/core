$(document).ready(function(){
	
	$(document).on('change', 'input[type="checkbox"]', function(){
		var $currRow = $(this).closest('tr');

		// uncheck all others except this one
		$(this).closest('table').find('input[type="checkbox"]').not($(this)).prop('checked', false);
		// remove success class from all rows
		$(this).closest('table').find('tbody tr').removeClass('success');

		if($(this).is(':checked'))
		{
			$(this).closest('tr').addClass('success');
			$(this).closest('tbody').children('tr').not($currRow).hide();
		}
		else
		{
			// show all rows
			$(this).closest('tbody').children('tr').show();
		}
	});

});