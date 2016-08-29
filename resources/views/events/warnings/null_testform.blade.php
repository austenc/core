{{-- Event contains assigned NULL Testform --}}
@if(Auth::user()->ability(['Admin', 'Staff'], []) && $event->hasNullTestform)
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Null Testform</strong> Event Lock is disabled until resolved.
	</div>
@endif