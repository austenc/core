$(document).ready(function(){
	
	var $mimic = $('[data-mimic="dropdown"]');
	var $hidden = $($mimic.data('mimic-target'));

	// when a list item is clicked
	$('.dropdown-menu li a', $mimic).click(function(){
		
		// mark the active item
		$('.dropdown-menu li', $mimic).removeClass('active');
		$(this).parent('li').addClass('active');

		// change the button text
		var selected = $(this).text();
		$('.mimic-selected', $mimic).html(selected);

		// update the hidden input
		$hidden.val(selected);

	});
});