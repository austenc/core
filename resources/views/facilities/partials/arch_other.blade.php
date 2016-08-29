<h3 id="approved-actions">Other</h3>
<div class="well">
	<div class="form-group">
		{!! Form::label('actions', 'Actions') !!}
		<div>{!! implode('<br>', $facility->actions) !!}</div>
	</div>

	<div class="form-group">
		{!! Form::label('expires', 'Expires') !!}
		<div>{{ $facility->expires }}</div>
	</div>

	@if(is_array($facility->actions) && in_array('Testing', $facility->actions))
	<div class="form-group">
		{!! Form::label('max_seats', 'Max Seats') !!}
		<div>{{ $facility->max_seats }}</div>
	</div>
	@endif

	@if($facility->parent_id)
	<div class="form-group">
		{!! Form::label('parent', 'Parent') !!}
		<div>
			<a href="{{ route('facilities.edit', $facility->parent->id) }}">{{ $facility->parent->full_name }}</a>
			@if($facility->parent->deleted_at)
				<small>(Archived)</small>
			@endif
		</div>
	</div>
	@endif

	<div class="form-group">
		{!! Form::label('site_type', 'Site Type') !!}
		<div>{{ empty($facility->site_type) ? 'N/A' : $facility->site_type }}</div>
	</div>
</div>