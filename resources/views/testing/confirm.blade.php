@extends('core::layouts.default')

@section('content')

@include('core::testing.partials.confirm', [
	'f'     => $f,
	's'     => $s,
	'event' => $event
])

@stop