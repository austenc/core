@extends('core::layouts.default')

@section('content')
	<div class="row">
		{!! Form::open(['route' => 'discipline.store']) !!}
		<div class="col-md-9">
			<div class="row">
                <div class="col-xs-8">
                    <h1>New Discipline</h1>
                </div>
                {!! HTML::backlink('discipline.index') !!}
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
						{!! Form::label('abbrev', 'Abbreviation') !!} @include('core::partials.required')
						{!! Form::text('abbrev') !!}
						<span class="text-danger">{{ $errors->first('abbrev') }}</span>
					</div>
				</div>
	      	</div>

	      	<h3>Notes</h3>
			<div class="well">
				<div class="form-group">
					<textarea name="description" id="description" class="form-control">@if(Input::old('description')){{ Input::old('description') }}@endif</textarea>
				</div>
			</div>
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			@include('core::discipline.sidebars.create')
		</div>
		{!! Form::close() !!}
	</div>
@stop