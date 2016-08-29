@extends('core::layouts.default')

@section('content')
	{!! Form::model($task, ['route' => ['tasks.update', $task->id], 'method' => 'PUT']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Edit Skill Task</h1>
			</div>
			{!! HTML::backlink('tasks.index') !!}
		</div>

		{{-- Warnings --}}
		@if($task->setup_review)
			<div class="alert alert-warning">
				<strong>Warning!</strong> This Task contains possible unconverted setups within the <i>Note for TO</i> field.
			</div>
		@endif
		@if($stepsToReview == true)
			<div class="alert alert-warning">
				<strong>Warning!</strong> This task's steps have variable inputs that need to be reviewed and fixed. 
			</div>
		@endif

		@if($task->status == 'draft')
		<div class="alert alert-warning">
			<strong>Draft</strong> Skill Task
		@elseif($task->status == 'active')
		<div class="alert alert-success">
			<strong>Active</strong> Skill Task
		@else
		<div class="alert alert-danger">
			<strong>Archived</strong> Skill Task
		@endif
		</div>

		<h3>Information</h3>
		<div class="well">
			@if($task->parent_id)
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('parent', 'Parent Task') !!}
					<p class="form-control-static"><a href="{{ route('tasks.edit', $task->parent_id) }}"># {{ $task->parent->id }} ... {{ $task->parent->title }}</a></p>
				</div>
			</div>
			@endif

			@if( ! $task->children->isEmpty())
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('children', 'Child Tasks') !!}
					@foreach($task->children as $child)
						<p class="form-control-static">
							<a href="{{ route('tasks.edit', $child->id) }}"># {{ $child->id }} ... {{ $child->title }}</a>
						</p>
					@endforeach
				</div>
			</div>
			@endif

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('title', 'Title') !!} @include('core::partials.required')
					{!! Form::text('title') !!}
					<span class="text-danger">{{ $errors->first('title') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('long_title', 'Long Title') !!}
					{!! Form::text('long_title') !!}
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('scenario', 'Scenario') !!} @include('core::partials.required')
					<textarea class="form-control" name="scenario">{{ $task->scenario }}</textarea>
					<span class="text-danger">{{ $errors->first('scenario') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('note', 'Note to '.Lang::choice('core::terms.observer', 1)) !!}
					<textarea class="form-control" name="note">{{ $task->note }}</textarea>
					<span class="text-danger">{{ $errors->first('note') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-4">
					{!! Form::label('weight', 'Weight') !!} @include('core::partials.required')
					{!! Form::text('weight') !!}
					<span class="text-danger">{{ $errors->first('weight') }}</span>
				</div>
				<div class="col-md-4">
					{!! Form::label('minimum', 'Minimum') !!} @include('core::partials.required')
					{!! Form::text('minimum') !!}
					<span class="text-danger">{{ $errors->first('minimum') }}</span>
				</div>
				<div class="col-md-4">
					{!! Form::label('average_time', 'Average Time') !!}
					{!! Form::text('average_time') !!}
					<span class="text-danger">{{ $errors->first('average_time') }}</span>
				</div>
			</div>

			<div class="form-group">
				{!! Form::label('enemies', 'Enemies') !!}
				<div class="input-group">
					{!! Form::text('enemies', $enemies) !!}
					<span class="input-group-btn">
						<a href="{{ route('tasks.enemies', $task->id) }}" class="btn btn-success find-enemies" data-toggle="modal" data-target="#select-enemies">{!! Icon::plus() !!}</a>
					</span>
				</div>
			</div>

			@if($task->status != "draft")
				<div class="form-group">
					{!! Form::label('attachedExams', 'Skillexams') !!}
					@foreach($task->skillexams as $exam)
						<br><a href="{{ route('skillexams.edit', $exam->id) }}">{{ $exam->name }}</a>
					@endforeach
				</div>
			@endif
		</div>

		{{-- Skillexams --}}
		<h3>
			Skill Exams
			<small>Task will appear on these Exams</small>
		</h3>
		<div class="well">
			@if($errors->has('skillexam_id'))
			<div class="alert alert-danger" role="alert">
				{{ $errors->first('skillexam_id') }}
			</div>
			@endif
			<table class="table table-striped" id="skillexams">
				<thead>
					<tr>
						<th></th>
						<th>Name</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@foreach($skillexams as $i => $exam)
						@if(Input::old('skillexam_id.'.$i) == $exam->id || ( ! Input::old() && in_array($exam->id, $task->skillexams->lists('id')->all())))
						<tr data-clickable-row class="success">
						@else
						<tr data-clickable-row>
						@endif
							<td>{!! Form::checkbox('skillexam_id['.$i.']', $exam->id, in_array($exam->id, $task->skillexams->lists('id')->all())) !!}</td>
							<td>
								{{ $exam->name }}
								@if( ! empty($exam->abbrev))
									<br><small>{{ $exam->abbrev }}</small>
								@endif
							</td>
							<td>
								<a href="{{ route('skillexams.edit', $exam->id) }}" class="btn btn-icon pull-right">{!! Icon::pencil() !!}</a>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		{{-- Setups --}}
		<div class="row">
			<div class="col-md-8">
				<h3>Setups</h3>
			</div>
			<div class="col-md-4 valign-heading">
				<a class="add-setup btn btn-info btn-sm pull-right">{!! Icon::tags() !!} Add Setup</a>
			</div>
		</div>
		<div class="well">
			<table class="table table-striped" id="setups">
				<thead>
					<tr>
						<th>#</th>
						<th>Setup</th>
						<th>Comments</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@if(Input::old())
						<?php
                            if (Input::old('setup_ids')) {
                                $setup_ids = array_values(Input::old('setup_ids'));
                                $setups = array_values(Input::old('setups'));
                                $setup_comments = array_values(Input::old('setup_comments'));
                            } else {
                                $setup_ids = null;
                            }
                        ?>
						@if(count($setup_ids) > 0)
							@for($i = 0; $i < count($setup_ids); $i++)
								<tr class="setup">
									<td><span class="setup-order">{{ $setups[$i] }}</span></td>
									<td>
										<input type="hidden" name="setup_ids[{{{ $i }}}]" value="{{{ $setup_ids[$i] }}}" class="setup-id">
										<textarea name="setups[{{{ $i }}}]" class="form-control setups">{{ $setups[$i] }}</textarea>
									</td>
									<td>
										<textarea name="setup_comments[{{{ $i }}}]" class="form-control setup_comments">{{ $setup_comments[$i] }}</textarea>
									</td>
									<td>
										<span class="btn btn-link remove-button pull-right">{!! Icon::trash() !!}</span>
									</td>
								</tr>
							@endfor
						@endif
					@else
						@foreach ($setups as $i => $setup)
							<tr class="setup">
								<td>
									<span class="setup-order">{{{ ($i + 1) }}}</span>
									{!! Form::hidden('setup_ids['.$i.']', $setup->id, ['class' => 'setup-id']) !!}
								</td>

								<td>
									<textarea name="setups[{{{ $i }}}]" class="form-control">{{ $setup->setup }}</textarea>
								</td>

								<td>
									<textarea name="setup_comments[{{{ $i }}}]" class="form-control">{{ $setup->comments }}</textarea>
								</td>
								
								<td>
									<a data-href="{{ route('setups.remove', $setup->id) }}" class="btn btn-link remove-button pull-right" data-confirm="Remove and permanently delete this Task Setup<br><br>Are you sure?">{!! Icon::trash() !!}</a>
								</td>
							</tr>
						@endforeach
					@endif
				</tbody>
			</table>
		</div>

		{{-- Steps --}}
		<div class="row">
			<div class="col-md-8">
				<h3>Steps</h3>
			</div>
			<div class="col-md-4 valign-heading">
				<a class="add-step btn btn-info btn-sm pull-right">{!! Icon::tags() !!} Add Step</a>
			</div>
		</div>
		<div class="well">
			<span class="text-danger">{{ $errors->first('step_ids') }}</span>
			<span class="text-danger">{{ $errors->first('step_weights') }}</span>
			<table class="table table-hover step-table" id="steps">
				<thead>
					<tr>
						<th></th>
						<th>#</th>
						<th>Key</th>
						<th>Weight</th>
						<th>Expected Outcome</th>
						<th>Comments</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					@if(Input::old())
						<?php
                            if (Input::old('step_ids')) {
                                $step_ids = array_values(Input::old('step_ids'));
                                $step_weights = array_values(Input::old('step_weights'));
                                $step_outcomes = array_values(Input::old('step_outcomes'));
                                $step_order = array_values(Input::old('step_order'));
                                $step_comments = array_values(Input::old('step_comments'));
                            } else {
                                $step_ids = null;
                            }
                        ?>
						@if(count($step_ids) > 0)
							@for($i = 0; $i < count($step_ids); $i++)
								<tr class="step">
									<td>
										@include('core::skills.steps.partials.ordinal')
									</td>
									<td>
										<span class="ordinal"></span>
									</td>
									<td>{}
										<input name="step_key[{{{ $step_ids[$i] }}}]" type="checkbox" value="1" class="step-key">
										<input name="step_ids[{{{ $step_ids[$i] }}}]" type="hidden" value="{{{ $step_ids[$i] }}}" class="step-ids">
									</td>
									<td class="col-md-1">
										<input name="step_weights[{{{ $step_ids[$i] }}}]" class="form-control step-weight" value="{{{ $step_weights[$i] }}}">
									</td>
									<td>
										<textarea name="step_outcomes[{{{ $step_ids[$i] }}}]" class="form-control step-outcomes">{{ $step_outcomes[$i] }}</textarea>
										<input name="step_order[{{{ $step_ids[$i] }}}]" type="hidden" class="step-order" value="{{{ $step_order[$i] }}}">
									</td>
									<td>
										<textarea name="step_comments[{{{ $step_ids[$i] }}}]" class="form-control step-comments">{{ $step_comments[$i] }}</textarea>
										<input name="step_alts[{{{ $step_ids[$i] }}}]" type="hidden" class="step-alts">
									</td>
									<td>
										<button class="btn btn-link remove-button pull-right">{!! Icon::trash() !!}</button>
									</td>
								</tr>
							@endfor
						@endif
					@else
						@foreach($steps as $i => $step)
							<tr class="step step-{{{ $step->id }}}">
								<td>
									@include('core::skills.steps.partials.ordinal')
								</td>
								<td>
									<span class="ordinal lead text-muted">
										{{ $step->ordinal }}
									</span>
								</td>
								<td>
									{!! Form::checkbox('step_key['.$i.']', $step->is_key, ($step->is_key == 1), ['class' => 'step-key']) !!}			
									{!! Form::hidden('step_ids['.$i.']', $step->id, ['class' => 'step-ids']) !!}
								</td>

								<td class="monospace">
									{!! Form::text('step_weights['.$i.']', $step->weight) !!}
								</td>

								<td>
									<textarea class="form-control step-outcomes" name="step_outcomes[{{{ $i }}}]">{{ $step->expected_outcome }}</textarea>			
									<input type="hidden" name="step_order[{{{ $i }}}]" class="step-order" value="{{{ $step->ordinal }}}">
								</td>

								<td>
									<textarea class="form-control" name="step_comments[{{{ $i }}}]">{{ $step->comments }}</textarea>
								</td>

								<td class="col-md-1">
									@include('core::skills.steps.partials.actions', ['step' => $step, 'addInput' => true, 'delete' => true])
								</td>
							</tr>
							
							@if($step->inputs->count() > 0)
								@foreach($step->inputs as $c => $input)
								<tr class="input input-{{{ $input->id }}}">
									<td colspan="7">
										@include('core::skills.steps.partials.add_input', ['c' => $c, 'toolbar' => true, 'input' => $input, 'display' => true, 'step' => $step])
									</td>
								</tr>
								@endforeach
							@endif
						@endforeach
					@endif
				</tbody>
			</table>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::skills.tasks.sidebars.edit')
	</div>

	{!! Form::hidden('task_id', $task->id, ['id' => 'task-id']) !!}
	{!! Form::close() !!}

	<div class="col-xs-12">
		@if($task->status == "draft")
			{!! Form::open(['route' => ['tasks.destroy', $task->id], 'method' => 'delete']) !!}
		        <button data-confirm="Permanently <strong>DELETE</strong> this Skill Task? This action <strong>CAN NOT BE REVERSED</strong>!<br><br>Are you sure?" type="submit" class="btn btn-danger">{!! Icon::trash() !!} Delete</button>
		    {!! Form::close() !!}
		@endif
	</div>

	{!! HTML::modal('select-enemies') !!}
    {!! HTML::modal('preview') !!}

	@include('core::skills.steps.partials.add', ['c' => 0])
	@include('core::skills.setups.partials.add')
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/skills/tasks/edit.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/bbcodes/helper.js') !!}
@stop