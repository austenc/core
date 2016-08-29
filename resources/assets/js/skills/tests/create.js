$(document).ready(function(){
	hideRows();

	$(document).on('change', '#skill_exam', function(e){
		var sel = $('#skill_exam').val();
		$('#num-tasks-table tbody tr').hide();
		$('tr.exam-'+sel).show();
	});

});