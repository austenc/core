@if(is_array($facility->actions) && in_array('Training', $facility->actions))
	{{-- Instructors --}}
	<h3 id="facility-instructors">{{ Lang::choice('core::terms.instructor', 2) }}</h3>
	<div class="well">
		<table class="table table-striped" id="allowed-observers">
			<thead>
				<tr>
					<th>Name</th>
					<th>Location</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($people as $p)
					@if($p->deleted_at)
					<tr>
					@else
					<tr class="success">
					@endif					
						<td>
							<a href="{{ route($route, $p->id) }}">{{ $p->commaName }}</a>
							@if($p->deleted_at)
								<small>(Archived)</small>
							@endif
						</td>
						<td>{{ $p->city }}, {{ $p->state }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endif