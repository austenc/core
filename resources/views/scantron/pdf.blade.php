<!DOCTYPE html>
<html>
<head>
	<title>Scantron Form</title>
	@if(isset($style))
		<style type="text/css">
			{{ $style }}
		</style>
	@else
		{!! HTML::style('css/style.min.css') !!}
	@endif
</head>
<body>
	<div class="scantron-form pdf">
		<div class="row">
			{{-- Last Name --}}
			<div class="col-xs-4 lastname">
				{!! HTML::bubbleBox(15, 'Last Name', ['data' => $person->last, 'table' => true]) !!}
			</div>
			{{-- First Name --}}
			<div class="col-xs-3 firstname">
				{!! HTML::bubbleBox(10, 'First Name', ['data' => $person->first]) !!}
			</div>
			{{-- MI --}}
			<div class="col-xs-1 middlename">
				{!! HTML::bubbleBox(1, 'MI', ['data' => $person->middle]) !!}
			</div>
			{{-- Right Column --}}
			<div class="col-xs-4 right-col">
				<div class="row">
					<div class="col-xs-12">
						<div class="box">
							<div class="title">Important</div>
							<ul>
								<li>MAKE DARK MARKS</li>
								<li>ERASE COMPLETELY TO CHANGE</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-8">
						{!! HTML::bubbleBox(9, 'Identification No.', [
							'data' => $person->id,
							'bubbleRange' => range(0, 9)
						]) !!}
					</div>
					<div class="col-xs-4">
						{!! HTML::bubbleBox(5, 'Special', [
							'data' => '1234',
							'bubbleRange' => range(0, 9)
						]) !!}
					</div>
				</div>
				<div class="row">
					<div class="col-xs-2">
						{!! HTML::bubbleBox(1, 'Sex', [
							'data' => $person->gender,
							'bubbleRange' => ['M', 'F'],
						]) !!}
					</div>
					<div class="col-xs-3">
						{!! HTML::bubbleBox(2, 'Test', [
							'data' => '12',
							'bubbleRange' => range(0, 9)
						]) !!}
					</div>
					<div class="col-xs-7">
						{!! HTML::bubbleBox(9, 'Subjective Totals', [
							'data' => '125857',
							'bubbleRange' => range(0, 9),
							'subtitle' => 
								'<div class="col-xs-4">1</div>
								<div class="col-xs-4">2</div>
								<div class="col-xs-4">3</div>'
						]) !!}
					</div>
				</div>
			</div> <!-- Right Column -->
		</div><!-- First Row -->

{{--
		<div class="answers-contain">
			<div class="row">
				@foreach(range(0, 5) as $i)
					{!! HTML::answerBox(20, $answers, $i) !!}
				@endforeach
			</div><!-- Second Row -->
			<div class="row">
				@foreach(range(0, 5) as $i)
					{!! HTML::answerBox(30, $answers, $i, 120) !!}
				@endforeach
			</div><!-- Third Row -->
		</div>
--}}

	</div><!-- Scantron Form -->
</body>
</html>