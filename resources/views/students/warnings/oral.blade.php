@if($student->is_oral)
<div class="alert alert-info">
	{!! Icon::volume_up() !!} <strong>Oral</strong> {{ Lang::choice('core::terms.student', 1) }} will be scheduled into Oral Tests only</a>
</div>
@endif