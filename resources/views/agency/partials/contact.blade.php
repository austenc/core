<h3>Contact</h3>
<div class="well">
    <div class="form-group">
        {!! Form::label('email', 'Email') !!} @include('core::partials.required')
        @if(isset($agency))
        {!! Form::text('email', $agency->user->email) !!}
        @else
        {!! Form::text('email') !!}
        @endif
        <span class="text-danger">{{ $errors->first('email') }}</span>
    </div>
    <div class="form-group">
        {!! Form::label('phone', 'Phone') !!}
        {!! Form::text('phone') !!}
        <span class="text-danger">{{ $errors->first('phone') }}</span>
    </div>
</div>