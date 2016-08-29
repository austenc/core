@extends('core::layouts.default')

@section('content')
<div class="row">
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Administrator's Report</h1>
			</div>
			<div class="col-xs-4 back-link">
				<a href="javascript:history.back()" class="pull-right btn btn-link">{!! Icon::arrow_left() !!} Back to Event</a>
			</div>
		</div>


		{{-- ***** TODO: Ask teresa about 'skill headers' line *****--}}


		<h3>Anomalies</h3>
		<div class="well">
			@if(empty($skillsWithSteps))
				None
			@else
				<div class="form-group row">
					<div class="col-md-12">
						<ul>
							@foreach($skillsWithSteps as $s)
								<li class="monospace"><small>{{ $s['anomalies'] }}</small></li>
							@endforeach
						</ul>
					</div>
				</div>
			@endif
		</div>

		<h3>Skills with Steps</h3>
		<div class="well">
			@if(empty($skillsWithSteps))
				None
			@else
				@foreach($skillsWithSteps as $s)
				<div class="row">
					<div class="col-xs-6">
						<strong>{{ $s['student']->fullName }}</strong> <br>
						<ul class="admin-report-list">
							@foreach($s['tasks'] as $t)
								<li>
								{{ $t['name'] }}
								<ul>
									@foreach($t['steps'] as $stepId => $response)
										<li>
											@if( ! $response['completed'])
												B. 
											@endif
											{{ $ordinals[$stepId] }}. 
											{{ str_limit(BBCode::strip($steps[$stepId]), 32) }}
										</li>
									@endforeach
								</ul>
								</li>
							@endforeach
						</ul>
					</div> {{-- .col-xs-6 --}}

					<div class="col-xs-6">
						<strong>&nbsp;</strong> <br>
						<ul class="admin-report-list">
							@foreach($s['tasks'] as $t)
								<li>
								&nbsp; <br>
								<ul>
									@foreach($t['steps'] as $stepId => $response)
										<li>
											{{ $ordinals[$stepId] }}. 
											{{ $response['comment'] }}
											@if(array_key_exists('data', $response))

												@if( ! empty($response['comment']))
												 | 
												@endif

												@foreach($response['data'] as $k => $data)
													@if($k > 0) | @endif {{ $data->value }} 
												@endforeach
											@endif
										</li>
									@endforeach
								</ul>
								</li>
							@endforeach
						</ul>
					</div>
				</div>
				@endforeach
			@endif
		</div>

		<h3>Score Report</h3>
		<div class="well table-responsive">
			<table class="table table-condensed">
				<thead>
					<tr>
						<th>ID</th>
						<th>{{ Lang::choice('core::terms.student', 1) }}</th>
						<th>Exam</th>
						<th>Form</th>
						<th>Site</th>
						<th>Score</th>
						<th></th>
					</tr>
				</thead>
				@foreach($written as $t)
					<tr class="monospace">
						<td>{{ $t->student_id }}</td>
						<td>{{ $t->student->commaName }}</td>
						<td>{{ $t->exam->abbrev }}</td>
						<td>{{ $t->testform_id }}</td>
						<td>{{ $event->facility->license }}</td>
						<td>&nbsp;{{ $t->score }}</td>
						<td><small class="text-muted">Knowledge</small></td>
					</tr>

					{{-- Is there a skill for this person too? --}}		
					@if(array_key_exists($t->student_id, $skills))
						@foreach($skills[$t->student_id] as $task)
							<tr class="monospace">
								<td>&nbsp;</td>
								<td>&nbsp;</td>
								<td>{{ $task['abbrev'] }}</td>
								<td>{{ $task['test'] }}</td>
								<td>{{ $event->facility->license }}</td>
								<td>
									{{-- This is nasty, but to get it spaced right it had to be one line --}}
									@if(empty($task['scoreType']))&nbsp;@else{{ $task['scoreType'] }}@endif{{ $task['score'] }}
								</td>
								<td>{{ $task['task'] }}</td>
							</tr>
						@endforeach
					@endif
				@endforeach
			</table>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">

	</div>
</div>
@stop