var btn_href;

$(document).ready(function(){
	btn_href = $('.add-task').prop('href');

	autosize($('textarea'));

	// add excluded ids
	adjust_add_task_url();

	// SORT up button
	$(document).on("click",".sort-up",function(e){
		e.preventDefault();
		
		var tr = $(this).parents('tr');
		
		// move up (nothing above header-row)
		if(tr.prev().attr('class') != "header-row")
		{
			tr.prev().before(tr);
			
			// update ordinals
			setTimeout(function() {
				update_ordinals();
			}, 300);
		}
	});
	
	// SORT down button
	$(document).on("click",".sort-down",function(e){
		e.preventDefault();
		
		var tr = $(this).parents('tr');
		tr.next().after(tr);
		
		// update ordinals
		setTimeout(function() {
			update_ordinals();
		}, 300);
	});

	// remove task
	$(document).on('click', '.remove-button', function(e){
		e.preventDefault();

		var hasConfirm = $(this).attr('data-confirm');

		if(hasConfirm != undefined)
			return true;

		var href = $(this).data('href');

		if(href)
		{
			// Do the ajax remove
			var $row = $(this).parents('tr');

			$.ajax({
				url: href,
				success: function(result){                    
					$('#dataConfirmModal').modal('hide');
					fadeAndReorder($row, 'Task removed.', 'danger');                    
				}
			});
		}
		else
		{
			// just remove the row
			var $row = $(this).parents('tr');
			fadeAndReorder($row, 'Task removed.', 'danger');
		}
	});

	// adding a new task to the page from popup
	$(document).on('click', '#pull-task', function(){
		var c = $('#task-table tbody tr').length;

		// get each selected task
		$('#task-select tbody input:radio:checked').each(function(){
			var $row = $(this).closest('tr');
			var id = $(this).val();
			var title = $row.find('.title').html();
			var scenario = $row.find('.scenario').html();
			var weight = $row.find('.weight').html();
			var minimum = $row.find('.minimum').html();

			var $newRow = $('#task-prototype').clone();
			$newRow.removeAttr('id');
			$newRow.find('.task-order').val(id);
			$newRow.find('.task-order').prop('name', 'task_ids['+ (c+1) +']');
			$newRow.find('.ordinal').html(c + 1);
			$newRow.find('.title').html(title);
			$newRow.find('.scenario').html(scenario);
			$newRow.find('.weight').html(weight);
			$newRow.find('.task-link').prop('href', '/tasks/'+id+'/edit');
			$newRow.find('.task-link').tooltip();
			$newRow.find('.remove-button').tooltip();

			$('#task-table').append($newRow);

			adjust_add_task_url();

			c++;
		});
	});

});
function update_ordinals()
{
	var j = 1;

	$('#task-table tr').has('.task-order').each(function(i){
		$(this).find('.task-order').prop('name', 'task_ids['+j+']');    	 // hidden ordinal #
		$(this).find('.ordinal').html(j);        // display #   
		j++;
	});
}
function fadeAndReorder(row, message, msg_type)
{
	row.fadeOut(400, function() { 
		row.remove(); 
		update_ordinals();

		if(message)
		{
			flash(message, msg_type);
		}

		adjust_add_task_url();
	});
}
function adjust_add_task_url()
{
	var exam_id = $('#skillexam_id').val();
	var task_ids = $('#task-table .task-order').map(function() { 
				return this.value; 
			}).get().join(',');

	$(".add-task").prop('href', btn_href+"?id="+exam_id+"&exclude="+task_ids);
}