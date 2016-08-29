@if(Auth::user()->can('instructors.create'))
	<a href="{{ route('instructors.create') }}" class="btn btn-block btn-success">
		{!! Icon::plus_sign() !!} New {{ Lang::choice('core::terms.instructor', 1) }}
	</a>
@endif

@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<hr>
	@include('core::staff.sidebars.filter')
@endif