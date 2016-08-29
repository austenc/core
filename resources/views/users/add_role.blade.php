@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => 'users.store_role', 'method' => 'post']) !!}
	<div class="col-md-9 sidebar">
		<div class="row">
			<div class="col-xs-8">
				<h1>Add Role to <em>{{ $user->username }}</em></h1>
			</div>
		</div>
		<div class="well">
			<div class="form-group">
				{!! Form::label('new_role', 'Select Role') !!}
				{!! Form::select('new_role', $roles) !!}
				{!! Form::hidden('user_id', $user->id) !!}
			</div>
		</div>
	</div>

	<div class="col-md-3 sidebar">
		<button type="submit" class="btn btn-success">{!! Icon::plus_sign() !!} Add Role</button>
	</div>
{!! Form::close() !!}
@stop