@extends('core::layouts.default')
@section('content')
	<div class="col-md-9">
		<div class="row">
			<div class="col-md-8">
				<h1>
					Knowledge Detail <span class="text-{{{ $attempt->statusClass }}}">{{ ucwords($attempt->status) }}</span>
				</h1>
			</div>
			<div class="col-md-4 back-link">
				<a href="{{ route('students.edit', $attempt->student->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ Lang::choice('core::terms.student', 1) }}</a>
			</div>
		</div>

		{{-- Warnings --}}
		@if($attempt->archived)
		<div class="alert alert-warning">
			{!! Icon::exclamation_sign() !!} <strong>Archived</strong> this Knowledge attempt has been archived
		</div>
		@endif
		@if($attempt->hold)
		<div class="alert alert-warning">
			{!! Icon::flag() !!} <strong>Hold</strong> Knowledge attempt has active hold
		</div>
		@endif

		<div class="well clearfix">
			<p class="lead">Test Record</p>
			<table class="table table-striped table-readonly">
				<tr>
					<td>
						<label class="control-label">{{ Lang::choice('core::terms.student', 1) }}</label>
					</td>
					<td>{{ $attempt->student->full_name }}</td>
				</tr>

				<tr>
					<td>
						<label class="control-label">Exam</label>
					</td>
					<td>
						@if(Auth::user()->isRole('Admin'))
						<a href="{{ route('exams.edit', $attempt->exam->id) }}">{{ $attempt->exam->name }}</a>
						@else
						{{ $attempt->exam->name }}
						@endif
					</td>
				</tr>

				@if(Auth::user()->can('students.view_full_test_history'))
			    <tr>
			        <td>
						<label class="control-label">Testform</label>
					</td>
			        <td>
			        	@if(Auth::user()->isRole('Admin'))
						<a href="{{ route('testforms.show', $attempt->testform_id) }}">{{ $attempt->testform_id }}</a>
						@else
						{{ $attempt->testform_id }}
						@endif
			        </td>
			    </tr>
			    @endif

				@if( ! Auth::user()->isRole('Instructor'))
					<tr>
						<td>
							<label class="control-label">Score</label>
						</td>
						<td>
							@if($attempt->seeResults && $attempt->score)
						        @if($attempt->score)
						        	{{ number_format($attempt->score, 2) . '%' }}
						        @else
									Not Released -
						        @endif
							@else
								-
							@endif
				        </td>
					</tr>

					<tr>
						<td>
							<label class="control-label">Status</label>
						</td>
				        <td>
			        		@if($attempt->status == "passed" || $attempt->status == "failed")
			        			@if($attempt->seeResults)
			        				@if($attempt->status == "passed")
									<span class="label label-success">
			        				@elseif($attempt->status == "failed")
									<span class="label label-danger">
									@else
									<span class="label label-default">
			        				@endif
			        					{{ ucfirst($attempt->status) }}
			        				</span>
			        			@else 
			        				Being Scored
			        			@endif
			        		@else
								<span class="label label-default">
			        				{{ ucfirst($attempt->status) }}
			        			</span>
			        		@endif
				        </td>
					</tr>
				@endif

				{{-- Start / end times --}}
				@include('core::testing.partials.start_end')

				<tr>
					<td>
						<label class="control-label">Oral</label>
					</td>
					<td>{{ $attempt->is_oral ? 'Yes' : 'No' }}</td>
				</tr>

				<tr>
					<td>
						<label class="control-label">Hold</label>
					</td>
					<td>{{ $attempt->hold ? 'Yes' : 'No' }}</td>
				</tr>

				<tr>
					<td>
						<label class="control-label">Archived</label>
					</td>
					<td>{{ $attempt->archived ? 'Yes' : 'No' }}</td>
				</tr>
			</table>
		</div>

		{{-- Test Event --}}
		@include('core::testing.partials.detail_event')

		{{-- Answers --}}
		@if($attempt->answers)
			<div class="well">
				@if(count((array) $attempt->answers))
					<p class="lead">Score Info</p>
					<table class="table table-striped table-hover">
						<tr>
							<td><strong>Status</strong></td>
							<td class="text-right text-{{{ $attempt->statusClass }}}"><strong>{{ ucwords($attempt->status) }}</strong></td>
						</tr>
						<tr>
							<td><strong>Score</strong></td>
							<td class="text-right text-{{{ $attempt->statusClass }}}">{{ $attempt->percent }}</td>
						</tr>

						@if(Auth::user()->ability(['Staff', 'Admin'], []))
						<tr>
							<td><strong>Number Answered</strong></td>
							<td class="text-right">{{ count((array) $attempt->answers) }}</td>
						</tr>
						@endif
						
						<tr>
							<td><strong>Total Questions</strong></td>
							<td class="text-right">{{ $attempt->total_questions }}</td>
						</tr>									
						<tr>
							<td><strong>Correct Answers</strong></td>
							<td class="text-right">{{ $attempt->correct_answers }} / {{ $attempt->total_questions }}</td>
						</tr>
					</table>
				@endif

				@if($attempt->correct_by_subject)
					<p class="lead">Correct Answers by Subject</p>
					@include('core::testing.partials.correct_by_subject', [
						$attempt, 
						$totals,
						$subjects
					])
				@endif
			</div>
		@endif
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			
			@if($attempt->taken)
				<a href="{{ route('testing.results_letter', ['knowledge', $attempt->id]) }}" class="btn btn-primary">
					<span class="glyphicon glyphicon-print"></span> Print Results
				</a>
			@else
				{{-- Directions --}}
				<a href="{{ route('facilities.directions', $attempt->facility_id) }}" class="btn btn-primary">
					{!! Icon::road() !!} Get Directions
				</a>
			@endif


			{{-- Archive/Hold --}}
			@if(Auth::user()->can('students.attempt.modify'))
				{{-- Archive --}}
				<a href="{{ route('students.attempt.toggle', [$attempt->student_id, $attempt->id, 'knowledge', 'archive']) }}" class="btn btn-danger" data-confirm="{{ $attempt->archive ? 'Restore' : 'Archive' }} test attempt?<br><br>Are you sure?">
					@if($attempt->archived)
						{!! Icon::exclamation_sign() !!} Restore
					@else
						{!! Icon::exclamation_sign() !!} Archive
					@endif
				</a>
				
				{{-- Hold --}}
				<a href="{{ route('students.attempt.toggle', [$attempt->student_id, $attempt->id, 'knowledge', 'hold']) }}" class="btn btn-default" data-confirm="{{ $attempt->hold ? 'Remove' : 'Add' }} test hold?<br><br>Are you sure?">
					@if($attempt->hold)
						{!! Icon::lock() !!} Remove Hold
					@else
						{!! Icon::lock() !!} Add Hold
					@endif
				</a>
			@endif

			{{-- Attach File --}}
			<a href="{{ route('students.attach_attempt_image', [$attempt->id, 'knowledge']) }}" class="btn btn-default" data-toggle="modal" data-target="#attach-media">
				{!! Icon::paperclip() !!} Attach
			</a>
		</div>
	</div>

	{!! HTML::modal('attach-media') !!}
@stop