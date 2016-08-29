@extends('core::layouts.full')

@section('content')
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="row">
				<div class="col-xs-8">
					<h1>Test Event #{{ $event->id }}</h1>
				</div>
				<div class="col-xs-4 back-link">
					<a href="javascript:history.back();" class="pull-right btn btn-link">
						{!! Icon::arrow_left() !!}
						Back
					</a>
				</div>
			</div>

			<h3>Event Info</h3>
			<div class="well">
				<div class="row">
					<div class="col-md-6">
						{!! Form::label('test_date', 'Test Date') !!}
						<div class="controls">
					  		<p class="form-control-static">{{ $event->test_date }}</p>
						</div>
					</div>
					<div class="col-md-6">
						{!! Form::label('start_time', 'Start Time') !!}
						<div class="controls">
					  		<p class="form-control-static">{{ $event->start_time }}</p>
						</div>
					</div>
				</div>
			</div>

			<h3>Test Site</h3>
			<div class="well">
				<div class="row">
					<div class="col-xs-12">						
						<address>
							<strong>{{ $event->facility->name }}</strong> <br>
							{{ $event->facility->address }} <br>
							{{ $event->facility->city }}, {{ $event->facility->state }} {{ $event->facility->zip }}
						</address>
					</div>
				</div>
			</div>

			<h3>Tests Offered</h3>
			<div class="well clearfix">
				<div class="controls">
					@foreach($event->skills as $skill)
						<p class="form-control-static">{{ $skill->pretty_name }}</p>
					@endforeach

					@foreach($event->exams as $exam)
						<p class="form-control-static">{{ $exam->pretty_name }}</p>
					@endforeach
				</div>
			</div>

		</div>
	</div>


@stop