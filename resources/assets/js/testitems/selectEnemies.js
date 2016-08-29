$(document).on('click', '.select-enemies-table tbody:last tr', function(e){
	// if it's not a checkbox, trigger the checkbox
	if( ! $(e.target).is(':checkbox'))
	{ 
		$(this).find('input:checkbox').prop('checked', function(i, val){ return !val });
	}
});

$(document).on('click', '.add-selected', function(e){
	var enemies = [];
	$(this).parents('.modal-content').find('.select-enemies-table input:checkbox').each(function(){
		if($(this).is(':checked'))
			enemies.push($(this).val());
	});
	$('#enemies').val(enemies.join(', '));
	$(this).parents('.modal').modal('hide');
});