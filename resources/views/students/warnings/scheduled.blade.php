@if( ! empty($allScheduledEventExams) || ! empty($allScheduledEventSkills))
	<div class="alert alert-info">
		{!! Icon::calendar() !!} <strong>Scheduled</strong> {{ Lang::choice('core::terms.student', 1) }} has upcoming scheduled events
	</div>
@endif
