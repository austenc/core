@extends('core::layouts.default')

@section('content')
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Archived {{ Lang::choice('core::terms.student', 2) }} Trainings <small>{{ $facility->name }}</small></h1>
				<h3>{{ $discipline->name }}</h3>
			</div>
			<div class="col-xs-4 back-link">
				<a href="{{ route('facilities.edit', $facility->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ Lang::choice('core::terms.facility_training', 1) }}</a>
			</div>
		</div>

		<div class="well">
			@if($students->isEmpty())
				No Archived {{ $discipline->abbrev }} {{ Lang::choice('core::terms.student', 2) }}
			@else
			<table class="table table-striped person-table">
				<thead>
					<tr>
						<th>Name</th>
						<th>{{ Lang::choice('core::terms.instructor', 1) }}</th>
						<th>Status</th>
						<th class="hidden-xs">Start</th>
						<th>Completed</th>
						<th class="hidden-xs">Expires</th>
						<th>Archived</th>
					</tr>
				</thead>
				<tbody>
					@foreach($students as $tr)
						<tr>
							<td>
								<a href="{{ route('students.edit', $tr->student_id) }}">{{ $tr->student->commaName }}</a>
							</td>

							<td>
								@if(Auth::user()->ability(['Admin', 'Staff'], []))
									<a href="{{ route('instructors.edit', $tr->instructor_id) }}">{{ $tr->instructor->fullname }}</a>
								@else
									{{ $tr->instructor->fullname }}
								@endif
							</td>

							<td>
								<span class="label label-default">
									{{ Lang::get('core::training.status_'.$tr->status) }}
								</span>
							</td>

							<td class="hidden-xs">
								@if( ! empty($tr->started))
									<small>{{ date('m/d/Y', strtotime($tr->started)) }}</small>				
								@endif
							</td>

							<td>
								@if( ! empty($tr->ended))
									<small>{{ date('m/d/Y', strtotime($tr->ended)) }}</small>				
								@endif
							</td>

							<td class="hidden-xs">
								@if( ! empty($tr->expires))
									<small>{{ date('m/d/Y', strtotime($tr->expires)) }}</small>				
								@endif
							</td>

							<td>
								@if( ! empty($tr->archived_at))
									<small>{{ date('m/d/Y', strtotime($tr->archived_at)) }}</small>				
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
			@endif
		</div>
	</div>

	<div class="col-md-3 sidebar">
	</div>
@stop