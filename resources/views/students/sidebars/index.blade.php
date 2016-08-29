@if(( ! Session::has('discipline.program') && Auth::user()->can('students.create')) || (Session::has('discipline.program') && Session::get('discipline.program.training_approved') === true))
	<a href="{{ route('students.create') }}" class="btn btn-block btn-success">{!! Icon::plus_sign() !!} New {{ Lang::choice('core::terms.student', 1) }}</a>
	<hr>
@endif

@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
	@include('core::staff.sidebars.filter')
@elseif(Auth::user()->ability(['Facility', 'Instructor'], []))
	@include('core::instructors.sidebars.filter')
@endif