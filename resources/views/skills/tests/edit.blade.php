@extends('core::layouts.default')

@section('content')
	{!! Form::model($skill, ['route' => ['skills.update', $skill->id], 'method' => 'PUT']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>
					{{ $skill->status == 'draft' ? 'Edit' : 'View' }} Skill Test
				</h1>
			</div>
			{!! HTML::backlink('skills.index') !!}
		</div>

		@if($skill->status == 'draft')
		<div class="alert alert-warning">
			<strong>Draft</strong> Skill Test
		@elseif($skill->status == 'active')
		<div class="alert alert-success">
			<strong>Active</strong> Skill Test
		@else
		<div class="alert alert-danger">
			<strong>Archived</strong> Skill Test
		@endif
		</div>

		{{-- Warnings --}}
		@if($skill->parent_id)
		<div class="alert alert-info">
			Child of <a href="{{ route('skills.edit', $skill->parent_id) }}">Skill #{{ $skill->parent_id }}</a>
		</div>
		@endif

		{{-- Basic Info --}}
		<h3>Information</h3>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('header', 'Skill Test') !!} @include('core::partials.required')
					@if($skill->status == 'draft')
						{!! Form::text('header') !!}
						<span class="text-danger">{{ $errors->first('header') }}</span>
					@else
						<p class="form-control-static">{{ ucfirst($skill->header) }}</p>
					@endif
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('skill_exam', 'Skill Exam') !!}
					@if($skill->status == 'draft')
						<div class="input-group">
							{!! Form::text('skill_exam', $exam->name, ['disabled']) !!}
							<div class="input-group-addon">
								<a href="{{ route('skillexams.edit', $exam->id) }}">
									{!! Icon::pencil() !!}
								</a>
							</div>
						</div>
					@else
						<p class="form-control-static">
							<a href="{{ route('skillexams.edit', $exam->id) }}">
								{{ ucfirst($exam->name) }}
							</a>
						</p>
					@endif

					{!! Form::hidden('skillexam_id', $exam->id, ['id' => 'skillexam_id']) !!}
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('minimum', 'Minimum') !!} @include('core::partials.required')
					@if($skill->status == 'draft')
						{!! Form::text('minimum') !!}
						<span class="text-danger">{{ $errors->first('minimum') }}</span>
					@else
						<p class="form-control-static">{{ $skill->minimum }}</p>
					@endif
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('description', 'Description') !!}
					@if($skill->status == 'draft')
						<textarea class="form-control" name="description">{{ $skill->description }}</textarea>
					@else
						<p class="form-control-static">{{ $skill->description }}</p>
					@endif
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('comments', 'Comments') !!}
					@if($skill->status == 'draft')
						<textarea class="form-control" name="comments">{{ $skill->comments }}</textarea>
					@else
						<p class="form-control-static">{{ $skill->comments }}</p>
					@endif
				</div>
			</div>
		</div>

		{{-- Tasks --}}
		<div class="row">
			<div class="col-md-8">
				<h3>Tasks</h3>
			</div>
			@if($skill->status == 'draft')
			<div class="col-md-4 valign-heading">
				<a href="{{ route('skills.add_task', [$skill->id]) }}" class="btn btn-sm btn-info pull-right add-task" data-toggle="modal" data-target="#add-task">
					{!! Icon::tags() !!} Add Task
				</a>
			</div>
			@endif
		</div>
		<div class="well">	
			<table class="table table-striped" id="task-table">
				<thead>
					<tr>
						@if($skill->status == 'draft')
						<th></th>
						<th>#</th>
						@else
						<th>#</th>
						@endif
						<th>Title</th>
						<th>Scenario</th>
						<th>Weight</th>
						{{-- Actions --}}
						@if($skill->status == 'draft')
							<th></th>
						@endif
					</tr>
				</thead>

				<tbody>
					@foreach ($tasks as $i => $task)
					<tr>
						@if($skill->status == 'draft')
						<td>
							@include('core::skills.tests.partials.ordinal')
						</td>
						@endif

						<td>
							<span class="ordinal lead text-muted">{{ $task->pivot->ordinal }}</span>
						</td>

						<td>
							<a href="{{ route('tasks.edit', $task->id) }}" target="_blank">
								{{ $task->title }}
							</a>
							{!! Form::hidden('task_ids['.($i + 1).']', $task->id, ['class' => 'task-order']) !!}
						</td>

						<td>{{ str_limit($task->scenario, 40) }}</td>

						<td class="monospace">{{ $task->weight }}</td>

						{{-- Actions --}}
						@if($skill->status == "draft")
						<td>
							<div class="btn-group pull-right">
								<a data-href="{{ route('skills.remove_task', [$skill->id, $task->id]) }}" data-confirm="Remove this Task from the Skill Test?" class="btn btn-link remove-button" data-toggle="tooltip" data-original-title="Remove Task">
									{!! Icon::trash() !!}
								</a>
							</div>
						</td>
						@endif
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			@if($skill->status == 'draft')
				{!! Button::success(Icon::refresh().' Update')->submit() !!}

				<a href="{{ route('skills.activate', $skill->id) }}" data-confirm="Activate this Skill Test?" class="add-setup btn btn-warning">
					{!! Icon::play_circle() !!} Activate
				</a>
			@endif

			@if($skill->status != 'archived')
				<a href="{{ route('person.archive', ['skills', $skill->id]) }}" data-confirm="Archive this Skill Test?<br><br>Are you sure?" class="add-setup btn btn-danger">
					{!! Icon::lock() !!} Archive
				</a>
			@endif

			<a href="{{ route('skills.save_as', $skill->id) }}?skill_exam={{ $exam->id }}" data-confirm="Clone (Save As) this Skill Test?<br><br>Are you sure?" class="btn btn-default">
				{!! Icon::share() !!} Save As
			</a>
		</div>
	</div>

	{!! Form::close() !!}
	{!! HTML::modal('add-task') !!}

	@include('core::skills.tests.partials.add_task')
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/skills/tests/edit.js') !!}
@stop