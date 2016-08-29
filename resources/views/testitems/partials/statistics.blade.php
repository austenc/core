<h3 id="stats">Statistics</h3>
<div class="well">
	@if($item->stats->isEmpty())
	No Stats available
	@else
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Client</th>
				<th>P-Value</th>
				<th>Difficulty</th>
				<th>Discrimination</th>
				<th>Guessing</th>
				<th>Angoff</th>
				<th>PBS</th>
			</tr>
		</thead>
		<tbody>
			@foreach($item->stats as $i => $stat)
				<tr>
					<td>
						<strong>{{ $stat->client }}</strong><br>
						<small>{{ date('m/d/Y', strtotime($stat->created_at)) }}</small>
					</td>
					<td>{{ $stat->pvalue }}</td>
					<td>{{ $stat->difficulty }}</td>
					<td>{{ $stat->discrimination }}</td>
					<td>{{ $stat->guessing }}</td>
					<td>{{ $stat->angoff }}</td>
					<td>{{ $stat->pbs }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
	@endif
</div>