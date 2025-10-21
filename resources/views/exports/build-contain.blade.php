<table>
    <thead>
    <tr>
        <th align="right" width="20"><h1><strong>{{__('BIN')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Containment ID')}}</strong></h1></th>
    </tr>
    </thead>
    <tbody>
    @foreach($buildingResults as $building)
        <tr>
            <td>{{ $building->bin  }}</td>
            <td>{{ $building->containment_id  }}</td>


        </tr>
    @endforeach
    </tbody>
</table>
