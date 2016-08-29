@if($student->hasFakeSSN())
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>SSN</strong> Record is using a fake SSN
	</div>
@endif