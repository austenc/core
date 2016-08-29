@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'skills.generate', 'method' => 'get']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Create Skill Test</h1>
			</div>
			{!! HTML::backlink('skills.index') !!}
		</div>

		<h3>Test Generation</h3>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('skill_exam', 'Select Skill Exam') !!} @include('core::partials.required')
					{!! Form::select('skill_exam', $skillexams->lists('name', 'id')->all(), Input::get('skillexam') || (Input::old() && Input::old('skill_exam'))) !!}
					<span class="text-danger">{{ $errors->first('skill_exam') }}</span>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('task_weights', 'Enter Test String') !!} @include('core::partials.required')
					{!! Form::text('task_weights', '', ['data-toggle' => 'tooltip', 'title' => 'String I522 generates Test containing 4 Tasks weighing: I, 5, and two 2\'s']) !!}
					<span class="text-danger">{{ $errors->first('task_weights') }}</span>
				</div>
			</div>
		</div>

		<h3>
			Active Tasks by Weight <small>per Skill Exam</small>
		</h3>
		<div class="well">
			<table class="table table-striped" id="num-tasks-table">
				<thead>
					<tr>
						<th>Skill Exam</th>
						<th>Weight</th>
						<th># Tasks</th>
					</tr>
				</thead>

				<tbody>
					@foreach($task_info as $skillexam_id => $info)
						<?php $i = 0; ?>
						@foreach($info['weights'] as $weight => $count)
						<tr class="exam-{{{ $skillexam_id }}}">
							@if($i == 0)
								<td rowspan="{{{ count($info['weights']) }}}">{{ $info['name'] }}</td>
							@endif
							<td><strong>{{ $weight }}</strong></td>
							<td>{{ $count }}</td>
						</tr>
						<?php $i++; ?>
						@endforeach
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			{!! Button::success(Icon::random().' Generate')->submit() !!}
		</div>
	</div>	
	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/skills/tests/create.js') !!}
@stop