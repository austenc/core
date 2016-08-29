<h3>Status</h3>
<div class="well">
	@if(isset($record))
		{{-- Override Sync --}}
		@include('core::partials.hold', ['holdStatus' => in_array('hold', explode(",", $record->status))])
		@include('core::partials.lock', ['lockStatus' => in_array('locked', explode(",", $record->status))])
	@else
		@include('core::partials.hold', ['holdStatus' => false])
		@include('core::partials.lock', ['lockStatus' => false])
	@endif
</div>