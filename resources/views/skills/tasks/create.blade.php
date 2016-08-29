@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'tasks.store', 'id' => 'task-create']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				@if($task->parent_id)
				<h1>Save Cloned Task</h1>
				@else
				<h1>Create Skill Task</h1>
				@endif
			</div>
			<div class="col-md-4 back-link">
				<a href="{{ route('tasks.index') }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to All Skill Tasks</a>
			</div>
		</div>

		@if($task->parent_id)
		<div class="alert alert-warning">
			<strong>Child of <a href="{{ route('tasks.edit', $task->parent_id) }}">Task #{{ $task->parent_id }}</a></strong>
		</div>
		@endif

		<h3>Task</h3>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('title', 'Title') !!} @include('core::partials.required')
					{!! Form::text('title', $task->title) !!}
					<span class="text-danger">{{ $errors->first('title') }}</span>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('long_title', 'Long Title') !!}
					{!! Form::text('long_title', $task->long_title) !!}
					<span class="text-danger">{{ $errors->first('long_title') }}</span>
				</div>
			</div>
			<div class="form-group">
				{!! Form::label('scenario', 'Scenario') !!} @include('core::partials.required')
				<textarea name="scenario" class="form-control">{{ Input::old('scenario') ? Input::old('scenario') : $task->scenario }}</textarea>
				<span class="text-danger">{{ $errors->first('scenario') }}</span>
			</div>
			<div class="form-group">
				{!! Form::label('note', 'Note to TO') !!}
				<textarea name="note" class="form-control">{{ Input::old('note') ? Input::old('note') : $task->note }}</textarea>
				<span class="text-danger">{{ $errors->first('note') }}</span>
			</div>
			<div class="form-group row">
				<div class="col-md-4">
					{!! Form::label('weight', 'Weight') !!} @include('core::partials.required')
					{!! Form::text('weight', $task->weight) !!}
					<span class="text-danger">{{ $errors->first('weight') }}</span>
				</div>
				<div class="col-md-4">
					{!! Form::label('minimum', 'Minimum') !!}  @include('core::partials.required')
					{!! Form::text('minimum', $task->minimum) !!}
					<span class="text-danger">{{ $errors->first('minimum') }}</span>
				</div>
				<div class="col-md-4">
					{!! Form::label('average_time', 'Average Time') !!}
					{!! Form::text('average_time', $task->avg_time) !!}
					<span class="text-danger">{{ $errors->first('average_time') }}</span>
				</div>
			</div>

			<div class="form-group">
				{!! Form::label('enemies', 'Enemies') !!}
				<div class="input-group">
					{!! Form::text('enemies', $enemies) !!}
					<span class="input-group-btn">
						<a href="{{ route('tasks.enemies') }}" class="btn btn-success find-enemies" data-toggle="modal" data-target="#select-enemies">{!! Icon::plus() !!}</a>
					</span>
				</div>
			</div>
		</div>

		<h3>Skillexams</h3>
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
					</tr>
				</thead>
				<tbody>
					@foreach($skillexams as $i => $exam)
						@if((Input::old('skillexam_id') && in_array($exam->id, Input::old('skillexam_id'))) || in_array($exam->id, $task->skillexams->lists('id')->all()) || (Input::get('skillexam') && Input::get('skillexam') == $exam->id))
						<tr data-clickable-row class="success">
						@else
						<tr data-clickable-row>
						@endif
							<td>{!! Form::checkbox('skillexam_id[]', $exam->id, (Input::old('skillexam_id') && in_array($exam->id, Input::old('skillexam_id'))) || in_array($exam->id, $task->skillexams->lists('id')->all()) || (Input::get('skillexam') && Input::get('skillexam') == $exam->id)) !!}</td>
							<td>
								{{ $exam->name }}
								@if( ! empty($exam->abbrev))
								<br>
								<small>{{ $exam->abbrev }}</small>
								@endif
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
					@if(Input::old('setups'))
						<?php
                            $setups = array_values(Input::old('setups'));
                            $setup_comments = array_values(Input::old('setup_comments'));
                        ?>
						@if(count($setups) > 0)
							@for($i = 0; $i < count($setups); $i++)
								<tr class="setup">
									<td><span class="setup-order">{{ $setups[$i] }}</span></td>

									<td>
										<input type="hidden" name="setup_ids[{{{ $i }}}]" value="{{{ $setups[$i] }}}" class="setup-id">
										<textarea name="setups[{{{ $i }}}]" class="form-control setups">{{{ $setups[$i] }}}</textarea>
									</td>

									<td>
										<textarea name="setup_comments[{{{ $i }}}]" class="form-control setup_comments">{{{ $setup_comments[$i] }}}</textarea>
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
									{{ ($i + 1) }}
									{!! Form::hidden('setup_ids[]', $setup->id, ['class' => 'setup-id']) !!}
								</td>

								<td>
									<textarea name="setups[]" class="form-control">{{ $setup->setup }}</textarea>
								</td>

								<td>
									<textarea name="setup_comments[]" class="form-control">{{ $setup->comments }}</textarea>
								</td>

								<td>
									<a href="{{ route('setups.remove', [$task->id, $setup->id]) }}" class="btn btn-link pull-right remove-button" data-confirm="Remove <strong>(and delete)</strong> this Setup?">{!! Icon::trash() !!}</a>
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
				<h3>
					Steps <small>Input may be added after saving Task</small>
				</h3>
			</div>
			<div class="col-md-4 valign-heading">
				<a class="add-step btn btn-info btn-sm pull-right">{!! Icon::tags() !!} Add Step</a>
			</div>
		</div>
		<div class="well">
			@if($errors->has('step_ids') || $errors->has('step_weights'))
			<div class="alert alert-danger" role="alert">
				{{ $errors->first('step_ids') }}
				{{ $errors->first('step_weights') }}
			</div>
			@endif
			
			<table class="table table-striped step-table" id="steps">
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
					<?php $x = 0; ?>

					@if(Input::old())
						<?php
                            if (Input::old('step_ids')) {
                                $step_ids = array_values(Input::old('step_ids'));
                                $step_weights = array_values(Input::old('step_weights'));
                                $step_outcomes = array_values(Input::old('step_outcomes'));
                                $step_order = array_values(Input::old('step_order'));
                                $step_comments = array_values(Input::old('step_comments'));
                                $step_alts = array_values(Input::old('step_alts'));
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

									<td>
										<input name="step_key[{{{ $step_ids[$i] }}}]" type="checkbox" value="1" class="step-key">
										<input name="step_ids[{{{ $step_ids[$i] }}}]" type="hidden" value="{{{ $step_ids[$i] }}}" class="step-ids">
									</td>

									<td class="col-md-1">
										<input name="step_weights[{{{ $step_ids[$i] }}}]" class="form-control step-weight" value="{{{ $step_weights[$i] }}}">
									</td>

									<td>
										<textarea name="step_outcomes[{{{ $step_ids[$i] }}}]" class="form-control step-outcomes">{{{ $step_outcomes[$i] }}}</textarea>
										<input name="step_order[{{{ $step_ids[$i] }}}]" type="hidden" class="step-order" value="{{{ $step_order[$i] }}}">
									</td>

									<td>
										<textarea name="step_comments[{{{ $step_ids[$i] }}}]" class="form-control step-comments">{{{ $step_comments[$i] }}}</textarea>
										<input name="step_alts[{{{ $step_ids[$i] }}}]" type="hidden" class="step-alts">
									</td>

									<td>
										<button class="btn btn-link remove-button pull-right">{!! Icon::trash() !!}</button>
									</td>
								</tr>
							@endfor
						@endif
					@else
						@foreach ($steps as $i => $step)
							<tr class="step step-{{{ $step->id }}}">
								<td>
									@include('core::skills.steps.partials.ordinal')
								</td>

								<td>
									<span class="ordinal">
										{{ $step->ordinal }}
									</span>
								</td>

								<td>
									{!! Form::checkbox('step_key[]', $step->is_key, ($step->is_key == 1), ['class' => 'step-key']) !!}
									{!! Form::hidden('step_ids[]', $step->id) !!}
								</td>

								<td class="col-md-1">
									{!! Form::text('step_weights[]', $step->weight) !!}
								</td>

								<td>
									<textarea class="form-control step-outcomes" name="step_outcomes[]">{{ $step->expected_outcome }}</textarea>
									<input type="hidden" name="step_order[]" class="step-order" value="{{{ $step->ordinal }}}">
								</td>

								<td>
									<textarea class="form-control" name="step_comments[]">{{ $step->comments }}</textarea>
								</td>

								<td class="col-md-1">
									@include('core::skills.steps.partials.actions', ['step' => $step])
								</td>
							</tr>

							@if($step->inputs->count() > 0)
								@foreach($step->inputs as $input)
								<tr class="input input-{{{ $input->id }}}">
									<td colspan="7">
										@include('core::skills.steps.partials.add_input', ['c' => $x, 'toolbar' => true, 'input' => $input, 'display' => true, 'step' => $step])
									</td>
								</tr>
								<?php $x++; ?>
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
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			@if($task->parent_id)
				{!! Button::success(Icon::plus_sign().' Save Cloned Task')->submit() !!}
			@else
				{!! Button::success(Icon::plus_sign().' Create')->submit() !!}
			@endif
		</div>
	</div>

	@if($task->parent_id)
		{!! Form::hidden('parent_id', $task->parent_id) !!}
	@endif

	{!! Form::close() !!}

	{!! HTML::modal('preview', 'modal-preserve') !!}
	{!! HTML::modal('select-enemies') !!}
	{!! HTML::modal('add-input') !!}

	@include('core::skills.steps.partials.add', ['c' => 0])
	@include('core::skills.steps.partials.add_input', ['c' => 0, 'id' => 'input-prototype'])
	@include('core::skills.setups.partials.add')
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/skills/tasks/edit.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/bbcodes/helper.js') !!}
@stop