<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	{{-- Update --}}
	@if( ! Form::isDisabled())
		{!! Button::success(Icon::refresh().' Update')->submit() !!}
	@endif

	{{-- Archive --}}
	@if(Auth::user()->can('person.archive'))
		<a href="{{ route('person.archive', ['instructors', $instructor->id]) }}" class="btn btn-danger" data-confirm="Archive this {{{ Lang::choice('core::terms.instructor', 1) }}}?<br><br>Are you sure?">
			{!! Icon::exclamation_sign() !!} Archive
		</a>
	@endif

	{{-- Add Program --}}
	@if(Auth::user()->can('person.manage_facilities'))
	<a href="{{ route('person.facility.add', ['instructors', $instructor->id]) }}" class="btn btn-default">
		{!! Icon::plus_sign() !!} Add {{ Lang::choice('core::terms.facility_training', 1) }}
	</a>
	@endif
	
	{{-- Add Role --}}
	@if($instructor->user->roles()->count() < 4 && Auth::user()->can('person.manage_roles'))
		<a href="{{ route('users.add_role', $instructor->user_id) }}" class="btn btn-default">
			{!! Icon::plus_sign() !!} Add Role
		</a>
	@endif

	{{-- Login As --}}
	@if((Auth::user()->can('login_as') || Auth::user()->can('facilities.login_as_own_instructor')) && ! $instructor->isLocked && ! $instructor->activeFacilities->isEmpty())
		<a href="{{ route('instructors.loginas', $instructor->id) }}" class="btn btn-default" data-confirm="Login as {{{ Lang::choice('core::terms.instructor', 1) }}} {{{ $instructor->full_name }}}<br><br>Are you sure?</strong>">
			{!! Icon::eye_open() !!} Login As
		</a>
	@endif
</div>