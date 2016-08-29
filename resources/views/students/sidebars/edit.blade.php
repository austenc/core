<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	
	{{-- Update --}}
	@if( ! Form::isDisabled())
		{!! Button::success(Icon::refresh().' Update')->submit() !!}
	@endif

	{{-- Reassign --}}
	@if(Auth::user()->ability(['Admin'], []))
		<a href="{{ route('students.history.reassign', $student->id) }}" class="btn btn-warning">{!! Icon::transfer() !!} Reassign History</a>
	@endif

	{{-- Active Student Only --}}
	@if($student->isActive)
		{{-- Archive --}}
		@if(Auth::user()->ability(['Admin', 'Staff'], []))
			<a href="{{ route('person.archive', ['students', $student->id]) }}" class="btn btn-danger" data-confirm="Archive {{{ Lang::choice('core::terms.student', 1) }}} {{{ $student->fullName }}}? All Testing and Training history will be archived.<br><br>Are you sure?">
				{!! Icon::exclamation_sign() !!} Archive
			</a>
		@endif

		{{-- Add Training --}}
		@if(Auth::user()->can('students.manage_trainings') && count($eligibleTrainingIds) > 0)
			<a href="{{ route('students.training.add.fresh', $student->id) }}" class="btn btn-default">{!! Icon::plus_sign() !!} Add Training</a>
		@endif

		@if(Auth::user()->ability(['Admin', 'Staff'], []))
			{{-- Add ADA --}}
			<a href="{{ route('students.add_ada', $student->id) }}" class="btn btn-default">{!! Icon::plus_sign() !!} Add ADA</a>

			{{-- Change Owner --}}
			<a href="{{ route('students.change_single_owner', $student->id) }}" class="btn btn-default">{!! Icon::cog() !!} Change Owner</a>
		@endif
	@endif

	{{-- Archived Student Only --}}
	@if(! $student->isActive)
		{{-- Restore --}}
		@if(Auth::user()->ability(['Admin', 'Staff'], []))
			<a href="{{ route('person.restore', ['students', $student->id]) }}" class="btn btn-warning" data-confirm="Restore {{{ Lang::choice('core::terms.student', 1) }}} {{{ $student->fullName }}}? All Testing and Training history will remain archived. The record will be brought back as active.<br><br>Are you sure?">
				{!! Icon::leaf() !!} Restore
			</a>
		@endif
	@endif

	{{-- Login As --}}
	@if(Auth::user()->can('login_as'))
		<a href="{{ route('students.loginas', $student->id) }}" class="btn btn-default" data-confirm="Are you sure you want to <strong>login as this {{{ Lang::choice('core::terms.student', 1) }}}?</strong>">
			{!! Icon::eye_open() !!} Login As
		</a>
	@endif
</div>