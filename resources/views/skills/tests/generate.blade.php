@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'skills.store']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Save New Skill Test</h1>
			</div>
			{!! HTML::backlink('skills.create') !!}
		</div>

		{{-- Warnings --}}
		@if($skill->parent_id)
			<div class="alert alert-warning">
				Child of <a href="{{ route('skills.edit', $skill->parent_id) }}">Skill #{{ $skill->parent_id }}</a>
			</div>
		@endif

		<h3>Information</h3>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('header', 'Skill Test') !!} @include('core::partials.required')
					{!! Form::text('header', $skill->header) !!}
					<span class="text-danger">{{ $errors->first('header') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('skillexam', 'Skill Exam') !!}
					<div class="input-group">
						{!! Form::text('skill_exam', $skillexam->name, ['disabled']) !!}
						<div class="input-group-addon">
							<a href="{{ route('skillexams.edit', $skillexam->id) }}">
								{!! Icon::pencil() !!}
							</a>
						</div>
					</div>

					@if(isset($skillexam))
						{!! Form::hidden('skillexam_id', $skillexam->id, ['id' => 'skillexam_id']) !!}
					@endif
					<span class="text-danger">{{ $errors->first('skillexam') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('minimum', 'Minimum') !!} @include('core::partials.required')
					{!! Form::text('minimum', $skill->minimum) !!}
					<span class="text-danger">{{ $errors->first('minimum') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('description', 'Description') !!}
					<textarea class="form-control" name="description">{{ $skill->description }}</textarea>
					<span class="text-danger">{{ $errors->first('description') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('comments', 'Comments') !!}
					<textarea class="form-control" name="comments">{{ $skill->comments }}</textarea>
					<span class="text-danger">{{ $errors->first('comments') }}</span>
				</div>
			</div>
		</div>

		<h3 id="tasks">Tasks</h3>
		<div class="well">
			<table class="table table-striped" id="task-table">
				<thead>
					<tr>
						<th></th>
						<th>#</th>
						<th>Title</th>
						<th>Scenario</th>
						<th>Weight</th>
						<th></th>
					</tr>
				</thead>

				<tbody>
					@foreach ($tasks as $i => $task)
					<tr>
						<td>
							@include('core::skills.tests.partials.ordinal')
						</td>

						<td>
							<span class="ordinal lead text-muted">
								{{ ($i + 1) }}
							</span>
							{!! Form::hidden('task_ids['.($i + 1).']', $task->id, ['class' => 'task-order']) !!}
						</td>

						<td>
							<a href="{{ route('tasks.edit', [$task->id]) }}">
								{{ $task->title }}
							</a>
						</td>

						<td>{{ str_limit($task->scenario, 40) }}</td>

						<td class="monospace">{{ $task->weight }}</td>

						<td>
							<div class="btn-group pull-right">
								<a data-confirm="Remove this Task from the Skill Test?" class="btn btn-link remove-button" data-toggle="tooltip" data-original-title="Remove Task">
									{!! Icon::trash() !!}
								</a>
							</div>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			{!! Button::success(Icon::plus_sign().' Save Skill Test')->submit() !!}

			<a href="{{ route('skills.add_task') }}" class="btn btn-default add-task" data-toggle="modal" data-target="#add-task">
				{!! Icon::tags() !!} Add Task
			</a>
		</div>
	</div>	

	@if($skill->parent_id)
		{!! Form::hidden('parent_id', $skill->parent_id); !!}
	@endif

	{!! Form::close() !!}

	@include('core::skills.tests.partials.add_task')

	{!! HTML::modal('add-task') !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/skills/tests/edit.js') !!}

	<script type="text/javascript">
		$(document).ready(function(){
			// add excluded ids
			adjust_add_task_url();
		});
	</script>
@stop