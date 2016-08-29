@extends('core::layouts.default')

@section('content')

<div class="row">
	<div class="col-sm-8">
		<h1>
			Scheduled Exams
			<small>
				@if($to === null && $from === null)
					for all time
				@else
					from {{ $from or 'the beginning of time' }} - {{ $to or 'today' }}
				@endif
			</small>
		</h1>
	</div>
	{!! HTML::backlink('reports.index', [], 'Back to Reports Home', 'col-sm-4') !!}
</div>

<table class="table table-striped table-hover">
	<thead>
		<tr>
			<th>License</th>
			<th>{{ Lang::choice('core::terms.facility_testing', 1) }}</th>
			<th>Test Date</th>
			<th>{{ Lang::choice('core::terms.observer', 1) }}</th>
			<th>{{ Lang::choice('core::terms.student', 1) }}</th>
			<th>Tests</th>
		</tr>
	</thead>
	<tbody>
		@foreach($events as $e)
			<tr>
				<td class="monospace">{{ $e->facility->license }}</td>
				<td>{{ $e->facility->name }}</td>
				<td class="monospace">{{ $e->test_date }}</td>
				<td>{{ $e->proctor->fullName }}</td>
				<td>
					@if($e->students->isEmpty())
						<small class="text-muted">None Scheduled</small>
					@else
						@foreach($e->students as $s)
							{{ $s->fullName }} <br>
						@endforeach
					@endif
				</td>
				<td class="monospace">
					@foreach($e->students as $s)
						@if(in_array($s->id, $e->testattempts->lists('student_id')->all()))
							{{ 'W' }}
						@endif

						@if(in_array($s->id, $e->skillattempts->lists('student_id')->all()))
						   {{ ' S' }}
						@endif
						<br>
					@endforeach
				</td>
			</tr>
		@endforeach
	</tbody>
</table>

@stop