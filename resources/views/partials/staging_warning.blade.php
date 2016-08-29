@if(Session::get('staging.hide_warning') !== true)
	<div class="staging-warning alert alert-warning hidden-print">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<strong>Warning!</strong> data on this server is for testing purposes only. It does not reflect actual data and may be cleared at any time.
		<a href="{{ route('warning.hide_staging') }}" class="btn btn-warning">
			Don't Show Again
		</a>
	</div>
@endif
