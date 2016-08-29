@extends('core::layouts.default')

@section('content')
	{!! Form::model($step, ['route' => ['steps.input.store', $step->id], 'method' => 'PUT']) !!}
	<div class="col-md-10">
		<div class="row">
			<div class="col-xs-8">
				<h1>
					Add Step Input
				</h1>
			</div>
			<div class="col-md-4 back-link">
				<a href="{{ route('steps.edit', $step->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to Step</a>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-8">
				<h3>
					Step
					<small># {{ $step->ordinal }} in Task Step order</small>
				</h3>
			</div>
		</div>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('outcome', 'Expected Outcome') !!}
					<p class="form-control-static">{{ $step->expected_outcome }}</p>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-4">
					{!! Form::label('weight', 'Weight') !!}
					<p class="form-control-static">{{ $step->weight }}</p>
				</div>

				<div class="col-md-4">
					{!! Form::label('Key', 'Key Step') !!}
					<p class="form-control-static">{{ $step->is_key == 1 ? 'Yes' : 'No' }}</p>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-8">
				<h3>Input</h3>
			</div>
		</div>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('type', 'Type') !!} @include('core::partials.required')
					{!! Form::select('type', ['textbox' => 'Textbox', 'dropdown' => 'Dropdown', 'radio' => 'Radio'], '', [
						'class' => 'input-type'
					]) !!}
					<span class="text-danger">{{ $errors->first('type') }}</span>
				</div>
			</div>
			@if( ! Input::old() || Input::old('type') == 'textbox')
			<div class="form-group row textbox-row">
			@else
			<div class="form-group row textbox-row" style="display:none;">
			@endif
				<div class="col-md-8">
					{!! Form::label('text_answer', 'Answer') !!}
					{!! Form::text('text_answer', '', [
						'data-toggle' 	=> 'tooltip', 
						'title' 		=> 'Correct answer to new Input'
					]) !!}
					<span class="text-danger">{{ $errors->first('text_answer') }}</span>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						{!! Form::label('tolerance', 'Tolerance') !!}
						{!! Form::text('tolerance', '', [
							'data-toggle' 	=> 'tooltip', 
							'title' 		=> 'Allow a range of answers'
						]) !!}
						<span class="text-danger">{{ $errors->first('tolerance') }}</span>
					</div>
				</div>
			</div>

			@if( ! Input::old() || Input::old('type') == 'textbox')
			<div class="form-group row options-row" style="display:none;">
			@else
			<div class="form-group row">
			@endif
				<div class="col-md-12">
					@if($errors->has('option') || $errors->has('answer') || $errors->has('value'))
					<div class="alert alert-danger">
						@foreach($errors->get('answer') as $msg)
							{{ $msg }}<br>
						@endforeach

						@foreach($errors->get('option') as $msg)
							{{ $msg }}<br>
						@endforeach

						@foreach($errors->get('value') as $msg)
							{{ $msg }}<br>
						@endforeach
					</div>
					@endif

					<table class="table table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>Answer @include('core::partials.required')</th>
								<th>Option @include('core::partials.required')</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
							@for($i = 0; $i < 5; $i++)
								<tr>
									<td>{{ $i + 1 }}</td>
									<td>{!! Form::radio('answer', $i) !!}</td>
									<td>{!! Form::text('option['.$i.']', '') !!}</td>
									<td class="col-md-3">{!! Form::text('value['.$i.']', '') !!}</td>
								</tr>
							@endfor
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-2 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			<button type="submit" class="btn btn-success">{!! Icon::plus_sign() !!} Create</button>
		</div>
	</div>
	{!! Form::hidden('step_id', $step->id, ['class' => 'step-id']) !!}
	@if(Input::get('v'))
		{!! Form::hidden('task_id', $step->skilltask_id) !!}
	@endif

	{!! Form::close() !!}
@stop

@section('scripts')
	<script type="text/javascript">
		$(function () { $("[data-toggle='tooltip']").tooltip({container: 'body', trigger: 'focus'}); });

		$(document).on('change', '.input-type', function(e)
		{
			var type = $(this).val();

			if(type=="radio" || type=="dropdown")
			{
				$('.textbox-row').hide();
				$('.options-row').show();
			}
			else
			{
				$('.textbox-row').show();
				$('.options-row').hide();
			}
		});
	</script>
@stop