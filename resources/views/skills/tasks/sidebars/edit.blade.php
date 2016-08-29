<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{{-- Update --}}
	{!! Button::success(Icon::refresh().' Update')->submit() !!}
	
	{{-- Activate --}}
	@if($task->status == 'draft')
		<a href="{{ route('tasks.activate', $task->id) }}" data-confirm="Activate this Skill Task?<br><br>Are you sure?" class="btn btn-warning">
			{!! Icon::play_circle() !!} Activate
		</a>
	@endif

	@if($task->status == "active")
		{{-- Archive --}}
		<a href="{{ route('person.archive', ['tasks', $task->id]) }}" data-confirm="Archive this Skill Task? All Skill Tests containing this Task will also be archived.<br><br>Are you sure?" class="btn btn-danger">
			{!! Icon::lock() !!} Archive
		</a>
	@endif

	{{-- Clone --}}
	<a href="{{ route('tasks.save_as', $task->id) }}" data-confirm="Clone (Save As) this Skill Task?" class="btn btn-default">
		{!! Icon::share() !!} Save As
	</a>

	<a href="{{ route('tasks.print', $task->id) }}" class="btn btn-default">
		<span class="glyphicon glyphicon-print"></span> Print Task
	</a>
</div>