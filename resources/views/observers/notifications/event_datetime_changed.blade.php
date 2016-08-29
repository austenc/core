A Test Event you are scheduled to manage has changed.
<br><br>
Old Event: {{ $old_datetime }}<br>
New Event: {{ $new_datetime }}<br><br>

For more detail, click 
<a href="{{ route('events.edit', $event->id) }}">here</a>