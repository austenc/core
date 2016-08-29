@extends('core::layouts.default')

@section('content')

@if(Session::has('skills.errors') && ! empty(Session::get('skills.errors')))
	<div class="alert alert-danger">
		<strong>Error(s) in Submission!</strong> Hover over red Task Numbers below to see individual Task Errors or missing required fields.
	</div>
@endif

{!! Form::open(['route' => 'skills.save', 'class' => 'form-horizontal', 'id' => 'testing_form']) !!}	
	<div class="row">
		<div class="col-md-9">
			@include('core::pagination.number_only', ['paginator' => $all_tasks, 'current' => $current])
		</div>
	</div>

	<div class="row">
		<div class="col-md-9">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-9">
							<span class="lead">Skill Test - {{ $student->fullname }}</span>
						</div>

						<div class="col-xs-3 text-right">
							<button type="submit" name="end" value="end" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#stop-modal">{!! Icon::floppy_save() !!} End Test</button>	
						</div>
					</div>
				</div>
				<div class="panel-body">
					{{-- Scenario --}}
					<div class="well">
						<div class="h3 title">Task #{{ $current }}. {{ $task->title }}</div>
						<div class="h4 scenario">{{ $task->scenario }}</div>
					</div>

					{{-- Setups --}}
					@if($setups->count() > 0)
					<h3>Select Setup</h3>
					<table class="table table-striped" id="setup-table">
						<thead>
							<tr>
								<th></th>
								<th>Setup</th>
							</tr>
						</thead>
						<tbody>
							@foreach($setups as $setup)
								@if($response && $response->setup_id == $setup->id)
								<tr class="success">
								@else
								<tr>
								@endif
									<td>{!! Form::radio('setup_id', $setup->id, ($response && $response->setup_id == $setup->id), ['class' => 'setup-sel']) !!}</td>
									<td>{{ $setup->setup }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
					@endif

					<hr>

					{{-- Steps --}}
					<h3>Steps</h3>
					<table class="table table-striped" id="step-table">
						<thead>
							<tr>
								<th>{!! Form::checkbox('check_all') !!}</th>
								<th>#</th>
								@if(Config::get('core.client.show_skill_key_steps'))
									<th>Key?</th>
								@endif
								<th>Step</th>
								<th>Comments</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($steps as $step)
								<?php 
                                $stepErrors = 'skills.errors.' . $current . '.' . $step->id;
                                ?>
								@if(Session::has($stepErrors))
									<tr class="bg-danger">
								@else
									<tr>
								@endif
									<td>
										{!! Form::checkbox('step_completed['.$step->id.']', $step->id, ($data && $data[$step->id]['completed'] == 1)) !!}
									</td>
									<td>
										#{{ $step->ordinal }}.
										{!! Form::hidden('step_id[]', $step->id) !!}
									</td>
									@if(Config::get('core.client.show_skill_key_steps'))
										<td>
											@if($step->is_key == 1)
												<span class="label label-danger">Yes</span>
											@else
												<span class="label label-default">No</span>
											@endif
										</td>
									@endif
									<td class="col-md-8">
										{!! $step->expected_outcome !!}
										@if(Session::has($stepErrors) && $theErrors = Session::get($stepErrors))
											<p class="text-danger">
												{!! implode('<br>', $theErrors) !!}
											</p>
										@endif
									</td>
									<td>{!! Form::text('step_comments['.$step->id.']', ($data ? $data[$step->id]['comment'] : '')) !!}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>


				{{-- Navigation --}}
				<div class="panel-footer">
					<div class="row">
						<div class="col-xs-12 col-sm-6 col-sm-push-3 text-center">
							<div class="form-group">
								<label class="checkbox">
									{!! Form::checkbox('out_of_time', 1, Session::get('skills.out_of_time.' . $task->id)) !!} Ran out of time?
								</label>
							</div>
						</div>
						<div class="col-xs-6 col-sm-3 col-sm-pull-6">
							<button name="prev" id="prev" value="prev" data-style="expand-right" data-size="s" class="btn btn-sm btn-ajax ladda-button {{ $current > 1 ? 'btn-primary' : 'btn-disabled' }}" {{ $current == 1 ? ' disabled="disabled"' : '' }}>
								{!! Icon::arrow_left() !!} Prev
							</button>
						</div>
						<div class="col-xs-6 col-sm-3">
							@if($total > $current)
							<button name="next" id="next" value="next" data-style="expand-left" data-size="s" class="btn btn-sm btn-ajax ladda-button pull-right btn-primary">
								Next {!! Icon::arrow_right() !!}
							</button>
							@else
							<button name="end" value="end" data-style="expand-left" data-size="s" class="btn btn-sm pull-right btn-danger">
								{!! Icon::floppy_save() !!} End Test
							</button>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			@include('core::skills.testing.sidebars.timestamps')
		</div>
	</div>

	{!! Form::hidden('current', $current, ['id' => 'current-task-ordinal']) !!}
	{!! Form::hidden('task_id', $task->id, ['id' => 'current-task-id']) !!}
	{!! Form::hidden('attempt_id', $attempt->id, ['id' => 'attempt-id']) !!}
	@if($response)
		{!! Form::hidden('response_id', $response->id, ['id' => 'attempt-id']) !!}
	@endif
{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/bootstrap-3-timepicker/js/bootstrap-timepicker.min.js') !!}

	<script type="text/javascript">
		$(document).on('click','#setup-table tbody tr',function(e)
		{
			$('#setup-table tbody tr').removeClass('success');
			$(this).addClass("success");

			if ( ! $(e.target).is(":radio")) 
			{
				$(this).find(':radio').click();
			}
		});

		// on focus in comment box, highlight row
		$('input[name^="step_comments"]').focus(function() {
			$(this).parents('tr').addClass('bg-info');
		}).blur(function() {
			$(this).parents('tr').removeClass('bg-info');
		});

		$(document).on('click', 'input[name="check_all"]', function(e){
			$('#step-table :checkbox').prop('checked', this.checked);
		});

		// add timepicker
		$('.time-picker').timepicker({
			minuteStep: 1,
			defaultTime: false
		});

		// add datepicker
		$('.date-picker').datepicker({ startDate: new Date() });
	</script>
@stop