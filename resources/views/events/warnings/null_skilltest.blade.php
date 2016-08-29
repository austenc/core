{{-- Event contains assigned NULL Skilltest --}}
@if(Auth::user()->ability(['Admin', 'Staff'], []) && $event->hasNullSkilltest)
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Null Skilltest</strong> Event Lock is disabled until resolved.
	</div>
@endif