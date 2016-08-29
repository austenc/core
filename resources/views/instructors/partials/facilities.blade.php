<div class="row">
	<div class="col-sm-8">
		<h3 id="facility-info">{{ Lang::choice('core::terms.facility', 2) }}</h3>
	</div>
</div>
<div class="well">
	@foreach($instructor->disciplines as $i => $disc)

		{{-- Training Programs under Discipline --}}
		<h4>
			{{ $disc->name }}

			@if( ! Form::isDisabled() && Auth::user()->can('instructors.remove.discipline'))
				<a href="{{ route('instructors.discipline.deactivate', [$instructor->id, $disc->id]) }}" id="remove-disc-{{{ $disc->id }}}" class="btn btn-sm btn-danger pull-right" data-confirm="Remove {{{ $disc->name }}} for this {{{ Lang::choice('core::terms.instructor', 1) }}}? All Trainings and {{{ Lang::choice('core::terms.facility_training', 2) }}} under this Discipline will be deactivated.<br><br>Are you sure?">
					Remove
				</a>
			@endif
		</h4>
		<table class="table table-striped" id="discipline-{{{ strtolower($disc->abbrev) }}}-programs">
			<thead>
				<th>Name</th>
				<th>License</th>
				<th class="xs-col-1"></th>
			</thead>
		
			<tbody>
				<?php
                    $prFound = false;
                ?>
				@foreach($instructor->facilities as $f)
					@if($f->pivot->discipline_id == $disc->id)
						@if($f->pivot->active)
						<tr class="success" id="facility-{{{ $f->id }}}">
						@else
						<tr id="facility-{{{ $f->id }}}">
						@endif
							<td>
								<a href="{{ route('facilities.edit', $f->id) }}">{{ $f->name }}</a>
							</td>

							<td class="monospace">
								{{ $f->pivot->tm_license }}
							</td>

							<td>
								<div class="btn-group pull-right">
									@if($f->pivot->active)
										{{-- Login As --}}
										<a href="{{ route('instructors.loginas', [$instructor->id, $f->pivot->tm_license]) }}" class="btn btn-link" data-toggle="tooltip" title="Login As" data-confirm="Login as {{{ Lang::choice('core::terms.instructor', 1) }}} at {{{ Lang::choice('core::terms.facility_training', 1) }}} {{{ $f->name }}} under {{{ $disc->name }}}?<br><br>Are you sure?">
											{!! Icon::eye_open() !!}
										</a>

										{{-- Deactivate Program --}}
										@if( ! Form::isDisabled())
											<a href="{{ route('person.toggle', ['instructors', $instructor->id, $disc->id, $f->id, 'deactivate']) }}" class="btn btn-link toggle-link" data-toggle="tooltip" title="Deactivate" data-confirm="Deactivate {{{ Lang::choice('core::terms.facility_training', 1) }}} {{{ $f->name }}} for {{{ $disc->name }}}?<br><br>Are you sure?">
												{!! Icon::thumbs_down() !!}
											</a>
										@endif
									@else
										{{-- Activate Program --}}
										@if( ! Form::isDisabled())
											<a href="{{ route('person.toggle', ['instructors', $instructor->id, $disc->id, $f->id, 'activate']) }}" class="btn btn-link toggle-link" data-toggle="tooltip" title="Activate" data-confirm="Activate {{{ Lang::choice('core::terms.facility_training', 1) }}} {{{ $f->name }}} for {{{ $disc->name }}}?<br><br>Are you sure?">
												{!! Icon::thumbs_up() !!}
											</a>
										@endif
									@endif
								</div>
							</td>
						</tr>
						<?php
                            $prFound = true;
                        ?>
					@endif
				@endforeach

				{{-- No Training Programs --}}
				@if( ! $prFound)
				<tr>
					<td align="center" colspan="3">
						No {{ Lang::choice('core::terms.facility', 2) }}
					</td>
				</tr>
				@endif
			</tbody>
		</table>

		@if($i < ($instructor->disciplines->count() - 1))
			<hr>
		@endif
	@endforeach
</div>