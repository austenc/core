@extends('core::layouts.default')

@section('title', 'Edit Billing Rate')

@section('content')
	{!! Form::open(['route' => 'billingrate.update', 'class' => 'form-horizontal']) !!}
	{!! Form::hidden('id', $billingRate->id, ['id' => 'id']) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Edit Billing Rate</h1>
				</div>
				{!! HTML::backlink('accounting.billrates') !!}
			</div>
			<div class="well">
				<div class="form-group">
					<div class="col-md-2">
						{!! Form::label('svc_name', 'Service Name') !!}
					</div>
					<div class="col-md-10">
						{!! Form::text('svc_name', $billingRate->svc_name, ['id' => 'svc_name']) !!}
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-2">
						{!! Form::label('discipline_id', 'Discipline') !!}
					</div>
					<div class="col-md-4">
						{!! Form::select('discipline_id', $disciplines, $billingRate->discipline_id, ['id' => 'discipline_id']) !!}
					</div>
					<div class="col-md-2">
						{!! Form::label('test_type', 'Test Type') !!}
					</div>
					<div class="col-md-4">
						{!! Form::select('test_type', ['knowledge' => 'Knowledge', 'oral' => 'Oral', 'skill' => 'Skill'], $billingRate->test_type, ['id' => 'test_type']) !!}
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-2">
						{!! Form::label('rate', 'Knowledge Rate') !!}
					</div>
					<div class="col-md-4">
						{!! Form::text('rate', $billingRate->rate, ['id' => 'rate']) !!}
					</div>
					<div class="col-md-2">
						{!! Form::label('rate_ns', 'No Show Rate') !!}
					</div>
					<div class="col-md-4">
						{!! Form::text('rate_ns', $billingRate->rate_ns, ['id' => 'rate_ns']) !!}
					</div>
				</div>
				<div class="form-group" style="text-align: center;">
					<button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i> Update Rate</button>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			
		</div>
	</div>
	{!! Form::close() !!}
@stop