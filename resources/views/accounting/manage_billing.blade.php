@extends('core::layouts.default')

@section('title', 'Invoicing Rates')

@section('content')
	<div class="row">
	{!! Form::open(['route' => 'accounting.getbilling', 'class' => 'form-horizontal']) !!}
		<h1>Manage Attempt Billing</h1>
		<h3>Billing Detail Paramaters</h3>
		<div class="well">
			<div class="form-group">
				<div class="col-md-1">
					{!! Form::label('start_date', 'Start:') !!}
				</div>
				<div class="col-md-2">
					@if(isset($start_date))
						{!! Form::text('start_date', $start_date, ['data-provide' => 'datepicker']) !!}
					@else
						{!! Form::text('start_date', '', ['data-provide' => 'datepicker']) !!}
					@endif
				</div>
				<div class="col-md-1">
					{!! Form::label('end_date', 'End:') !!}
				</div>
				<div class="col-md-2">
					@if(isset($end_date))
						{!! Form::text('end_date', $end_date, ['data-provide' => 'datepicker']) !!}
					@else
						{!! Form::text('end_date', '', ['data-provide' => 'datepicker']) !!}
					@endif
				</div>
				<div class="col-md-1">
					{!! Form::label('billing_status', 'Status:') !!}
				</div>
				<div class="col-md-2">
					{!! Form::select('billing_status', ['' => 'All', 'free' => 'Free', 'invoiced' => 'Invoiced', 'paid' => 'Paid', 'uninvoiced' => 'Uninvoiced'], $billing_status, ['id' => 'billing_status', 'class' => 'form-control']) !!}
				</div>
				<div class="col-md-2">
					<button type="submitz" class="btn btn-success">Get Tests</button>
				</div>
			</div>
		</div>
	{!! Form::close() !!}
		<h3>Billing Detail List</h3>
		<div class="well">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Facility</th>
						<th>Candidate</th>
						<th>Test Type</th>
						<th>Status</th>
						<th>Payment Status</th>
						<th>Payable Status</th>
						<th>Billing Status</th>
					</tr>
				</thead>
				<tbody>
					@if(isset($billingAttempts))
						@foreach($billingAttempts AS $attempt)
							<tr>
								<td>{{ ucwords($attempt['facility_name']) }}</td>
								<td>{{ ucwords($attempt['student_name']) }}</td>
								<td>{{ $attempt['test_type'] }}</td>
								<td>{{ ucfirst($attempt['status']) }}</td>
								<td>{{ ucfirst($attempt['payment_status']) }}</td>
								<td>{{ ucfirst($attempt['payable_status']) }}</td>
								<td>
									<select id="billState" name="billState" class="form-control" onchange="updateBillingStatus($(this), {{ $attempt['attempt_id'] }}, '{{ substr($attempt['test_type'], 0, 1) }}')">
										<option value="free" @if($attempt['billing_status'] == 'free') selected="selected" @endif> Free</option>
										<option value="invoiced" @if($attempt['billing_status'] == 'invoiced') selected="selected" @endif> Invoiced</option>
										<option value="paid" @if($attempt['billing_status'] == 'paid') selected="selected" @endif> Paid</option>
										<option value="uninvoiced" @if($attempt['billing_status'] == 'uninvoiced') selected="selected" @endif> Uninvoiced</option>
									</select>
								</td>
							</tr>
						@endforeach
					@endif
				</tbody>
			</table>
		</div>
	</div>
@stop
@section('scripts')
	<script type="text/javascript" type="javascript">
		function updateBillingStatus(field, attemptID, attemptType)
		{
			$.ajax({
				url: '/accounting/billing/update/' + attemptID + '/' + attemptType + '/' + $(field).val(),
				data: 'json',
				success: function(data){
					$(".flash-messages").append('<div class="alert alert-success alert-dismissable fade in"><button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button><div class="messages"><ul><li>Billing Status Updated.</li></ul></div></div></div>');
				}
			})
		}
	</script>
@stop