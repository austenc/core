@if($event->skills->count() > 0)
	<h3 id="skill-info">Skill Tests</h3>
	@foreach ($event->skills as $sk)
		<div class="row">
			{{-- Header --}}
			<div class="col-sm-8">
				<h4>
					{{ Str::title($sk->name) }}
					@if($sk->pivot->open_seats == $students['skill'][$sk->id]->count())
						<span class="label label-danger">Full</span>
					@endif
				</h4>	
			</div>

			{{-- Change/Fill Seats --}}
			<div class="col-sm-4">
				<div class="btn-group pull-right-sm">
					@if($event->locked == 0 && Auth::user()->can('events.change_seats'))
						<a href="{{ route('events.skill.change_seats', [$event->id, $sk->id]) }}" class="btn btn-info btn-sm" data-toggle="modal" data-target="#change-skill-seats">Change Seats</a>
					@endif

					@if($event->locked == 0 && Auth::user()->can('students.schedule') && $students['skill'][$sk->id]->count() < $sk->pivot->open_seats)
						<a href="{{ route('events.skill.fill_seats', [$event->id, $sk->id]) }}" data-loader class="btn btn-sm btn-primary fill-skill-btn">Fill Seats</a>
					@endif
				</div>
			</div>	
		</div>

		{{-- Corequired Tests? --}}
		@if($sk->corequired_exams->count() > 0)
			<div class="alert alert-warning">
				<strong>Knowledge Co-requirements: </strong> 
				{!! implode('<br>', $sk->corequired_exams->lists('name')->all()) !!}

				@if(array_diff($sk->corequired_exams->lists('id')->all(), $event->exams->lists('id')->all()))
					<strong>Warning!</strong> Event does not offer all co-required Knowledge Exams. Only Students that have previously passed ALL co-requirements will be able to schedule into this Exam.	
				@endif
			</div>
		@endif

		{{-- Seats --}}
		<div class="well table-responsive">
			<table class="table table-striped" id="skill-exam-{{{ $sk->id }}}-table">
				<thead>
					<tr>
						<th class="hidden-xs"></th>
						<th>Name</th>
						
						@if(Auth::user()->isRole('Observer'))
							<th>Contact</th>
						@endif

						<th>Skilltest</th>

						@if(Auth::user()->ability(['Admin', 'Staff'], []))
							<th>Pay Status</th>
						@endif

						<th>Options</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{{-- Fill Scheduled Seats --}}
					@foreach($students['skill'][$sk->id] as $i => $student)
						<tr>
							<td class="hidden-xs"><span class="lead text-muted">{{ ($i + 1) }}</span></td>

							{{-- Name --}}
							<td>
								@if($student->pivot->printed_by)
									<a data-toggle="tooltip" title="Skill Test previously printed by: {{{ Auth::user()->username }}}">
										{!! Icon::exclamation_sign() !!}
									</a>
								@endif 
								{{ $student->commaName }}<br>

								@include('core::partials.attempt_status', ['attempt' => $student->pivot])
							</td>

							{{-- Contact --}}
							@if(Auth::user()->isRole('Observer'))
							<td>
								{{ $student->phone }}<br>
								<small>{{ $student->user->email }}</small>
							</td>
							@endif

							{{-- SkillTest --}}
							<td class="monospace">
								@if($student->pivot->skilltest_id)
									#{{ $student->pivot->skilltest_id }}
									{!! Form::hidden('student_skilltest_id', $student->pivot->skilltest_id, ['class' => 'student-skilltest-id']) !!}
								@else
									NULL
								@endif
							</td>

							{{-- Payment Status --}}
							@if(Auth::user()->ability(['Admin', 'Staff'], []))
							<td>
								<select class="form-control input-sm" onchange="changeSkillPaymentStatus({{{ $student->pivot->id }}}, $(this))">
									<option value="free" @if($student->pivot->payment_status == 'free') selected @endif> Free</option>
									<option value="paid" @if($student->pivot->payment_status == 'paid') selected @endif> Paid</option>
									<option value="unpaid" @if($student->pivot->payment_status == 'unpaid') selected @endif> Unpaid</option>
								</select>
							</td>
							@endif

							{{-- Options --}}
							<td>
								<div class="btn-group">
									{{-- Accepted ADAs --}}
									@if( ! $student->acceptedAdas->isEmpty())
										@foreach($student->acceptedAdas as $ada)
											<a class="btn btn-link" data-toggle="tooltip" title="{{{ ucfirst($ada->pivot->status) }}} ADA: {{{ $ada->name }}}">
												{!! Icon::apple() !!}
											</a><br>
										@endforeach
									@endif

									{{-- Pending ADA --}}
									@if( ! $student->pendingAdas->isEmpty())
										@foreach($student->pendingAdas as $ada)
											<a class="btn btn-link" data-toggle="tooltip" title="{{{ ucfirst($ada->pivot->status) }}} ADA: {{{ $ada->name }}}">
												{!! Icon::alert() !!}
											</a>
										@endforeach
									@endif
								</div>
							</td>

							{{-- Actions --}}
							<td>
								<div class="btn-group pull-right">
									@if(Auth::user()->can('students.edit'))
										<a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-default">Edit</a>
										<button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									    	<span class="caret"></span>
									    	<span class="sr-only">Toggle Dropdown</span>
									  	</button>
									@else
										<button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											Select Action
									    	<span class="caret"></span>
									    	<span class="sr-only">Toggle Dropdown</span>
									  	</button>
									@endif

								  	<ul class="dropdown-menu">
								  		{{-- Print Test Confirmation or Results Letter if applicable --}}
										@include('core::events.partials.test_status_buttons', [
											'test' => $student->pivot,
											'type' => 'skill'
										])

										{{-- Print test packet button --}}
										@include('core::events.partials.print_packet_single', [
											'event'        => $event, 
											'student'      => $student,
											'includeSkill' => true
										])

										{{-- Event Not-Ended Actions --}}
										@if( ! $event->ended)

											{{-- Change Skilltest --}}
											@if(Auth::user()->can('events.manage_testforms') && ! $event->locked)
												<li>
													<a href="{{ route('events.skilltest.change', [$event->id, $sk->id, $student->id]) }}">
														Change Skilltest
													</a>
												</li>
											@endif

											{{-- Update Student Password --}}
											@if(Auth::user()->can('students.update_password'))
												<li>
													<a href="{{ route('students.change_password', $student->id) }}" class="change-pwd" data-toggle="modal" data-target="#change-password">
														Change Password
													</a>
												</li>
											@endif

											{{-- Reschedule --}}
											@if(Auth::user()->can('students.unschedule'))
												<li>
													<a href="{{ route('events.skill.unschedule', [$event->id, $sk->id, $student->id]) }}" data-confirm="Remove {{{ Lang::choice('core::terms.student', 1) }}} <strong>{{{ $student->fullname }}}</strong> from Skill Test <strong>{{{ $sk->name }}}</strong>?">
														Reschedule
													</a>
												</li>
											@endif

											{{-- Loginas Student --}}
											@if(Auth::user()->can('login_as'))
												<li>
													<a href="{{ route('students.loginas', [$student->id]) }}" data-confirm="Login as {{{ $student->full_name }}}?<br><br>Are you sure?">
														Login As
													</a>								
												</li>
											@endif
										@endif
									
										{{-- Initialize/Continue Skill Test Only for Online tests --}}
										@if( ! $event->is_paper)
											@if($student->pivot->status == 'pending' && Auth::user()->can('skills.begin'))
												<li>
													<a href="{{ route('skills.initialize', [$student->pivot->id]) }}" data-confirm="Begin Skill Test <strong>{{{ $sk->name }}}</strong> for {{{ Lang::choice('core::terms.student', 1) }}} <strong>{{{ $student->commaName }}}</strong>?">
														Begin Test
													</a>
												</li>
											@elseif($student->pivot->status == 'started' && Auth::user()->can('skills.begin'))
												<li>
													<a href="{{ route('skills.initialize', [$student->pivot->id]) }}" data-confirm="Continue Skill Test <strong>{{{ $sk->name }}}</strong> for {{{ Lang::choice('core::terms.student', 1) }}} <strong>{{{ $student->commaName }}}</strong>?">
														Continue Test
													</a>
												</li>
											@endif
										@endif

										{{-- extra buttons on each attempt row --}}
										@if(View::exists('skillattempts.buttons.extra'))
											@include('skillattempts.buttons.extra', ['attempt' => $student->pivot])
										@endif
									</ul>
								</div>
							</td>
						</tr>
					@endforeach
				
					{{-- Empty Seats, only if event hasn't ended --}}
					@if(empty($event->ended))
						@for($j = $students['skill'][$sk->id]->count(); $j < $sk->pivot->open_seats; $j++)
							<tr>
								<td class="hidden-xs"><span class="lead text-muted">{{ ($j + 1) }}</span></td>
								<td></td>
								<td></td>

								@if(Auth::user()->isRole('Observer'))
									<td></td>
								@endif

								@if(Auth::user()->ability(['Admin', 'Staff'], []))
									<td></td>
								@endif

								<td></td>
								<td></td>
							</tr>
						@endfor
					@endif
				</tbody>
			</table>
		</div>
	@endforeach
@endif