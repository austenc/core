@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-md-9">
			<h1>Welcome</h1>
			<div class="well">
				<p class="lead">
					You are logged in as {{ $user->username }} <small class="text-muted">({{ Lang::choice('core::terms.instructor', 1) }})</small>.
					
					@if(Session::has('discipline.program') && Session::get('discipline.program.training_approved') !== true)
						<span class="text-danger">
						Current {{ Lang::choice('core::terms.facility_training', 1) }} is <strong>not approved for Training</strong>. {{ Lang::choice('core::terms.student', 1) }} creation is disabled.
						</span>
					@endif
				</p>
				<p class="lead">
					Viewing {{ Session::get('discipline.name') }} <small class="text-muted">({{ Session::get('discipline.abbrev') }})</small> records at {{ Session::get('discipline.program.name') }} <small class="text-muted">({{ Session::get('discipline.program.license') }})</small>.
				</p>
			</div>
		</div>

		<div class="col-md-3">
			<h3>Quick Links</h3>
			<div class="list-group">
				<a href="{{ route('notifications') }}" class="list-group-item">
					{!! Icon::inbox() !!} Inbox
				</a>

				<a href="{{ route('account') }}" class="list-group-item">
					{!! Icon::pencil() !!} Your Profile
				</a>

				<a href="{{ route('students.index') }}" class="list-group-item">
					{!! Icon::user() !!} Manage Students
				</a>

				@if($user->userable->activeFacilities()->count() > 1)
				<a href="{{ route('instructors.login.reset') }}" class="list-group-item">
					{!! Icon::eye_open() !!} Reset Login
				</a>
				@endif
			</div>
		</div>
	</div>
@stop