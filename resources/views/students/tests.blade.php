@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-8">
			<h1>Tests</h1>
		</div>
	</div>

	<h3>Testing <small>Complete testing history</small></h3>
	@include('core::students.partials.test_history', [
		'attempts'  => $allAttempts
	])

	<h3>Scheduling <small>Showing availability for all Exams</small></h3>
	<div class="well">
		<table class="table table-striped" id="knowledge-table">
			<thead>
				<tr>
					<th>Exam</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach($exams as $ex)
				<tr>
					<td>{{ $ex->pretty_name }}</td>
					<td>
						<div class="btn-group pull-right">
							@if(in_array($ex->id, $student->scheduledAttempts->lists('exam_id')->all()))
								<a href="#" class="btn btn-sm btn-warning disabled">Scheduled</a>						
							@elseif(in_array($ex->id, $eligibleExams->lists('id')->all()))
								<a href="{{ route('students.find.knowledge.event', [$student->id, $ex->id]) }}" class="btn btn-success btn-sm">Schedule</a>
							@else
								<a href="#" class="btn btn-sm btn-default disabled">Not Eligible</a>
							@endif
						</div>
					</td>
				</tr>
				@endforeach

				@foreach($skills as $sk)
				<tr>
					<td>{{ $sk->pretty_name }}</td>
					<td>
						<div class="btn-group pull-right">
							@if(in_array($sk->id, $student->scheduledSkillAttempts->lists('skillexam_id')->all()))
								<a href="#" class="btn btn-sm btn-warning disabled">Scheduled</a>						
							@elseif(in_array($sk->id, $eligibleSkills->lists('id')->all()))
								<a href="{{ route('students.find.skill.event', [$student->id, $sk->id]) }}" class="btn btn-success btn-sm">Schedule</a>
							@else
								<a href="#" class="btn btn-sm btn-default disabled">Not Eligible</a>
							@endif
						</div>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@stop