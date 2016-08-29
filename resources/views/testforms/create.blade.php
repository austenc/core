@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => ['testforms.store']]) !!}
	<div class="row">
		<div class="col-md-9">
			<div class="row">
				<h1>Save New Testform</h1>
			</div>

			<div class="form-group">
				<div class="well">
					{!! Form::label('name', 'Name') !!}
					{!! Form::text('name', null, ['autofocus']) !!}
					<span class="text-danger">{{ $errors->first('name') }}</span>
				</div>
			</div>

			<div class="panel panel-info">
				<div class="panel-heading">Drag and Drop to re-order testitems</div>
				<table class="table table-striped table-hover table-condensed dragdrop">
					<thead>
						<tr>
							<th></th>
							<th>Stem</th>
							<th>Answer</th>
						</tr>
					</thead>
					<tbody>
						@foreach($items as $index => $item)
						<tr>
							<td align="center">
								<span class="ordinal">{{ $index+1 }}</span>
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

		{{-- Sidebar --}}
		<div class="col-md-3 sidebar">
			<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
				<button class="btn btn-success" type="submit">{!! Icon::save() !!} Save New Testform</button>
			</div>
		</div>
	</div>
	
	{!! Form::hidden('testplan_id', $plan->id) !!}
{!! Form::close() !!}
@stop

@section('scripts')
	{!! HTML::script('vendor/jquery-sortable/source/js/jquery-sortable-min.js') !!}
	{!! HTML::script('vendor/hdmaster/core/js/testforms/ordinal.js') !!}

	<script>
		$(document).ready(function(){
			// set order numbers since new items don't have an order yet
			updateOrderNumbers();
		});
	</script>
@stop