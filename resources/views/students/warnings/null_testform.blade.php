{{-- Student is scheduled for a NULL testform --}}
@if(in_array(null, $student->scheduledAttempts->lists('testform_id')->all()))
	<div class="alert alert-danger">
		{!! Icon::warning_sign() !!} <strong>Null Testform</strong> {{ Lang::choice('core::terms.student', 1) }} is scheduled with a NULL testform
	</div>
@endif

{{-- Student is scheduled for a NULL skilltest --}}
@if(in_array(null, $student->scheduledSkillAttempts->lists('skilltest_id')->all()))
	<div class="alert alert-danger">
		{!! Icon::warning_sign() !!} <strong>Null Skilltest</strong> {{ Lang::choice('core::terms.student', 1) }} is scheduled with a NULL skilltest
	</div>
@endif