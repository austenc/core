@if(Auth::user()->can('students.view_exams') && $student->isActive)
	<h3 id="scheduling">Scheduling</h3>
	<div class="well table-responsive">
		<table class="table table-striped" id="scheduling-table">
			<thead>
				<tr>
					<th>Exam</th>
					<th>Status</th>
					@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
						<th class="hidden-xs">Missing Requirements</th>
					@endif
					@if(Auth::user()->can('students.schedule'))
						<th></th>
					@endif
				</tr>
			</thead>

			<tbody>
				@foreach ($allExams as $ex)
					@if(in_array($ex->id, $ineligibleExams->lists('id')->all()))
						@if(in_array($ineligibleExams->get($ex->id)->errors['status'], ['Scheduled', 'Previously Passed']))
							<tr class="warning">
						@else
							<tr>
						@endif
					@else
					<tr class="success">
					@endif
						<td>{{ $ex->pretty_name }}</td>

						{{-- Status --}}
						<td>
							@if(in_array($ex->id, $ineligibleExams->lists('id')->all()))
								@if(in_array($ineligibleExams->get($ex->id)->errors['status'], ['Scheduled', 'Previously Passed']))
								<span class="label label-warning">
								@else
								<span class="label label-default">
								@endif
									{{ $ineligibleExams->get($ex->id)->errors['status'] }}
								</span>
							@else
								<span class="label label-success">Ready</span>
							@endif
						</td>
						
						{{-- Missing Requirements --}}
						@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
						<td class="hidden-xs">
							@if(in_array($ex->id, $ineligibleExams->lists('id')->all()) && isset($ineligibleExams->get($ex->id)->errors['missing']))
								@foreach($ineligibleExams->get($ex->id)->errors['missing'] as $type => $missing)
									@foreach($missing as $missingId => $info)
										{{ $info['name'] }}<br>
										<small>{{ $type }}</small>@if(isset($info['reason'])),
										<small>{{ $info['reason'] }}</small>
										@endif
										<br>
									@endforeach
								@endforeach
							@endif
						</td>
						@endif

						{{-- Actions --}}
						@if(Auth::user()->can('students.schedule'))
						<td>
							<div class="btn-group pull-right">
								@if(array_key_exists($ex->id, $allScheduledEventExams))
									{{-- Link to Event --}}
									@if(Auth::user()->can('events.edit'))
									<a href="{{ route('events.edit', $allScheduledEventExams[$ex->id]) }}" class="btn btn-default btn-sm">
										Event
									</a>
									@endif

									{{-- Scheduled Details --}}
									@if(Auth::user()->isRole('Instructor'))
										@foreach($student->scheduledAttempts as $a)		
											<a href="{{ route('students.scheduled.detail', [$student->id, $a->id, 'knowledge']) }}" class="btn btn-sm btn-default" target="_blank">
												Detail
											</a>
										@endforeach
									@endif

								@elseif( ! in_array($ex->id, $ineligibleExams->lists('id')->all()))
									{{-- Find Knowledge Event --}}
									<a href="{{ route('students.find.knowledge.event', [$student->id, $ex->id]) }}" data-toggle="tooltip" title="Schedule Knowledge" class="btn btn-sm btn-success" id="sched-know-{{ $ex->id }}">
										Schedule
									</a>
								@endif
							</div>
						</td>
						@endif

					</tr>
				@endforeach

				@foreach ($allSkills as $sk)
					@if (in_array($sk->id, $ineligibleSkills->lists('id')->all()))
						@if(in_array($ineligibleSkills->get($sk->id)->errors['status'], ['Scheduled', 'Previously Passed']))
							<tr class="warning">
						@else
							<tr>
						@endif
					@else
					<tr class="success">
					@endif
						<td>{{ $sk->pretty_name }}</td>

						{{-- Status --}}
						<td>
							@if(in_array($sk->id, $ineligibleSkills->lists('id')->all()))
								@if(in_array($ineligibleSkills->get($sk->id)->errors['status'], ['Scheduled', 'Previously Passed']))
								<span class="label label-warning">
								@else
								<span class="label label-default">
								@endif
									{{ $ineligibleSkills->get($sk->id)->errors['status'] }}
								</span>
							@else
								<span class="label label-success">Ready</span>
							@endif
						</td>

						{{-- Missing Requirements --}}
						@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
						<td class="hidden-xs">
							@if(in_array($sk->id, $ineligibleSkills->lists('id')->all()) && isset($ineligibleSkills->get($sk->id)->errors['missing']))
								@foreach($ineligibleSkills->get($sk->id)->errors['missing'] as $type => $missing)
									@foreach($missing as $missingId => $info)
										{{ $info['name'] }}<br>
										<small>{{ $type }}</small>@if(isset($info['reason'])),
										<small>{{ $info['reason'] }}</small>
										@endif
										<br>
									@endforeach
								@endforeach
							@endif
						</td>
						@endif

						{{-- Actions --}}
						@if(Auth::user()->can('students.schedule'))
						<td>
							<div class="btn-group pull-right">
								@if(array_key_exists($sk->id, $allScheduledEventSkills))
									{{-- Link to Event --}}
									@if(Auth::user()->can('events.edit'))
									<a href="{{ route('events.edit', $allScheduledEventSkills[$sk->id]) }}" class="btn btn-sm btn-default">
										Event
									</a>
									@endif

									{{-- Detail --}}
									@if(Auth::user()->isRole('Instructor'))
										@foreach($student->scheduledSkillAttempts as $s)
											<a href="{{ route('students.scheduled.detail', [$student->id, $s->id, 'skill']) }}" class="btn btn-sm btn-default" target="_blank">
												Detail
											</a>
										@endforeach
									@endif
								@elseif( ! in_array($sk->id, $ineligibleSkills->lists('id')->all()))
									{{-- Find Skill Event --}}
									<a href="{{ route('students.find.skill.event', [$student->id, $sk->id]) }}" class="schedule-sk-btn btn btn-sm btn-success" id="sched-skill-{{ $sk->id }}">
										Schedule
									</a>
								@endif
							</div>
						</td>
						@endif
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endif