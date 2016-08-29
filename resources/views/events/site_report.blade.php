@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'events.creating']) !!}
	<div class="row">
		<div class="col-md-9">
			<h1>Potential Conflict Report</h1>

			<div class="alert alert-warning">
				{!! Icon::flag() !!} <strong>Possible Conflict</strong> Scheduled events at {{ $facility->name }} may cause a conflict or over-booking with your new Event
			</div>

			<h3>New Event</h3>
			<div class="well">
				<dl class="dl-horizontal dl-padded">
					<dt>Discipline</dt>
					<dd>{{ $discipline->name }}</dd>
					
					<dt>{{ Lang::choice('core::terms.facility_testing', 1) }}</dt>
					<dd>{{{ $facility->name }}}</dd>

					<dt>Test Dates</dt>
					<dd>
						@foreach(Input::old('test_date') as $i => $d)
							{{ Input::old('test_date')[$i] }} @ {{ Input::old('start_time')[$i] }}<br>
						@endforeach
					</dd>
					
					{{-- Knowledge Exams --}}
					@if( ! empty($knowledge))
						<dt>Knowledge Exams</dt>
						<dd>
				  			@foreach($knowledge as $comboInfo => $k)
					  			<?php list($examId, $disciplineId) = explode('|', $comboInfo); ?>
								{{ Input::old('exam_names')[$examId] }}; {{ $knowledge[$comboInfo] }} Seats<br>
				  			@endforeach							
						</dd>
					@endif

					{{-- Skill Exams --}}
					@if( ! empty($skill))
						<dt>Skill Exams</dt>
						<dd>
							@foreach($skill as $comboInfo => $s)
								<?php list($skillId, $disciplineId) = explode('|', $comboInfo); ?>
								{{ Input::old('skill_names')[$skillId] }}; {{ $skill[$comboInfo] }} Seats<br>
							@endforeach							
						</dd>
					@endif

					<dt>Options</dt>
					<dd>
						@if(Input::old('is_regional'))
							{{ Lang::get('core::events.regional') }} Event
						@else
							{{ Lang::get('core::events.closed') }} Event
						@endif

						<br>

						@if(Input::old('is_paper'))
				  			Paper Event
				  		@else 
							Web Event
				  		@endif
					</dd>
				</dl>
			</div>

			<h3>Test Events @ {{ $facility->name }}</h3>
			<div class="well table-responsive">
				<table class="table table-striped">
					<thead>
						<th>Test Date</th>
						<th>Discipline Exams</th>
						<th>Scheduled Students</th>
						<th>Total Seats</th>
					</thead>
					<tbody>
						@foreach($events as $evt)
						<tr>
							<td>
								{{ $evt->test_date }}<br>
								<small>{{ $evt->start_time }}</small>
							</td>

							<td>
								<strong>{{ $evt->discipline->name }}</strong><br>
								
								@if( ! $evt->exams->isEmpty())
									{!! implode('<br>', $evt->exams->lists('pretty_name')->all()) !!}<br>
								@endif
								@if( ! $evt->skills->isEmpty())
									{!! implode('<br>', $evt->skills->lists('pretty_name')->all()) !!}
								@endif
							</td>

							<td>
								@foreach($evt->exams as $ex)
									{{ $evt->knowledgeStudents()->where('exam_id', $ex->id)->count() }}
									<br><br>
								@endforeach

								@foreach($evt->skills as $sk)
									{{ $evt->skillStudents()->where('skillexam_id', $sk->id)->count() }}
									<br><br>
								@endforeach
							</td>

							<td>
								@foreach($evt->exams as $ex)
									{{ $ex->pivot->open_seats }}<br><br>
								@endforeach

								@foreach($evt->skills as $sk)
									{{ $sk->pivot->open_seats }}<br><br>
								@endforeach
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
				{!! Button::success(Icon::plus_sign().' Continue, Choose Test Team')->submit() !!}
				<button type="submit" name="cancel", value="true" class="btn btn-warning">
					{!! Icon::warning_sign().' Cancel, Modify Event' !!}
				</button>
			</div>
		</div>
	</div>

	{!! Form::hidden('site_report', true) !!}
	{!! Form::hidden('discipline_id', Input::old('discipline_id')) !!}
	{!! Form::hidden('comments', Input::old('comments')) !!}
	{!! Form::hidden('facility_id', Input::old('facility_id')) !!}

	{{-- Event DateTime --}}
	@foreach(Input::old('test_date') as $i => $td)
		{!! Form::hidden('test_date['.$i.']', $td) !!}
	@endforeach
	@foreach(Input::old('start_time') as $i => $st)
		{!! Form::hidden('start_time['.$i.']', $st) !!}
	@endforeach

	{{-- Exam Seats --}}
	@foreach(Input::old('exam_seats') as $i => $seats)
		{!! Form::hidden('exam_seats['.$i.']', $seats) !!}
	@endforeach
	@foreach(Input::old('skill_seats') as $i => $seats)
		{!! Form::hidden('skill_seats['.$i.']', $seats) !!}
	@endforeach

	{{-- Exam Names --}}
	@foreach(Input::old('exam_names') as $i => $en)
		{!! Form::hidden('exam_names['.$i.']', $en) !!}
	@endforeach
	@foreach(Input::old('skill_names') as $i => $sn)
		{!! Form::hidden('skill_names['.$i.']', $sn) !!}
	@endforeach

	{{-- Event Options --}}
	@if(Input::old('is_regional'))
		{!! Form::hidden('is_regional', 1) !!}
	@endif
	@if(Input::old('is_paper'))
		{!! Form::hidden('is_paper', 1) !!}
	@endif

	{!! Form::close() !!}
@stop