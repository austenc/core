<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	
	{{-- Update --}}
	@if( ! Form::isDisabled())
		{!! Button::success(Icon::refresh().' Update')->submit()->block() !!}
	@endif

	{{-- Archive --}}
	@if(Auth::user()->can('facilities.archive'))
		<a href="{{ route('person.archive', ['facilities', $facility->id]) }}" class="btn btn-danger" data-confirm="Archive this {{{ Lang::choice('core::terms.facility', 1) }}}?<br><br>Are you sure?">
			{!! Icon::exclamation_sign() !!} Archive
		</a>
	@endif

	{{-- Login As --}}
	@if($facility->canLoginAs)
		<a href="{{ route('facilities.loginas', $facility->id) }}" class="btn btn-default btn-block" data-confirm="Are you sure you want to <strong>login as this {{{ Lang::choice('core::terms.facility', 1) }}}?</strong>">
			{!! Icon::eye_open() !!} Login As
		</a>
	@endif

	{{-- Driving Directions --}}
	<a href="{{ route('facilities.directions', $facility->id) }}" class="btn btn-default btn-block">
		{!! Icon::road() !!} Get Directions
	</a>

	{{-- Add Person --}}
	@if(Auth::user()->can('facilities.person.add'))
		<a href="{{ route('facilities.person.add', $facility->id) }}" class="btn btn-default btn-block">
			{!! Icon::plus_sign() !!} Add Person
		</a>
	@endif

	{{-- Add Discipline --}}
	@if(Auth::user()->ability(['Admin', 'Staff'], []) && $facility->disciplines->count() != Discipline::all()->count())
		<a href="{{ route('facilities.discipline.add', $facility->id) }}" data-toggle="modal" data-target="#add-discipline" class="btn btn-default btn-block">
			{!! Icon::plus_sign() !!} Add Discipline
		</a>
	@endif
</div>