<table>
    <thead>
    <tr>
        <th align="right" width="20"><h1><strong>{{__('Containment Type')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Containments')}}</strong></h1></th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['ContainmentType'] }}</td>
            <td>{{ $row['Containments'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>