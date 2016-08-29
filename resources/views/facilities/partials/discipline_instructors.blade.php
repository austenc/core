<div class="row">
	<div class="col-xs-8">
		<h3>{{ $discipline->name }} - {{ Lang::choice('core::terms.instructor', 2) }}</h3>
	</div>
</div>
<div class="well">
	<table class="table table-striped" id="disc-{{ strtolower($discipline->abbrev) }}-instructor-table">
		<thead>
			<tr>
				<th>Name</th>
				<th>Status</th>
				<th>License</th>
				<th class="hidden-xs"></th>
			</tr>
		</thead>
		<tbody>
			@foreach($instructors as $instructor)
				<tr id="instructor-{{{ $instructor->id }}}" class="{{ $instructor->pivot->active ? 'success' : '' }}">
					<td>
						<a target="_blank" href="{{ route('instructors.edit', $instructor->id) }}">
							{{ $instructor->commaName }}
						</a>

						@if($instructor->isArchived)
							<a data-toggle="tooltip" title="Archived {{ Lang::choice('core::terms.instructor', 1) }}">{!! Icon::exclamation_sign() !!}</a>
						@endif
					</td>
					
					<td>
						<label class="label {{ $instructor->pivot->active ? 'label-success' : 'label-default' }}">
							{{ $instructor->pivot->active ? 'Active' : 'Disabled' }}
						</label>
					</td>

					<td class="monospace">{{ $instructor->pivot->tm_license }}</td>

					<td class="hidden-xs">
						<div class="btn-group pull-right">
							@if(Auth::user()->can('facilities.manage_people'))
								
								@if($instructor->pivot->active)
									@if((Auth::user()->can('login_as') || Auth::user()->can('facilities.login_as_own_instructor')) && ! $instructor->isLocked)
										<a class="btn btn-sm btn-default" href="{{ route('instructors.loginas', [$instructor->id, $instructor->pivot->tm_license]) }}" class="btn btn-default" data-confirm="Login as {{{ $instructor->full_name }}}?<br><br>Are you sure?</strong>">
											Login
										</a>
									@endif

									<a href="{{ route('person.toggle', ['instructors', $instructor->id, $discipline->id, $facility->id, 'deactivate']) }}" class="btn btn-sm btn-default deactivate-btn" data-confirm="Deactivate {{{ $instructor->fullname }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
										Deactivate
									</a>
								@else
									<a href="{{ route('person.toggle', ['instructors', $instructor->id, $discipline->id, $facility->id, 'activate']) }}" class="btn btn-sm btn-default activate-btn" data-confirm="Activate {{{ $instructor->fullname }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
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
</div>