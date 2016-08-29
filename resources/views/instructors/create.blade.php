@extends('core::layouts.default')

@section('content')
	<div class="row">
		{!! Form::open(['route' => 'instructors.store']) !!}
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Create New {{ Lang::choice('core::terms.instructor', 1) }}</h1>
				</div>
				{!! HTML::backlink('instructors.index') !!}
			</div>

			{{-- Identification --}}
			@include('core::instructors.partials.identification')

			{{-- Contact --}}
			@include('core::partials.contact', ['name' => 'instructor'])

			{{-- Address --}}
			@include('core::partials.address')

			{{-- Disciplines --}}
			@include('core::instructors.partials.discipline_select', ['disciplines' => $disciplines])
			
			{{-- Comments --}}
			@include('core::partials.comments')
		</div>

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			@include('core::instructors.sidebars.create')
		</div>
		{!! Form::close() !!}
	</div>
@stop

@section('scripts')
	{!! HTML::script('vendor/hdmaster/core/js/instructors/create.js') !!}
@stop