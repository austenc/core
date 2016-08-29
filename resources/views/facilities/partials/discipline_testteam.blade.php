<div class="row">
	<div class="col-xs-8">
		<h3>{{ $discipline->name }} - Test Team</h3>
	</div>
</div>
<div class="well">
	<table class="table table-striped" id="disc-{{ strtolower($discipline->abbrev) }}-test-team-table">
		<thead>
			<tr>
				<th></th>
				<th>Name</th>
				<th>Status</th>
				<th>License</th>
				<th class="hidden-xs"></th>
			</tr>
		</thead>
		<tbody>
			@foreach($testteam as $person)
				{{-- Ensure Current Discipline --}}
				<tr class="{{ $person->pivot->active ? 'success' : '' }}" id="{{ strtolower($person->getMorphClass()) }}-{{{ $person->id }}}">
					<td><span class="lead text-muted">{{ $person->getMorphClass() }}</span></td>

					<td>
						@if(Auth::user()->ability(['Staff', 'Admin'], []))
							<a href="{{ route(strtolower(str_plural($person->getMorphClass())).'.edit', $person->id) }}">{{ $person->commaName }}</a>
						@else
							{{ $person->commaName }}
						@endif

						@if($person->isArchived)
							<a data-toggle="tooltip" title="Archived {{ $person->getMorphClass() }}">{!! Icon::exclamation_sign() !!}</a>
						@endif
					</td>

					<td>
						<label class="label {{ $person->pivot->active ? 'label-success' : 'label-default' }}">
							{{ $person->pivot->active ? 'Active' : 'Inactive' }}
						</label>
					</td>

					<td class="monospace">{{ $person->pivot->tm_license }}</td>

					{{-- Activate/Deactivate --}}
					<td>
						<div class="btn-group pull-right">
							@if(Auth::user()->can('facilities.manage_people'))
								@if($person->pivot->active)
									<a href="{{ route('person.toggle', [str_plural($person->getMorphClass()), $person->id, $discipline->id, $facility->id, 'deactivate']) }}" class="btn btn-sm btn-default deactivate-btn" data-confirm="Deactivate {{{ $person->fullname }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
										Deactivate
									</a>
								@else
									<a href="{{ route('person.toggle', [str_plural($person->getMorphClass()), $person->id, $discipline->id, $facility->id, 'activate']) }}" class="btn btn-sm btn-default activate-btn" data-confirm="Activate {{{ $person->fullname }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
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