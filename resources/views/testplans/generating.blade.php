@extends('core::layouts.default')

@section('content')
<h1>Generating Testform</h1>

<div class="panel panel-default">
	<div class="panel-heading">
		<span class="h4 text-muted">
			Attempt #<span id="attempt">{{ $attempt }}</span> of {{ $max_attempts }}
		</span>
		<div class="col-xs-4 pull-right">
			<div class="progress progress-striped active">
				<div class="progress-bar progress-bar-primary"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
					<span>Working...</span>
				</div>
			</div>
		</div>

	</div>
	<div class="panel-body">
		<div id="ajaxContent">
			@include('core::partials.loading')
		</div>
	</div>
</div>
@stop

@section('scripts')
	<script type="text/javascript">
		var attempt = {{ $attempt }};

		$(document).ready(function(){
			$('.working').hide();
			generateAttempt();
		});

		function generateAttempt()
		{
			$.ajax({
				url: "/testplans/{{ $testplan_id }}/generating?attempt="+attempt,
				global: false,
				success: function(result){

					// successful attempt
					if(result.success)
					{
						setTimeout(function(){
							window.location.href = '/testforms/create';
						}, 2000);
						return;
					} 

					if(result.redirect)
					{
						setTimeout(function(){
							window.location.href = result.redirect;
						}, 2000);
						return;
					} 

					// update the content on the page
					$content = $('#ajaxContent');
					$content.children().fadeOut(400, function(){
						// Put in stat success / error messages
						$content.html(buildAlert(result.messages, 'success'));
						$content.append(buildAlert(result.errors, 'danger'));

						// update the attempt #
						$('#attempt').text(result.attempt);

						// Fade in the new info
						$content.children().fadeIn();
					});

					// now try again, if we get a real success it will redirect
					attempt += 1; // increment to next attempt so we can limit it
					generateAttempt();
				},
				errors: function()
				{
					$('#ajaxContent').html(showErrors(['Error sending AJAX request, contact a developer immediately.']));
				}
			});		
		}

	</script>
@stop