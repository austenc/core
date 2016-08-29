@extends('core::layouts.default')

@section('content')
	<div class="col-md-9">
		<div class="row">
			<div class="col-md-8">
				<h1>
					Skill Detail <span class="text-{{{ $attempt->statusClass }}}">{{ ucwords($attempt->status) }}</span>
				</h1>
			</div>
			<div class="col-md-4 back-link">
				<a href="{{ route('students.edit', $attempt->student->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to {{ Lang::choice('core::terms.student', 1) }}</a>
			</div>
		</div>

		{{-- Warnings --}}
		@if($attempt->archived)
		<div class="alert alert-warning">
			{!! Icon::exclamation_sign() !!} <strong>Archived</strong> this Skill attempt has been archived
		</div>
		@endif
		@if($attempt->hold)
		<div class="alert alert-warning">
			{!! Icon::flag() !!} <strong>Hold</strong> Skill attempt has active hold
		</div>
		@endif

		<div class="well clearfix">
			<p class="lead">Skill Record</p>
			<table class="table table-striped table-readonly">
				<tr>
					<td>
						<label class="control-label">{{ Lang::choice('core::terms.student', 1) }}</label>
					</td>
					<td>
						<a href="{{ route('students.edit', $attempt->student_id) }}">{{ $attempt->student->full_name }}</a></td>
				</tr>

				<tr>
					<td>
						<label class="control-label">Skillexam</label>
					</td>
					<td>
						@if(Auth::user()->isRole('Admin'))
							<a href="{{ route('skillexams.edit', $attempt->skillexam_id) }}">{{ $attempt->skillexam->name }}</a>
						@else
							{{ $attempt->skillexam->name }}
						@endif
					</td>
				</tr>

			    <tr>
			        <td>
						<label class="control-label">Skilltest</label>
					</td>
			        <td>
			        	@if(Auth::user()->isRole('Admin'))
			        		<a href="{{ route('skills.edit', $attempt->skilltest_id) }}">#{{ $attempt->skilltest_id }}</a>
			        	@else
							#{{ $attempt->skilltest_id }}
			        	@endif
			        </td>
			    </tr>

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

				{{-- Start / end times --}}
				@include('core::testing.partials.start_end')

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
		@include('core::testing.partials.detail_event', ['attempt' => $attempt])

		{{-- Tasks --}}
		@foreach($attempt->skilltest->tasks as $task)
			<div class="well clearfix">
				<p class="lead">
					Task #{{ $task->pivot->ordinal }} 

					{{-- Attempt to find matching task response --}}
					@if($re = $taskResponses->get($task->id))
						<span class="text-{{{ $re->statusClass }}}">{{ ucfirst($re->status) }}</span>
					@endif
				</p>
				<table class="table table-striped table-readonly">
					<tr>
						<td>
							<label class="control-label">Title</label>
						</td>
						<td>{{ $task->title }}</td>
					</tr>

					{{-- Task Response --}}
					@if($re = $taskResponses->get($task->id))
						<tr>
							<td>
								<label class="control-label">Status</label>
							</td>
							<td>
								@if($re->status == "passed")
								<span class="label label-success">
		        				@elseif($re->status == "failed")
								<span class="label label-danger">
								@else
								<span class="label label-default">
		        				@endif
		        					{{ ucfirst($re->status) }}
		        				</span>
							</td>
						</tr>

						<tr>
							<td>
								<label class="control-label">Score</label>
							</td>
							<td>{{ $re->score }}</td>
						</tr>

						<tr>
							<td>
								<label class="control-label">Setup</label>
							</td>
							<td>{{ $re->setup ?: 'None' }}</td>
						</tr>

						<tr>
							<td>
								<label class="control-label">Archived</label>
							</td>
							<td>{{ $re->archived ? 'Yes' : 'No' }}</td>
						</tr>

					{{-- Couldnt find Skill Task Response --}}
					@else
						<tr>
							<td></td>
							<td>
								<span class="text text-danger">{!! Icon::exclamation_sign() !!} Missing Task response</span>
							</td>
						</tr>
					@endif
				</table>

				<hr>

				{{-- Steps --}}
				<p class="lead">Steps</p>
				<table class="table table-striped table-readonly table-hover">
					@foreach($task->steps as $i => $step)
						<tr>
							<td>
								<label class="control-label">
									<a title="{{{ $step->expected_outcome }}}" data-toggle="tooltip">Step #{{ $i + 1 }} - {{ str_limit($step->expected_outcome, 40) }}</a>
								</label>
							</td>

							@if(isset($re->step_responses[$step->id]))
								<td class="clearfix">
									@if($re->step_responses[$step->id]['completed'])
										<span class="text-success">{!! Icon::ok() !!} Completed</span>
									@else
										<span class="text-danger">{!! Icon::remove() !!} Missed</span>
									@endif

									{{-- Staff and admins can see observer comments if they exist --}}
									@if(Auth::user()->ability(['Admin', 'Staff'], []) && ! empty($re->step_responses[$step->id]['comment']))
										<a class="btn btn-sm btn-primary pull-right" title="{{{ $re->step_responses[$step->id]['comment'] }}}" data-toggle="tooltip">
											<span class="glyphicon glyphicon-comment"></span> Observer Comments
										</a>
									@endif
								</td>
							@else
								<td><span class="text text-danger">{!! Icon::exclamation_sign() !!} Missing Step response</span></td>
							@endif
						</tr>
					@endforeach
				</table>
			</div>
		@endforeach
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">

			{{-- Print results if taken, otherwise show directions to facility --}}
			@if($attempt->taken)
				<a href="{{ route('testing.results_letter', ['skill', $attempt->id]) }}" class="btn btn-primary">
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
				<a href="{{ route('students.attempt.toggle', [$attempt->student_id, $attempt->id, 'skill', 'archive']) }}" class="btn btn-danger" data-confirm="{{ $attempt->archived ? 'Restore' : 'Archive' }} skill attempt?<br><br>Are you sure?">
					@if($attempt->archived)
						{!! Icon::exclamation_sign() !!} Restore
					@else
						{!! Icon::exclamation_sign() !!} Archive
					@endif
				</a>

				{{-- Hold --}}
				<a href="{{ route('students.attempt.toggle', [$attempt->student_id, $attempt->id, 'skill', 'hold']) }}" class="btn btn-warning" data-confirm="{{ $attempt->hold ? 'Remove' : 'Add' }} skill hold?<br><br>Are you sure?">
					@if($attempt->hold)
						{!! Icon::lock() !!} Remove Hold
					@else
						{!! Icon::lock() !!} Add Hold
					@endif
				</a>
			@endif

			{{-- Attach File --}}
			<a href="{{ route('students.attach_attempt_image', [$attempt->id, 'skill']) }}" class="btn btn-default" data-toggle="modal" data-target="#attach-media">
				{!! Icon::paperclip() !!} Attach
			</a>
		</div>
	</div>

	{!! HTML::modal('attach-media') !!}
@stop