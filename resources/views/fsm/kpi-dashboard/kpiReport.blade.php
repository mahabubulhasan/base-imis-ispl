<!DOCTYPE html>
<html>

    <head>
        <style>
            @page {
            size: A4;
            margin: 0.5in;
            }

            body {
            padding: 0.5in;
            font-family: 'Noto Sans Bengali', 'SolaimanLipi', 'Kalpurush', 'Arial Unicode MS', sans-serif;
            }

            .logo {
                max-width: 120px;
                max-height: 120px;
            }

            .header {
                text-align: center; /* Add margin to the header for separation */
            }

            td,
            td {
                border: 0.5px solid #dddddd;
                text-align: left;
                padding: 8px;
            }

            tr:nth-child(even) {

                border : 0.5px solid;
            }
            .text-right {
                text-align: right !important;
            }
            table#headerTable,
            table#headerTable td {
                border: none !important;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <table width="100%" cellpadding="0" cellspacing="0" style="border:none; padding: 15px 0;">
                <tr>
                    <td style="width:80px; vertical-align:middle; border:none;">
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/stl/logo-Lakshmipur.png'))) }}"
                            alt="" style="width:100px; display:block;">
                    </td>
                    <td style="vertical-align:middle; text-align:center; border:none;">
                        <h1 class="heading" style="text-transform:uppercase; margin: 0;">{{ __('লক্ষীপুর পৌরসভা কার্যালয়')
                            }}</h1>
                        <h2 style="text-transform:uppercase; margin: 10px 0;">{{ __('লক্ষীপুর') }}</h2>
                        <h3 style="text-transform:uppercase; margin: 10px 0;">KEY PERFORMANCE INDICATORS REPORT</h3>
                        <!-- <h3 style="text-transform:uppercase; margin: 0;">Integrated Municipal Information System</h3> -->
                    </td>
                    <td style="width:80px; border:none;"></td>
                </tr>
            </table>
        </div>

        @foreach($distinctYears as $year)
            <table class="table" width="100%" style="margin-top: 20px; border-collapse: collapse;">
                <tr>
                    <td style="float:right;font-size: 18px; margin: 0; border: none;">Year: {{$year}} </td>
                </tr>
            </table>

            @if (request()->year === "null")
                <table class="table table-bordered " width="100%" style="margin-top: 30px; border-collapse: collapse;">

                    <tr style="background-color: #ddddde;">
                        <td style="letter-spacing: 0.2px; font-size : 18px; ">Indicator</td>
                        <td style="letter-spacing: 0.2px; font-size : 18px; ">Target</td>
                        <td style="letter-spacing: 0.2px; font-size : 18px; ">Achievement</td>
                        @if($keyPerformanceData[0]['serviceprovider'] != null)
                        <td style="letter-spacing: 0.2px; font-size : 18px; ">Service Provider</td>
                        @endif
                    </tr>
                    @php
                            $targetPrinted = false;
                        @endphp
                    @foreach($keyPerformanceData as $data)

                        @if($data['year'] == $year )
                                <tr>
                                    <td style="letter-spacing: 0.2px; font-size : 18px;">{{ $data['indicator'] }}</td>
                                    <td style="letter-spacing: 0.2px; text-align: right; ">{{ $data['target'] }}</td>
                                    <td style="letter-spacing: 0.2px; text-align: right; ">
                                                    @if(empty($data['achievement']))
                                                        0
                                                    @elseif(is_array($data['achievement']) && isset($data['achievement'][0]->time))
                                                        {{ $data['achievement'][0]->time }}
                                                    @else
                                                        {{ $data['achievement'] }}
                                                    @endif
                                    </td>
                                    @if($data['serviceprovider'] != null )
                                     @if(!$targetPrinted)
                                     <td style="letter-spacing: 0.2px; font-size : 18px;"  rowspan="{{ count($keyPerformanceData) }}">
                                        <?php
                                            $serviceId = $data['serviceprovider'];

                                            // Check if $serviceId is set and is a valid integer
                                            if (isset($serviceId) && is_numeric($serviceId)) {
                                                $serviceId = (int)$serviceId; // Explicitly cast to integer

                                                $service = App\Models\Fsm\ServiceProvider::find($serviceId);

                                                if ($service) {
                                                    echo $service->company_name;
                                                } else {
                                                    echo '-';
                                                }
                                                } else {
                                                    echo '-';
                                                } ?>

                                            @php
                                                $targetPrinted = true;
                                            @endphp
                                            </td>
                                        @endif

                                    @endif
                                </tr>
                        @endif
                    @endforeach
                </table>
            @else
                @foreach($distinctKpi as $kpi)
                    <table class="table" width="100%" style="margin-top: 30px; border-collapse: collapse;">
                        <thead><p style="text-align:left;font-size : 19px; font-weight: bold;background-color: #ddddde; padding: 2px"> Indicator: {{$kpi}} </p></thead>
                    </table>
                    <table class="table table-bordered " width="100%" style=" border-collapse: collapse;">
                        <tr>
                            <td style="letter-spacing: 0.2px; font-size : 18px; ">Quarter</td>
                            <td style="letter-spacing: 0.2px; font-size : 18px; ">Target</td>
                            <td style="letter-spacing: 0.2px; font-size : 18px; ">Achievement</td>

                            @if($keyPerformanceData[0]['serviceprovider'] != null)
                                <td style="letter-spacing: 0.2px; font-size : 18px; ">Service Provider</td>
                            @endif
                        </tr>
                        @php
                            $targetPrinted = false;
                            $tragetSP = false;
                        @endphp
                        @foreach($keyPerformanceData as $data)
                            @if($data['year'] == $year && $data['indicator'] == $kpi)
                                    <tr>
                                        <td style="letter-spacing: 0.2px; ">{{ $data['quartername'] }}</td>
                                        @if(!$targetPrinted)
                                            <td style="letter-spacing: 0.2px; text-align: right;" rowspan="{{ count($keyPerformanceData) }}">{{ $data['target'] }}</td>
                                            @php
                                                $targetPrinted = true;
                                            @endphp
                                        @endif
                                        <td style="letter-spacing: 0.2px; text-align: right;">
                                                    @if(empty($data['achievement']))
                                                        0
                                                    @elseif(is_array($data['achievement']) && isset($data['achievement'][0]->time))
                                                        {{ $data['achievement'][0]->time }}
                                                    @else
                                                        {{ $data['achievement'] }}
                                                    @endif
                                        </td>
                                        @if($data['serviceprovider'] != null)

                                        @if(!$tragetSP)
                                        <td style="letter-spacing: 0.2px; font-size : 18px;"  rowspan="{{ count($keyPerformanceData) }}">
                                            <?php
                                            $serviceId = $data['serviceprovider'];

                                            // Check if $serviceId is set and is a valid integer
                                            if (isset($serviceId) && is_numeric($serviceId)) {
                                                $serviceId = (int)$serviceId; // Explicitly cast to integer

                                                $service = App\Models\Fsm\ServiceProvider::find($serviceId);

                                                if ($service) {
                                                    echo $service->company_name;
                                                } else {
                                                    echo '-';
                                                }
                                                } else {
                                                    echo '-';
                                                } ?>

                                            @php
                                                $tragetSP = true;
                                            @endphp

                                        </td>
                                        @endif
                                        @endif
                                    </tr>
                            @endif
                        @endforeach
                    </table>
                @endforeach
            @endif
        @endforeach
    </body>
</html>
