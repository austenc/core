<p>
<a href="{{ route('events.edit', $event->id) }}">Test event #{{ $event->id }}</a> has not been ended after several days. Manual action is required. Please review the event, contact the {{ Lang::choice('core::terms.observer', 1) }} if needed, and end once finished reviewing. 
</p>

<p class="text-center">
    <a href="{{ route('events.edit', $event->id) }}" class="btn btn-primary">
        Click here to view test event #{{ $event->id }}
    </a>
</p>