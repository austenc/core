<h3>Other</h3>
<div class="well">
	{{-- Site Type --}}
	@include('core::facilities.partials.site_type')

	{{-- Expires --}}
	@if(isset($facility))
		@include('core::facilities.partials.expiration')

		{{-- Testing Only --}}
		@if(in_array('Testing', $facility->actions))
			@include('core::facilities.partials.max_seats')
		@endif

		@if(in_array('Training', $facility->actions))
			@include('core::facilities.partials.last_training_approval')
		@endif
	@endif

	{{-- Actions --}}
	@include('core::facilities.partials.actions')
</div>