@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'actors.store']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Create {{ Lang::choice('core::terms.actor', 1) }}</h1>
			</div>
			{!! HTML::backlink('actors.index') !!}
		</div>

		{{-- Identification --}}
		@include('core::actors.partials.identification')

		{{-- Contact --}}
		@include('core::partials.contact', ['name' => 'actor'])
		
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
		@include('core::actors.sidebars.create')
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	@if( ! App::environment('production'))
		{!! HTML::script('vendor/hdmaster/core/js/utility/populate.js') !!}
	@endif
@stop