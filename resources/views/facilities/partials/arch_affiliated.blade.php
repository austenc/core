@if(is_array($facility->actions) && in_array('Testing', $facility->actions))
	<h3 id="facility-affiliates">
		Affiliated {{ Lang::choice('core::terms.facility_training', 2) }} 
		<small>Closed Events -- {{ Lang::choice('core::terms.student', 2) }} trained here are eligible</small>
	</h3>
	<div class="well table-responsive">
		<table class="table table-striped" id="affiliated-sites">
			<thead>
				<tr>
					<th>{{ Lang::choice('core::terms.facility_training', 1) }}</th>
					<th>Location</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($facility->allAffiliated as $aff)
				@if($aff->deleted_at)
				<tr>
				@else
				<tr class="success">
				@endif
					<td>
						<a href="{{ route('facilities.edit', $aff->id) }}">{{ $aff->name }}</a>
						@if($aff->deleted_at)
							<small>(Archived)</small>
						@endif
					</td>
					<td>{{ $aff->city }}, {{ $aff->state }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endif