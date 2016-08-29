@foreach($items as $person)
	{{-- Ensure Current Discipline --}}
	@if($discipline->id == $person->pivot->discipline_id)
		<tr id="{{{ $type }}}-{{{ $person->id }}}">
			<td>
				@if(Auth::user()->ability(['Staff', 'Admin'], []))
					<a href="{{ route(str_plural($type).'.edit', $person->id) }}">{{ $person->commaName }}</a>
				@else
					{{ $person->commaName }}
				@endif
				<br>

				<small>
					<label class="label {{ $person->pivot->active ? 'label-success' : 'label-warning' }}">
						{{ $person->pivot->active ? 'Active' : 'Disabled' }}
					</label>
				</small>
			</td>

			<td class="monospace">{{ $person->pivot->tm_license }}</td>

			<td>{{ ucfirst($type) }}</td>

			{{-- Activate/Deactivate --}}
			<td>
				<div class="btn-group pull-right">
					@if(Auth::user()->can('facilities.manage_people'))
						@if($person->pivot->active)
							<a href="{{ route('person.toggle', [$type.'s', $person->id, $discipline->id, $facility->id, 'deactivate']) }}" class="btn btn-sm btn-default deactivate-btn" data-confirm="Deactivate {{{ $person->fullname }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
								Deactivate
							</a>
						@else
							<a href="{{ route('person.toggle', [$type.'s', $person->id, $discipline->id, $facility->id, 'activate']) }}" class="btn btn-sm btn-default activate-btn" data-confirm="Activate {{{ $person->fullname }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
								Activate
							</a>
						@endif
					@endif
				</div>
			</td>
		</tr>
	@endif
@endforeach