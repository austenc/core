<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{{-- Update --}}
	@if( ! Form::isDisabled())
		{!! Button::success(Icon::refresh().' Update')->submit() !!}
	@endif

	{{-- Archive --}}
	@if(Auth::user()->can('person.archive'))
		<a href="{{ route('person.archive', ['actors', $actor->id]) }}" class="btn btn-danger" data-confirm="Archive this {{{ Lang::choice('core::terms.actor', 1) }}}?<br><br>Are you sure?">
			{!! Icon::exclamation_sign() !!} Archive
		</a>
	@endif

	{{-- Add Facility --}}
	@if(Auth::user()->can('person.manage_facilities'))
		<a href="{{ route('person.facility.add', ['actors', $actor->id]) }}" class="btn btn-default">
			{!! Icon::plus_sign() !!} Add New {{ Lang::choice('core::terms.facility', 1) }}
		</a>
	@endif

	{{-- Login As --}}
	@if($user->roles()->count() < 4 && Auth::user()->can('person.manage_roles'))
		<a href="{{ route('users.add_role', $actor->user_id) }}" class="btn btn-default">
			{!! Icon::plus_sign() !!} Add Role
		</a>
	@endif
	
	{{-- Login As --}}
	@if(Auth::user()->can('login_as') && ! $actor->isLocked)
		<a href="{{ route('actors.loginas', array('id' => $actor->id)) }}" class="btn btn-default" 
		data-confirm="Are you sure you want to <strong>login as this {{{ Lang::choice('core::terms.actor', 1) }}}?</strong>">
			{!! Icon::eye_open() !!} Login As
		</a>
	@endif
</div>