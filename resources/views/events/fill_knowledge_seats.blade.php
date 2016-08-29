@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => array('events.knowledge.fill_seats', $event->id, $exam->id)]) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-md-8">
				<h1>Fill Seats <small>Event {{ $event->test_date }}</small></h1>
			</div>
			<div class="col-md-4 back-link">
				<a href="{{ route('events.edit', $event->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to Event</a>
			</div>
		</div>

		{{-- Errors --}}
		@if($errors->first('student_id'))
			<div class="alert alert-danger">{{ $errors->first('student_id') }}</div>
		@endif
		@if($errors->first('exam_id'))
			<div class="alert alert-danger">{{ $errors->first('exam_id') }}</div>
		@endif

		<div class="panel panel-primary">
			<div class="panel-heading">
				Knowledge Test
				<span class="seat-count label label-success">
					{{ $exam_info['exam'][$exam->id]['rem_seats'] }}
				</span>
			</div>
			<div class="panel-body">
				{{ $exam->name }}
			</div>
		</div>
		
		@if($exam->corequired_skills->count() > 0)
			@foreach($exam->corequired_skills as $sk)
			<div class="panel panel-warning">
				<div class="panel-heading">
					Corequired Skill Test
					@if(in_array($sk->id, $event->skills->lists('id')->all()))
						@if($exam_info['skill'][$sk->id]['rem_seats'] > 0)
						<span class="seat-count label label-success">
						@else
						<span class="seat-count label label-danger">
						@endif
							{{ $exam_info['skill'][$sk->id]['rem_seats'] }}
						</span>
					@endif
				</div>
				<div class="panel-body">
					{{ $sk->name }}
					@if( ! in_array($sk->id, $event->skills->lists('id')->all()))
						<br>
						<small class="text-danger">
							Not offered in this Event. Only Students that previously passed this Skill Test will be included in results.
						</small>			
					@endif				
				</div>
			</div>
			@endforeach
		@endif

		{{-- Search Students --}}
		@include('core::events.partials.fill_search')

		<?php
            // add search form that's somehow split from other form that wraps everything
            // tie in the search to the eligible_students function in Testevent
            // write a test ensuring this all works
            // do the above 3 steps to skills side
            // commit, run all tests, and (hopefully) push
        ?>
				
		@include('core::events.partials.fill_knowledge_students')

		{{-- In some places we might be pulling in a list, so it might not always have a paginator --}}
		{!! method_exists($eligible_students, 'appends') ? $eligible_students->appends(Input::except('page'))->render() : '' !!}
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			{!! Button::success(Icon::book().' Schedule')->submit()->block() !!}
			
			@if(Auth::user()->ability(['Admin', 'Staff'], []))
				<hr>
				@include('core::events.sidebars.fill_seats')
			@endif
		</div>
	</div>

	{{-- Hidden Fields --}}
	{!! Form::hidden('event_id', $event->id, ['id' => 'event_id']) !!}
	{!! Form::hidden('exam_id', $exam->id, ['id' => 'exam_id']) !!}
	@foreach($exam->corequired_skills as $sk)
		@if(in_array($sk->id, $event->skills->lists('id')->all()))
			{!! Form::hidden('exam_coreq_skill_id[]', $sk->id) !!}
		@endif
	@endforeach

	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/events/fill_seats.js') !!}
@stop