<h3 id="testsite-info">{{ Lang::choice('core::terms.facility_testing', 2) }}</h3>
<div class="well table-responsive">
	@foreach($record->disciplines as $i => $discipline)
		<h4>{{ $discipline->name }}</h4>
		<table class="table table-striped" id="testsite-{{{ strtolower($discipline->abbrev) }}}-table">
			<thead>
				<tr>
					<th>{{ Lang::choice('core::terms.facility_testing', 1) }}</th>
					<th>License</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach ($record->facilities as $facility)
					@if($facility->pivot->discipline_id == $discipline->id)
						@if($facility->pivot->active && empty($facility->deleted_at))
						<tr class="success active" id="facility-{{{ $facility->id }}}">
						@else
						<tr class="inactive" id="facility-{{{ $facility->id }}}">
						@endif
							<td>
								<a href="{{ route('facilities.edit', $facility->id) }}">{{ $facility->name }}</a>
								@if($facility->deleted_at)
									<span class="label label-warning">Archived</span>
								@endif
							</td>
							<td class="monospace">{{ $facility->pivot->tm_license }}</td>
							<td>
								<div class="btn-group pull-right">
									@if( ! empty($facility->deleted_at))
										{{-- Inactivate --}}
										<a class="btn btn-link" data-toggle="tooltip" title="Archived Facility">
											{!! Icon::ban_circle() !!}
										</a>
									@elseif(Auth::user()->can('person.toggle'))
										@if($facility->pivot->active)
											{{-- Deactivate --}}
											<a href="{{ route('person.toggle', [strtolower(class_basename($record)).'s', $record->id, $discipline->id, $facility->id, 'deactivate']) }}" class="btn btn-link toggle-link" data-toggle="tooltip" title="Deactivate" data-confirm="Deactivate {{{ $facility->name }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
												{!! Icon::thumbs_down() !!}
											</a>
										@else
											{{-- Activate --}}
											<a href="{{ route('person.toggle', [strtolower(class_basename($record)).'s', $record->id, $discipline->id, $facility->id, 'activate']) }}" class="btn btn-link toggle-link" data-toggle="tooltip" title="Activate" data-confirm="Activate {{{ $facility->name }}} for {{{ $discipline->name }}}?<br><br>Are you sure?">
												{!! Icon::thumbs_up() !!}
											</a>
										@endif
									@endif
								</div>
							</td>
						</tr>
					@endif
				@endforeach
			</tbody>
		</table>

		{{-- Add Divider --}}
		@if($i < ($record->disciplines->count() - 1))
			<hr>
		@endif
	@endforeach
</div>