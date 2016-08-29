A Test Event at your {{ Lang::choice('core::terms.facility', 1) }} has changed. 
<br><br>
Old Event: {{ $old_datetime }}<br>
New Event: {{ $new_datetime }}<br><br>

For more detail, click 
<a href="{{ route('events.edit', $event->id) }}">here</a>