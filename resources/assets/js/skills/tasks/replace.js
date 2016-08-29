$(document).ready(function(){

	// select via row click
	$(document).on('click','#replace-table tbody tr',function(e)
	{
        $('#replace-table tbody tr').removeClass('success');
		$(this).addClass("success");
    	$(this).find(':radio').prop('checked', true);
	});

});