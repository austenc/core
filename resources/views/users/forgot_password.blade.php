@extends('core::layouts.default')

@section('content')
	<form method="POST" action="{{{ URL::to('/users/forgot') }}}" accept-charset="UTF-8">
	    <input type="hidden" name="_token" value="{{{ Session::getToken() }}}">
	
		<h3>Forgot Password</h3>
		<p>
			Enter your email in the form below to receive an email containing instructions on how to recover your password.
		</p>
	    <div class="well">
	    	<div class="form-group">
	    	    <label for="email">{{{ Lang::get('confide::confide.e_mail') }}}</label>
	    	    <div class="input-append input-group">
	    	        <input class="form-control" placeholder="{{{ Lang::get('confide::confide.e_mail') }}}" type="text" name="email" id="email" value="{{{ Input::old('email') }}}">
	    	        <span class="input-group-btn">
	    	            <input class="btn btn-success" type="submit" value="{{{ Lang::get('confide::confide.forgot.submit') }}}">
	    	        </span>
	    	    </div>
	    	</div>
	    </div>

	    @if ( Session::get('error') )
	        <div class="alert alert-error alert-danger">{{{ Session::get('error') }}}</div>
	    @endif

	    @if ( Session::get('notice') )
	        <div class="alert">{{{ Session::get('notice') }}}</div>
	    @endif
	</form>
@stop