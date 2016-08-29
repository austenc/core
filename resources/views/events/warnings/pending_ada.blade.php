@if(Auth::user()->ability(['Admin', 'Staff'], []) && $event->hasPendingADAStudent)
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Pending ADA</strong> {{ Lang::choice('core::terms.student', 1) }} {{ $event->hasPendingADAStudent->full_name }} has a pending ADA. Event Lock is disabled until resolved.
	</div>
@endif