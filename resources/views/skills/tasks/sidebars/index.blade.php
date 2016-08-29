<div class="col-md-3">
	<a href="{{ route('tasks.create') }}" class="btn btn-block btn-success">{!! Icon::plus_sign() !!} New Skill Task</a>

	<hr>
	
	<div class="list-group">
		<a class="list-group-item list-group-item-success">
			{!! Icon::star() !!} Active 
			@if(isset($count['active']))
				<span class="badge">{{ $count['active'] }}</span>
			@endif
		</a>

		<a class="list-group-item list-group-item-warning">
			{!! Icon::pencil() !!} Draft
			@if(isset($count['draft']))
				<span class="badge">{{ $count['draft'] }}</span>
			@endif
		</a>
		
		<a class="list-group-item list-group-item-danger">
			{!! Icon::flag() !!} Archived
			@if(isset($count['archived']))
				<span class="badge">{{ $count['archived'] }}</span>
			@endif
		</a>
	</div>
</div>