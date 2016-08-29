{{-- Show Admin/Staff other timestamps --}}
@if(Auth::user()->ability(['Admin', 'Staff', 'Agency'], []))
<h3>Timestamps</h3>
<div class="well">
	@if($training->archived_at)
	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('archived_at', 'Archived At') !!}
			{!! Form::text('archived_at', $training->archived_at, ['disabled']) !!}
		</div>
	</div>
	@endif

	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('updated_at', 'Updated At') !!}
			{!! Form::text('updated_at', $training->updated_at, ['disabled']) !!}
		</div>
	</div>

	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('created_at', 'Created At') !!}
			{!! Form::text('created_at', $training->created_at, ['disabled']) !!}
		</div>
	</div>
	
	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('created_by', 'Created By') !!}
			{!! Form::text('created_by', ($training->creator ? $training->creator->full_name : ''), ['disabled']) !!}
		</div>
	</div>

	<div class="form-group row">
		<div class="col-md-12">
			{!! Form::label('creator_type', 'Creator Type') !!}
			{!! Form::text('creator_type', ($training->creator ? $training->creator->getMorphClass() : ''), ['disabled']) !!}
		</div>
	</div>
</div>
@endif