@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<h3>Timestamps</h3>
	<div class="well">
		<table class="table table-striped table-condensed">
			<tbody>
				<tr>
					<th scope="row">Created</th>
					<td>{{ $record->created_at }}</td>
				</tr>
				<tr>
					<th scope="row">Updated</th>
					<td>{{ $record->updated_at }}</td>
				</tr>

				@if($record->creator)
					<tr>
						<th scope="row">Created By</th>
						<td>
				  			{{ $record->creator->fullname }} ({{ $record->creator->getMorphClass() }})
						</td>
					</tr>
				@endif

				@if( ! empty($record->synced_at))
					<tr>
						<th scope="row">Last Sync</th>
				  		<td>{{ $record->synced_at }}</td>	
					</tr>
				@endif

				@if( ! empty($record->deleted_at))
					<tr>
						<th scope="row">Archived</th>
				  		<td>{{ $record->deleted_at }}</td>	
					</tr>
				@endif

				@if( ! empty($record->activated_at))
					<tr>
						<th scope="row">Last Activation</th>
				  		<td>{{ $record->activated_at }}</td>	
					</tr>
				@endif

				@if( ! empty($record->deactivated_at))
					<tr>
						<th scope="row">Last Deactivation</th>
				  		<td>{{ $record->deactivated_at }}</td>	
					</tr>
				@endif
			</tbody>
		</table>
	</div>
@endif