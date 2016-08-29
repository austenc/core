@extends('core::layouts.default')

@section('content')
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Archived {{ Lang::choice('core::terms.student', 2) }} Trainings <small>{{ $instructor->fullname }}</small></h1>
				<h3>{{ $discipline->name }}</h3>
			</div>
			<div class="col-xs-4 back-link">
				<a href="{{ route('instructors.edit', $instructor->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ Lang::choice('core::terms.instructor', 1) }}</a>
			</div>
		</div>

		<div class="well">
			@if($instructor->studentTrainings->isEmpty())
				No Archived {{ $discipline->abbrev }} {{ Lang::choice('core::terms.student', 2) }}
			@else
			<table class="table table-striped person-table">
				<thead>
					<tr>
						@if(Auth::user()->isRole('Admin'))
							<th>#</th>
						@endif
						<th>Name</th>
						<th>{{ Lang::choice('core::terms.facility_training', 1) }}</th>
						<th>Status</th>
						<th class="hidden-xs">Start</th>
						<th class="hidden-xs">Completed</th>
						<th class="hidden-xs">Archived</th>
					</tr>
				</thead>
				<tbody>
					@foreach($instructor->studentTrainings as $tr)
						<tr>
							<td><span class="lead text-muted">{{ $tr->id }}</span></td>

							<td>
								<a href="{{ route('students.edit', $tr->student_id) }}">{{ $tr->student->commaName }}</a>
							</td>

							<td>
								@if(Auth::user()->ability(['Admin', 'Staff'], []))
									<a href="{{ route('facilities.edit', $tr->facility->id) }}">
										{{ $tr->facility->name }}
									</a>
								@else
									{{ $tr->facility->name }}
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

							<td class="hidden-xs">
								@if( ! empty($tr->ended))
									<small>{{ date('m/d/Y', strtotime($tr->ended)) }}</small>				
								@endif
							</td>

							<td class="hidden-xs">
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