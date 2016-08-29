<div class="well">
	@if( ! $eligible_students->isEmpty())
	<div class="table-responsive">
		<table id="sel-students-table" class="table table-striped table-condensed">
			<thead>
				<tr>
					<th></th>
					<th>
						{!! Sorter::link('events.knowledge.fill_seats', 'Last', [$event->id, $exam->id, 'sort' => 'last']) !!}
					</th>
					<th>
						{!! Sorter::link('events.knowledge.fill_seats', 'First', [$event->id, $exam->id, 'sort' => 'first']) !!}
					</th>
					@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
						<th class="hidden-xs">
							{!! Sorter::link('events.knowledge.fill_seats', 'Training Expires', [$event->id, $exam->id, 'sort' => 'expires', 'default' => true]) !!}
						</th>

						<th class="hidden-xs">Phone</th>
						<th>Options</th>
						<th></th>
					@endif
				</tr>
			</thead>
			<tbody>
				@foreach ($eligible_students as $st)
					<tr>
						<td>{!! Form::checkbox('student_id[]', $st->id) !!}</td>

						<td>
							@if( ! $st->scheduledExams->isEmpty() || ! $st->scheduledSkills->isEmpty())
								<a data-toggle="tooltip" title="Upcoming Scheduled Event">{!! Icon::flag() !!}</a>
							@endif
							{{ $st->last }}
						</td>

						<td>{{ $st->first }}</td>

						@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
							<td class="hidden-xs">
								{{ $st->getFirstTrainingExpirationForTrainingIds($exam->required_trainings->lists('id')->all()) }}
							</td>

							<td class="monospace hidden-xs">
								{{ $st->main_phone }}
							</td>


							<td>
								<div class="btn-group">
									@if($st->is_oral)
										<a title="Oral Test" data-toggle="tooltip" class="btn btn-link">{!! Icon::volume_up() !!}</a>
									@endif
								</div>
							</td>

							<td>
								<a href="{{ route('students.edit', $st->id) }}" class="pull-right btn btn-sm btn-primary">
									Edit
								</a>
							</td>
						@endif
					</tr>
				@endforeach

				@if($eligible_students->isEmpty())
					<tr>
						<td colspan="7">No eligible {{ Lang::choice('core::terms.student', 1) }}</td>
					</tr>
				@endif
			</tbody>
		</table>
	</div>
	@else
		<div align="center">No Eligible {{ Lang::choice('core::terms.student', 2) }}</div>
	@endif
</div>