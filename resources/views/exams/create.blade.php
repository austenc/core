@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'exams.store']) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>New Exam</h1>
				</div>
				{!! HTML::backlink('exams.index') !!}
			</div>

			<div class="alert alert-info">
				{!! Icon::info_sign() !!} <strong>Requirements</strong> can be set after successful create
			</div>

			<h3>Basic Info</h3>
			<div class="well">
				<div class="form-group row">
					<div class="col-md-8">
						{!! Form::label('name', 'Name') !!} @include('core::partials.required')
						{!! Form::text('name') !!}
						<span class="text-danger">{{ $errors->first('name') }}</span>
					</div>

					<div class="col-md-4">
						{!! Form::label('abbrev', 'Abbrev') !!} @include('core::partials.required')
						{!! Form::text('abbrev') !!}
						<span class="text-danger">{{ $errors->first('abbrev') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('discipline_id', 'Discipline') !!} @include('core::partials.required')
						{!! Form::select('discipline_id', [0 => 'Select Discipline'] + $disciplines->lists('name', 'id')->all()) !!}
						<span class="text-danger">{{ $errors->first('discipline_id') }}</span>
					</div>
				</div>

				<hr>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('max_attempts', 'Max Attempts') !!}
						{!! Form::text('max_attempts') !!}
						<span class="text-danger">{{ $errors->first('max_attempts') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('price', 'Price') !!}
					    <div class="input-group">
					        <div class="input-group-addon">$</div>
					        {!! Form::text('price', '', ['class' => 'form-control']) !!}
					    </div>
						<span class="text-danger">{{ $errors->first('price') }}</span>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-md-12">
						{!! Form::label('has_paper', 'Has Paper?') !!}
						{!! Form::select('has_paper', [0 => 'No', 1 => 'Yes']) !!}
						<span class="text-danger">{{ $errors->first('has_paper') }}</span>
					</div>
				</div>
			</div>

			<h3>Notes</h3>
			<div class="well">
				<div class="form-group">
					<textarea name="comments" id="comments" class="form-control">@if(Input::old('comments')){{ Input::old('comments') }}@endif</textarea>
				</div>
			</div>
		</div>

		{{-- Sidebar  --}}
		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				{!! Button::success(Icon::plus_sign().' Create')->submit() !!}
			</div>
		</div>
	</div>
	{!! Form::close() !!}
@stop