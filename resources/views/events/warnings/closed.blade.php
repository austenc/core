@if( ! $event->is_regional)
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Closed Event</strong> Only {{ Lang::choice('core::terms.student', 2) }} trained at {{ Lang::choice('core::terms.facility_testing', 1) }} or associated {{ Lang::choice('core::terms.facility_training', 1) }} are eligible
	</div>
@endif