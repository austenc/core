<div class="row">
	<div class="col-sm-8">
		<h3 id="facility-info">{{ $discipline->name }}</h3>
	</div>
	<div class="col-sm-4">
		@if( ! Form::isDisabled() && Auth::user()->can('instructors.remove.discipline'))
			<a href="{{ route('instructors.discipline.deactivate', [$instructor->id, $discipline->id]) }}" id="remove-disc-{{{ $discipline->id }}}" class="btn btn-sm btn-danger pull-right" data-confirm="Remove {{{ $discipline->name }}} for this {{{ Lang::choice('core::terms.instructor', 1) }}}? All Trainings and {{{ Lang::choice('core::terms.facility_training', 2) }}} under this Discipline will be deactivated.<br><br>Are you sure?" class="pull-right">
				{!! Icon::exclamation_sign()  !!} Deactivate
			</a>
		@endif
	</div>
</div>
<div class="well">
	<h4>Training</h4>
	<table class="table table-striped table-hover" id="training-info">
		<thead>
			<tr>
				<th>Name</th>
				<th>Status</th>
				@if(Auth::user()->can('instructors.manage_trainings'))
				<th class="xs-col-1"></th>
				@endif
			</tr>
		</thead>
			@foreach($disciplineInfo[$discipline->id]['trainings'] as $tr)
				@if(in_array($tr->id, $instructor->teaching_trainings->lists('id')->all()))
				<tr class="success">
				@else
				<tr>
				@endif
					<td>{{ $tr->name }}</td>

					<td>
						@if(in_array($tr->id, $instructor->teaching_trainings->lists('id')->all()))
						<span class="label label-success">Active</span>
						@else
						<span class="label label-warning">Inactive</span>
						@endif
					</td>
	
					{{-- Activate/Inactivate Staff/Admin Only --}}
					@if(Auth::user()->can('instructors.manage_trainings'))
					<td>
						<div class="btn-group pull-right">
							@if(in_array($tr->id, $instructor->teaching_trainings->lists('id')->all()))
								{{-- Deactivate Training --}}
								<a href="{{ route('instructors.training.deactivate', [$instructor->id, $tr->id]) }}" class="btn btn-sm btn-default" data-confirm="Deactivate Training {{{ $tr->name }}}?<br><br>Are you sure?">
									Deactivate
								</a>
							@else
								{{-- Activate Training --}}
								<a href="{{ route('instructors.training.activate', [$instructor->id, $tr->id]) }}" class="btn btn-sm btn-default" data-confirm="Activate Training {{{ $tr->name }}}?<br><br>Are you sure?">
									Activate
								</a>
							@endif
						</div>
					</td>
					@endif
				</tr>
			@endforeach
		</tbody>
	</table>
	
	<hr>

	<h4>{{ Lang::choice('core::terms.facility_training', 2) }}</h4>

	@if( ! array_key_exists($discipline->id, $disciplineInfo) || $disciplineInfo[$discipline->id]['facilities']->isEmpty())
		No {{ Lang::choice('core::terms.facility_training', 2) }}
	@else
	<table class="table table-striped" id="discipline-{{{ strtolower($discipline->abbrev) }}}-programs">
		<thead>
			<th>Name</th>
			<th>Status</th>
			<th>License</th>
			<th class="xs-col-1"></th>
		</thead>
		<tbody>
			@foreach($disciplineInfo[$discipline->id]['facilities'] as $fac)
				@if($fac->pivot->active)
				<tr class="success" id="facility-{{{ $fac->id }}}">
				@else
				<tr id="facility-{{{ $fac->id }}}">
				@endif
					<td>
						<a href="{{ route('facilities.edit', $fac->id) }}">{{ $fac->name }}</a>
						{{-- Training Approved? --}}
						@if(in_array('Training', $fac->actions))
							<br><span class="label label-primary">Training Approved</span>
						@endif
					</td>

					<td>
						@if($fac->pivot->active)
						<span class="label label-success">Active</span>
						@else
						<span class="label label-default">Inactive</span>
						@endif
					</td>

					<td class="monospace">
						{{ $fac->pivot->tm_license }}
					</td>

					<td>
						<div class="btn-group pull-right">
							@if($fac->pivot->active)
								{{-- Login As --}}
								<a href="{{ route('instructors.loginas', [$instructor->id, $fac->pivot->tm_license]) }}" class="btn btn-sm btn-default" data-toggle="tooltip" data-confirm="Login at {{{ $fac->name }}} under {{{ $discipline->name }}}?<br><br>Are you sure?">
									Login
								</a>

								{{-- Deactivate Program --}}
								@if( ! Form::isDisabled())
									<a href="{{ route('person.toggle', ['instructors', $instructor->id, $discipline->id, $fac->id, 'deactivate']) }}" class="btn btn-sm btn-default" data-confirm="Deactivate {{{ $fac->name }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
										Deactivate
									</a>
								@endif
							@else
								{{-- Activate Program --}}
								@if( ! Form::isDisabled())
									<a href="{{ route('person.toggle', ['instructors', $instructor->id, $discipline->id, $fac->id, 'activate']) }}" class="btn btn-sm btn-default" data-confirm="Activate {{{ $fac->name }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
										Activate
									</a>
								@endif
							@endif
						</div>
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	@endif
</div>