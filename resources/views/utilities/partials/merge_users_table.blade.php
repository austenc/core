@if(empty($items))
    No {{ Lang::choice('core::terms.'.$type, 1) }} Matches
@else
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>User#</th>
            <th>{{ Lang::choice('core::terms.'.$type, 1) }}</th>
            <th>Consume User</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $id => $matches)
            <tr>
                <td>
                    <span class="text-muted lead">{{ $lookup->get($id)->user_id }}</span>
                </td>

                <td>
                    <a href="{{ route(str_plural($type).'.edit', $id) }}">{{ $lookup->get($id)->fullname }}</a>
                </td>

                <td>
                    {!! Form::select('consume_user_id['.$lookup->get($id)->user_id.']', [0 => 'Select User'] + $matches->lists('descript_name','user_id')->all(), false, ['class' => 'user-sel-'.$lookup->get($id)->user_id]) !!}
                </td>

                <td>
                    <button type="submit" class="btn btn-danger pull-right merge-{{ $lookup->get($id)->user_id }}" name="user" data-confirm="Merge and Consume User? The orphaned User record will be deleted.<br><br>Are you sure?" value="{{ $lookup->get($id)->user_id }}">
                        {!! Icon::random().' Merge' !!}
                    </button>
                </td>

                {!! Form::hidden('user_role['.$lookup->get($id)->user_id.']', $type) !!}
                {!! Form::hidden('person_id['.$lookup->get($id)->user_id.']', $id) !!}
            </tr>
        @endforeach
    </tbody>
</table>
@endif