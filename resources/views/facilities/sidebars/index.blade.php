
@if(Auth::user()->can('facilities.create'))
	<a href="{{ route('facilities.create') }}" class="btn btn-block btn-success">
		{!! Icon::plus_sign() !!} New {{ Lang::choice('core::terms.facility', 1) }}
	</a>
@endif

@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<hr>
	@include('core::staff.sidebars.filter')
@endif