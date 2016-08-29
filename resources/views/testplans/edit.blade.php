@extends('core::layouts.default')

@section('content')
{!! Form::model($plan, ['route' => ['testplans.update', $plan->id], 'method' => 'PUT', 'class' => 'form-horizontal']) !!}
<div class="col-md-9">
	<div class="row">
		<div class="col-xs-8">
			<h1>Edit Testplan</h1>
		</div>
		{!! HTML::backlink('testplans.index') !!}
	</div>

	<h3>Plan Information</h3>
	<div class="well">
		<div class="form-group">
			<div class="col-md-12">
				{!! Form::label('name', 'Name') !!} @include('core::partials.required')
				{!! Form::text('name') !!}
				<span class="text-danger">{{ $errors->first('name') }}</span>
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-12">
				{!! Form::label('timelimit', 'Time Limit') !!} @include('core::partials.required')
				{!! Form::text('timelimit') !!}
				<span class="text-danger">{{ $errors->first('timelimit') }}</span>
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-12">
				{!! Form::label('minimum_score', 'Minimum Score') !!} @include('core::partials.required')
				{!! Form::text('minimum_score') !!}
				<span class="text-danger">{{ $errors->first('minimum_score') }}</span>
			</div>
		</div>
	</div>

	<h3>Test Parameters</h3>
	<div class="well">
		<div class="form-group">			
			{!! Form::label('readinglevel', 'Reading Level', ['class' => 'col-sm-3']) !!}
			<div class="col-sm-4">{!! Form::text('readinglevel', null, ['placeholder' => 'Minimum']) !!}</div>
			<div class="col-sm-4">{!! Form::text('readinglevel_max', null, ['placeholder' => 'Maximum']) !!}</div>
		</div>
		<div class="form-group">
			{!! Form::label('reliability', 'Reliability', ['class' => 'col-sm-3']) !!}
			<div class="col-sm-4">{!! Form::text('reliability', null, ['placeholder' => 'Minimum']) !!}</div>
			<div class="col-sm-4">{!! Form::text('reliability_max', null, ['placeholder' => 'Maximum']) !!}</div>
		</div>
		<div class="form-group">
			{!! Form::label('pvalue', 'P-Value', ['class' => 'col-sm-3']) !!}
			<div class="col-sm-4">{!! Form::text('pvalue', null, ['placeholder' => 'Minimum']) !!}</div>
			<div class="col-sm-4">{!! Form::text('pvalue_max', null, ['placeholder' => 'Maximum']) !!}</div>
		</div>
		<div class="form-group">
			{!! Form::label('difficulty', 'Difficulty', ['class' => 'col-sm-3']) !!}
			<div class="col-sm-4">{!! Form::text('difficulty', null, ['placeholder' => 'Minimum']) !!}</div>
			<div class="col-sm-4">{!! Form::text('difficulty_max', null, ['placeholder' => 'Maximum']) !!}</div>
		</div>
		<div class="form-group">
			{!! Form::label('discrimination', 'Discrimination', ['class' => 'col-sm-3']) !!}
			<div class="col-sm-4">{!! Form::text('discrimination', null, ['placeholder' => 'Minimum']) !!}</div>
			<div class="col-sm-4">{!! Form::text('discrimination_max', null, ['placeholder' => 'Maximum']) !!}</div>
		</div>
		<div class="form-group">
			{!! Form::label('guessing', 'Guessing', ['class' => 'col-sm-3']) !!}
			<div class="col-sm-4">{!! Form::text('guessing', null, ['placeholder' => 'Minimum']) !!}</div>
			<div class="col-sm-4">{!! Form::text('guessing_max', null, ['placeholder' => 'Maximum']) !!}</div>
		</div>
		<div class="form-group">
			{!! Form::label('cutscore', 'Cut Score', ['class' => 'col-sm-3']) !!}
			<div class="col-sm-4">{!! Form::text('cutscore', null, ['placeholder' => 'Minimum']) !!}</div>
			<div class="col-sm-4">{!! Form::text('cutscore_max', null, ['placeholder' => 'Maximum']) !!}</div>
		</div>
		<div class="form-group">
			{!! Form::label('target_theta', 'Target Theta', ['class' => 'col-sm-3']) !!}
			<div class="col-sm-8">{!! Form::text('target_theta') !!}</div>
		</div>
		<div class="form-group">
			{!! Form::label('pbs', 'Minimum PBS', ['class' => 'col-sm-3']) !!}
			<div class="col-sm-8">{!! Form::text('pbs') !!}</div>
		</div>
	</div><!-- .col-md-6 -->

	<h3>Items by Subject <small>@include('core::partials.required')</small></h3>
	<div class="well">
		<span class="text-danger">{{ $errors->first('subjects') }}</span>
		<div class="form-group">
			<div class="col-sm-4"><em class="pull-right">Subject</em></div>
			<div class="col-sm-6"><em># of Items</em></div>
		</div>
		@foreach($subjects as $subject)
			{{-- Only show subjects that aren't reporting as other ones --}}
			@if( ! $subject->report_as)
				<div class="form-group">
					{!! Form::label('subjects['.$subject->id.']', $subject->name, ['class' => 'col-sm-4']) !!}
					<div class="col-sm-6">{!! Form::text('subjects['.$subject->id.']', $plan->items_by_subject->{$subject->id}, ['placeholder' => '# of items']) !!}</div>
				</div>
			@endif
		@endforeach
	</div><!-- .col-md-6 -->	


	<h3>Item Pool Parameters</h3>
	<div class="well">
		<div class="form-group">
			<div class="col-md-6">
				{!! Form::label('item_pvalue', 'Item P-Value') !!}
				{!! Form::text('item_pvalue') !!}
				<span class="text-danger">{{ $errors->first('item_pvalue') }}</span>			
			</div>
			<div class="col-md-6">
				{!! Form::label('item_pvalue_max', 'Item P-Value Max') !!} @include('core::partials.required')
				{!! Form::text('item_pvalue_max') !!}
				<span class="text-danger">{{ $errors->first('item_pvalue_max') }}</span>			
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-12">
				{!! Form::label('max_attempts', 'Max Generation Attempts') !!} @include('core::partials.required')
				{!! Form::text('max_attempts') !!}
				<span class="text-danger">{{ $errors->first('max_attempts') }}</span>
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-12">
				{!! Form::label('max_pvalue_attempts', 'Max P-Value Attempts') !!}
				{!! Form::text('max_pvalue_attempts') !!}
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-12">
				{!! Form::label('ignore_stats', 'Ignore Item Stats for #') !!}
				{!! Form::text('ignore_stats') !!}
			</div>
		</div>
	</div>

	<h3>Comments</h3>
	<div class="well">
		<div class="form-group">
			<div class="col-md-12">
				{!! Form::textarea('comments') !!}
			</div>
		</div>
	</div>
</div>

<div class="col-md-3 sidebar">
	<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
		{!! Button::success(Icon::refresh().' Update')->submit() !!}
	
		<a href="{{ route('testplans.generate', $plan->id) }}" class="btn btn-info">{!! Icon::tasks() !!} Generate New Testform</a>
	</div>
</div>
{!! Form::close() !!}
@stop