@extends('core::layouts.default')

@section('title', 'Invoicing Rates')

@section('content')
	<div class="row">
		<div class="col-md-9">
			<h1>Invoicing Rates</h1>
			<div class="well">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Service Name</th>
							<th>Discipline</th>
							<th>Test Type</th>
							<th>Rate</th>
							<th>No Show Rate</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($billingRates as $rate)
							<tr>
								<td>{{ $rate->svc_name }}</td>
								<td>
									@foreach($disciplines as $discipline)
										@if($rate->discipline_id == $discipline->id)
											{{ $discipline->abbrev }}
										@endif
									@endforeach
								</td>
								<td>{{ ucfirst($rate->test_type) }}</td>
								<td>${{ $rate->rate }}</td>
								<td>${{ $rate->rate_ns }}</td>
								<td>
									<a href="{{ route('billingrate.edit', [$rate->id]) }}" data-toggle="tooltip" title="Edit Rate" class="btn btn-link pull-right"><i class="glyphicon glyphicon-pencil"></i></a>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
		<div class="col-md-3" style="padding-top: 13px;">
			<h3>Quick Links</h3>
			<div class="list-group">
				<a href="/accounting/billrates/create" class="list-group-item">
					<span class="glyphicon glyphicon-plus"></span> Add Rate
				</a>
			</div>
		</div>
	</div>
@stop