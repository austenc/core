<h3>Identification</h3>
<div class="well">
    @include('core::partials.name')

    {{-- DOB --}}
    @include('core::partials.birthdate', ['optional' => true])

    {{-- Gender --}}
    @include('core::partials.gender')
</div>