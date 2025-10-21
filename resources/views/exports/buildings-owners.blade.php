<table>
    <thead>
    <tr>
        <th align="right" width="20"><h1><strong>{{__('BIN')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Tax Code/Holding ID')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Ward Number')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Road Code')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Presence of Toilet')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Number of Floors')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Number of Households')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Population of Building')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Structure Type')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Estimated Area of the Building(m²)')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Building Associated')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Owner Name')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Owner Gender')}}</strong></h1></th>
        <th align="right" width="20"><h1><strong>{{__('Owner Contact Number')}}</strong></h1></th>
    </tr>
    </thead>
    <tbody>
    @foreach($buildingResults as $building)
        <tr>
            <td>{{ $building->bin  }}</td>
            <td>{{ $building->tax_code  }}</td>
            <td>{{ $building->ward   }}</td>
            <td>{{ $building->road_code   }}</td>
            <td>{{ $building->toilet_status_text   }}</td>
            <td>{{ $building->floor_count   }}</td>
            <td>{{ $building->household_served   }}</td>
            <td>{{ $building->population_served   }}</td>
            <td>{{ $building->structure_type  }}</td>
            <td>{{ $building->estimated_area  }}</td>
            <td>{{ $building->building_associated_to }}</td>
            <td>{{ $building->owner_name }}</td>
            <td>{{ $building->owner_gender }}</td>
            <td>{{ $building->owner_contact }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
