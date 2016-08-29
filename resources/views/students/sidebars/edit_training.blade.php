<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
	@if($training->archived)
		@if(Auth::user()->ability(['Admin', 'Staff'], []))
			<a href="{{ route('students.training.restore', [$training->id]) }}" class="btn btn-warning" data-confirm="Are you sure you want to restore this training?">{!! Icon::leaf() !!} Restore</a>
		@endif
	@else
		{{-- Update --}}
		@if(Auth::user()->can('students.manage_trainings'))
			{!! Button::success(Icon::refresh().' Update')->submit() !!}
		@endif

		{{-- Archive --}}
		@if(Auth::user()->ability(['Admin', 'Staff'], []))
			<a href="{{ route('students.training.archive', [$training->student_id, $training->id]) }}" class="btn btn-danger" data-confirm="Are you sure you want to archive this training?">{!! Icon::exclamation_sign() !!} Archive</a>
		@endif
	@endif
</div>