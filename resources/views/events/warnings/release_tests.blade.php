@if($event->locked == 1 && ! $all_released && $event->test_date == date('m/d/Y'))
	<div class="alert alert-info">
		<strong>Attention!</strong> Click <span class="monospace">Release Tests</span> to begin testing.
	</div>
@endif