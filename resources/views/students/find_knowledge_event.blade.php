@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => array('students.schedule.knowledge.event'), 'id' => 'confirm-schedule-frm']) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-md-8">
					<h1>Schedule Knowledge <small>{{ $student->fullname }}</small></h1>
				</div>
				<div class="col-md-4 back-link">
					<a href="{{ route('students.edit', $student->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to Student</a>
				</div>
			</div>

			{{-- Oral Student? --}}
			@if($student->is_oral)
			<div class="alert alert-info">
				{!! Icon::volume_up() !!} <strong>Oral</strong> Currently only Paper Events are available for Oral schedules.
			</div>
			@endif
		
			<div class="panel panel-primary">
				<div class="panel-heading">
					Knowledge Exam
				</div>
				<div class="panel-body">
					{{ $exam->name }}<br>
					<small class="text-danger">
					@if(isset($expirations['exam'][$exam->id]))
						Training Expires: {{ date('m/d/Y', strtotime($expirations['exam'][$exam->id])) }}
					@else
						No Training Required
					@endif
					</small>
				</div>
			</div>

			@if( ! empty($scheduleSkillIds))
				@foreach($scheduleSkillIds as $i => $skillId)
					<div class="panel panel-warning">
						<div class="panel-heading">
							Corequired Skill Exam
						</div>
						<div class="panel-body">
							{{ $exam->corequired_skills->keyBy('id')->get($skillId)->name }}<br>
							<small class="text-danger">
								@if(isset($expirations['skill'][$skillId]))
									Training Expires: {{ date('m/d/Y', strtotime($expirations['skill'][$skillId])) }}
								@else
									No Training Required
								@endif
							</small>
						</div>
					</div>
				@endforeach
			@endif

			{{-- Eligible Events --}}
			<span class="text-danger">{{ $errors->first('event_id') }}</span>
			<div class="well">
				@include('core::students.partials.available_events', [
					'events' => $eligible_events,
					'type'   => 'knowledge'
				])
			</div>
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				{!! Button::success(Icon::book().' Schedule')->submit() !!}
			</div>
		</div>
	</div>

	{!! Form::hidden('student_id', $student->id) !!}
	{!! Form::hidden('exam_id', $exam->id) !!}
	@foreach($scheduleSkillIds as $coSkillId)
		{!! Form::hidden('skill_id[]', $coSkillId) !!}
	@endforeach

	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/students/find_event.js') !!}
@stop