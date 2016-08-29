@if($event->locked)
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Locked</strong> Scheduling and other functions have been disabled
	</div>
@endif