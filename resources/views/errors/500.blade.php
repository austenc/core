@extends('core::layouts.default')

@section('content')
	<h1 class="text-warning">{!! Icon::exclamation_sign() !!} Error 500 - Internal Server Error</h1>
	<hr>
	<p class="lead">There has been an error. We have been notified and are working to fix it. Sorry for any inconvenience.</p>
@stop