@extends('core::layouts.default')

@section('content')
	<div class="row">
		{!! Form::model($facility, ['route' => ['account.update', 'facility', $facility->id], 'method' => 'PUT']) !!}
		<div class="col-md-9">
			<h1>Your Account</h1>

			<h3>Identification</h3>
			<div class="well">
				<div class="form-group">
					{!! Form::label('name_readonly', 'Name') !!}
					{!! Form::text('name_readonly', $facility->name, ['disabled']) !!}
				</div>
				<div class="form-group">
					{!! Form::label('don', 'Director of Nursing') !!}
					{!! Form::text('don', $facility->don, ['disabled']) !!}
				</div>
				<div class="form-group">
					{!! Form::label('admin', 'Administrator') !!}
					{!! Form::text('admin', $facility->administrator, ['disabled']) !!}
				</div>
			</div>

			<h3>Login</h3>
			<div class="well">
				<div class="form-group">
					{!! Form::label('discipline', 'Discipline') !!}
					{!! Form::text('discipline', Session::get('discipline.name'), ['disabled']) !!}
				</div>
				<div class="form-group">
					{!! Form::label('license', 'License') !!}
					{!! Form::text('license', Session::get('discipline.license'), ['disabled']) !!}
				</div>

				<div class="form-group">
					{!! Form::label('username', 'Username') !!}
					{!! Form::text('username', $facility->user->username) !!}
					<span class="text-danger">{{ $errors->first('username') }}</span>
				</div>

				<hr>

				{{-- Password Fields --}}
				@include('core::partials.change_password')
			</div>

			<h3>Contact</h3>
			<div class="well">
				<div class="form-group">
					{!! Form::label('email', 'Email') !!}
					{!! Form::text('email', $facility->user->email) !!}
					<span class="text-danger">{{ $errors->first('email') }}</span>
				</div>
				<div class="form-group">
					{!! Form::label('phone', 'Phone') !!}
					{!! Form::text('phone') !!}
					<span class="text-danger">{{ $errors->first('phone') }}</span>
				</div>
				<div class="form-group">
					{!! Form::label('fax', 'Fax') !!}
					{!! Form::text('fax') !!}
					<span class="text-danger">{{ $errors->first('fax') }}</span>
				</div>
	
				<hr>

				<div class="form-group">
					{!! Form::label('address', 'Address') !!}
					{!! Form::text('address') !!}
					<span class="text-danger">{{ $errors->first('address') }}</span>
				</div>
				<div class="form-group row">
					<div class="col-md-6">
						{!! Form::label('city', 'City') !!}
						{!! Form::text('city') !!}
						<span class="text-danger">{{ $errors->first('city') }}</span>
					</div>
					<div class="col-md-2">
						{!! Form::label('state', 'State') !!}
						{!! Form::text('state') !!}
						<span class="text-danger">{{ $errors->first('state') }}</span>
					</div>
					<div class="col-md-4">
						{!! Form::label('zip', 'Zipcode') !!}
						{!! Form::text('zip') !!}
						<span class="text-danger">{{ $errors->first('zip') }}</span>
					</div>
				</div>
			</div>

			@if(in_array('Testing', $facility->actions))
			<h3>Test Site</h3>
			<div class="well">
				<div class="form-group">
					{!! Form::label('max_seats', 'Max Seats') !!} <small class="text-muted">Max # of seats per Test</small>
					{!! Form::text('max_seats') !!}
					<span class="text-danger">{{ $errors->first('max_seats') }}</span>
				</div>
			</div>
			@endif

			@if(in_array('Training', $facility->actions))
			<h3>{{ Lang::choice('core::terms.instructor', 2) }}</h3>
			<div class="well table-responsive">
				<table class="table table-striped ins-table">
					<thead>
						<tr>
							<th>Name</th>
							<th>License</th>
							@if(Auth::user()->can('facilities.person.toggle'))
								<th></th>
							@endif
						</tr>
					</thead>
					<tbody>
						@foreach ($facility->activeInstructors as $i)
							{{-- Only show active instructors --}}
							{{-- Filter instructors to current discipline --}}
							@if($i->pivot->active && $i->hasDisciplineFacility(Session::get('discipline.id'), Auth::user()->userable->id))
							<tr>
								<td>{{ $i->commaName }}</td>
								<td class="monospace">{{ $i->pivot->tm_license }}</td>
								<td>
									<div class="btn-group pull-right">

									</div>
								</td>
							</tr>
							@endif
						@endforeach
					</tbody>
				</table>
			</div>
			@endif
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			@include('core::facilities.sidebars.account')
		</div>

		{!! Form::hidden('name', $facility->name) !!}		
		{!! Form::hidden('license', $facility->license) !!}
		{!! Form::hidden('discipline_id', Session::get('discipline.name')) !!}
	{!! Form::close() !!}
@stop