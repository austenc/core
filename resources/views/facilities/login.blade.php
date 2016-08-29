@extends('core::layouts.full')

@section('content')
	{!! Form::open(['route' => 'facilities.login.save']) !!}
	<h2>Choose Discipline</h2>
	<div class="row">
		@foreach($facility->disciplines as $discipline)
		<div class="col-xs-12 col-sm-6">			
			<div class="small-box bg-green">
				<div class="inner">
					<h4>{{{ $discipline->name }}}</h4>
				</div>
				<div class="icon">
					<i class="glyphicon glyphicon-ok-sign"></i>
				</div>
				<span href="#" class="small-box-footer">
					<button type="submit" class="btn btn-lg btn-block btn-clear" name="discipline_id" value="{{{ $discipline->id }}}">
						<strong>Login {{ $discipline->pivot->tm_license }} <i class="fa fa-arrow-circle-right"></i></strong>
					</button>
				</span>
			</div>			
		</div>
		@endforeach
	</div>
	{!! Form::close() !!}
@stop