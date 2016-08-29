@extends('core::layouts.modal')

@section('header')
	<h3 class="text-center">Time is Almost Up!</h3>
@stop

@section('body')
	<span class="hide time-limit">{{ Config::get('session.lifetime') }}</span>
	<span class="hide logout-route">{{ route('logout', ['timeout' => true]) }}</span>

	<p class="text-center">
		You've been inactive for a while. Click the button below to remain logged in.
	</p>
	<p class="text-center lead text-muted">
		Time Remaining: <span class="timer"></span>
	</p>
@stop

@section('footer')
	<a class="btn btn-success" data-dismiss="modal">Keep Me Logged In!</a>
@stop