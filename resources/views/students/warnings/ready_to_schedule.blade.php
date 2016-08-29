@if($ineligibleExams->count() < $allExams->count() || $ineligibleSkills->count() < $allSkills->count())
<div class="alert alert-info">
	{!! Icon::calendar() !!} <strong>Scheduling</strong> {{ Lang::choice('core::terms.student', 1) }} is ready for scheduling
</div>
@endif