@if($student->is_hold)
<div class="alert alert-warning">
	{!! Icon::flag() !!} <strong>Hold</strong> preventing Agency sync
</div>
@endif
