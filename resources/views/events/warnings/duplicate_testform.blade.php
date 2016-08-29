@if(Auth::user()->ability(['Admin', 'Staff'], []) && $event->hasDuplicateTestforms)
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Duplicate Testform</strong> Knowledge Test {{ $event->exams->keyBy('id')->get($event->has_duplicate_testforms)->name }} has duplicate assigned testforms. 

		{{-- Admin Only can proceed with Lock --}}
		@if( ! Auth::user()->isRole('Admin'))
			Event lock is disabled until resolved.
		@endif
	</div>
@endif