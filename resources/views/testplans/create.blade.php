@extends('core::layouts.default')

@section('content')
	{!! Form::open(['route' => 'testplans.store', 'class' => 'form-horizontal']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Create Testplan</h1>
			</div>
			{!! HTML::backlink('testplans.index') !!}
		</div>

		<h3>Plan Information</h3>
		<div class="well">
			<div class="row form-group">
				<div class="col-md-12">
					{!! Form::label('exam', 'Exam') !!}
					{!! Form::text('exam', $exam->name, ['disabled']) !!}
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-12">
					{!! Form::label('name', 'Name') !!} @include('core::partials.required')
					{!! Form::text('name', null, ['autofocus' => 'autofocus']) !!}
					<span class="text-danger">{{ $errors->first('name') }}</span>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-12">
					{!! Form::label('timelimit', 'Time Limit') !!} @include('core::partials.required')
					{!! Form::text('timelimit', Config::get('core.testplans.timelimit')) !!}
					<span class="text-danger">{{ $errors->first('timelimit') }}</span>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-12">
					{!! Form::label('minimum_score', 'Minimum Score') !!} @include('core::partials.required')
					{!! Form::text('minimum_score', Config::get('core.testplans.minimum_score')) !!}
					<span class="text-danger">{{ $errors->first('minimum_score') }}</span>
				</div>
			</div>
		</div>

		<h3>Test Parameters</h3>
		<div class="well">
			<div class="form-group">
				{!! Form::label('readinglevel', 'Reading Level', ['class' => 'col-sm-3']) !!}
				<div class="col-sm-4">{!! Form::text('readinglevel', Config::get('core.testplans.readinglevel'), ['placeholder' => 'Minimum']) !!}</div>
				<div class="col-sm-4">{!! Form::text('readinglevel_max', Config::get('core.testplans.readinglevel_max'), ['placeholder' => 'Maximum']) !!}</div>
			</div>
			<div class="form-group">
				{!! Form::label('reliability', 'Reliability', ['class' => 'col-sm-3']) !!}
				<div class="col-sm-4">{!! Form::text('reliability', Config::get('core.testplans.reliability'), ['placeholder' => 'Minimum']) !!}</div>
				<div class="col-sm-4">{!! Form::text('reliability_max', Config::get('core.testplans.reliability_max'), ['placeholder' => 'Maximum']) !!}</div>
			</div>
			<div class="form-group">
				{!! Form::label('pvalue', 'P-Value', ['class' => 'col-sm-3']) !!}
				<div class="col-sm-4">{!! Form::text('pvalue', Config::get('core.testplans.pvalue'), ['placeholder' => 'Minimum']) !!}</div>
				<div class="col-sm-4">{!! Form::text('pvalue_max', Config::get('core.testplans.pvalue_max'), ['placeholder' => 'Maximum']) !!}</div>
			</div>
			<div class="form-group">
				{!! Form::label('difficulty', 'Difficulty', ['class' => 'col-sm-3']) !!}
				<div class="col-sm-4">{!! Form::text('difficulty', Config::get('core.testplans.difficulty'), ['placeholder' => 'Minimum']) !!}</div>
				<div class="col-sm-4">{!! Form::text('difficulty_max', Config::get('core.testplans.difficulty_max'), ['placeholder' => 'Maximum']) !!}</div>
			</div>
			<div class="form-group">
				{!! Form::label('discrimination', 'Discrimination', ['class' => 'col-sm-3']) !!}
				<div class="col-sm-4">{!! Form::text('discrimination', Config::get('core.testplans.discrimination'), ['placeholder' => 'Minimum']) !!}</div>
				<div class="col-sm-4">{!! Form::text('discrimination_max', Config::get('core.testplans.discrimination_max'), ['placeholder' => 'Maximum']) !!}</div>
			</div>
			<div class="form-group">
				{!! Form::label('guessing', 'Guessing', ['class' => 'col-sm-3']) !!}
				<div class="col-sm-4">{!! Form::text('guessing', Config::get('core.testplans.guessing'), ['placeholder' => 'Minimum']) !!}</div>
				<div class="col-sm-4">{!! Form::text('guessing_max', Config::get('core.testplans.guessing_max'), ['placeholder' => 'Maximum']) !!}</div>
			</div>
			<div class="form-group">
				{!! Form::label('cutscore', 'Cut Score', ['class' => 'col-sm-3']) !!}
				<div class="col-sm-4">{!! Form::text('cutscore', Config::get('core.testplans.cutscore'), ['placeholder' => 'Minimum']) !!}</div>
				<div class="col-sm-4">{!! Form::text('cutscore_max', Config::get('core.testplans.cutscore_max'), ['placeholder' => 'Maximum']) !!}</div>
			</div>
			<div class="form-group">
				{!! Form::label('target_theta', 'Target Theta', ['class' => 'col-sm-3']) !!}
				<div class="col-sm-4">{!! Form::text('target_theta', Config::get('core.testplans.target_theta')) !!}</div>
			</div>
			<div class="form-group">
				{!! Form::label('pbs', 'Minimum PBS', ['class' => 'col-sm-3']) !!}
				<div class="col-sm-4">{!! Form::text('pbs', Config::get('core.testplans.pbs')) !!}</div>
			</div>
		</div>

		<h3>Items by Subject <small>@include('core::partials.required')</small></h3>
		<div class="well">
			<span class="text-danger">{{ $errors->first('subjects') }}</span>
			<table class="table results-table table-striped">
				<thead>
					<tr>
						<th>Subject</th>
						<th>Total # Subject Items</th>
						<th># of Items</th>
					</tr>
				</thead>
				<tbody>
				@foreach ($subjects as $subject)
					{{-- Only show subjects that aren't reporting as other ones --}}
					@if( ! $subject->report_as)
						<tr>
							<td>{{ $subject->name }}</td>
							<td>{{ $subject->itemPool->count() }}</td>
							<td>{!! Form::text('subjects['.$subject->id.']', null, ['placeholder' => '# of items']) !!}</td>
						</tr>
					@endif
				@endforeach
				</tbody>
			</table>
		</div>

		<h3>Item Pool Parameters</h3>
		<div class="well">
			<div class="row form-group">
				<div class="col-md-6">
					{!! Form::label('item_pvalue', 'Item P-Value') !!}
					{!! Form::text('item_pvalue', Config::get('core.testplans.item_pvalue')) !!}
				</div>
				<div class="col-md-6">
					{!! Form::label('item_pvalue_max', 'Item P-Value Max') !!}
					{!! Form::text('item_pvalue_max', Config::get('core.testplans.item_pvalue_max')) !!}
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-12">
					{!! Form::label('max_attempts', 'Max Generation Attempts') !!} @include('core::partials.required')
					{!! Form::text('max_attempts', Config::get('core.testplans.max_attempts')) !!}
					<span class="text-danger">{{ $errors->first('max_attempts') }}</span>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-12">
					{!! Form::label('max_pvalue_attempts', 'Max P-Value Attempts') !!}
					{!! Form::text('max_pvalue_attempts', Config::get('core.testplans.max_pvalue_attempts')) !!}
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-12">
					{!! Form::label('ignore_stats', 'Ignore Item Stats for #') !!}
					{!! Form::text('ignore_stats') !!}
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-12">
					{!! Form::label('comments', 'Comments') !!}
					{!! Form::textarea('comments') !!}
				</div>
			</div>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			{!! Button::success(Icon::plus_sign().' Save New Testplan')->submit() !!}
		</div>
	</div>
	
	{!! Form::hidden('client', Config::get('core.client.abbrev')) !!}
	{!! Form::hidden('exam_id', $exam->id) !!}

	{!! Form::close() !!}
@stop