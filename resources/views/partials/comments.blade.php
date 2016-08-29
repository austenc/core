@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<h3 id="comments-info">Notes</h3>
	<div class="well">
		<div class="form-group">
			{!! Form::label('comments', 'Comments') !!}
			@if( ! Form::isDisabled())
				<textarea name="comments" id="comments" class="form-control">@if(isset($record)){{ Input::old('comments') ? Input::old('comments') : $record->comments }}@else{{ Input::old('comments') }}@endif</textarea>
			<span class="text-danger">{{ $errors->first('comments') }}</span>
			@else
				<textarea name="comments" id="comments" class="form-control" disabled>@if(isset($record)){{ Input::old('comments') ? Input::old('comments') : $record->comments }}@else{{ Input::old('comments') }}@endif</textarea>
			@endif
		</div>
	</div>
@endif