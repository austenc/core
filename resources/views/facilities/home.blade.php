@extends('core::layouts.default')

@section('content')
	<h1>Welcome</h1>
	<div class="well">
		<p class="lead">
			You are logged in as {{ $user->username }} <small class="text-muted">({{ Lang::choice('core::terms.facility', 1) }})</small> 
		</p>
		<p class="lead">Viewing {{ Session::get('discipline.name') }} <small class="text-muted">({{ Session::get('discipline.abbrev') }}) records.</small>
		</p>
	</div>
@stop