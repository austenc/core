You have been <strong>removed</strong> from a Test Event.<br><br>

Knowledge Test <strong>{{ $exam->name }}</strong> 
was scheduled to start {{ $event->test_date.' '.$event->start_time }} 
at {{ Lang::choice('core::terms.facility_testing', 1) }} {{ $event->facility->name }} 
has been cancelled or rebooked.