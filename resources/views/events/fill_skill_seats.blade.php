@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => array('events.skill.fill_seats', $event->id, $skill->id)]) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-md-8">
				<h1>Fill Seats <small>Event {{ $event->test_date }}</small></h1>
			</div>
			<div class="col-md-4 back-link">
				<a href="{{ route('events.edit', $event->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to Event</a>
			</div>
		</div>

		<div class="panel panel-primary">
			<div class="panel-heading">
				Skill Test
				<span class="seat-count label label-success">
					{{ $exam_info['skill'][$skill->id]['rem_seats'] }}
				</span>
			</div>
			<div class="panel-body">
				{{ $skill->name }}
			</div>
		</div>

		@if($skill->corequired_exams->count() > 0)
			@foreach($skill->corequired_exams as $ex)
				<div class="panel panel-warning">
					<div class="panel-heading">
						Corequired Knowledge Test
						@if(in_array($ex->id, $event->exams->lists('id')->all()))
							@if($exam_info['exam'][$ex->id]['rem_seats'] > 0)
							<span class="seat-count label label-success">
							@else
							<span class="seat-count label label-danger">
							@endif
								{{ $exam_info['exam'][$ex->id]['rem_seats'] }}
							</span>
						@endif
					</div>
					<div class="panel-body">
						{{ $ex->name }}
						@if( ! in_array($ex->id, $event->exams->lists('id')->all()))
							<br>
							<small class="text-danger">
								Not offered. Only Students that previously passed this Exam will be included in results.
							</small>			
						@elseif($exam_info['exam'][$ex->id]['rem_seats'] < 1)
							<br>
							<small class="text-danger">
								Offered but full. Only Students that previously passed this Exam will be included in results.
							</small>			
						@endif
					</div>	
				</div>
			@endforeach
		@endif

		{{-- Search Students --}}
		@include('core::events.partials.fill_search')

		@include('core::events.partials.fill_skill_students')

		{{-- In some places we might be pulling in a list, so it might not always have a paginator --}}
		{!! method_exists($eligible_students, 'appends') ? $eligible_students->appends(Input::except('page'))->render() : '' !!}
	</div>

	
	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			{!! Button::success(Icon::book().' Schedule')->submit()->block() !!}

			@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
				<hr>
				@include('core::events.sidebars.fill_seats')
			@endif
		</div>
	</div>

	{{-- Hidden Fields --}}
	{!! Form::hidden('event_id', $event->id, ['id' => 'event_id']) !!}
	{!! Form::hidden('skill_id', $skill->id, ['id' => 'skill_id']) !!}
	@foreach($skill->corequired_exams as $ex)
		@if(in_array($ex->id, $event->exams->lists('id')->all()))
			{!! Form::hidden('skill_coreq_exam_id[]', $ex->id) !!}
		@endif
	@endforeach

	{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/events/fill_seats.js') !!}
	<script type="text/javascript">
		$(document).ready(function(){

			var $mimic = $('[data-mimic="dropdown"]');
			var $hidden = $($mimic.data('mimic-target'));

			// when a list item is clicked
			$('.dropdown li a').click(function(){
				// mark the active item
				$('.dropdown li', $mimic).removeClass('active');
				$(this).parent('li').addClass('active');

				// change the button text
				var selected = $(this).text();
				$('.mimic-selected', $mimic).html(selected);

				// update the hidden input
				$hidden.val(selected);
			});
		});
	</script>
@stop