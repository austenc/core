<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{{-- Update --}}
	@if( ! Form::isDisabled())
		{!! Button::success(Icon::refresh().' Update')->submit() !!}
	@endif

	{{-- Archive --}}
	@if(Auth::user()->can('person.archive'))
		<a href="{{ route('person.archive', ['proctors', $proctor->id]) }}" class="btn btn-danger" data-confirm="Archive this {{{ Lang::choice('core::terms.proctor', 1) }}}?<br><br>Are you sure?">
			{!! Icon::exclamation_sign() !!} Archive
		</a>
	@endif

	{{-- Add Working At Relation --}}
	@if(Auth::user()->can('person.manage_facilities'))
		<a href={{ route('person.facility.add', ['proctors', $proctor->id]) }} class="btn btn-default">
			{!! Icon::plus_sign() !!} Add New {{ Lang::choice('core::terms.facility', 1) }}
		</a>
	@endif
	
	{{-- Add Role --}}
	@if($user->roles()->count() < 4 && Auth::user()->can('person.manage_roles'))
		<a href="{{ route('users.add_role', $proctor->user_id) }}" class="btn btn-default">
			{!! Icon::plus_sign() !!} Add Role
		</a>
	@endif

	{{-- Login As --}}
	@if(Auth::user()->can('login_as') && ! $proctor->isLocked)
		<a href="{{ route('proctors.loginas', array('id' => $proctor->id)) }}" class="btn btn-default" data-confirm="Login as {{{ Lang::choice('core::terms.proctor', 1) }}} {{{ $proctor->fullname }}}?<br><br>Are you sure?">
			{!! Icon::eye_open() !!} Login As
		</a>
	@endif
</div>