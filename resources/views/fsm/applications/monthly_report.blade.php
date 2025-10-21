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
    }

       

        .logo {
            max-width: 120px;
            max-height: 120px;
        }

        .header {
      text-align: center; /* Add margin to the header for separation */
    }
   

            td,
            th {
                border: 0.5px solid #dddddd;
                text-align: left;
                padding: 8px;
            }

         
            .text-right {
                text-align: right !important;
            }
            /* table#headerTable,
            table#headerTable th {
                border: none !important;
            } */
        </style>
        <title>{{ __('Monthly Application Report') }}</title>

    </head>

    <body>
    <div class="container">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-imis.png'))) }}" class="logo" style=" width: 120px;">
                <div class="header">
                    <h1 class="heading" style="text-transform:uppercase; margin: 0;">{{ __('Municipality') }}</h1>
                    <h2 style="text-transform:uppercase; margin: 10px; ">{{ __('Monthly Application Report') }}</h2>
                    <!-- <h3 style=" text-transform:uppercase; margin: 0;">Integrated Municipal Information System</h3> -->
                </div>
         
            <table class="table" width="100%" style="margin-top: 20px; border-collapse: collapse;">
                <tr>
                    <td style="font-size: 18px; margin: 0; border: none;">{{ __('Month') }}: {{$monthName}}</td>
                    <td style="text-align: right; font-size: 18px; margin: 0; border: none;">{{ __('Year') }}: {{$year}}</td>
                </tr>
            </table>

        </div>
        @if(!$monthWisecount)
        <div style=" margin-top: 30px;letter-spacing: 0.2px; ">
                <div style="text-align: center;"> No Data for Month </div>
        </div>

        @endif
        @foreach($monthWisecount as $operator)
            <div style=" margin-top: 30px;letter-spacing: 0.2px; padding:2px; background-color: #ddddde; ">
                <span style="font-size : 19px; font-weight: bold;">{{ __('Operator Name') }}:</span>
                <span  style="font-size : 19px; font-weight: bold;" >{{$operator->serv_name}}</span>
            </div>
            <table class="table" width="100%" style="margin-top: 20px; border-collapse: collapse;">

                <tr>
                <td style="font-size : 18px; " >{{ __('Nos. Applications Received') }} </td>
                    @if(!$operator->applicationcount)
                    <td style="text-align: right;"> NA</td>
                    @else
                    <td style="text-align: right;"> {{$operator->applicationcount}}</td>
                    @endif
                    </tr>
                <tr>
                    <td style="font-size : 18px; " >{{ __('Nos. Containments Emptied') }} </td>
                    @if(!$operator->emptycount)
                    <td style="text-align: right;"> NA</td>
                    @else
                    <td style="text-align: right; "> {{$operator->emptycount}} </td>
                    @endif
                    
                </tr>
                <tr>
                <td style="font-size: 18px;"> {{ __('Nos. Safe Disposals') }}  </td>
                    @if(!$operator->scount)
                    <td style="text-align: right;"> NA</td>
                    @else
                    <td style="text-align: right;"> {{$operator->scount}}</td>
                    @endif
                    </tr>
                <tr>
                    <td style="font-size: 18px;"> {{ __('Sludge Collected (m³)') }}  </td>
                    @if(!$operator->sludgecount)
                    <td style="text-align: right;"> NA</td>
                    @else
                    <td style="text-align: right;"> {{$operator->sludgecount}}</td>
                    @endif
                    
                </tr>
                <tr>
                <td style="font-size : 18px; ">{{ __('Total Revenue') }}  </td>
                    @if(!$operator->totalcost)
                    <td style="text-align: right;"> NA</td>
                    @else
                    <td style="text-align: right;"> {{number_format($operator->totalcost)}}</td>
                    @endif
                   
                </tr>
         </table>
        @endforeach
        <!-- end for each -->
        <div style="margin-top: 30px; letter-spacing: 0.2px; padding: 2px; background-color: #ddddde;">
                <span style="font-size: 19px; font-weight: bold;">{{ __('Cumulative Data for')}} {{$year}} {{ __('upto')}} {{$monthName}}
                </span>
            </div>

           @if(!$yearCount)
                    <div style=""> No Data</div> 
                @else
               
                <table class="table table-bordered table-striped" width="100%" style="margin-top: 20px; border-collapse: collapse;">

                @foreach($yearCount as $data)

                    <tr>
                        <td style="font-size : 18px; " > {{ __('Nos. Applications Received') }}  </td>
                        @if(!$data->applicationcount)
                        <td style="text-align: right;"> NA</td>
                        @else
                        <td style="text-align: right;"> {{$data->applicationcount}}</td>
                        @endif
                    </tr>
                    <tr>
                        <td style="font-size : 18px; " > {{ __('Nos. Containments Emptied') }}  </td>
                        @if(!$data->emptycount)
                        <td style="text-align: right;"> NA</td>
                        @else
                        <td style="text-align: right;"> {{$data->emptycount}}</td>
                        @endif
                        
                    </tr>
                    <tr>
                        <td style="font-size : 18px; " >  {{ __('Nos. Safe Disposals') }} </td>
                            @if(!$data->scount)
                            <td style="text-align: right;"> NA</td>
                            @else
                            <td style="text-align: right;"> {{$data->scount}}</td>
                            @endif
                    </tr>
                    <tr>
                        <td style="font-size : 18px; " > {{ __('Sludge Collected (m³)') }}  </td>
                        @if(!$data->sludgecount)
                        <td style="text-align: right;"> NA</td>
                        @else
                        <td style="text-align: right;"> {{$data->sludgecount}}</td>
                        @endif
                    </tr>
                    <tr>
                     
                        <td style="font-size : 18px; " > {{ __('Total Revenue') }}  </td>
                        @if(!$data->totalcost)
                        <td style="text-align: right;"> NA</td>
                        @else
                        <td style="text-align: right;"> {{ number_format($data->totalcost) }}</td>
                        @endif
                       
                    </tr>
                    @endforeach
                </table>
        @endif

        <div style="margin-top: 30px; letter-spacing: 0.2px; padding: 2px; background-color: #ddddde;">
                <span style="font-size: 19px; font-weight: bold;">
                    {{ __('Ward Wise Cumulative Data upto')}} {{$monthName}}
                </span>
            </div>

                
        @if(!$wardData)
      
                <div style="text-align: center;"> No Data for Wards </div>
                @else
                <table class="table table-bordered table-striped" width="100%" style="margin-top: 20px; letter-spacing: 0.2px; border-collapse: collapse;">

                <tr>
                    <td style="letter-spacing: 0.2px;" > {{ __('Ward No') }} </td>
                   <td style="letter-spacing: 0.2px;" > {{ __('Nos. Applications Received') }}    </td>
                   <td style="letter-spacing: 0.2px;" >  {{ __('Nos. Containments Emptied') }}   </td>
                   <td style="letter-spacing: 0.2px;" >  {{ __('Nos. Safe Disposals') }}   </td>
                   <td style="letter-spacing: 0.2px;" > {{ __('Sludge Collected (m³)') }}    </td>
                   <td style="letter-spacing: 0.2px;" >  {{ __('Total Revenue') }}   </td>
                </tr>
                @foreach($wardData as $data)
                <tr>
                @if(!$data->award)
                <th style="text-align: right;"> NA</th>
                @else
                <td style="text-align: right;">{{ $data-> award }} </td>
                @endif

                @if(!$data->applicationcount)
                <th style="text-align: right;"> NA</th>
                @else
                <td style="text-align: right;"> {{$data->applicationcount}}</td> 
                @endif
                
                @if(!$data->emptycount)
                <th style="text-align: right;"> NA</th>
                @else
                <td style="text-align: right;"> {{$data->emptycount}}</td>
                @endif
                  
                @if(!$data->scount)
                <th style="text-align: right;"> NA</th>
                @else
                    <td style="text-align: right;"> {{$data->scount}}</td>
                @endif
                @if(!$data->sludgecount)
                <th style="text-align: right;"> NA</th>
                @else
                    <td style="text-align: right;"> {{$data->sludgecount}}</td>
                @endif
            
                @if(!$data->totalcost)
                <th style="text-align: right;"> NA</th>
                @else
                    <td style="text-align: right;"> {{number_format($data->totalcost)}}</td>
                @endif
            

                </tr>
                @endforeach

         </table>
        @endif

    </body>


</html>
