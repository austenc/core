@if($student->is_locked)
<div class="alert alert-warning">
	{!! Icon::flag() !!} <strong>Locked</strong> Login has been disabled
</div>
@endif