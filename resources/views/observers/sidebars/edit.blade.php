<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{{-- Update --}}
	@if( ! Form::isDisabled())
		{!! Button::success(Icon::refresh().' Update')->submit() !!}
	@endif
	
	@if(Auth::user()->can('person.archive'))
		{{-- Archive --}}
		<a href="{{ route('person.archive', ['observers', $observer->id]) }}" class="btn btn-danger" data-confirm="Archive this {{{ Lang::choice('core::terms.observer', 1) }}}?<br><br>Are you sure?">
			{!! Icon::exclamation_sign() !!} Archive
		</a>
	@endif

	{{-- Add Facility --}}
	@if(Auth::user()->can('person.manage_facilities'))
		<a href="{{ route('person.facility.add', ['observers', $observer->id]) }}" class="btn btn-default">
			{!! Icon::plus_sign() !!} Add New {{ Lang::choice('core::terms.facility', 1) }}
		</a>
	@endif

	{{-- Add Role --}}
	@if($user->roles()->count() < 4)
		<a href="{{ route('users.add_role', $observer->user_id) }}" class="btn btn-default">
			{!! Icon::plus_sign() !!} Add Role
		</a>
	@endif

	@if(Auth::user()->can('login_as') && ! $observer->isLocked)
		{{-- Login As --}}
		<a href="{{ route('observers.loginas', array('id' => $observer->id)) }}" class="btn btn-default" 
		data-confirm="Are you sure you want to <strong>login as this {{{ Lang::choice('core::terms.observer', 1) }}}?</strong>">
			{!! Icon::eye_open() !!} Login As
		</a>
	@endif
</div>