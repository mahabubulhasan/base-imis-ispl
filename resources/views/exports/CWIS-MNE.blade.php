<table>
    <thead>
        <tr>
            <th>{{ __('CWIS Monitoring and Evaluation Indicator')}}</th>
        </tr>
        <tr>
            <th> {{ $years }}</th>
        </tr>
        <tr>
            <th> </th>
        </tr>
        <tr>
           <th>{{__('Indicator')}}</th>
            <th>{{__('Outcome')}}</th>
            <th>{{__('Value')}}</th>
        </tr>

    </thead>
    <tbody>
        @foreach ($results as $result)
            <tr>
                <td>{{ $result->label}}</td>
                <td> {{ $result->outcome }}</td>
                <td>{{ $result->data_value }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
