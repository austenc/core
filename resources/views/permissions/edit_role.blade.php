@extends('core::layouts.default')

@section('content')
{!! Form::open(['route' => ['permissions.update_role', $role->id], 'method' => 'POST', 'class' => 'form-inline']) !!}
<div class="row">
	<div class="col-md-9">
		<div class="row">
			<div class="col-xs-8">
				<h1>Edit Role <small>{{ $role->name }}</small></h1>
			</div>
			{!! HTML::backlink('permissions') !!}
		</div>

		<div class="well">
			<table class="table table-striped table-hover">
				<thead>
					<tr>
						<th>
						{!! Form::checkbox('select-all', NULL, FALSE, [
							'data-action' => 'select-all',
							'data-target' => 'input[name="permissions[]"]',
							'data-toggle-class' => 'success'
						]) !!}
						</th>
						<th>Permission</th>
					</tr>
				</thead>
				<tbody>
					@foreach($permissions as $p)
						<tr class="{{{ in_array($p->id, $hasPermissions) ? 'success' : '' }}}">
							<td>{!! Form::checkbox('permissions[]', $p->id, in_array($p->id, $hasPermissions), ['data-toggle-row' => 'success']) !!}</td>
							<td>{{ $p->readableName }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	{{-- Sidebar --}}
	<div class="col-md-3 sidebar">
		<div class="sidebar-contain" data-spy="affix" data-offset-top="{{ Config::get('core.affixOffset') }}" data-clampedwidth=".sidebar">
			<button type="submit" class="btn btn-success">{!! Icon::refresh() !!} Update</button>
		</div>
	</div>
</div>
{!! Form::close() !!}
@stop