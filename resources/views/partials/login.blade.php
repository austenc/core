<form method="POST" action="{{{ URL::to('/users/login') }}}" accept-charset="UTF-8">
    <input type="hidden" name="_token" value="{{{ csrf_token() }}}">

        @if ( Session::get('error') )
            <div class="alert alert-danger">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>                            
                {{{ Session::get('error') }}}                
            </div>
        @endif

        @if ( Session::get('notice') )
            <div class="alert">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>            
                {{{ Session::get('notice') }}}
            </div>
        @endif

    <fieldset>
        <div class="form-group">
            <label for="email">{{{ Lang::get('confide::confide.username_e_mail') }}}</label>
            <input class="form-control" autofocus="autofocus" tabindex="1" placeholder="{{{ Lang::get('confide::confide.username_e_mail') }}}" type="text" name="email" id="email" value="{{{ Input::old('email') }}}">
        </div>
        <div class="form-group">
        <label for="password">
            {{{ Lang::get('confide::confide.password') }}}
            <small>
                <a href="{{{ route('users.forgot') }}}">{{{ Lang::get('confide::confide.login.forgot_password') }}}</a>
            </small>
        </label>
        <input class="form-control" tabindex="2" placeholder="{{{ Lang::get('confide::confide.password') }}}" type="password" name="password" id="password">
        </div>
        <div class="form-group">
            <div class="checkbox">
                <label for="remember">
                    <input tabindex="4" type="checkbox" name="remember" id="remember" value="1">
                    {{{ Lang::get('confide::confide.login.remember') }}}
                </label>
            </div>
        </div>
        <div class="form-group">
            <button id="login-btn" tabindex="3" type="submit" class="btn btn-primary">{{{ Lang::get('confide::confide.login.submit') }}}</button>
        </div>
    </fieldset>
</form>

@section('scripts')
@parent
<script>
    setTimeout('location.reload(true);', {{ Config::get('session.lifetime') * 60 * 1000 }})
</script>
@stop