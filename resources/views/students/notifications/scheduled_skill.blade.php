You have been successfully scheduled for a <strong>Skill Test</strong> 
beginning <strong>{{ $event->test_date.' '.$event->start_time }}</strong> 
at {{ Lang::choice('core::terms.facility_testing', 1) }} <strong>{{ $event->facility->name }}</strong>