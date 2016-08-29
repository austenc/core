@extends('core::layouts.default')

@section('content')
<div class="test-results-form">
	<div class="letter-header">
		<div class="pull-left">
			<a href="javascript:history.back()" class="btn btn-default">
				{!! Icon::arrow_left() !!} Back
			</a>
		</div>
		<div class="pull-right">
			<a href="javascript:window.print();" class="btn btn-primary">
				<span class="glyphicon glyphicon-print"></span> Print
			</a>
		</div>

		<p class="text-center text-uppercase center-block">
			<strong>Headmaster, LLP</strong><br>
			P.O. Box 6609, Helena, MT 59604-6609 <br>
			800-393-8664 &mdash; Fax: 406-442-3357
			www.hdmaster.com
		</p>
	
		<h4 class="text-center text-uppercase">{{ Config::get('core.client.name') }} {{ $exam }} Exam Results Report</h4>
	
		<div class="to-address text-uppercase">
			{{ $student->addressName }} <br>
			{{ $student->address }} <br>
			{{ $student->city }}, {{ $student->state }} {{ $student->zip }} <br>
			<strong>Important Test Results</strong>
		</div>
	</div> {{-- .letter-header --}}
	
	<div class="row">
		<div class="col-xs-12">
			<p>TEST DATE: {{ $event->prettyDate }}</p>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12">
			<p>
				Dear {{ $student->first }}, <br>
				You have 
				@if($knowledge)
					<strong>{{ $knowledge->status }}</strong> the <strong>Knowledge</strong> portion
				@endif 
				@if($knowledge && $skill) and @endif
				@if($skill)
					<strong>{{ $skill->status }}</strong> the <strong>Manual Skill</strong> portion
				@endif
				of the {{ $exam }} exam.





				You must have an overall score 
				@if($knowledge)
					of <strong>{{ $kMin }}%</strong> or better on the knowledge test
				@endif
				@if($knowledge && $skill) and @endif
				@if($skill)
					<strong>{{ $sMin }}%</strong> or better on each skill task without missing any "Key Steps" to pass the skills test. 
				@endif
			</p>

			<p>
				@if($knowledge)
					Your overall knowledge test score is {{ $knowledge->score }}%.
				@endif
				 Any weaknesses indicated in your knowledge and skill test results are listed below:
			</p>
		</div>
	</div>

	<div class="row">
		{{--  Is there a knowledge attempt + answers? --}}
		@if($knowledge && $knowledge->correct_by_subject)
		<div class="col-xs-6">
			<div class="panel panel-default panel-small">
				<div class="panel-heading">Knowledge Exam Results By Subject Area</div>
				<div class="panel-body no-top-padding">
					@include('core::testing.partials.correct_by_subject', [
						'attempt'   => $knowledge, 
						'condensed' => true,
						$totals,
						$subjects
					])
				</div>
			</div>
		</div>
		@endif

		<div class="col-xs-6">
			<div class="panel panel-default panel-small">
				<div class="panel-heading">Skill Exam Incomplete Steps</div>
				<div class="panel-body">
				@if($steps)
					<ul class="step-results-list">
						@foreach($steps as $data)
							<li>
								<strong>{{ $data['task'] }}</strong>

								@if(is_array($data['steps']) && ! empty($data['steps']))
									<ul>
									@foreach($data['steps'] as $step)
										<li>{{ str_limit(BBCode::parseInput($step, 0, 'paper'), 40) }}</li>
									@endforeach
									</ul>
								@endif
							</li>
						@endforeach
					</ul>
				@endif
				</div>
			</div>			
		</div>
	</div>

	@if($tasks)
		<div class="row">
			<div class="col-xs-12">
				<small>
					<strong>Manual Skill Task(s) Failed:</strong> {!! implode(', ', $tasks->lists('title')->all()) !!}
				</small>
			</div>
		</div>
	@endif

	@if( ! empty($vocab))
		<div class="row">
			<div class="col-xs-12">
				<small><strong>Vocabulary words to study:</strong> {!! implode(', ', $vocab) !!}</small>
			</div>
		</div>
	@endif

</div>

@stop