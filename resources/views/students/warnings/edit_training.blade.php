@if($training->archived)
	<div class="alert alert-danger">
		{!! Icon::exclamation_sign() !!} <strong>Archived</strong> Training
	</div>
@elseif($training->expired)
	<div class="alert alert-danger">
		{!! Icon::exclamation_sign() !!} <strong>Expired</strong> Training
	</div>
@endif