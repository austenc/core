@extends('core::layouts.default')

@section('content')

	<h1>Welcome</h1>
	<p class="lead">
		You are logged in as {{ $user->username }} <small class="text-muted">({{ Lang::choice('core::terms.actor', 1) }})</small>
	</p>
	
	<hr>

	<div class="col-sm-6">
		<a href="{{ route('events.index') }}" class="btn-icon-giant">
			{!! Icon::calendar() !!}
			Events
		</a>
	</div>	
	<div class="col-sm-6">
		<a href="{{ route('account') }}" class="btn-icon-giant">
			{!! Icon::user() !!}
			Your Profile
		</a>
	</div>

@stop