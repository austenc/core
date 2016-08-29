@if (! $attempt->testevent->is_paper)
    {{-- Start Time --}}
    <tr>
        @if($attempt->status == 'assigned' || $attempt->status == 'pending')
            <td>
                <label class="control-label">Starts At</label>
            </td>
            <td>
                {{ $attempt->testevent->start_time }}
            </td>
        @else
            <td>
                <label class="control-label">Start Time</label>
            </td>
            <td>{{ date('g:i A', strtotime($attempt->start_time)) }}</td>
        @endif
    </tr>

    {{-- End Time --}}
    @if($attempt->status != 'assigned' && $attempt->status != 'pending')
    <tr>
        <td>
            <label class="control-label">End Time</label>
        </td>
        <td>{{ date('g:i A', strtotime($attempt->end_time)) }}</td>
    </tr>
    @endif
@endif