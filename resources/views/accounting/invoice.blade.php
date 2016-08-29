@extends('core::layouts.default')

@section('title', 'Invoicing')

@section('content')
	{!! Form::open(['route' => 'accounting.gettests', 'class' => 'form-horizontal']) !!}
	@if(isset($eventlist))
		{{ Session::put('eventlist', $eventlist)}}
	@endif
	<div class="row">
		<div class="col-md-12">
			<h1>Invoicing</h1>
			<div class="well hidden-print">
				<div>
					<div class="form-group">
						<div class="col-md-2">
							{!! Form::label('report_type', 'Report Type:') !!}
						</div>
						<div class="col-md-3">
							@if(isset($report_type))
								{!! Form::select('report_type', $disciplines, $report_type, ['id' => 'report_type', 'class' => 'form-control']) !!}
							@else
								{!! Form::select('report_type', $disciplines, 0, ['id' => 'report_type', 'class' => 'form-control']) !!}
							@endif
						</div>
					</div>
				</div>
				<div>
					<div class="form-group">
						<div class="col-md-2">
							{!! Form::label('start_date', 'Start Date:') !!}
						</div>
						<div class="col-md-3">
							@if(isset($start_date))
								{!! Form::text('start_date', $start_date, ['data-provide' => 'datepicker']) !!}
							@else
								{!! Form::text('start_date', '', ['data-provide' => 'datepicker']) !!}
							@endif
						</div>
						<div class="col-md-2">
							{!! Form::label('end_date', 'End Date:') !!}
						</div>
						<div class="col-md-3">
							@if(isset($end_date))
								{!! Form::text('end_date', $end_date, ['data-provide' => 'datepicker']) !!}
							@else
								{!! Form::text('end_date', '', ['data-provide' => 'datepicker']) !!}
							@endif
						</div>
						<div class="col-md-2">
							<button type="submit" class="btn btn-success">Get Tests</button>
						</div>
					</div>
				</div>
			</div>
			@if(isset($start_date))
				<div class='container pull-right' style='text-align: right;'>
					<button type='button' class='btn btn-primary' onclick='window.print()'>Print Invoice</button>
					<button type='button' class='btn btn-default' onclick="markInvoiced()">Mark All Invoiced</button>
				</div>
			@endif
			<h3>
				Event/Facility - 
				@if(isset($report_type))
					@if($report_type == 1)
						CNA
					@else
						CMA
					@endif
				@endif
			</h3>
			<div class="well">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Test Date</th>
							<th>Site</th>
							<th>Type</th>
							<th>Knowledge</th>
							<th>NS</th>
							<th>Oral</th>
							<th>NS</th>
							<th>Skill</th>
							<th>NS</th>
						</tr>
					</thead>
					<tbody>
						@if(isset($eventlist))
							@foreach($eventlist as $test)
								<tr>
									<td>{{ $test['event_date'] }}</td>
									<td>{{ $test['facility_id'] }}</td>
									<td>
										{{ $test['test_type'] }}
									</td>
									<td>
										@if($test['know'] > 0)
											{{ $test['know'] }}
										@endif
									</td>
									<td>
										@if($test['know_ns'] > 0)
											{{ $test['know_ns'] }}
										@endif
									</td>
									<td>
										@if($test['oral'] > 0)
											{{ $test['oral'] }}
										@endif
									</td>
									<td>
										@if($test['oral_ns'] > 0)
											{{ $test['oral_ns'] }}
										@endif
									</td>
									<td>
										@if($test['skill'] > 0)
											{{ $test['skill'] }}
										@endif
									</td>
									<td>
										@if($test['skill_ns'] > 0)
											{{ $test['skill_ns'] }}
										@endif
									</td>
								</tr>
							@endforeach
							<tr><td colspan="9">&nbsp;</td></tr>
							<tr>
								<td colspan="2">Paper-Regional</td>
								<td>[R]</td>
								<td>
									@if($know_r_total > 0)
										{{ $know_r_total }}
									@endif
								</td>
								<td>
									@if($know_r_ns_total > 0)
										{{ $know_r_ns_total }}
									@endif
								</td>
								<td>
									@if($oral_r_total > 0)
										{{ $oral_r_total }}
									@endif
								</td>
								<td>
									@if($oral_r_ns_total > 0)
										{{ $oral_r_ns_total }}
									@endif
								</td>
								<td>
									@if($skill_r_total > 0)
										{{ $skill_r_total }}
									@endif
								</td>
								<td>
									@if($skill_r_ns_total > 0)
										{{ $skill_r_ns_total }}
									@endif
								</td>
							</tr>
							<tr>
								<td colspan="2">Paper-Flexible</td>
								<td>[X]</td>
								<td>
									@if($know_x_total > 0)
										{{ $know_x_total }}
									@endif
								</td>
								<td>
									@if($know_x_ns_total > 0)
										{{ $know_x_ns_total }}
									@endif
								</td>
								<td>
									@if($oral_x_total > 0)
										{{ $oral_x_total }}
									@endif
								</td>
								<td>
									@if($oral_x_ns_total > 0)
										{{ $oral_x_ns_total }}
									@endif
								</td>
								<td>
									@if($skill_x_total > 0)
										{{ $skill_x_total }}
									@endif
								</td>
								<td>
									@if($skill_x_ns_total > 0)
										{{ $skill_x_ns_total }}
									@endif
								</td>
							</tr>
							<tr>
								<td colspan="2">Web-Flexible</td>
								<td>[W]</td>
								<td>
									@if($know_w_total > 0)
										{{ $know_w_total }}
									@endif
								</td>
								<td>
									@if($know_w_ns_total > 0)
										{{ $know_w_ns_total }}
									@endif
								</td>
								<td>
									@if($oral_w_total > 0)
										{{ $oral_w_total }}
									@endif
								</td>
								<td>
									@if($oral_w_ns_total > 0)
										{{ $oral_w_ns_total }}
									@endif
								</td>
								<td>
									@if($skill_w_total > 0)
										{{ $skill_w_total }}
									@endif
								</td>
								<td>
									@if($skill_w_ns_total > 0)
										{{ $skill_w_ns_total }}
									@endif
								</td>
							</tr>
							<tr>
								<td colspan="2">Web-Regional</td>
								<td>[Y]</td>
								<td>
									@if($know_y_total > 0)
										{{ $know_y_total }}
									@endif
								</td>
								<td>
									@if($know_y_ns_total > 0)
										{{ $know_y_ns_total }}
									@endif
								</td>
								<td>
									@if($oral_y_total > 0)
										{{ $oral_y_total }}
									@endif
								</td>
								<td>
									@if($oral_y_ns_total > 0)
										{{ $oral_y_ns_total }}
									@endif
								</td>
								<td>
									@if($skill_y_total > 0)
										{{ $skill_y_total }}
									@endif
								</td>
								<td>
									@if($skill_y_ns_total > 0)
										{{ $skill_y_ns_total }}
									@endif
								</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
								<td style="text-align: left;">${{ money_format('%i', $know_due) }}</td>
								<td style="text-align: left;">${{ money_format('%i', $know_ns_due) }}</td>
								<td style="text-align: left;">${{ money_format('%i', $oral_due) }}</td>
								<td style="text-align: left;">${{ money_format('%i', $oral_ns_due) }}</td>
								<td style="text-align: left;">${{ money_format('%i', $skill_due) }}</td>
								<td style="text-align: left;">${{ money_format('%i', $skill_ns_due) }}</td>
							</tr>
							<tr>
								<td colspan="3">Total Due:</td>
								<td colspan="4">${{ money_format('%i', $total_due) }}</td>
								<td colspan="2" style="text-align: left;"></td>
							</tr>
						@endif
					</tbody>
				</table>
			</div>
			@if(isset($start_date))
				<div class='container pull-right hidden-print' style='text-align: right;'>
					<button type='button' class='btn btn-primary' onclick='window.print()'>Print Invoice</button>
					<button type='button' class='btn btn-default' onclick="markInvoiced()">Mark All Invoiced</button>
					<a href='/accounting/invoice/{{ substr($start_date, 0, 2) . substr($start_date, 3, 2) . substr($start_date, 6, 4) }}/{{ substr($end_date, 0, 2) . substr($end_date, 3, 2) . substr($end_date, 6, 4) }}/{{ $report_type }}/csv' class='btn btn-success'>Download CSV</a>
				</div>
			@endif
			<div style="page-break-after: always;"></div>
			<h3>
				Invoice Detail - 
				@if(isset($report_type))
					@if($report_type == 1)
						CNA
					@else
						CMA
					@endif
				@endif
			</h3>
			<div class="well">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Site ID</th>
							<th>Site</th>
							<th>Pkt#</th>
							<th>Date</th>
							<th>Typ.</th>
							<th>Candidate</th>
							<th>DOB</th>
							<th>Rater</th>
							<th>Tests</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						@if(isset($studentlist))
							@foreach($studentlist as $student)
								<tr>
									<td>{{ $student['facility_id'] }}</td>
									<td>{{ $student['facility'] }}</td>
									<td>{{ $student['event_id'] }}</td>
									<td>{{ $student['test_date'] }}</td>
									<td>{{ $student['type'] }}</td>
									<td>{{ $student['name'] }}</td>
									<td>{{ $student['dob'] }}</td>
									<td>{{ $student['rater'] }}</td>
									<td>{{ $student['knowledge'] }}{{ $student['skill'] }}</td>
									<td>{{ $student['noshow'] }} {{ $student['free'] }} {{ $student['oral'] }}</td>
								</tr>
							@endforeach
						@endif
					</tbody>
				</table>
			</div>
		</div>
		@if(isset($start_date))
			<div class='container pull-right' style='text-align: right;'>
				<button type='button' class='btn btn-primary' onclick='window.print()'>Print Invoice</button>
					<button type='button' class='btn btn-default' onclick="markInvoiced()">Mark All Invoiced</button>
				<a href='/accounting/invoice/{{ substr($start_date, 0, 2) . substr($start_date, 3, 2) . substr($start_date, 6, 4) }}/{{ substr($end_date, 0, 2) . substr($end_date, 3, 2) . substr($end_date, 6, 4) }}/{{ $report_type }}/csv' class='btn btn-success'>Download CSV</a>
			</div>
		@endif
	</div>
	{!! Form::close() !!}
@stop
@section('scripts')
	<script type="text/javascript">
		function markInvoiced(){
			window.location.href = '/accounting/invoiced';
		}
	</script>
@stop