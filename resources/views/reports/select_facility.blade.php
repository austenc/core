@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => ['reports.pass_fail', $from, $to], 'method' => 'POST']) !!}
	<div class="row">
		<div class="col-sm-8">
			<h2>Select {{ Lang::choice('core::terms.facility', 1) }}</h2>
		</div>
		<div class="col-sm-4">
			<a href="javascript:history.back()" class="pull-right back-link btn-link">
				{!! Icon::arrow_left() !!} Back
			</a>
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('facility_id', Lang::choice('core::terms.facility', 1)) !!}
		{!! Form::select('facility_id', $facilities) !!}
	</div>

	<div class="form-group">
		{!! Form::label('instructor_id', Lang::choice('core::terms.instructor', 1)) !!}
		{!! Form::select('instructor_id', $instructors) !!}
		<span class="text-danger">{{ $errors->first('instructor_id') }}</span>
	</div>
	<button type="submit" class="btn btn-success">Go</button>
{!! Form::close() !!}
@stop

@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		// Disable the first option in the facility select
		$('#facility_id option:first-child').attr('disabled', 'disabled');
	
		$('#facility_id').change(updateInstructors);
	});


	function updateInstructors()
	{
		var facility      = $('#facility_id').val();
		var $instructorId = $('#instructor_id');

		// Reset the instructors dropdown
		$('#instructor_id').empty();
		$('#instructor_id').append(
		$('<option></option>').val(0).html("Show All")
		);

		$.ajax({
	        url: "/facilities/"+facility+"/instructors/json",
	        success: function(result){                    
	            if( ! $.isEmptyObject(result))
	            {
	            	$.each(result, function(i, val){
						$('#instructor_id').append(
							$('<option></option>').val(val).html(val)
						);
					});
	            }
	            else {
	            	flash('No instructors found for selected facility, please choose another.', 'warning', 5000);
	            }
	        }
	    });	
	}


</script>
@stop