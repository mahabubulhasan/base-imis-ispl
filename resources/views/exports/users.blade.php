<table>
    <thead>
        <tr>
            <th>{{__('Name')}}</th>
            <th>{{__('Email')}}</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
