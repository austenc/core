@extends('core::layouts.default')

@section('title', 'Accounts Payable')

@section('content')
{!! Form::open(['route' => 'accounting.observer.processallpayments', 'id' => 'frmPayAllObserver']) !!}
	{{ Session::put('payable', $payables)}}
	<button type="button" class="btn btn-sm btn-primary" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> Print Statements</button>
	<button type="submit" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-usd"></span> Mark All Paid</button>
	<?php $cnt = 0; ?>
	@foreach($payables as $payable)
		@if(count($payable['events']))
			<?php $cnt++; ?>
			<div style="text-align: center;">
				<h3>D&amp;S Diversified Technologies</h3>
				<h5>3310 McHugh Drive</h5>
				<h5>Helena, MT 59604</h5>
			</div>

			<div class="row">
				<div class="col-md-12">
					<h4><?php echo date("F j, Y"); ?></h4>
				</div>
			</div>
			<div class="row">
				<div class="col-md-11">
					<address style="font-weight: bold; padding-left: .75in; padding-top: .55in;">
						{{ $payable['first'] }} {{ $payable['last'] }}<br />
						{{ $payable['address'] }}<br />
						{{ $payable['city'] }}, {{ $payable['state'] }} {{ $payable['zip'] }}
					</address>
				</div>
			</div>
			<div class="well">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Test Date</th>
							<th>Discipline</th>
							<th>Pkt #</th>
							<th>Type</th>
							<th>Knowledge</th>
							<th>NS</th>
							<th>Oral</th>
							<th>NS</th>
							<th>Skill</th>
							<th>NS</th>
							<th>ADA</th>
						</tr>
					</thead>
					<tbody>
						@foreach($payable['events'] as $due)
							@if($due['knowledge'] > 0 || $due['knowledge_ns'] > 0 || $due['oral'] > 0 || $due['oral_ns'] > 0 || $due['skill'] > 0 || $due['skill_ns'] > 0)
							<tr>
								<td>{{ $due['test_date'] }}</td>
								<td>{{ $due['discipline'] }}</td>
								<td>{{ $due['event_id'] }}</td>
								<td>{{ $due['test_type'] }}</td>
								<td>
									@if($due['knowledge'] > 0)
										{{ $due['knowledge'] }}
									@endif
								</td>
								<td>
									@if($due['knowledge_ns'] > 0)
										{{ $due['knowledge_ns'] }}
									@endif
								</td>
								<td>
									@if($due['oral'] > 0)
										{{ $due['oral'] }}
									@endif
								</td>
								<td>
									@if($due['oral_ns'] > 0)
										{{ $due['oral_ns'] }}
									@endif
								</td>
								<td>
									@if($due['skill'] > 0)
										{{ $due['skill'] }}
									@endif
								</td>
								<td>
									@if($due['skill_ns'] > 0)
										{{ $due['skill_ns'] }}
									@endif
								</td>
								<td>
									@if($due['adas'] > 0)
										{{ $due['adas'] }}
									@endif
								</td>
							</tr>
							@endif
						@endforeach
						<tr style="font-weight: bold;">
							<td colspan="4" style="text-align: right;">Sub-Totals:</td>
							<td colspan="2">
								@if($payable['totalKnowledgeDue'] > 0)
									${{ money_format('%i', $payable['totalKnowledgeDue']) }}
								@endif
							</td>
							<td colspan="2">
								${{ money_format('%i', $payable['totalOralDue']) }}
							</td>
							<td colspan="2">
								${{ money_format('%i', $payable['totalSkillDue']) }}
							</td>
							<td>
								${{ money_format('%i', $payable['totalAdaDue']) }}
							</td>
						</tr>
						<tr style="font-weight: bold;">
							<td colspan="4" style="text-align: right;">Total Due Observer:</td>
							<td colspan="7">
								${{ money_format('%i', $payable['totalDue']) }}
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div style="page-break-before: always;"></div>
		@endif
	@endforeach
	@if($cnt == 0)
		<p>&nbsp;</p>
		<div class="well">
			<p>No payables are due</p>
		</div>
	@endif
	<button type="button" class="btn btn-sm btn-primary" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> Print Statements</button>
	<button type="submit" class="btn btn-sm btn-default"><span class="glyphicon glyphicon-usd"></span> Mark All Paid</button>
	{!! Form::close() !!}
@stop