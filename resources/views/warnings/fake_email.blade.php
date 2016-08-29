@if($user->hasFakeEmail())
	<div class="alert alert-warning">
		{!! Icon::flag() !!} <strong>Temp Email</strong> User will be forced to change email on next login 
	</div>
@endif