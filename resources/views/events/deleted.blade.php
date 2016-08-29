@extends('core::layouts.default')

@section('content')
	<div class="row">
		<div class="col-xs-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Event #{{ $event->id }} <small class="text-danger">Deleted</small></h1>
				</div>
				{!! HTML::backlink('events.index') !!}
			</div>

			<div class="well">
				<div class="form-group">
					{!! Form::label('test_date', 'Test Date') !!}
					{!! Form::text('test_date', $event->test_date, ['disabled']) !!}
				</div>

				<div class="form-group">
					{!! Form::label('test_date', 'Test Site') !!}
					{!! Form::text('test_date', $event->facility->name, ['disabled']) !!}
				</div>

				<div class="form-group">
					{!! Form::label('observer', Lang::choice('core::terms.observer', 1)) !!}
					{!! Form::text('test_date', $event->observer->fullname, ['disabled']) !!}
				</div>

				<hr>
				
				<div class="form-group">
					{!! Form::label('started', 'Started') !!}
					{!! Form::text('started', $event->start_time, ['disabled']) !!}
				</div>

				<div class="form-group">
					{!! Form::label('ended', 'Ended') !!}
					{!! Form::text('ended', $event->ended, ['disabled']) !!}
				</div>

				<hr>

				<div class="form-group">
					{!! Form::label('created', 'Created At') !!}
					{!! Form::text('created', $event->created, ['disabled']) !!}
				</div>
				
				<div class="form-group">
					{!! Form::label('deleted', 'Deleted at') !!}
					{!! Form::text('deleted', $event->deleted, ['disabled']) !!}
				</div>

				@if( ! empty($events->comments)) 
					<hr>

					<div class="form-group">
						{!! Form::label('comments', 'Comments') !!}
						{!! Form::textarea('comments', $event->comments, ['disabled']) !!}
					</div>
				@endif
			</div>
		</div>

		<div class="col-md-3"></div>
	</div>
@stop