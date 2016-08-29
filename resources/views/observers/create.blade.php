@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'observers.store']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Create {{ Lang::choice('core::terms.observer', 1) }}</h1>
			</div>
			{!! HTML::backlink('observers.index') !!}
		</div>

		{{-- Identification --}}
		@include('core::observers.partials.identification')
	
		{{-- Payable Rate --}}
		@include('core::observers.partials.payable_rate', ['payableRates' => $payableRates])

		{{-- Contact --}}
		@include('core::partials.contact', ['name' => 'observer'])
		
		{{-- Address --}}
		@include('core::partials.address')

		{{-- Disciplines --}}
		@include('core::partials.discipline_select', ['disciplines' => $disciplines])

		{{-- Test Sites --}}
		@include('core::testteam.partials.testsites')

		{{-- Test Sites --}}
		@include('core::partials.new_password')

		{{-- Comments --}}
		@include('core::partials.comments')
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::observers.sidebars.create')
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	@if( ! App::environment('production'))
		{!! HTML::script('vendor/hdmaster/core/js/utility/populate.js') !!}
	@endif
@stop