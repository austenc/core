@extends('core::layouts.default')

@section('content')
	{!! Form::model($observer, ['route' => ['observers.archived.update', $observer->id], 'method' => 'POST']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>{{ $observer->fullname }} <small>{{ Lang::choice('core::terms.observer', 1) }}</small></h1>
			</div>
			{!! HTML::backlink('observers.index') !!}
		</div>

		{{-- Warnings --}}
		@include('core::warnings.archived')

		{{-- Identification --}}
		@include('core::person.partials.arch_identification', ['record' => $observer])

		{{-- Contact --}}
		@include('core::person.partials.arch_contact', ['record' => $observer])

		{{-- Test Events --}}
		@include('core::person.partials.arch_test_events', ['events' => $observer->events])

		{{-- Working At --}}
		@include('core::person.partials.arch_working_at', ['record' => $observer])

		{{-- Other Roles --}}
		@include('core::users.other_roles', ['user' => $observer->user, 'ignore' => 'Observer'])

		{{-- Timestamps --}}
		@include('core::partials.timestamps', ['record' => $observer])

		{{-- Comments --}}
		@include('core::partials.comments', ['record' => $observer])
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::observers.sidebars.archived')
	</div>
	{!! Form::close() !!}
@stop