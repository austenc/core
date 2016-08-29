You may now begin the <strong>{{ $exam->name }}</strong> Test.<br><br>

<a href="{{ route('testing.start', $attempt_id) }}" class="btn btn-primary">Click here to begin testing</a>