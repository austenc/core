A Skill Test you are scheduled to manage has changed.
<br><br>
Skill Test: {{ $skill->name }}<br>
Old Seat Limit: {{ $old_seats }}<br>
New Seat Limit: {{ $new_seats }}<br><br>

For more detail, click 
<a href="{{ route('events.edit', $event->id) }}">here</a>