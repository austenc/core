<div class="flash-messages">
    @foreach($flashMessages as $type => $messages)
        @if($messages)
            <div class="alert alert-{{ $type }} alert-dismissable fade in">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
               
                @if(array_key_exists($type, $flashTitles) && $flashTitles[$type] !== null) 
                    <p class="lead">{{ $flashTitles[$type] }}</p>
                @endif

                <div class="messages">
                    @if(is_array($messages))
                        <ul>
                            @foreach($messages as $msg)
                                <li>{!! $msg !!}</li>
                            @endforeach
                        </ul>
                    @else
                        {!! $messages !!}
                    @endif
                </div>
            </div>
        @endif
    @endforeach
</div>
