@extends('core::layouts.default')

@section('title', 'Invoicing Rates')

@section('content')
	{!! Form::open(['route' => 'payrates.store', 'class' => 'form-horizontal']) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<div class="col-xs-8">
					<h1>Edit Payable Rate</h1>
				</div>
				{!! HTML::backlink('accounting.payrates') !!}
			</div>
			<div class="well">
				<div class="form-group">
					<div class="col-md-3">
						{!! Form::label('level_name', 'Level Name') !!}
					</div>
					<div class="col-md-3">
						{!! Form::text('level_name', null, ['id' => 'level_name']) !!}
					</div>
					<div class="col-md-3">
						{!! Form::label('discipline_id', 'Discipline') !!}
					</div>
					<div class="col-md-3">
						{!! Form::select('discipline_id', $disciplines, null, ['id' => 'discipline_id', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-3">
						{!! Form::label('knowledge_rate', 'Knowledge Rate') !!}
					</div>
					<div class="col-md-3">
						{!! Form::text('knowledge_rate', null, ['id' => 'knowledge_rate']) !!}
					</div>
					<div class="col-md-3">
						{!! Form::label('special_rate', 'Special Rate') !!}
					</div>
					<div class="col-md-3">
						{!! Form::text('special_rate', null, ['id' => 'special_rate']) !!}
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-3">
						{!! Form::label('oral_rate', 'Oral Rate') !!}
					</div>
					<div class="col-md-3">
						{!! Form::text('oral_rate', null, ['id' => 'oral_rate']) !!}
					</div>
					<div class="col-md-3">
						{!! Form::label('skill_rate', 'Skill Rate') !!}
					</div>
					<div class="col-md-3">
						{!! Form::text('skill_rate', null, ['id' => 'skill_rate']) !!}
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-3">
						{!! Form::label('ada_rate', 'ADA Rate') !!}
					</div>
					<div class="col-md-3">
						{!! Form::text('ada_rate', null, ['id' => 'ada_rate']) !!}
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