@foreach ($colors as $color => $title)
    <span class="glyphicon glyphicon-stop" style="color: {{{ $color }}}"></span> {{{ $title }}} 
    @if(isset($block))<br>@endif
@endforeach