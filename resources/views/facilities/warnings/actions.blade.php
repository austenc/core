@if(Auth::user()->ability(['Admin', 'Staff'], []))
	@if( ! in_array('Training', $facility->actions) && ! in_array('Testing', $facility->actions))
	<div class="alert alert-warning">
		{!! Icon::flag()  !!}<strong>Actions</strong> Not approved for Testing or Training
	</div>
	@endif
@endif