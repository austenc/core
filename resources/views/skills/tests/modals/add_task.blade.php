<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title">Add Task to Skill Test</h4>
	<div class="form-group">
		{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
		{!! Form::text('search', Input::get('search'), 
		['placeholder' => 'Filter by Title', 'autofocus' => 'autofocus', 'autocomplete' => 'off']) !!}
	</div>
	<small class="text-danger">Adding a Task will not take effect until the Skill Test is saved.</small>
</div>
<div class="modal-body">
	<table class="table table-striped table-condensed" id="task-select">
		<thead>
			<tr>
				<th></th>
				<th>Weight</th>
				<th>Title</th>
				<th>Scenario</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($tasks as $task)
				<tr>
					<td class="id">{!! Form::radio('task_id[]', $task->id) !!}</td>
					<td class="weight">{{ $task->weight }}</td>
					<td class="title">{{ ucwords($task->title) }}</td>
					<td class="scenario">{{ str_limit($task->scenario) }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>	
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-success" data-dismiss="modal" id="pull-task">Add Task</button>
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
<script type="text/javascript">
	$(document).on('click','#task-select tbody tr',function(e)
	{
		$('#task-select tbody:last tr').removeClass('success');
		$(this).addClass("success");

		if ( ! $(e.target).is(":radio")) 
		{
			$(this).find(':radio').click();
		}
	});

	$(document).on('keyup', '#search', function(e){
		var $search = $(this);

		$('#task-select .title').each(function(){
			var curr_title = $(this).html();

			if(curr_title.indexOf($search.val()) >= 0)
			{
				// found occurence, show row
				$(this).parents('tr').show();
			}  
			else
			{
				$(this).parents('tr').hide();
			}  
		});

	});
</script>