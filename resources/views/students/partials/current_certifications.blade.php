<h2>Current Certifications</h2>
@if( ! $certs->isEmpty())
<table class="table table-striped">
	<thead>
		<tr>
			<th>Name</th>
			<th>Abbrev</th>
		</tr>
	</thead>
	@foreach($certs as $c)
		<tr>
			<td>{{ $c->name }}</td>
			<td>{{ $c->abbrev }}</td>
		</tr>
	@endforeach
</table>
@else
	<p class="well">No certifications on record.</p>
@endif