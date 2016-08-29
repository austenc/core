<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	<h4 class="modal-title">Swap Testitem</h4>
	<div class="form-group">
		{!! Form::label('search', 'Search', ['class' => 'sr-only']) !!}
		{!! Form::text('search', Input::get('search'), 
		['placeholder' => 'Filter by Minimum P-Value', 'autofocus' => 'autofocus', 'autocomplete' => 'off']) !!}
	</div>
	<small class="text-danger">Swapping in an item will not take effect until the testform is saved.</small>
</div>
<div class="modal-body">
	<div class="hide" id="old-id">{{ $oldId }}</div>
	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th>LegacyId</th>
				<th>Stem</th>
				<th>Answer</th>
				<th>Pvalue</th>
				<th>Swap</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($items as $item)
				<tr>
					<td class="legacyid">{{ ucwords($item->number) }}</td>
					<td class="stem">{{ ucwords($item->excerpt) }}</td>
					<td class="answer">{{ ucwords($item->content) }}</td>
					<td class="pvalue">{{ $item->pvalue }}</td>
					<td>
						<button class="btn btn-sm btn-warning btn-swap">{!! Icon::retweet() !!} Swap</button>
						{!! Form::hidden('items[]', $item->id) !!}
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>	
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
</div>

<script type="text/javascript">
	$(document).on('keyup', '#search', function(e){
		var $search = $(this);

		$('.pvalue').each(function(){
			if($(this).text() >= $search.val())
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

	// Swap item button clicked within modal
	$(document).on('click', '.btn-swap', function(){
		var $swapRow = $(this).parents('tr');

		// get the ID's 
		var oldId = $('#old-id').text();
		var newId = $swapRow.find('input[type=hidden]').val();

		// This is the row we want to replace
		var $toReplace = $('#item-row-'+oldId);

		// Now replace everything that needs to be
		$toReplace.find('.answer').text($swapRow.find('.answer').text());
		$toReplace.find('.stem').text($swapRow.find('.stem').text());
		$toReplace.find('.swap').prop('href', '/testitems/'+newId+'/swap');
		$toReplace.find('input[type=hidden]').val(newId);

		// Hide the modal
		$('#swap-item').modal('hide');

		// Flash a color on the row to show that it changed
		var bgc = $toReplace.find('td').css('backgroundColor');
		$toReplace.children('td').animate( { backgroundColor: "#ffffcc" }, 1 ).animate( { backgroundColor: bgc }, 3000 );	

		// Finally, change the id of the row
		$toReplace.attr('id', 'item-row-'+newId);
	
	});

	$('body').on('hidden.bs.modal', '.modal', function () {
    	$(this).removeData('bs.modal');
    	//$('.modal-content', this).empty();
	});
</script>