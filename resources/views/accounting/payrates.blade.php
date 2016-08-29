@extends('core::layouts.default')

@section('title', 'Billing Rates')

@section('content')
	<div class="row">
		<div class="col-md-9">
			<h1>Accounts Payable Rates</h1>
			<div class="well">
				<table class="table table-striped">
					<thead>
						<th>Level</th>
						<th>Knowledge</th>
						<th>Special Knowledge</th>
						<th>Oral</th>
						<th>Skill</th>
						<th>ADA</th>
						<th>Discipline</th>
						<th>&nbsp;</th>
					</thead>
					<tbody>
						@foreach($payrates as $payrate)
							<tr>
								<td>{{ $payrate->level_name }}</td>
								<td>${{ $payrate->knowledge_rate }}</td>
								<td>
									${{ $payrate->special_rate }}
								</td>
								<td>
									${{ $payrate->oral_rate }}
								</td>
								<td>
									${{ $payrate->skill_rate }}
								</td>
								<td>
									${{ $payrate->ada_rate }}
								</td>
								<td>
									@foreach($disciplines as $discipline)
										@if($discipline->id == $payrate->discipline_id)
											{{ $discipline->abbrev }}
										@endif
									@endforeach
								</td>
								<td>
									<a href="{{ route('payrate.edit', [$payrate->id]) }}" data-toggle="tooltip" title="Edit Rate" class="btn btn-link pull-right"><i class="glyphicon glyphicon-pencil"></i></a>
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
				<a href="/accounting/payrates/create" class="list-group-item">
					<span class="glyphicon glyphicon-plus"></span> Add Rate
				</a>
			</div>
		</div>
	</div>
@stop