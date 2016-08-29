<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title">Select Enemies</h4>
	<div class="form-group">
		{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
		{!! Form::text('search', Input::get('search'), 
		['placeholder' => 'Filter by Stem', 'autofocus' => 'autofocus', 'autocomplete' => 'off']) !!}
	</div>

</div>
<div class="modal-body">
	<table class="table select-enemies-table">
		<thead>
			<tr>
				<th>Select</th>
				<th>Stem</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($items as $item)
				<tr class="bg-{{{ $item->statusClass }}}">
					<td>{!! Form::checkbox('select_enemies[]', $item->id, in_array($item->id, $enemies)) !!}</td>
					<td class="stem">{{ $item->excerpt }}</td>
					<td class="text-{{{ $item->statusClass }}}">{{ ucwords($item->status) }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>	
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
<button type="button" class="btn btn-success add-selected">Add Selected</button>
</div>

<script type="text/javascript">
	$(document).on('keyup', '#search', function(e){
		var $search = $(this);

		$('.stem').each(function(){
			if($(this).text().toLowerCase().indexOf($search.val().toLowerCase()) != -1)
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