@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'tasks.search', 'method' => 'POST']) !!}
	<div class="row">
		<div class="col-md-9">
			<p class="lead">Skill Tasks</p>

			@if(isset($tasksForReview) && $tasksForReview === true)
				<div class="alert alert-warning clearfix">
					<strong>Heads Up!</strong> There are tasks with setups and/or steps that need to be reviewed. 
					<a href="{{ route('tasks.review') }}" class="btn btn-warning pull-right">Review Tasks</a>
				</div>
			@endif

			<div class="form-group search-form">
				<div class="input-group">
					<!-- Single button -->
					<div class="btn-group input-group-btn" data-mimic="dropdown" data-mimic-target="#search-type">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							<span class="mimic-selected">Title</span> <span class="caret"></span>
						</button>
						@include('core::skills.tasks.partials.search_types')
					</div>

					{!! Form::text('search', null, ['placeholder' => 'Enter search term(s)', 'autofocus' => 'autofocus']) !!}
					<span class="input-group-btn">
						<button class="btn btn-primary search-submit" type="submit">
							{!! Icon::search() !!} <span class="hidden-xs">Search</span>
						</button>
					</span>
      			</div>
      			{!! Form::hidden('search_type', 'Title', ['id' => 'search-type']) !!}
      		</div>

      		@if( ! empty($searchTypes))
				@include('core::partials.search_terms', [
					'searchTypes' => $searchTypes,
					'controller'  => 'tasks'
				])
			@endif

			<div class="well">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>{!! Sorter::link('tasks.index', 'Title', ['sort' => 'title']) !!}</th>
							<th>Scenario</th>
							<th>Steps</th>
							<th>Setups</th>
						</tr>
					</thead>
					<tbody>
					@foreach ($tasks as $task)
						@if($task->status == 'draft')
						<tr class="warning">
						@elseif($task->status == 'active')
						<tr class="success">
						@else
						<tr class="danger">
						@endif
							<td>
								<a href="{{ route('tasks.edit', $task->id) }}">
									{{ $task->title }}
								</a><br>

								@if($task->status == 'draft')
								<span class="label label-warning">
								@elseif($task->status == 'active')
								<span class="label label-success">
								@else
								<span class="label label-danger">
								@endif
									{{ ucfirst($task->status) }}
								</span>
							</td>

							<td>{{ str_limit($task->scenario, 40) }}</td>
							<td class="monospace">{{ $task->steps->count() }}</td>
							<td class="monospace">{{ $task->setups->count() }}</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			</div>
		</div>

		{{-- Sidebar --}}
		@include('core::skills.tasks.sidebars.index')
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/skills/tasks/index.js') !!}
@stop