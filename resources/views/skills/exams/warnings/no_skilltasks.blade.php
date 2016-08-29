@if($skillexam->tasks->count() == 0)
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>No Skill Tasks</strong> Add Skill Test not available until Skill Tasks have been created
	</div>
@endif