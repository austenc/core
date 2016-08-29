<h4>Affiliated {{ Lang::choice('core::terms.facility_training', 2) }}</h4>
<table class="table table-striped" id="affiliated-sites">
	<thead>
		<tr>
			<th>Name</th>
			<th>TM License</th>
			<th>Location</th>
			@if(Auth::user()->can('facilities.manage_affiliated'))
				<th></th>
			@endif
		</tr>
	</thead>
	<tbody>
		@if($affiliates->isEmpty())
			<tr>
				<td class="text-center" colspan="4">No Affiliated {{ Lang::choice('core::terms.facility_training', 2) }}</td>
			</tr>
		@else
			@foreach($affiliates->sortBy('name') as $affiliate)
			<tr>
				<td><a href="{{ route('facilities.edit', $affiliate->id) }}">{{ $affiliate->name }}</a></td>

				<td>{{ $affiliate->allDisciplines->keyBy('id')->get($discipline->id)->pivot->tm_license }}</td>

				<td>{{ $affiliate->city }}, {{ $affiliate->state }}</td>

				@if(Auth::user()->can('facilities.manage_affiliated'))
					<td>
						<div class="btn-group pull-right">
							<a data-confirm="Remove Affiliate?<br><br>Are you sure?" href="{{ route('facilities.affiliate.remove', [$facility->id, $discipline->id, $affiliate->id]) }}" class="btn btn-sm btn-default" id="remove-affiliate-{{ strtolower($discipline->abbrev) }}-{{ $affiliate->id }}-btn">Remove</a>
						</div>
					</td>
				@endif
			</tr>
			@endforeach
		@endif
	</tbody>
</table>