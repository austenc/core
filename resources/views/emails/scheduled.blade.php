You have been scheduled for {{ ucfirst($type) }} Test <strong>{{ $exam->name }}</strong> 
on <strong>{{ $event->test_date.' '.$event->start_time }}</strong> 
at {{ Lang::choice('core::terms.facility_testing', 1) }} <strong>{{ $event->facility->name }}</strong>

<br><br>
<strong>Address: </strong><br>
{{ $event->facility->address }} <br>
{{ $event->facility->city }}, {{ $event->facility->state }} {{ $event->facility->zip }}
<hr>

<p>
    <strong>For more information, log in at the link below to view your confirmation letter: </strong><br><br>
    <a href="{{ $route }}">{{ $route }}</a>

    <br><br><br>

    Call {{ Config::get('core.helpPhone') }} if you need assistance.
</p>