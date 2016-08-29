@if(Auth::user()->ability(['Admin', 'Staff'], []))
	@if($instructor->teaching_trainings->isEmpty())
		<div class="alert alert-warning">
			{!! Icon::flag() !!} <strong>No Trainings</strong> Unable to add {{ Lang::choice('core::terms.student', 1) }} Training
		</div>
	@endif
@endif