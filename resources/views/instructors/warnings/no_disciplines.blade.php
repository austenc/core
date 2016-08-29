@if(Auth::user()->ability(['Admin', 'Staff'], []))
	@if($instructor->disciplines->isEmpty())
	<div class="alert alert-warning">
		<strong>Warning! </strong>No Disciplines associated with this {{ Lang::choice('core::terms.instructor', 1) }}.
	</div>
	@endif
@endif