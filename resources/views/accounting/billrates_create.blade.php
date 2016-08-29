@extends('core::layouts.default')

@section('title', 'Add Billing Rate')

@section('content')
	{!! Form::open(['route' => 'billingrate.store', 'class' => 'form-horizontal']) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Create New Billing Rate</h1>
				</div>
				{!! HTML::backlink('accounting.billrates') !!}
			</div>
			<div class="well">
				<div class="form-group">
					<div class="col-md-3">
						{!! Form::label('svc_name', 'Service Name') !!}
					</div>
					<div class="col-md-9">
						{!! Form::text('svc_name') !!}
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-3">
						{!! Form::label('discipline_id', 'Discipline') !!}
					</div>
					<div class="col-md-3">
						{!! Form::select('discipline_id', $disciplines, ['id' => 'discipline_id']) !!}
					</div>
					<div class="col-md-3">
						{!! Form::label('test_type', 'Test Type') !!}
					</div>
					<div class="col-md-3">
						{!! Form::select('test_type', ['knowledge' => 'Knowledge', 'oral' => 'Oral', 'skill' => 'Skill'], null, ['id' => 'test_type']) !!}
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-3">
						{!! Form::label('rate', 'Rate') !!}
					</div>
					<div class="col-md-3">
						{!! Form::text('rate') !!}
					</div>
					<div class="col-md-3">
						{!! Form::label('rate_ns', 'No Show Rate') !!}
					</div>
					<div class="col-md-3">
						{!! Form::text('rate_ns') !!}
					</div>
				</div>
				<div class="form-group" style="text-align: center;">
					<button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i> Add Rate</button>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			
		</div>
	</div>
	{!! Form::close() !!}
@stop