<h3>Identification</h3>
<div class="well">
	{{-- Name --}}
	@include('core::partials.name')
	
	{{-- License --}}
	@include('core::instructors.partials.license')

	{{-- DOB --}}
	@include('core::partials.birthdate', ['optional' => true])

	{{-- Gender --}}
	@include('core::partials.gender')
</div>