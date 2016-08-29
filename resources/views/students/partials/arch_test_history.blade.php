@if(Auth::user()->can('students.view_test_history'))
	<h3 id="testing">Testing</h3>
	<div class="well table-responsive">
		@if($allAttempts->isEmpty())
			No Current Test Attempts
		@else
		<table class="table table-striped" id="testing-table">
			<thead>
				<tr>
					<th>Exam</th>
					<th>Status</th>
					<th>Test Date</th>
					<th>Testform</th>
					<th class="hidden-xs">Start</th>
					<th class="hidden-xs">End</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach ($allAttempts as $attempt)
					@if($attempt->archived == 1)
					<tr>
					@elseif($attempt->status == 'passed')
					<tr class="success">
					@elseif($attempt->status == 'failed')
					<tr class="danger">
					@elseif($attempt->status == 'assigned')
					<tr class="warning">
					@else
					<tr>
					@endif
						<td>
							{{-- Archived? --}}
							@if($attempt->archived)
								<a data-toggle="tooltip" title="Archived">{!! Icon::exclamation_sign() !!}</a>
							@endif

							@if($attempt->exam)
								{{ $attempt->exam->name }}<br>
								<small>Knowledge</small>
							@elseif($attempt->skillexam)
								{{ $attempt->skillexam->name }}<br>
								<small>Skill</small>
							@else
								Other
							@endif
						</td>

						<td>
							@if($attempt->archived == 1)
							<span class="label label-default">
							@elseif($attempt->status == 'passed')
							<span class="label label-success">
							@elseif($attempt->status == 'failed')
							<span class="label label-danger">
							@elseif($attempt->status == 'assigned')
							<span class="label label-warning">
							@else
							<span>
							@endif
								{{ ucfirst($attempt->status) }}
							</span>
						</td>

						<td>{{ $attempt->testevent->test_date }}</td>

						<td>
							@if($attempt->testform_id)
								#{{ $attempt->testform_id }}
							@elseif($attempt->skilltest_id)
								#{{ $attempt->skilltest_id }}
							@else
								?
							@endif
						</td>

						<td class="hidden-xs">
							@if($attempt->start_time)
								<small>{{ date('H:i A', strtotime($attempt->start_time)) }}</small>
							@endif
						</td>

						<td class="hidden-xs">
							@if($attempt->end_time)
								<small>{{ date('H:i A', strtotime($attempt->end_time)) }}</small>
							@endif
						</td>

						<td>
							<div class="btn-group pull-right">
								{{-- Knowledge/Skill --}}
								@if($attempt->exam)
									<a href="{{ route('testing.show', [$attempt->id]) }}" class="btn btn-link make-tooltip" title="View">
										{!! Icon::search() !!}
									</a>
								@else
									<a href="{{ route('skills.testing.show', [$attempt->id]) }}" class="btn btn-link make-tooltip" title="View">
										{!! Icon::search() !!}
									</a>
								@endif
							</div>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		@endif
	</div>
@endif