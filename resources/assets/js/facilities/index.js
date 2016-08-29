$(document).ready(function()
{
	var $mimic = $('[data-mimic="dropdown"]');
	var $hidden = $($mimic.data('mimic-target'));

	var $mimic2 = $('[data-discipline="discipline"]');
	var $hidden2 = $($mimic2.data('discipline-target'));

	getDiscipline();

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

		// Set visibility of discipline dropdown, selected item, hidden search value
		// based on if License or TM License is selected in the search by field
		if($(this).text() == "State License" || $(this).text() == "Testmaster License")
		{
			$('#divDiscipline').hide();
			$('#search_discipline').val("All");
			$('.discipline-selected', $mimic2).html("All");
			discSelect = $('.discipline').find('li');
			$.each(discSelect, function(idx, select)
			{
				if($(select).hasClass('active'))
				{
					$(select).removeClass('active');
				}
				if(idx == 0) { $(select).addClass('active') }
			});
			$hidden2.val("All");
			setDiscipline("All");
		}
		else
		{
			$('#divDiscipline').show();
		}
	});
			
	$('.discipline li a').click(function(){
		$('.discipline li', $mimic2).removeClass('active');
		$(this).parent('li').addClass('active');

		$('.discipline-selected', $mimic2).html($(this).text());

		$hidden2.val($(this).text());
		setDiscipline($(this).text());
	})
});
function setDiscipline(discipline)
{
	if(typeof(Storage) !== "undefined")
	{
		sessionStorage.setItem("discipline", discipline);
	}
	else
	{
		document.cookie("discipline=" + discipline + "; path=/");
	}
}
function getDiscipline()
{
	if(typeof(Storage) !== "undefined")
	{
		var disc = sessionStorage.getItem("discipline");
	}
	else
	{
		disc = getCookie("discipline");
	}
	// Check when page first loads and no cookie or sessionStorage data is available
	// Set to the default All
	if(disc === null || disc == "")
	{
		disc = "All";
	}
	setSelectedDiscipline(disc);
}
function getCookie(cname)
{
	var name = cname + "=";
	var ca = document.cookie.split(";");
	for(var i = 0; i < ca.length; i++)
	{
		var c = ca[i];
		while (c.charAt(0) == " ") c = c.substring(1);
		if(c.indexOf(name) == 0) return c.substring(name.length, c.length);
	}
	return "";
}
function setSelectedDiscipline(disc)
{
	var $mimic2 = $('[data-discipline="discipline"]');
	var $hidden2 = $($mimic2.data('discipline-target'));

	discSelect = $('.discipline').find('li');
	for(var x = 0; x < discSelect.length; x++)
	{
		if($(discSelect[x]).hasClass('active'))
		{
			$(discSelect[x]).removeClass('active');
		}
		var chkStr = $(discSelect[x]).html().substr($(discSelect[x]).html().indexOf(">") + 1);
		chkStr = chkStr.substr(0, chkStr.indexOf("<"));

		if(chkStr == disc)
		{
			$(discSelect[x]).addClass('active');
			x = discSelect.length;
		}
	}
	$('.discipline-selected', $mimic2).html(disc);
	$hidden2.val(disc);
}