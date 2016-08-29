@extends('core::layouts.default')

@section('title', 'Accounts Payable')

@section('content')
	{!! Form::open(['route' => 'accounting.observer.processallpayments', 'id' => 'frmPayAllObserver']) !!}
	{{ Session::put('payable', $payableArr)}}
	<button type="button" class="btn btn-sm btn-primary" onclick="printPay()">Pay All Observers/Print Statements</button>
	@foreach($payableArr as $payable)
		<div style="text-align: center;">
			<h3>D&S Diversified Technologies</h3>
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
				<address style="font-weight: bold;">
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
						<th>Pkt #</th>
						<th>Type</th>
						<th>Knowledge</th>
						<th>NS</th>
						<th>Oral</th>
						<th>NS</th>
						<th>Special</th>
						<th>NS</th>
						<th>Skill</th>
						<th>NS</th>
					</tr>
				</thead>
				<tbody>
					@foreach($payable['payables'] as $due)
						<tr>
							<td>{{ $due['date'] }}</td>
							<td>{{ $due['id'] }}</td>
							<td>{{ $due['test_type'] }}</td>
							<td>
								@if($due['know'] > 0)
									{{ $due['know'] }}
								@endif
							</td>
							<td>
								@if($due['know_ns'] > 0)
									{{ $due['know_ns'] }}
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
								@if($due['special'] > 0)
									{{ $due['special'] }}
								@endif
							</td>
							<td>
								@if($due['special_ns'])
									{{ $due['special_ns'] }}
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
						</tr>
					@endforeach
					<tr style="font-weight: bold;">
						<td colspan="5" style="text-align: right;">Special Knowledge:</td>
						<td colspan="4">
							${{ money_format('%i', $payable['totalSpecialDue']) }}
						</td>
						<td colspan="2">
							<em>({{ $payable['numSpecial'] }}) @ rate {{ money_format('%i', $payable['specialRate']) }}</em>
						</td>
					</tr>
					<tr style="font-weight: bold;">
						<td colspan="5" style="text-align: right;">Totals:</td>
						<td colspan="2">
							@if($payable['totalKnowledgeDue'] > 0)
								${{ money_format('%i', $payable['totalKnowledgeDue']) }}
							@endif
						</td>
						<td colspan="2">
							@if($payable['totalOralDue'] > 0)
								${{ money_format('%i', $payable['totalOralDue']) }}
							@endif
						</td>
						<td colspan="2">
							@if($payable['totalSkillDue'] > 0)
								${{ money_format('%i', $payable['totalSkillDue']) }}
							@endif
						</td>
					</tr>
					<tr style="font-weight: bold;">
						<td colspan="5" style="text-align: right;">ADA Total:</td>
						<td colspan="6">
							${{ money_format('%i', $payable['totalAdaDue']) }}
						</td>
					</tr>
					<tr style="font-weight: bold;">
						<td colspan="5" style="text-align: right;">Total Due Observer:</td>
						<td colspan="6">
							${{ money_format('%i', $payable['totalPayable']) }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div style="page-break-before: always;"></div>
	@endforeach
	<button type="button" class="btn btn-sm btn-primary" onclick="printPay()">Pay All Observers/Print Statements</button>
	{!! Form::close() !!}
@stop
@section('scripts')
	<script type="text/javascript">
		function printPay(){
			window.print();
			$("#frmPayAllObserver").submit();
		}
	</script>
@stop