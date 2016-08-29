A Test Event you are scheduled for has changed.
<br><br>
<strong>Old Event</strong>: {{ $old_datetime }}<br>
<strong>New Event</strong>: {{ $new_datetime }}<br><br>

<a href="{{ route('skills.testing.show', [$attempt_id]) }}">
    Click here for more details
</a>