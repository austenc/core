<a href="{{ route('proctors.create') }}" class="btn btn-block btn-success">{!! Icon::plus_sign() !!} New {{ Lang::choice('core::terms.proctor', 1) }}</a>

@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<hr>
	@include('core::staff.sidebars.filter')
@endif