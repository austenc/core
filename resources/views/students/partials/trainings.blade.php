@if(Auth::user()->can('students.view_trainings') || Auth::user()->can('students.manage_trainings'))
	<h3 id="trainings">
		Trainings

		{{-- Only show this warning if non-archived Student --}}
		@if(Auth::user()->can('students.manage_trainings') && isset($eligibleTrainingIds) && count($eligibleTrainingIds) < 1 && ! $student->isArchived)
			<small>No Eligible Trainings</small>
		@endif
	</h3>
	<div class="well table-responsive">
		@if($trainings->isEmpty())
			No Current Trainings
		@else
		<table class="table table-striped" id="trainings-table">
			<thead>
				<tr>
					<th>Training</th>
					<th>Status</th>

					@if( ! Auth::user()->isRole('Facility'))
						<th>{{ Lang::choice('core::terms.facility_training', 1) }}</th>
					@endif

					@if( ! Auth::user()->isRole('Instructor'))
						<th>{{ Lang::choice('core::terms.instructor', 1) }}</th>
					@endif

					<th>Ended</th>
					<th class="hidden-xs">Expires</th>
					@if(Auth::user()->can('students.manage_trainings'))
						<th></th>
					@endif
				</tr>
			</thead>
			<tbody>
				@foreach ($trainings as $training)
					@if($training->is_archived)
					<tr>
					@elseif($training->pivot->status == 'passed')
					<tr class="success">
					@elseif($training->pivot->status == 'failed')
					<tr class="danger">
					@else
					<tr class="warning">
					@endif
						<td>
							@if($training->is_archived)
								<a data-toggle="tooltip" title="Archived">{!! Icon::exclamation_sign() !!}</a>
							@endif
							{{ ucwords($training->tr_name) }}
						</td>


						<td>
							@if($training->is_archived)
							<span class="label label-default">
							@elseif($training->pivot->status == 'passed')
							<span class="label label-success">
							@elseif($training->pivot->status == 'failed')
							<span class="label label-danger">
							@else
							<span class="label label-warning">
							@endif
								{{ Lang::get('core::training.status_'.$training->pivot->status) }}
							</span>
						</td>
						
						{{-- Training Program --}}
						@if( ! Auth::user()->isRole('Facility'))
						<td>
							@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
								<a href="{{ route('facilities.edit', $training->pivot->facility_id) }}">
									{{ ucwords($training->name) }}
								</a>
							@else
								{{ ucwords($training->name) }}
							@endif
						</td>
						@endif 

						{{-- Instructor --}}
						@if( ! Auth::user()->isRole('Instructor'))
						<td>
							@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
								<a href="{{ route('instructors.edit', $training->pivot->instructor_id) }}">
									{{ ucwords($training->inc_name) }}
								</a>
							@else
									{{ ucwords($training->inc_name) }}
							@endif
						</td>
						@endif

						<td>
							<small>{{ isset($training->pivot->ended) ? date('m/d/Y',strtotime($training->pivot->ended)) : '' }}</small>
						</td>

						@if($training->is_expired)
						<td class="hidden-xs danger">
						@else
						<td class="hidden-xs">
						@endif
							<small>
							@if($training->pivot->expires)
								{{ date('m/d/Y',strtotime($training->pivot->expires)) }}	
							@endif
							</small>
						</td>

						@if(Auth::user()->can('students.manage_trainings'))
							<td class="nowrap">
								<div class="btn-group pull-right">
									@if($training->is_archived)
										{{-- View Archived Training (Admin/Staff) --}}
										@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
											<a href="{{ route('students.training.edit', [$student->id, $training->pivot->id]) }}" data-toggle="tooltip" title="View" class="btn btn-sm btn-primary pull-right">
												View
											</a>
										@endif
									@else
										{{-- Print Cert --}}
										@if($training->pivot->status == 'passed')
											<a href="{{ route('students.training_certificate', [$student->id, $training->pivot->id]) }}" target="_blank" class="btn btn-sm btn-primary pull-right">
												Print
											</a>
										@endif

										{{-- Edit Training --}}
										@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []) || Auth::user()->isRole('Instructor'))
											<a href="{{ route('students.training.edit', [$student->id, $training->pivot->id]) }}" class="btn btn-sm btn-primary pull-right">
												Edit
											</a>
										@endif
									@endif

									{{-- extra buttons on each attempt row --}}
									@if(View::exists('students.buttons.trainings'))
										@include('students.buttons.trainings', ['training' => $training->pivot])
									@endif
								</div>
							</td>
						@endif
					</tr>
				@endforeach
			</tbody>
		</table>
		@endif
	</div>
@endif