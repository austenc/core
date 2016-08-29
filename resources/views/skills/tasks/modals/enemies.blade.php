<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title">Add Task Enemies</h4>
	<div class="form-group">
		{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
		{!! Form::text('search', Input::get('search'), 
		['placeholder' => 'Filter by Title', 'autofocus' => 'autofocus', 'autocomplete' => 'off']) !!}
	</div>
</div>
<div class="modal-body">
	<table class="table table-striped table-condensed" id="enemies-table">
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
					<td class="id">{!! Form::checkbox('task_id[]', $task->id) !!}</td>
					<td class="weight">{{ $task->weight }}</td>
					<td class="title">{{ ucwords($task->title) }}</td>
					<td class="scenario">{{ str_limit($task->scenario) }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>
<div class="modal-footer">
	<button type="button" class="btn btn-success" data-dismiss="modal" id="add-enemies-btn">Add Enemies</button>
	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>
<script type="text/javascript">
	$(document).on('click','#enemies-table tbody tr',function(e)
	{
		table_row_select(e);
	});

	$(document).on('keyup', '#search', function(e){
		var $search = $(this);

		$('#enemies-table .title').each(function(){
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