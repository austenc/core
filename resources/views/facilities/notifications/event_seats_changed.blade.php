A Knowledge Test at this {{ Lang::choice('core::terms.facility_testing', 1) }} has changed.
<br><br>
Exam: {{ $exam->name }}<br>
Old Seat Limit: {{ $old_seats }}<br>
New Seat Limit: {{ $new_seats }}<br><br>

For more detail, click 
<a href="{{ route('events.edit', $event->id) }}">here</a>