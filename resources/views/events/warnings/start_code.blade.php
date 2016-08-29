@if($event->locked && $event->start_code && $all_released)
	<div class="alert alert-info">
		{!! Icon::info_sign() !!} <strong>Start Code</strong> is <span class="monospace">{{ $event->start_code }}</span>
	</div>
@endif