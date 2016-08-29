@extends('core::layouts.default')

@section('content')

	<h1>Welcome</h1>
	<p class="lead">
		You are logged in as {{ $user->username }} <small class="text-muted">({{ Lang::choice('core::terms.student', 1) }})</small>.
		<a href="{{ route('account') }}">View your profile</a>.
	</p>
	<hr>

	<div class="row">
		<div class="col-sm-6">
			<a href="{{ route('students.tests', [$user->userable_id]) }}" class="btn-icon-giant">
				{!! Icon::list_alt() !!}
				Testing
			</a>
		</div>	
		<div class="col-sm-6">
			<a href="{{ route('account') }}" class="btn-icon-giant">
				{!! Icon::user() !!}
				Your Profile
			</a>
		</div>
	</div>

	<hr>
	
	@if(Config::get('core.certification.show'))
		@include('core::students.partials.current_certifications', [
			'certs'  => $certifications
		])
	@endif
@stop