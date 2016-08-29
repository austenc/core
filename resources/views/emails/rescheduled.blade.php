<p>This is notification that a {{ Lang::choice('core::terms.student', 1) }} who was in one of your upcoming test events has been rescheduled.</p>

<strong>Test Site: </strong> {{{ $facility->name }}} <br>
<strong>Test Date: </strong> {{{ $event->test_date }}} at {{{ $event->start_time }}} <br>
<strong>{{ Lang::choice('core::terms.student', 1) }}: </strong> {{{ $student->fullName }}}
<p>
    Call {{ Config::get('core.helpPhone') }} if you need assistance.
</p>