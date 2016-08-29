@if(Auth::user()->ability(['Admin', 'Staff'], []) && $event->hasDuplicateSkilltests)
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Duplicate Skilltest</strong> Skill Test {{ $event->skills->keyBy('id')->get($event->has_duplicate_skilltests)->name }} has duplicate assigned skilltests.

		{{-- Admin Only can proceed with Lock --}}
		@if( ! Auth::user()->isRole('Admin'))
			Event lock is disabled until resolved.
		@endif
	</div>
@endif