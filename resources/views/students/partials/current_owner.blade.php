@if(Auth::user()->ability(['Admin', 'Staff'], []))
<h3>
	Current Owner <small>Add Training will update Owner</small>
</h3>
<div class="well table-responsive">
	@if($currentInstructor !== null)
	<table class="table table-striped" id="certs-table">
		<thead>
			<tr>
				<th>Owner</th>
				<th>Status</th>
				<th class="hidden-xs">Created</th>
			</tr>
		</thead>
		<tbody>	
			<tr>
				<td>
					<a href="{{ route('instructors.edit', $currentInstructor->id) }}">
						{{ $currentInstructor->fullName }}
					</a>
				</td>
				<td>Active</td>
				<td class="hidden-xs">
					{{ date('m/d/Y', strtotime($currentInstructor->pivot->created_at)) }}
				</td>
			</tr>
		</tbody>
	</table>
	@else
		No Current Owner
	@endif
</div>
@endif