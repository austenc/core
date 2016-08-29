@if($attempts->isEmpty())
<p class="well">No Test History on record.</p>
@else
<div class="well">
	<table class="table table-striped" id="knowledge-table">
		<thead>
			<tr>
				<th>Test Date</th>
				<th>Exam</th>
				<th>Test Site</th>
				<th>Status</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			@foreach ($attempts as $attempt)
			@if($attempt->seeResults)
				@if($attempt->status == "failed")
					<tr class="danger">
				@elseif($attempt->status == "passed")
					<tr class="success">
				@else
					<tr>
				@endif
			@elseif($attempt->status == "assigned" || $attempt->status == "pending")
			<tr class="warning">
			@else
			<tr>
			@endif
				<td>{{ $attempt->testevent->test_date }}</td>
				<td>
					@if($attempt->exam)
						{{ $attempt->exam->pretty_name }}
					@else
						{{ $attempt->skillexam->pretty_name }}
					@endif
				</td>
				<td>{{ $attempt->testevent->facility->pretty_name_address }}</td>
				<td>
					{{-- Test attempt status badge --}}
					@include('core::partials.attempt_status', ['attempt' => $attempt])
				</td>
				<td>
					<div class="btn-group pull-right">
						{{-- Knowledge --}}
						@if($attempt->exam)
							@if($attempt->status == 'pending')
								<a href="{{ route('testing.start', $attempt->id) }}" class="btn btn-sm btn-warning">Begin Testing</a>
							@elseif($attempt->status == 'started' && $attempt->timeRemaining > 0)
								<a href="{{ route('testing.resume', $attempt->id) }}" class="btn btn-sm btn-success">Resume Testing</a>
							@endif

							@if($attempt->seeResults)
								<a href="{{ route('testing.show', [$attempt->id]) }}" class="btn btn-sm btn-primary">Details</a>		
							@endif				

							{{-- Print test confirmation page --}}
							@if($attempt->status == 'assigned')
								<a href="{{ route('testing.confirm', ['knowledge', $attempt->id]) }}" class="btn btn-sm btn-default" data-toggle="tooltip" title="Test Confirmation Page">
									{!! Icon::check() !!}
								</a>
							@endif

						@else
						{{-- Skill --}}
							@if($attempt->seeResults)
								<a href="{{ route('skills.testing.show', [$attempt->id]) }}" class="btn btn-sm btn-primary">Details</a>				
							@endif		

							{{-- Print test confirmation page --}}
							@if($attempt->status == 'assigned')
								<a href="{{ route('testing.confirm', ['skill', $attempt->id]) }}" class="btn btn-sm btn-default" data-toggle="tooltip" title="Test Confirmation Page">
									{!! Icon::check() !!}
								</a>
							@endif

						@endif
	
						@if($attempt->taken && $attempt->seeResults)
							<?php
                                $testType = isset($attempt->exam) ? 'knowledge' : 'skill';
                            ?>
							<a href="{{ route('testing.results_letter', [$testType, $attempt->id]) }}" class="btn btn-default btn-sm" data-toggle="tooltip" title="Print Test Results Letter"><span class="glyphicon glyphicon-print"></span></a>
						@elseif( ! $attempt->taken)
							{{-- Get directions --}}
							<a href="{{ route('facilities.directions', $attempt->testevent->facility_id) }}" class="btn btn-sm btn-default" data-toggle="tooltip" title="Get Directions">{!! Icon::road() !!}</a>
						@endif
					</div>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>
</div>
@endif