@extends('core::layouts.default')

@section('content')
    <h2>Notifications</h2>
    
    {!! Form::open(['route' => 'notifications.update']) !!}

        @if($notifications->isEmpty())
            <p class="well">You don't have any notifications at this time.</p>
        @else
            <table class="table table-hover notifications-table">
                <thead>
                    <tr>
                        <th>
                            {!! Form::checkbox('select-all', null, false, [
                                'data-action' => 'select-all',
                                'data-target' => 'input[name="notifications[]"]',
                                'data-toggle-class' => 'warning'
                            ]) !!}
                        </th>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($notifications as $n)
                    <tr class="{{ $n->is_read ? 'bg-muted' : 'bg-'.$n->type }}">
                        <td>
                            {!! Form::checkbox('notifications[]', $n->id, false, ['data-toggle-row' => 'warning']) !!}
                        </td>
                        <td>{{ $n->sent_date }}</td>
                        <td><a href="{{ route('notification.detail', $n->id) }}">{{ $n->subject }}</a></td>
                        <td>{{ str_limit($n->flat_body) }}</td>
                        <td>
                            <span class="btn-group">
                                <a href="{{ route('notifications.delete', $n->id) }}" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Send to Trash" data-confirm="Are you sure you want to delete this notification?">
                                    <span class="sr-only">Trash</span>
                                    {!! Icon::trash() !!}
                                </a>

                                @if($n->is_read)
                                    <a href="{{ route('notification.unread', $n->id) }}" 
                                    class="btn btn-sm btn-primary btn-action" data-toggle="tooltip" 
                                    title="Mark as Unread">{!! Icon::flag() !!}</a>
                                @endif

                                <a href="{{ route('notification.detail', $n->id) }}" 
                                class="btn btn-sm btn-primary btn-action">
                                    View
                                </a>
                            </span>

                        </td>
                    </tr>    
                @endforeach
                </tbody>
            </table>
            {!! $notifications->render() !!}

            <small class="text-muted">With Selected:</small>
            <button type="submit" name="mark-unread" class="btn btn-default" data-toggle="tooltip" title="Mark as Unread" value="true">{!! Icon::flag() !!} Mark Unread</button>
            <button type="submit" name="mark-read" class="btn btn-default" data-toggle="tooltip" title="Mark as Read" value="true">{!! Icon::envelope() !!} Mark as Read</button>
            <button type="submit" name="mark-trash" class="btn btn-default" data-toggle="tooltip" title="Delete Messages" value="true" data-confirm="Are you sure you want to send all selected messages to the trash?">{!! Icon::trash() !!} Send to Trash</button>
        @endif
    {!! Form::close() !!}

@stop