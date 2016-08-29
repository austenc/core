@if(Auth::user()->ability(['Admin', 'Staff'], []) && $hold)
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Hold</strong> Record is being ignored by all external sync scripts
	</div>
@endif