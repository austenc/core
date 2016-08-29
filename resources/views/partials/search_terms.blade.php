<div class="panel panel-default">
	<div class="panel-heading clearfix">
		<div class="panel-title pull-left"><small>Search Parameters</small></div>
		<a href="{{ route($controller.'.search.clear') }}" class="btn btn-sm btn-default pull-right">Clear Search</a>
	</div>
	<div class="panel-body">
		<table class="table table-condensed">
			<thead>
				<tr>
					<th>Search</th>
					<th>Type</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				@foreach($searchTypes as $k => $type)
					<tr>
						<td>{{ $searchQueries[$k] }}</td>
						<td class="text-muted">{{ $type }}</td>
						<td>
							<a href="{{ route($controller.'.search.delete', $k) }}"
							data-toggle="tooltip" title="Remove Search Term">
								{!! Icon::remove_sign() !!}
								<span class="sr-only">Remove</span>
							</a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>

@if(count($searchTypes) > 1)
	<h4 class="text-center text-muted">Showing results that match all above criteria.</h4>
@endif