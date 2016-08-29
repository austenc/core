@extends('core::layouts.default')

@section('content')
	{!! Form::model($proctor, ['route' => ['proctors.archived.update', $proctor->id], 'method' => 'POST']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>{{ $proctor->fullname }} <small>{{ Lang::choice('core::terms.proctor', 1) }}</small></h1>
			</div>
			{!! HTML::backlink('proctors.index') !!}
		</div>

		{{-- Warnings --}}
		@include('core::warnings.archived')

		{{-- Identification --}}
		@include('core::person.partials.arch_identification', ['record' => $proctor])

		{{-- Contact --}}
		@include('core::person.partials.arch_contact', ['record' => $proctor])
			
		{{-- Test Events --}}
		@include('core::person.partials.arch_test_events', ['events' => $proctor->events])

		{{-- Working At --}}
		@include('core::person.partials.arch_working_at', ['record' => $proctor])

		{{-- Other Roles --}}
		@include('core::users.other_roles', ['user' => $proctor->user, 'ignore' => 'Proctor'])

		{{-- Timestamps --}}
		@include('core::partials.timestamps', ['record' => $proctor])

		{{-- Comments --}}
		@include('core::partials.comments', ['record' => $proctor])
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::proctors.sidebars.archived')
	</div>
	{!! Form::close() !!}
@stop