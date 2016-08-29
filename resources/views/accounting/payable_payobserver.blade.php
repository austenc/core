@extends('core::layouts.default')

@section('title', 'Pay Observer')

@section('content')
	{!! Form::open(['route' => 'accounting.observer.processpayment', 'id' => 'frmPayObserver']) !!}
	{{ Session::put('payable', $payables)}}
	<div class="row">
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
					{{ $first }} {{ $last }}<br />
					{{ $address }}<br />
					{{ $city }}, {{ $state }} {{ $zip }}
				</address>
			</div>
			<div class="col-md-1 pull-right" style="text-align: right;">
				{!! HTML::backlink('accounting.billing') !!}
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
					@foreach($payables as $payable)
						<tr>
							<td>{{ $payable['date'] }}</td>
							<td>{{ $payable['id'] }}</td>
							<td>{{ $payable['test_type'] }}</td>
							<td>
								@if($payable['know'] > 0)
									{{ $payable['know'] }}
								@endif
							</td>
							<td>
								@if($payable['know_ns'] > 0)
									{{ $payable['know_ns'] }}
								@endif
							</td>
							<td>
								@if($payable['oral'] > 0)
									{{ $payable['oral'] }}
								@endif
							</td>
							<td>
								@if($payable['oral_ns'] > 0)
									{{ $payable['oral_ns'] }}
								@endif
							</td>
							<td>
								@if($payable['special'] > 0)
									{{ $payable['special'] }}
								@endif
							</td>
							<td>
								@if($payable['special_ns'])
									{{ $payable['special_ns'] }}
								@endif
							</td>
							<td>
								@if($payable['skill'] > 0)
									{{ $payable['skill'] }}
								@endif
							</td>
							<td>
								@if($payable['skill_ns'] > 0)
									{{ $payable['skill_ns'] }}
								@endif
							</td>
						</tr>
					@endforeach
					<tr style="font-weight: bold;">
						<td colspan="5" style="text-align: right;">Special Knowledge:</td>
						<td colspan="4">
							${{ money_format('%i', $totalSpecialDue) }}
						</td>
						<td colspan="2">
							<em>({{ $numSpecial }}) @ rate {{ money_format('%i', $specialRate) }}</em>
						</td>
					</tr>
					<tr style="font-weight: bold;">
						<td colspan="5" style="text-align: right;">Totals:</td>
						<td colspan="2">
							@if($totalKnowledgeDue > 0)
								${{ money_format('%i', $totalKnowledgeDue) }}
							@endif
						</td>
						<td colspan="2">
							@if($totalOralDue > 0)
								${{ money_format('%i', $totalOralDue) }}
							@endif
						</td>
						<td colspan="2">
							@if($totalSkillDue > 0)
								${{ money_format('%i', $totalSkillDue) }}
							@endif
						</td>
					</tr>
					<tr style="font-weight: bold;">
						<td colspan="5" style="text-align: right;">ADA Total:</td>
						<td colspan="6">
							${{ money_format('%i', $totalAdaDue) }}
						</td>
					</tr>
					<tr style="font-weight: bold;">
						<td colspan="5" style="text-align: right;">Total Due Observer:</td>
						<td colspan="6">
							${{ money_format('%i', $totalPayable) }}
						</td>
					</tr>
					<tr>
						<td colspan="11" style="text-align: center;">
							<button type="button" class="btn btn-sm btn-primary" onclick="printPay()">Pay Observer/Print Statement</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	{!! Form::close() !!}
@stop
@section('scripts')
	<script type="text/javascript">
		function printPay(){
			window.print();
			$("#frmPayObserver").submit();
		}
	</script>
@stop