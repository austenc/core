@extends('core::layouts.default')

@section('content')

@if(Session::has('skills.errors') && ! empty(Session::get('skills.errors')))
	<div class="alert alert-danger">
		<strong>Errors on Test</strong> you are submitting. Hover over red Task Numbers below to see individual Task errors.
	</div>
@endif

@if(Session::has('skills.general_errors') && ! empty(Session::get('skills.general_errors')))
	<div class="alert alert-danger">
		{!! implode('<br>', Session::get('skills.general_errors')) !!}
	</div>
@endif

{!! Form::open(['route' => ['skills.end', $attempt->id]]) !!}	
	<div class="row">
		<div class="col-xs-9">
			@include('core::pagination.number_only', ['paginator' => $tasks])
		</div>
	</div>

	<div class="row">
		<div class="col-xs-9">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-12">
							<span class="lead">Skill Test - {{ $student->fullname }}</span>
						</div>
					</div>
				</div>
				<div class="panel-body">
					<div class="alert alert-warning">
						<h4>Security Affidavit</h4>
							<p>
								I hereby swear to and verify that all security measures were followed and all the candidates listed above completed their tests (both written and skills) without any assistance from any outside source. Further I declare that all testing materials were secure at all times and exclusively in my control and no copies, in any form, were made of any of the testing materials. I certify that I have listed any and all testing irregularities on the irregularities report.
							</p>
					</div>	
					<div class="text-center checkbox">
						<label>
							{!! Form::checkbox('affidavit', 1, false) !!} <strong>I Agree to the Terms Above</strong>
						</label>
					</div>

					<hr>

					<h4>Test Irregularities</h4>
					<div class="form-group row">
						<div class="col-md-12">
							{!! Form::textarea('anomalies', Session::get('skills.anomalies')) !!}
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-3">
			@include('core::skills.testing.sidebars.timestamps')

			<div>
				<button name="end" value="end" data-style="expand-left" class="btn btn-danger btn-block">
					<i class="glyphicon glyphicon-save"></i> End Test
				</button>
			</div>
		</div>
	</div>
{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/bootstrap-3-timepicker/js/bootstrap-timepicker.min.js') !!}
	<script>	
		// add timepicker
		$('.time-picker').timepicker({
			minuteStep: 15,
			defaultTime: false
		});

		// add datepicker
		$('.date-picker').datepicker({ startDate: new Date() });

		function formatAMPM(date) {
		  var hours = date.getHours();
		  var minutes = date.getMinutes();
		  var ampm = hours >= 12 ? 'PM' : 'AM';
		  hours = hours % 12;
		  hours = hours ? hours : 12; // the hour '0' should be '12'
		  minutes = minutes < 10 ? '0'+minutes : minutes;
		  var strTime = hours + ':' + minutes + ' ' + ampm;
		  return strTime;
		}

		// if the end time is empty on page load, fill it with current time
		$(document).ready(function() {
			if ($('#end_time').val() == "") {
				$('#end_time').val(formatAMPM(new Date))
			}
		});
	</script>
@stop