@extends('core::layouts.default')

@section('content')
	{!! Form::model($actor, ['route' => ['actors.archived.update', $actor->id], 'method' => 'POST']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>{{ $actor->fullname }} <small>{{ Lang::choice('core::terms.actor', 1) }}</small></h1>
			</div>
			{!! HTML::backlink('actors.index') !!}
		</div>

		{{-- Warnings --}}
		@include('core::warnings.archived')

		{{-- Identification --}}
		@include('core::person.partials.arch_identification', ['record' => $actor])

		{{-- Contact --}}
		@include('core::person.partials.arch_contact', ['record' => $actor])

		{{-- Test Events --}}
		@include('core::person.partials.arch_test_events', ['events' => $actor->events])

		{{-- Working At --}}
		@include('core::person.partials.arch_working_at', ['record' => $actor])

		{{-- Other Roles --}}
		@include('core::users.other_roles', ['user' => $actor->user, 'ignore' => 'Actor'])

		{{-- Timestamps --}}
		@include('core::partials.timestamps', ['record' => $actor])

		{{-- Comments --}}
		@include('core::partials.comments', ['record' => $actor])
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		@include('core::actors.sidebars.archived')
	</div>
	{!! Form::close() !!}
@stop