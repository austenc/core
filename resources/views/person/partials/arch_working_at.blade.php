@foreach($record->disciplines as $discipline)
<h3 id="testsite-{{{ strtolower($discipline->abbrev) }}}-info">
	{{ $discipline->name }} <small>{{ Lang::choice('core::terms.facility_testing', $record->facilities->count()) }}</small>
</h3>
<div class="well table-responsive">
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
					<tr>
						<td>
							<a href="{{ route('facilities.edit', $facility->id) }}">{{ $facility->name }}</a>
							@if($facility->deleted_at)
								<span class="label label-warning">Archived</span>
							@endif
						</td>
						<td class="monospace">{{ $facility->pivot->tm_license }}</td>
						<td>
							<div class="btn-group pull-right">
								
							</div>
						</td>
					</tr>
				@endif
			@endforeach
		</tbody>
	</table>
</div>
@endforeach