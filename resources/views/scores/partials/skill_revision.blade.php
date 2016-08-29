@extends(Request::ajax() ? 'core::layouts.ajax' : 'core::layouts.default')

@section('content')

	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title">Task Revision Detail</h4>
	</div>

	<div class="modal-body">
		<div class="table-responsive well">
		  <table class="table table-hover table-striped">
		    <thead>
		    	<tr>
		    		<th>Step</th>
		    		<th>Old Value</th>
		    		<th>New Value</th>
		    	</tr>
		    </thead>
		    <tbody>
		    @foreach($values as $k => $v)
		    	<tr>
		    		<td>{{ $k + 1 }}</td>
		    		<td>{{ $v->old }}</td>
		    		<td>
						{{-- If it has changed, bold and change its color --}}
						@if($v->old != $v->new)
							<strong class="text-danger">{{ $v->new }}</strong>
						@else
			    			{{ $v->new }}
			    		@endif
		    		</td>
		    	</tr>
		    @endforeach
		    </tbody>
		  </table>
		</div>
	</div>
@stop