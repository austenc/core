@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => array('students.schedule.skill.event'), 'id' => 'confirm-schedule-frm']) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-md-8">
					<h1>Schedule Skill <small>{{ $student->fullname }}</small></h1>
				</div>
				<div class="col-md-4 back-link">
					<a href="{{ route('students.edit', $student->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to Student</a>
				</div>
			</div>

			<div class="panel panel-primary">
				<div class="panel-heading">
					Skill Exam
				</div>
				<div class="panel-body">
					{{ $skill->name }}<br>
					<small class="text-danger">
						@if(isset($expirations['skill'][$skill->id]))
							Training Expires: {{ date('m/d/Y', strtotime($expirations['skill'][$skill->id])) }}
						@else
							No Training Required
						@endif
					</small>
				</div>
			</div>

			@if( ! empty($scheduleKnowIds))
				@foreach($scheduleKnowIds as $i => $examId)
					<div class="panel panel-warning">
						<div class="panel-heading">
							Corequired Knowledge Exam
						</div>
						<div class="panel-body">
							{{ $skill->corequired_exams->keyBy('id')->get($examId)->name }}<br>
							<small class="text-danger">
								@if(isset($expirations['exam'][$examId]))
									Training Expires: {{ date('m/d/Y', strtotime($expirations['exam'][$examId])) }}
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
					'type'   => 'skill'
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

	{!! Form::hidden('skill_id', $skill->id, ['id' => 'skill_id']) !!}
	{!! Form::hidden('student_id', $student->id) !!}
	@foreach($scheduleKnowIds as $coKnowId)
		{!! Form::hidden('exam_id[]', $coKnowId) !!}
	@endforeach

	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/students/find_event.js') !!}
@stop