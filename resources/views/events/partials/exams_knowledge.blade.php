@if($event->exams->count() > 0)
	<h3 id="knowledge-info">Knowledge Tests</h3>
	@foreach ($event->exams as $ex)
		<div class="row">
			{{-- Header --}}
			<div class="col-sm-8">
				<h4>
					{{ Str::title($ex->name) }}
					@if($ex->pivot->open_seats == $students['knowledge'][$ex->id]->count())
						<span class="label label-danger">Full</span>
					@endif
				</h4>	
			</div>

			{{-- Change/Fill Seats --}}
			<div class="col-sm-4">
				<div class="btn-group pull-right-sm">
					@if($event->locked == 0 && Auth::user()->can('events.change_seats'))
						<a href="{{ route('events.knowledge.change_seats', [$event->id, $ex->id]) }}" class="btn btn-info btn-sm" data-toggle="modal" data-target="#change-knowledge-seats">Change Seats</a>
					@endif
					
					@if($event->locked == 0 && Auth::user()->can('students.schedule') && ($students['knowledge'][$ex->id]->count() < $ex->pivot->open_seats))
						<a href="{{ route('events.knowledge.fill_seats', [$event->id, $ex->id]) }}" data-loader class="btn btn-sm btn-primary fill-knowledge-btn">Fill Seats</a>
					@endif
				</div>
			</div>	
		</div>

		{{-- Corequired Tests? --}}
		@if($ex->corequired_skills->count() > 0)
			<div class="alert alert-warning">
				<strong>Skill Co-requirements: </strong> 
				{!! implode('<br>', $ex->corequired_skills->lists('name')->all()) !!} 

				@if(array_diff($ex->corequired_skills->lists('id')->all(), $event->skills->lists('id')->all()))
					<br><br>
					<strong>Warning!</strong> Event does not offer all co-required Skill Exams. Only {{ Lang::choice('core::terms.student', 2) }} that have previously passed ALL co-requirements will be able to schedule into this Exam.	
				@endif
			</div>
		@endif

		{{-- Seats --}}
		<div class="well table-responsive">
			<table class="table table-striped" id="knowledge-exam-{{{ $ex->id }}}-table">
				<thead>
					<tr>
						<th class="hidden-xs"></th>
						<th>Name</th>

						@if(Auth::user()->isRole('Observer'))
							<th>Contact</th>
						@endif

						<th>Testform</th>

						@if(Auth::user()->ability(['Admin', 'Staff'], []))
							<th>Pay Status</th>
						@endif

						<th>Options</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{{-- Fill Scheduled Seats --}}
					@foreach($students['knowledge'][$ex->id] as $i => $student)
						<tr>
							<td class="hidden-xs"><span class="lead text-muted">{{ ($i + 1) }}</span></td>

							{{-- Student --}}
							<td>
								@if($student->pivot->printed_by)
									<a data-toggle="tooltip" title="Scanform previously printed by: {{{ Auth::user()->username }}}">
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

							{{-- Testform --}}
							<td class="monospace">
								@if($student->pivot->testform_id)
									#{{ $student->pivot->testform_id }}
									{!! Form::hidden('student_testform_id', $student->pivot->testform_id, ['class' => 'student-testform-id']) !!}
								@else
									NULL
								@endif
							</td>

							{{-- Payment Status --}}
							@if(Auth::user()->ability(['Admin', 'Staff'], []))
							<td>
								<select class="form-control input-sm" onchange="changeKnowledgePaymentStatus({{{ $student->pivot->id }}}, $(this))">
									<option value="free" @if($student->pivot->payment_status == 'free') selected @endif>Free</option>
									<option value="paid" @if($student->pivot->payment_status == 'paid') selected @endif>Paid</option>
									<option value="unpaid" @if($student->pivot->payment_status == 'unpaid') selected @endif>Unpaid</option>
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
											</a>
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

									{{-- Oral --}}
									@if($student->pivot->is_oral)
										<a class="btn btn-link" data-toggle="tooltip" title="Oral Test">
											{!! Icon::volume_up() !!}
										</a>
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
											'type' => 'knowledge'
										])
				
										{{-- Print test packet button --}}
										@include('core::events.partials.print_packet_single', [
											'event'   => $event, 
											'student' => $student
										])

										{{-- Event Not-Ended Actions --}}
										@if( ! $event->ended)

											{{-- Change Testform --}}
											@if(Auth::user()->can('events.manage_testforms') && ! $event->locked)
												<li>
													<a href="{{ route('events.testform.change', [$event->id, $ex->id, $student->id]) }}">
														Change Testform
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
													<a href="{{ route('events.knowledge.unschedule', [$event->id, $ex->id, $student->id]) }}" data-confirm="Remove {{{ Lang::choice('core::terms.student', 1) }}} <strong>{{{ $student->fullname }}}</strong> from Knowledge Test <strong>{{{ $ex->name }}}</strong>?">
														Reschedule
													</a>
												<li>
											@endif

											{{-- Login As --}}
											@if(Auth::user()->can('login_as'))
												<li>
													<a href="{{ route('students.loginas', [$student->id]) }}" data-confirm="Login as {{{ $student->full_name }}}?<br><br>Are you sure?</strong>">
														Login As
													</a>			
												<li>						
											@endif
										@endif
										
										{{-- extra buttons on each attempt row --}}
										@if(View::exists('testattempts.buttons.extra'))
											@include('testattempts.buttons.extra', ['attempt' => $student->pivot])
										@endif
									</ul>
								</div>
							</td>
						</tr>
					@endforeach
			
					{{-- Empty Seats, only if event hasn't ended --}}
					@if(empty($event->ended))
						@for($j = $students['knowledge'][$ex->id]->count(); $j < $ex->pivot->open_seats; $j++)
							<tr>
								<td><span class="lead text-muted">{{ ($j + 1) }}</span></td>
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