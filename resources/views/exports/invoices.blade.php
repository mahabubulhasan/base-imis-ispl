<table>
    <thead>
    <tr>
        <th>{{__('Name')}}</th>
        <th>{{__('Email')}}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($invoices as $invoice)
        <tr>
            <td>{{ $invoice->name }}</td>
            <td>{{ $invoice->email }}</td>
        </tr>
    @endforeach
    </tbody>
</table>