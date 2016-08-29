@if(Auth::user()->ability(['Admin', 'Staff'], []))
	@if($user->roles()->count() > 1)
		<div class="alert alert-warning">
			{!! Icon::flag() !!} <strong>Multi Role</strong> User record has multiple assigned roles
		</div>
	@endif
@endif