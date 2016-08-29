@extends('core::layouts.full')

@section('content')
	{!! Form::open(['route' => ['instructors.login.save']]) !!}
	<h2>Select Login <small>{{ Auth::user()->userable->full_name }}</small></h2>
	<div class="row">
		@foreach($disciplines as $i => $d)
			@foreach($activeFacilities as $fac)
				@if($i == $fac->pivot->discipline_id)
					<div class="col-xs-12 col-sm-6">			
						<div class="small-box bg-green">
							<div class="inner">
								<h4>{{{ $disciplines[$fac->pivot->discipline_id] }}}</h4>
								<p>{{{ $fac->name }}}</p>
							</div>
							<div class="icon">
								<i class="glyphicon glyphicon-ok-sign"></i>
							</div>
							<span href="#" class="small-box-footer">
								<button type="submit" class="btn btn-lg btn-block btn-clear" name="license" value="{{{ $fac->pivot->tm_license }}}">
									<strong>Login {{ $fac->pivot->tm_license }} <i class="fa fa-arrow-circle-right"></i></strong>
								</button>
							</span>
						</div>			
					</div>
				@endif
			@endforeach
		@endforeach
	</div>

	{{-- Turn Role Select on/off --}}
	@if($roles->count() > 1 && ! Input::get('role'))
		{!! Form::hidden('has_roles', $roles->count(), ['id' => 'has_roles']) !!}
	@endif

	{!! Form::hidden('currRole', Auth::user()->userable_type, ['id' => 'currRole']) !!}
	{!! Form::close() !!}

	{{-- Multiple Role Select --}}
	{!! Form::open(['route' => 'instructors.role.swap', 'id' => 'sel-role-form']) !!}
	<div class="modal fade modal-preserve" id="role-modal">
	  	<div class="modal-dialog">
	    	<div class="modal-content">
	      		<div class="modal-header">
	        		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
	        		<h4 class="modal-title">Login As</h4>
	     		</div>

		      	<div class="modal-body">
		      		{!! Form::label('role', 'Select Role') !!}
		      		{!! Form::select('role', $roles->lists('name', 'name')->all(), Auth::user()->userable_type, ['id' => 'selRole']) !!}
	      		</div>

		      	<div class="modal-footer">
		        	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		        	<button class="btn btn-primary" type="submit">Login</button>
		      	</div>
	    	</div>
	  	</div>
	</div>
	{!! Form::close() !!}
@stop

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			if($('#has_roles').length)
			{
				$('#role-modal').modal();
			}

			$('#sel-role-form').submit(function(e)
			{
				if($('#currRole').val() == $('#selRole').val())
				{
					$('#role-modal').modal('hide');
					return false;
				}
			});
		});
	</script>
@stop