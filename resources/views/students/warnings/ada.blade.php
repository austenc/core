@if( ! $student->acceptedAdas->isEmpty())
	<div class="alert alert-info">
		{!! Icon::apple() !!} <strong>ADA</strong> {{ Lang::choice('core::terms.student', 1) }} has accepted ADA
	</div>
@endif

@if( ! $student->pendingAdas->isEmpty())
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>ADA</strong> {{ Lang::choice('core::terms.student', 1) }} has pending ADA
	</div>
@endif