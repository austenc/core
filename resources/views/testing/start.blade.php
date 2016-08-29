@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => ['testing.initialize', $attempt->id]]) !!}
	<h1>Verify Your Information</h1>
	<div class="alert alert-warning">
		Please verify that you are the person whose information is below <strong>AND</strong> that it is correct before beginning the test.
	</div>
	<div class="well">
		<div class="form-group">
			<address>
				<strong>{{ $student->fullName }}</strong><br>
				{{ $student->address }}<br>
				{{ $student->city }}, {{ $student->state }} {{ $student->zip }}<br>
			</address>
		</div>
		<div class="form-group">
			{!! Form::label('birthdate', 'Birthdate') !!}
			<p class="form-control-static">
				{{ $student->birthdate }}
			</p>
		</div>

		{{-- Address --}}
		@if( ! empty($student->address))
			<div class="form-group">
				{!! Form::label('email', 'Email Address') !!}
				<p class="form-control-static">
					{{ $student->user->email }}
				</p>
			</div>
		@else
			<div class="form-group">No Address Listed</div>
		@endif

		{{-- Start Code --}}
		<div class="form-group">
			{!! Form::label('startcode', 'Start Code') !!} @include('core::partials.required')
			{!! Form::text('startcode', null, ['autocomplete' => 'off', 'placeholder' => 'Enter test start code', 'id' => 'startcode']) !!}
			<p class="help-block">This will be given to you by the test's {{ Lang::choice('core::terms.observer', 1) }}.</p>
		</div>
	</div>

	{!! Form::hidden('attempt_id', $attempt->id) !!}

	<a href="{{ route('account') }}" class="btn btn-primary pull-left">{!! Icon::arrow_left() !!} Go Back, Edit Information</a>
	<button type="submit" class="pull-right btn btn-success">{!! Icon::ok() !!} Information Correct, Begin Test</button>
{!! Form::close() !!}
@stop