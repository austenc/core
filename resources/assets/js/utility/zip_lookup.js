$(document).ready(function(){

	$(document).on('change', '#zip', function(e) {
		$.ajax({
			type: "GET",
		  	url: "/zip/"+$(this).val()+"/lookup",
		  	dataType: "json",
		  	success: function(data){
		  		$('#city').val(data.city);
		  		$('#state').val(data.state);
		  	}
		});
	});

	$(document).on('change', '#mail_zip', function(e) {
		$.ajax({
			type: "GET",
		  	url: "/zip/"+$(this).val()+"/lookup",
		  	dataType: "json",
		  	success: function(data){
		  		$('#mail_city').val(data.city);
		  		$('#mail_state').val(data.state);
		  	}
		});
	});

});