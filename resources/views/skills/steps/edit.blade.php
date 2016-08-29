@extends('core::layouts.default')

@section('content')
	{!! Form::model($step, ['route' => ['steps.update', $step->id], 'method' => 'PUT']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Edit Step</h1>
			</div>
			{!! HTML::backlink('steps.index') !!}
		</div>

		{{-- Warnings --}}
		@if($step->vinput_review)
		<div class="alert alert-warning">
			<strong>Warning!</strong> Step contains possible unconverted variable input. 
			Inspect the <u>Expected Outcome</u> carefully. When you are sure the step is correct, use the <u>Unflag</u> button.
		</div>
		@endif
		@if($step->task->status == 'active')
		<div class="alert alert-warning">
			<strong>Warning!</strong> Step belongs to an Active Task! Changes may affect ongoing tests.
		</div>
		@endif

		{{-- Step --}}
		<h3>
			Step
			<small>#{{ $step->ordinal }} in Task Step order</small>
		</h3>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('expected_outcome', 'Expected Outcome') !!} @include('core::partials.required')
					<textarea class="form-control" name="expected_outcome" id="expected_outcome">{{ $step->expected_outcome }}</textarea>
					<span class="text-danger">{{ $errors->first('expected_outcome') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('alt_display', 'Alt Display') !!}
					<textarea class="form-control" name="alt_display" id="alt_display">{{ $step->alt_display }}</textarea>
					<span class="text-danger">{{ $errors->first('alt_display') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('weight', 'Weight') !!} @include('core::partials.required')
					{!! Form::text('weight') !!}
					<span class="text-danger">{{ $errors->first('weight') }}</span>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('comments', 'Comments') !!}
					<textarea class="form-control" name="comments" id="comments">{{ $step->comments }}</textarea>
					<span class="text-danger">{{ $errors->first('comments') }}</span>
				</div>
			</div>
		</div>

		{{-- Task --}}
		<h3>Task</h3>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('title', 'Title') !!}
					<p class="form-control-static">
						<a href="{{ route('tasks.edit', $step->task->id) }}" target="_blank">
							{{ $step->task->title }}
						</a>
					</p>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('scenario', 'Scenario') !!}
					<p class="form-control-static">{{ $step->task->scenario }}</p>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('note', 'Note for '.Lang::choice('core::terms.observer', 1)) !!}
					<p class="form-control-static">{{ $step->task->note }}</p>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-4">
					{!! Form::label('status', 'Status') !!}
					<p class="form-control-static">{{ ucfirst($step->task->status) }}</p>
				</div>

				<div class="col-md-4">
					{!! Form::label('taskweight', 'Weight') !!}
					<p class="form-control-static monospace">{{ $step->task->weight }}</p>
				</div>

				<div class="col-md-4">
					{!! Form::label('minimum', 'Minimum') !!}
					<p class="form-control-static monospace">{{ $step->task->minimum }}</p>
				</div>
			</div>
		</div>

		{{-- Step Inputs --}}
		<h3>Inputs</h3>
		<div class="well">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>#</th>
						<th>Type</th>
						<th>Answer</th>
						<th>Tolerance</th>
						<th>Option Text</th>
						<th>Value</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				@foreach ($step->inputs as $input)
					<tr class="input input-{{{ $input->id }}}">
						<td>
							<span class="lead text-muted">
								{{ $input->id }}
							</span>
							{!! Form::hidden('input_id[]', $input->id, ['class' => 'input-id']) !!}
						</td>
						<td>{{ ucfirst($input->type) }}</td>
						<td>{{ $input->answer }}</td>
						<td>{{ $input->tolerance }}</td>
						<td>{{ isset($options[$input->id]['text']) ? implode('<br>', $options[$input->id]['text']) : '' }}</td>
						<td>{{ isset($options[$input->id]['val']) ? implode('<br>', $options[$input->id]['val']) : '' }}</td>
						<td class="col-md-2">
							@include('core::skills.steps.partials.input_actions')
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::skills.steps.sidebars.edit')
	</div>

	{!! Form::hidden('step_id', $step->id, ['class' => 'step-id', 'id' => 'step_id']) !!}
	{!! Form::close() !!}

	{!! HTML::modal('preview') !!}
	{!! HTML::modal('add-input') !!}

	@include('core::skills.steps.partials.add_input', ['c' => 0, 'id' => 'input-prototype'])
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/skills/steps/edit.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/bbcodes/helper.js') !!}
@stop