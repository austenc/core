@extends('core::layouts.full')

@section('content')
	<div class="well">
		<h1 class="text-danger">{!! Icon::exclamation_sign() !!} You are not authorized to access this page</h1>
		@if(Auth::check())
			<p class="lead">Please login below to continue.</p>
		@endif
	</div>
	@if( ! Auth::check())
	<div class="well">
		@include('core::partials.login')
	</div>
	@endif
@stop