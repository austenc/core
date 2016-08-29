@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'scantron.save_offsets']) !!}
	<h1>Adjust Scan Form Print Offsets</h1>
	<div class="alert alert-info">
		The directions refer to where the <strong>bubbles</strong> will move. Numbers shown refer to vertical and horizontal offsets, respectively.
	</div>
	<hr>
	<div class="scanform-adjust">
		<div class="row vspaced">

			{{-- Offset boxes / print test / save button --}}
			<div class="col-xs-7 text-center">
				<div class="col-xs-6">
					<button name="print_test" value="true" class="btn btn-warning" type="submit"><span class="glyphicon glyphicon-print"></span> Print Test</button>
					<button class="btn btn-success" type="submit">{!! Icon::floppy_save() !!} Save Settings</button>
				</div>

				<div class="col-xs-3">
					<div class="input-group">
						<span class="input-group-addon">V:</span>
						{!! Form::text('voff', $vOff, ['placeholder' => 'Vertical Offset', 'class' => 'pull-left offset-input', 'id' => 'voff']) !!}
					</div>
				</div>
				<div class="col-xs-3">
					<div class="input-group">
						<span class="input-group-addon">H:</span>
						{!! Form::text('hoff', $hOff, ['placeholder' => 'Horizontal Offset', 'class' => 'pull-left offset-input', 'id' => 'hoff']) !!}
					</div>
				</div>						
			</div>

			{{-- Directional Controls --}}
			<div class="col-xs-5 text-center">
				<div class="col-xs-3 col-xs-offset-3">
					<a href="#" class="btn btn-info" data-direction="left">
						{!! Icon::arrow_left(); !!} Left
					</a>
				</div>
				<div class="col-xs-3">
					<a href="#" class="btn btn-info btn-up" data-direction="up">
						{!! Icon::arrow_up(); !!} Up
					</a>
					<br>
					<a href="#" class="btn btn-info" data-direction="down">
						{!! Icon::arrow_down(); !!} Down
					</a>
				</div>
				<div class="col-xs-3">
					<a href="#" class="btn btn-info" data-direction="right">
						{!! Icon::arrow_right(); !!} Right
					</a>
				</div>
			</div>		
		</div>
		
		{!! Form::close() !!}
		
		{{-- Embedded PDF --}}
		<object id="pdf-display" data="{{ route('scantron.example', [$vOff, $hOff]) }}" type="application/pdf" width="100%" height="1000">
			PDF could not be displayed. Try using a different browser.
		</object>
	</div>

@stop

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			var pdfTimer;   // timer that fires when a 'move' button is clicked
			var $vInput     = $('#voff');
			var $hInput     = $('#hoff');
			var step        = 0.01; // the amount to move each time
			var updateDelay = 1000; // delay in milliseconds for pdf to refresh after action

			/**
			 * When a directional button is clicked, we:
			 * - change appropriate offset box
			 * - refresh the pdf
			 */
	        $('body').on('click', "[data-direction]", function(){
	        	var d = $(this).attr('data-direction');

	        	// Clear the timer
	        	clearTimeout(pdfTimer);

	        	// left / right
	        	if(d == 'left')
	        	{
	        		setInput($hInput, step, '-');
	        	}
	        	if(d == 'right')
	        	{
	        		setInput($hInput, step);
	        	}

	        	// up / down
	        	if(d == 'up')
	        	{
	        		setInput($vInput, step, '-');
	        	}
	        	if(d == 'down')
	        	{
	        		setInput($vInput, step);
	        	}

	        	// reset the timer
	        	pdfTimer = setTimeout(updatePdf, updateDelay);

	        	return false;
	        });

	        // Also update the pdf with the latest values on keyup for the inputs
	        $('.offset-input').keydown(function() {
	        	clearTimeout(pdfTimer);
	        });
	        $('.offset-input').keyup(function() {
	        	clearTimeout(pdfTimer);
	        	pdfTimer = setTimeout(updatePdf, updateDelay);
	        });

		}); // end document.ready

		function updatePdf()
		{
			var $vInput    = $('#voff');
			var $hInput    = $('#hoff');
			var $pdf       = $('#pdf-display');
			var url = "{{ route('scantron.example') }}/" + prepNumber($vInput.val()) +'/'+ prepNumber($hInput.val())+'/';
			
			// reload the data in the pdf display
			$pdf.attr('data', url);
			$pdf.load(url);		
		}

		function setInput(input, step, operator)
		{
			if(operator == '-')
			{
				input.val((Number(input.val()) - step).toFixed(3))	
			}
			else
			{
				input.val((Number(input.val()) + step).toFixed(3))
			}
			
		}

		function prepNumber(n)
		{
			return Number(n).toFixed(3);
		}
	</script>
@stop