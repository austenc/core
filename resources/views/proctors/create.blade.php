@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'proctors.store']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Create {{ Lang::choice('core::terms.proctor', 1) }}</h1>
			</div>
			{!! HTML::backlink('proctors.index') !!}
		</div>

		{{-- Identification --}}
		@include('core::proctors.partials.identification')

		{{-- Contact --}}
		@include('core::partials.contact', ['name' => 'proctor'])
		
		{{-- Address --}}
		@include('core::partials.address')

		{{-- Disciplines --}}
		@include('core::partials.discipline_select', ['disciplines' => $disciplines])

		{{-- Test Sites --}}
		@include('core::testteam.partials.testsites')

		{{-- Comments --}}
		@include('core::partials.comments')
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::proctors.sidebars.create')
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	@if( ! App::environment('production'))
		{!! HTML::script('vendor/hdmaster/core/js/utility/populate.js') !!}
	@endif
@stop