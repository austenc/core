@if(Auth::user()->ability(['Admin', 'Staff'], []))
	<h3>Status</h3>
	<div class="well">
		@if(isset($facility))
			{{-- Override Sync --}}
			@include('core::partials.hold', ['holdStatus' => in_array('hold', explode(",", $facility->status))])
			@include('core::partials.lock', ['lockStatus' => in_array('locked', explode(",", $facility->status))])
			@include('core::facilities.partials.agency_only', ['agencyOnly' => $facility->agency_only])
		@else
			@include('core::partials.hold', ['holdStatus' => false])
			@include('core::partials.lock', ['lockStatus' => false])
			@include('core::facilities.partials.agency_only', ['agencyOnly' => false])
		@endif
	</div>
@endif