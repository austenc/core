@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'subjects.index', 'method' => 'get']) !!}
	<div class="row">
		<div class="col-md-9">
			@foreach($exams as $exam)
				<p class="lead">{{ $exam->name }} - Subjects</p>
				<div class="well">
		      		<table class="table table-striped">
		      			<thead>
		      				<th>#</th>
		      				<th>Subject</th>
		      				<th>Old Subject #</th>
		      				<th>Reporting As</th>
		      			</thead>
						<tbody>
							@foreach($exam->subjects as $subject)
								<tr>
									<td><span class="lead text-muted">{{ $subject->id }}</span></td>
									<td><a href="{{ route('subjects.edit', $subject->id) }}">{{ $subject->name }}</a></td>
									<td>{{ $subject->old_number }}</td>
									<td>
										@if($subject->report_as)
											<a href="{{ route('subjects.edit', $subject->report_as) }}">
												{{ $subject->reportAs->name }}
											</a>
										@else
											<p class="text-muted">Self</p>
										@endif
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			@endforeach
		</div>

		{{-- Sidebar --}}
		@include('core::subjects.sidebars.index')
	</div>
	{!! Form::close() !!}
@stop