<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{!! Button::success(Icon::refresh().' Update')->submit()->block() !!}

	{{-- Add Test --}}
	@if($skillexam->tasks->count() > 0)
		<a href="{{ route('skills.create', ['skillexam' => $skillexam->id]) }}" class="btn btn-default btn-block">
			{!! Icon::list_alt() !!} Add Test
		</a>
	@endif
	
	{{-- Add Task --}}
	<a href="{{ route('tasks.create', ['skillexam' => $skillexam->id]) }}" class="btn btn-default btn-block">
		{!! Icon::leaf() !!} Add Task
	</a>	

	<hr>

	<ul class="nav list-group">
		<li class="list-group-item">
			<a href="#exam-info">{!! Icon::info_sign() !!} Information</a>
		</li>
		<li class="list-group-item">
			<a href="#exam-tasks">{!! Icon::leaf() !!} Skill Tasks</a>
		</li>
		<li class="list-group-item">
			<a href="#exam-tests">{!! Icon::list_alt() !!} Skill Tests</a>
		</li>
		<li class="list-group-item">
			<a href="#exam-requirements">{!! Icon::flag() !!} Requirements</a>
		</li>
	</ul>
</div>