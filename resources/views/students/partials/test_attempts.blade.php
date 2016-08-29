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
					@if(Auth::user()->ability(['Admin', 'Staff'], []))
						<th>Pay Status</th>
					@endif
					<th>Options</th>
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
						{{-- Test --}}
						<td>
							@if($attempt->archived)
								<a data-toggle="tooltip" title="Archived">{!! Icon::exclamation_sign() !!}</a>
							@endif

							@if($attempt->hold)
								<a data-toggle="tooltip" title="Active Hold">{!! Icon::lock() !!}</a>
							@endif

							@if($attempt->exam)
								{{ $attempt->exam->name }}<br>
								<small class="testtype">Knowledge</small>
							@elseif($attempt->skillexam)
								{{ $attempt->skillexam->name }}<br>
								<small class="testtype">Skill</small>
							@else
								Other
							@endif
						</td>

						{{-- Status --}}
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

						{{-- Test Date --}}
						<td>
							<a data-toggle="tooltip" title="Observer: {{ $attempt->testevent->observer->full_name }}">
								{{ $attempt->testDate ? date('m/d/Y', strtotime($attempt->testDate)) : '' }}
							</a>
						</td>

						{{-- Testform --}}
						@if(Auth::user()->ability(['Admin', 'Staff'], []))
							<td>
								<select class="form-control input-sm" onchange="changePaymentStatus({{{ $attempt->id }}}, $(this))">
									<option value="free" @if($attempt->payment_status == 'free') selected @endif> Free</option>
									<option value="paid" @if($attempt->payment_status == 'paid') selected @endif> Paid</option>
									<option value="unpaid" @if($attempt->payment_status == 'unpaid') selected @endif> Unpaid</option>
								</select>
							</td>
						@endif

						{{-- Options --}}
						<td>
							<div class="btn-group pull-right">
								@if($attempt->is_oral)
									<a data-toggle="tooltip" title="Oral Test" class="btn btn-link">{!! Icon::volume_up() !!}</a>
								@endif
							</div>
						</td>

						{{-- Actions --}}
						<td>
							@include('core::students.partials.test_attempt_actions')
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		@endif
	</div>
@endif