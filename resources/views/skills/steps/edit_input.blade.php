@extends('core::layouts.default')

@section('content')
	{!! Form::model($step, ['route' => ['steps.input.update', $step->id, $input->id], 'method' => 'PUT']) !!}
	<div class="col-md-10">
		<div class="row">
			<div class="col-xs-8">
				<h1>
					Edit Step Input
				</h1>
			</div>
			<div class="col-md-4 back-link">
				<a href="{{ route('tasks.edit', $step->task->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to Task</a>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-8">
				<h3>
					Step
					<small># {{ $step->ordinal }} in Task Step order</small>
				</h3>
			</div>
			<div class="col-md-4 back-link">
				<a href="{{ route('steps.edit', $step->id) }}" class="btn btn-link pull-right">{!! Icon::arrow_left() !!} Back to Step</a>
			</div>
		</div>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('outcome', 'Expected Outcome') !!}
					<p class="form-control-static">{{ str_replace($input->bbcode, '<strong>'.$input->bbcode.'</strong>', $step->expected_outcome) }}</p>
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
				<h3>
					Input
					<small>BBCode Tag {{ $input->bbcode }}</small>
				</h3>
			</div>
		</div>
		<div class="well">
			<div class="form-group row">
				<div class="col-md-12">
					{!! Form::label('type', 'Type') !!}
					<p class="form-control-static">{{ ucfirst($input->type) }}</p>
					{!! Form::hidden('type', $input->type) !!}
					<span class="text-danger">{{ $errors->first('type') }}</span>
				</div>
			</div>
			@if($input->type == 'textbox')
			<div class="form-group row textbox-row">
			@else
			<div class="form-group row textbox-row" style="display:none;">
			@endif
				<div class="col-md-8">
					{!! Form::label('text_answer', 'Answer') !!} @include('core::partials.required')
					{!! Form::text('text_answer', Input::old('text_answer') ? Input::old('text_answer') : $input->answer) !!}
					<span class="text-danger">{{ $errors->first('text_answer') }}</span>
				</div>
				<div class="form-group row">
					<div class="col-md-4">
						{!! Form::label('tolerance', 'Tolerance') !!}
						{!! Form::text('tolerance', Input::old('tolerance') ? Input::old('tolerance') : $input->tolerance) !!}
						<span class="text-danger">{{ $errors->first('tolerance') }}</span>
					</div>
				</div>
			</div>

			@if($input->type != 'textbox')
			<div class="form-group row options-row">
			@else
			<div class="form-group row" style="display:none;">
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
									<td>{!! Form::radio('answer', $i, ($i === $input->answer_key)) !!}</td>
									<td>
										@if(Input::old())
											{!! Form::text('option['.$i.']', Input::old('option['.$i.']')) !!}
										@else
											{!! Form::text('option['.$i.']', isset($input->option[$i]) ? $input->option[$i] : '') !!}
										@endif
									</td>
									<td class="col-md-3">
										@if(Input::old())
											{!! Form::text('value['.$i.']', Input::old('value['.$i.']')) !!}
										@else
											{!! Form::text('value['.$i.']', isset($input->values[$i]) ? $input->values[$i] : '') !!}
										@endif
									</td>
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
			<button type="submit" class="btn btn-success">{!! Icon::refresh() !!} Update</button>
			<a href="{{ route('steps.preview.paper', $step->id) }}" class="btn btn-info" data-toggle="modal" data-target="#preview">{!! Icon::tree_conifer() !!} Paper Preview</a>
			<a href="{{ route('steps.preview.web', $step->id) }}" class="btn btn-info" data-toggle="modal" data-target="#preview">{!! Icon::facetime_video() !!} Web Preview</a>
		</div>
	</div>
	{!! Form::hidden('step_id', $step->id, ['class' => 'step-id']) !!}
	@if(Input::get('v'))
		{!! Form::hidden('task_id', $step->skilltask_id) !!}
	@endif

	{!! Form::close() !!}
	{!! HTML::modal('preview') !!}
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