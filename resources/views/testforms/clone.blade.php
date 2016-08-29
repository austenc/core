@extends('core::layouts.default')

@section('content')
	{!! Form::model($form, ['route' => 'testforms.store']) !!}
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Save Scrambled As New Testform</h1>	
			</div>
			{!! HTML::backlink('testforms.index') !!}
		</div>

		<div class="form-group">
			<div class="well">
				{!! Form::label('name', 'Name') !!}
				{!! Form::text('name') !!}
				<span class="text-danger">{{ $errors->first('name') }}</span>
			</div>
		</div>
	
		<!-- Items -->
		<div class="panel panel-info">
			<div class="panel-heading">Drag and Drop to re-order testitems</div>
			<table class="table table-striped table-condensed table-hover dragdrop">
				<thead>
					<tr>
						<th></th>
						<th>Stem</th>
						<th>Answer</th>
					</tr>
				</thead>
				<tbody>
					@foreach($form->testitems as $item)
					<tr>
						<td align="center">
							<span class="ordinal">{{ $item->pivot->ordinal }}</span>
							{!! Form::hidden('testitems[]', $item->id) !!}
						</td>
						<td>{{ $item->excerpt }}</td>
						<td>{{ $item->theAnswer->content }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	<div class="col-md-3">
		<button class="btn btn-success btn-block" type="submit">{!! Icon::save() !!} Save New Testform</button>
	</div>
	
	{!! Form::hidden('testplan_id', $form->testplan_id) !!}
	{!! Form::hidden('scramble_source', $form->id) !!}
{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/jquery-sortable/source/js/jquery-sortable-min.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/testforms/ordinal.js') !!}
@stop