<!-- // Last Modified: 12-03-2026
// Developed By: Streams Tech Ltd.
// Description: Sidebar navigation links and active-state handling for dashboard modules. -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    @if (request()->is('maps'))
    <a href="{{ url('/') }}" class="brand-link">
        <img src="{{ asset('/img/logo-imis.png') }}" alt="Municipality Logo" id="sidebar-logo" style="filter: brightness(0) invert(1) grayscale(1); line-height: .8;
        display:block; margin:0 0.75rem; width:auto; height:48px; max-width:calc(100% - 1.5rem); object-fit:contain">
    </a>
    @else
    <a href="{{ url('/') }}" class="brand-link" id="sidebar-brand-link">
        <img src="{{ asset('/img/logo-imis.png') }}" alt="Municipality Logo" id="sidebar-logo" style="filter: brightness(0) invert(1) grayscale(1); line-height: .8;
        display:block; margin:0 0.6rem; width:auto; height:34px; max-width:calc(100% - 1.2rem); object-fit:contain">
        <img src="{{ asset('/img/logo-imis.png') }}" alt=" Municipality Logo" id="hello-text" style="filter: brightness(0) invert(1) grayscale(1); line-height : .8;
         margin-left: 0.75rem; margin-right: 0.75rem; margin-top:6px; height:48px; width:auto; display: none; ">
    </a>
    @endif
    <div class="sidebar" style='font-family: Open Sans, sans-serif'>
        <nav class="mt-4">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @if(Auth::user()->hasanyPermissionInGroup(['Dashboard','Building Dashboard','Utility Dashboard','FSM Dashboard']) || Auth::user()->hasRole('Super Admin'))
                <li class="nav-item">
                    <a href="{{ url('/') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-house"></i>
                        <p>{{__('Dashboard')}}</p>
                    </a>
                </li>
                @endif

                @if (Auth::user()->hasanyPermissionInGroup(['Building Dashboard','Building Structures','Building Surveys','Low Income Communities', 'Building Info Households']) || Auth::user()->hasRole('Super Admin'))
                <li class="nav-item {{ request()->is('building-info/*','layer-info/low-income-communities') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('building-info/*','layer-info/low-income-communities') ? 'active' : '' }}">
                        <img src="{{ asset('img/svg/imis-icons/buildingIMS.svg') }}" class="nav-icon" alt="Building Icon">
                        <p>
                            {{__('Building IMS')}}<i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(Auth::user()->hasanyPermissionInGroup(['Building Dashboard']) || Auth::user()->hasRole('Super Admin'))
                        <li class="nav-item treeview menu-open">
                            <a href="{{ action('BuildingInfo\BuildingDashboardController@index') }}" class="nav-link {{ request()->is('building-info/buildings/buildingdashboard') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Building Dashboard')}}</p>
                            </a>
                        </li>
                        @endif

                        @can('List Building Structures')
                        <li class="nav-item treeview menu-open">
                            <a href="{{ action('BuildingInfo\BuildingController@index') }}" class="nav-link {{ request()->is('building-info/buildings') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Buildings')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can('List Building Surveys')
                        <li class="nav-item">
                            <a href="{{ action('BuildingInfo\BuildingSurveyController@index') }}" class="nav-link {{ request()->is('building-info/building-surveys') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Building Survey')}}</p>
                            </a>
                        </li>
                        @can('List Households')
                        <li class="nav-item">
                            <a href="{{ action('BuildingInfo\HouseholdController@index') }}" class="nav-link {{ request()->is('building-info/households', 'building-info/households/*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Households')}}</p>
                            </a>
                        </li>
                        @endcan
                        @endcan
                        @can('List Low Income Communities')
                        <li class="nav-item">
                            <a href="{{ action('LayerInfo\LowIncomeCommunityController@index') }}" class="nav-link {{ request()->is('layer-info/low-income-communities') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Low Income Community')}}</p>
                            </a>
                        </li>
                        @endcan

                    </ul>
                </li>
                @endif

                @if (Auth::user()->hasanyPermissionInGroup([
                'FSM Dashboard',
                'Containments',
                'Service Providers',
                'Employee Infos',
                'Desludging Vehicles',
                'Treatment Plants',
                'Treatment Plant Efficiency Standards',
                'Treatment Plant Efficiency Tests',
                'Applications',
                'Emptyings',
                'Sludge Collections',
                'Feedbacks',
                'Help Desks',
                ]) || Auth::user()->hasRole('Super Admin'))
                <li class="nav-item {{ request()->is(
                            'fsm/fsmdashboard',
                            'fsm/containments','fsm/containments/*',
                           'fsm/service-providers', 'fsm/service-providers/*',
                            'fsm/employee-infos/*','fsm/employee-infos',
                            'fsm/desludging-vehicles','fsm/desludging-vehicles/*',
                            'fsm/treatment-plants/*','fsm/treatment-plants',
                            'fsm/treatment-plant-effectiveness/*','fsm/treatment-plant-effectiveness',
                            'fsm/pending-application/*','fsm/pending-application',
                            'fsm/application/*','fsm/application',
                            'fsm/emptying/*','fsm/emptying',
                            'fsm/sludge-collection/*','fsm/sludge-collection',
                            'fsm/feedback','fsm/feedback/*',
                            'fsm/help-desks/*','fsm/help-desks',
                            'fsm/treatment-plant-test/*','fsm/treatment-plant-test','fsm/treatment-plant-performance-test/*','fsm/treatment-plant-performance-test'
                        )
                            ? 'menu-is-opening menu-open'
                            : '' }}">
                    <a href="#" class="nav-link {{ request()->is(
                                'fsm/fsmdashboard',
                                'fsm/containments','fsm/containments/*',
                                'fsm/service-providers', 'fsm/service-providers/*',
                                'fsm/employee-infos/*','fsm/employee-infos',
                                'fsm/desludging-vehicles','fsm/desludging-vehicles/*',
                                'fsm/treatment-plants/*','fsm/treatment-plants',
                                'fsm/treatment-plant-effectiveness/*', 'fsm/treatment-plant-effectiveness',
                                'fsm/pending-application/*','fsm/pending-application',
                                'fsm/application/*','fsm/application',
                                'fsm/emptying/*','fsm/emptying',
                                'fsm/sludge-collection','fsm/sludge-collection/*',
                                'fsm/feedback','fsm/feedback/*',
                                'fsm/help-desks/*','fsm/help-desks',
                                'fsm/treatment-plant-test/*','fsm/treatment-plant-test','fsm/treatment-plant-performance-test/*','fsm/treatment-plant-performance-test'
                            )
                                ? 'active'
                                : '' }}">
                        <img src="{{ asset('img/svg/imis-icons/fecalSludgeIMS.svg') }}" class="nav-icon" alt="Fecal Sludge Icon">

                        <p>
                            {{__('Fecal Sludge IMS')}}<i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(Auth::user()->hasanyPermissionInGroup(['FSM Dashboard']) || Auth::user()->hasRole('Super Admin'))
                        <li class="nav-item ">
                            <a href="{{ action('Fsm\FsmDashboardController@index') }}" class="nav-link {{ request()->is('fsm/fsmdashboard') ? 'active' : '' }}">
                                <i class="nav-icon fa-solid fa-gauge"></i>
                                <p>{{__('FSM Dashboard')}}</p>
                            </a>
                        </li>
                        @endif

                        @can('List Containments')
                        <li class="nav-item {{ request()->is('fsm/containments','fsm/containments/*') ? 'menu-is-opening menu-open' : '' }}"><a href="#" class="nav-link {{ request()->is('fsm/containments/*', 'fsm/containments') ? 'active subnav' : '' }}">

                                <i class="nav-icon fa-regular fa-building"></i>
                                <p>
                                    {{__('Containment IMS')}} <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @can('List Containments')
                                <li class="nav-item">
                                    <a href="{{ action('Fsm\ContainmentController@index') }}" class="nav-link {{ request()->is('fsm/containments','fsm/containments/*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{__('Containments')}}</p>
                                    </a>
                                </li>
                                @endcan

                            </ul>
                        </li>
                        @endcan

                        @if (auth()->user()->can('List Service Providers') ||
                        auth()->user()->can('List Employee Infos') ||
                        auth()->user()->can('List Desludging Vehicles'))
                        <li class="nav-item {{ request()->is('fsm/service-providers', 'fsm/service-providers/*', 'fsm/employee-infos/*','fsm/employee-infos', 'fsm/desludging-vehicles','fsm/desludging-vehicles/*') ? 'menu-is-opening menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->is('fsm/service-providers', 'fsm/service-providers/*', 'fsm/employee-infos/*','fsm/employee-infos', 'fsm/desludging-vehicles','fsm/desludging-vehicles/*') ? 'active subnav' : '' }}">
                                <i class="nav-icon fa-regular fa-building"></i>
                                <p>
                                   {{__('Service Provider IMS')}} <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @can('List Service Providers')
                                <li class="nav-item">
                                    <a href="{{ action('Fsm\ServiceProviderController@index') }}" class="nav-link {{ request()->is('fsm/service-providers', 'fsm/service-providers/*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{__('Service Providers')}}</p>
                                    </a>
                                </li>
                                @endcan
                                @can('List Employee Infos')
                                <li class="nav-item">
                                    <a href="{{ action('Fsm\EmployeeInfoController@index') }}" class="nav-link {{ request()->is('fsm/employee-infos/*','fsm/employee-infos') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{__('Employee Information')}}</p>
                                    </a>
                                </li>
                                @endcan
                                @can('List Desludging Vehicles')
                                <li class="nav-item">
                                    <a href="{{ action('Fsm\VacutugTypeController@index') }}" class="nav-link {{ request()->is('fsm/desludging-vehicles','fsm/desludging-vehicles/*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{__('Desludging Vehicles')}}</p>
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endif
                        @if (auth()->user()->can('List Treatment Plants') || auth()->user()->can('List Treatment Plant Efficiency Tests') || auth()->user()->can('View Treatment Plant Efficiency Standard'))
                        <li class="nav-item {{ request()->is('fsm/treatment-plants', 'fsm/treatment-plants/*','fsm/treatment-plant-test','fsm/treatment-plant-test/*','fsm/treatment-plant-performance-test/*','fsm/treatment-plant-performance-test',) ? 'menu-is-opening menu-open' : '' }}">
                            <a href="#" class="nav-link  {{ request()->is('fsm/treatment-plants','fsm/treatment-plants/*', 'fsm/treatment-plant-test/*','fsm/treatment-plant-test','fsm/treatment-plant-performance-test/*','fsm/treatment-plant-performance-test') ? 'active subnav' : '' }}">
                                <i class="nav-icon fa-regular fa-building"></i>
                                <p>
                                    {{__('Treatment Plant IMS')}} <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @can('List Treatment Plants')
                                <li class="nav-item">
                                    <a href="{{ action('Fsm\TreatmentPlantController@index') }}" class="nav-link {{ request()->is('fsm/treatment-plants/*','fsm/treatment-plants') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{__('Treatment Plants')}}</p>
                                    </a>
                                </li>
                                    <li class="nav-item">
                                        <a href="{{ action('Fsm\TreatmentplantPerformanceTestController@index') }}" class="nav-link {{ request()->is('fsm/treatment-plant-performance-test/*','fsm/treatment-plant-performance-test') ? 'active' : '' }}">
                                        <i class="nav-icon fa-solid fa-gear "style="font-size: 14px;"></i>
                                            <p>{{__('Performance')}}<br>{{__('Efficiency Standards')}}</p>
                                        </a>
                                    </li>
                                @endcan
                                {{-- @can('List Treatment Plant Efficiency Tests')
                                        <li class="nav-item">
                                            <a href="{{ action('Fsm\TreatmentPlantEffectivenessController@index') }}"
                                class="nav-link {{ request()->is('fsm/treatment-plant-effectiveness/*','fsm/treatment-plant-effectiveness') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Treatment Plant Efficiency Standard</p>
                                </a>
                        </li>
                        @endcan --}}

                        @can('List Treatment Plant Efficiency Tests')
                        <li class="nav-item">
                            <a href="{{ action('Fsm\TreatmentPlantTestController@index') }}" class="nav-link {{ request()->is('fsm/treatment-plant-test','fsm/treatment-plant-test/*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Performance')}}<br>{{__('Efficiency Test')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endif

                @if (Auth::user()->hasAnyPermission(
                'List Applications',
                'List Emptyings',
                'List Feedbacks',
                'List Sludge Collections',
                'List Help Desks') || Auth::user()->hasRole('Super Admin'))
                <li class="nav-item  {{ request()->is('fsm/pending-application/*', 'fsm/pending-application', 'fsm/application/*', 'fsm/application','fsm/emptying', 'fsm/emptying/*','fsm/sludge-collection/*','fsm/sludge-collection', 'fsm/feedback/*','fsm/feedback', 'fsm/help-desks/*','fsm/help-desks') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('fsm/pending-application/*', 'fsm/pending-application', 'fsm/application/*', 'fsm/application','fsm/emptying', 'fsm/sludge-collection/*','fsm/sludge-collection', 'fsm/feedback/*','fsm/feedback', 'fsm/help-desks/*','fsm/help-desks') ? 'active subnav' : '' }}">
                        <i class="nav-icon fa-regular fa-building"></i>
                        <p>
                            {{__('Emptying Service IMS')}} <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @can('List Applications')
                        <li class="nav-item">
                            <a href="{{ route('pending-application.index') }}" class="nav-link {{ request()->is('fsm/pending-application/*','fsm/pending-application') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Pending Application')}}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('application.index') }}" class="nav-link {{ request()->is('fsm/application/*','fsm/application') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Application')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can('List Emptyings')
                        <li class="nav-item">
                            <a href="{{ route('emptying.index') }}" class="nav-link {{ request()->is('fsm/emptying','fsm/emptying/*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Emptying')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can('List Sludge Collections')
                        <li class="nav-item">
                            <a href="{{ route('sludge-collection.index') }}" class="nav-link {{ request()->is('fsm/sludge-collection/*','fsm/sludge-collection') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Sludge Collections')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can('List Feedbacks')
                        <li class="nav-item">
                            <a href="{{ action('Fsm\FeedbackController@index') }}" class="nav-link {{ request()->is('fsm/feedback/*','fsm/feedback') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Feedbacks')}}</p>
                            </a>
                        </li>
                        @endcan
                        @can('List Help Desks')
                        <li class="nav-item">
                            <a href="{{ action('Fsm\HelpDeskController@index') }}" class="nav-link {{ request()->is('fsm/help-desks/*','fsm/help-desks') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>{{__('Help Desks')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endif
            </ul>
            </li>
            @endif

            @if(Auth::user()->hasanyPermissionInGroup(['Sewer Connection']) || Auth::user()->hasRole('Super Admin'))
            <li class="nav-item">
                <a href="{{ action('SewerConnection\SewerConnectionController@index') }}" class="nav-link {{ request()->is('sewerconnection/sewerconnection') ? 'active' : '' }}">
                <img src="{{ asset('img/svg/imis-icons/sewerConnectionIMS.svg')}}" class="nav-icon" alt="Sewer Connection Icon">
                    <p>{{__('Sewer Connection IMS')}}</p>
                </a>
            </li>
            @endif

            @if(Auth::user()->hasanyPermissionInGroup(['PT/CT Toilets','PT Users Logs']) || Auth::user()->hasRole('Super Admin'))
            <li class="nav-item {{ request()->is('fsm/ctpt', 'fsm/ctpt/*','fsm/ctpt-users/*','fsm/ctpt-users') ? 'menu-is-opening menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->is('fsm/ctpt','fsm/ctpt/*', 'fsm/ctpt-users/*','fsm/ctpt-users') ? 'active' : '' }}">
                    <img src="{{ asset('img/svg/imis-icons/ptctIMS.svg')}}" class="nav-icon" alt="PTCT  Icon">
                    <p>
                        {{__('PT/CT IMS')}}<i class="right fas fa-angle-left"></i>
                    </p>
                </a>

                <ul class="nav nav-treeview">
                    @can('List PT/CT Toilets')
                    <li class="nav-item">

                        <a href="{{ action('Fsm\CtptController@index') }}" class="nav-link {{ request()->is('fsm/ctpt/*','fsm/ctpt') ? 'active' : '' }}">
                            <i class="nav-icon far fa-circle nav-icon"></i>
                            <p>{{__('Public / Community Toilets')}}</p>
                        </a>
                    </li>
                    @endcan
                    @can('List PT Users Log')
                    <li class="nav-item">
                        <a href="{{ action('Fsm\CtptUserController@index') }}" class="nav-link {{ request()->is('fsm/ctpt-users/*','fsm/ctpt-users') ? 'active' : '' }}">
                            <i class="nav-icon far fa-circle nav-icon"></i>
                            <p>{{__('PT Users Log')}}</p>
                        </a>
                    </li>
                    @endcan
                </ul>

            </li>
            @endif
            @if(Auth::user()->hasanyPermissionInGroup(['CWIS','KPI Dashboard','KPI Target','NSD Setting']) || Auth::user()->hasRole('Super Admin'))
            <li class="nav-item {{ request()->is('cwis/*', 'fsm/kpi-dashboard', 'fsm/kpi-targets/*','fsm/kpi-targets','fsm/cwis-setting/*','fsm/cwis-setting',
                'fsm/nsd-setting','fsm/nsd-setting/*') ? 'menu-is-opening menu-open' : '' }}">
                <a href="" class="nav-link {{ request()->is('cwis/*', 'fsm/kpi-dashboard', 'fsm/kpi-targets/*','fsm/kpi-targets','fsm/cwis-setting/*','fsm/cwis-setting', 'fsm/nsd-setting', 'fsm/nsd-setting/*') ? 'active' : '' }}">
                <img src="{{ asset('img/svg/imis-icons/cwis.svg')}}" class="nav-icon" alt="CWIS  Icon">
                    <p> {{__('CWIS IMS')}}<i class="right fas fa-angle-left"></i> </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('List CWIS')
                    <li class="nav-item">
                        <a href="{{ url('cwis/cwis/getall') }}" id="cwis-link" class="nav-link {{ request()->is('cwis/cwis/getall') ? 'active' : '' }}">
                            <i class="nav-icon far fa-circle nav-icon"></i>
                            <p>{{__('CWIS Dashboard')}}</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ action('Cwis\CwisMneController@index') }}" class="nav-link {{ request()->is('cwis/cwis/cwis-df-mne') ? 'active' : '' }}">
                            <i class="nav-icon far fa-circle nav-icon"></i>
                            <p>{{__('CWIS Generator')}}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ action('Fsm\CwisSettingController@index') }}" class="nav-link {{ request()->is('fsm/cwis-setting','fsm/cwis-setting/*') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-gear "style="font-size: 14px;"></i>
                            <p>{{__('CWIS Setting')}}</p>
                        </a>
                    </li>
                    @endcan

                    @if(Auth::user()->hasanyPermissionInGroup(['KPI Dashboard']) || Auth::user()->hasRole('Super Admin'))
                    @can('LisT NSD Setting')
                    <li class="nav-item">
                        <a href="{{ action('Fsm\NsdSettingController@index') }}" class="nav-link {{ request()->is('fsm/nsd-setting','fsm/nsd-setting/*') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-gear "style="font-size: 14px;"></i>
                            <p>NSD Integration Setting</p>
                        </a>
                    </li>
                    @endcan
                    @endif

                    @if(Auth::user()->hasanyPermissionInGroup(['KPI Dashboard']) || Auth::user()->hasRole('Super Admin'))
                    <li class="nav-item">
                            <a href="{{ action('Fsm\KpiDashboardController@index') }}" class="nav-link {{ request()->is('fsm/kpi-dashboard') ? 'active' : '' }}">
                                <i class="nav-icon far fa-circle nav-icon"></i>
                                <p>{{__('KPI Dashboard')}} </p>
                            </a>
                        </li>
                    @endif

                    @can('List KPI Target')
                    <li class="nav-item">
                        <a href="{{ action('Fsm\KpiTargetController@index') }}" class="nav-link {{ request()->is('fsm/kpi-targets/*','fsm/kpi-targets') ? 'active' : '' }}">
                        <i class="nav-icon fa-solid fa-gear "style="font-size: 14px;"></i>
                            <p>{{__('KPI Target')}} </p>
                        </a>
                    </li>
                    @endcan

                </ul>
            </li>
            @endif

            @if(Auth::user()->hasanyPermissionInGroup(['Utility Dashboard','Roads','Sewers','WaterSupply Network','Drain']) || Auth::user()->hasRole('Super Admin'))
            <li class="nav-item {{ request()->is('utilityinfo/*') ? 'menu-is-opening menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->is('utilityinfo/*') ? 'active' : '' }}">
                    <img src="{{ asset('img/svg/imis-icons/utilityIMS.svg')}}" class="nav-icon" alt="Utility Icon">
                    <p>
                        {{__('Utility IMS')}}<i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @if(Auth::user()->hasanyPermissionInGroup(['Utility Dashboard']) || Auth::user()->hasRole('Super Admin'))
                    <li class="nav-item">
                        <a href="{{ action('UtilityInfo\UtilityDashboardController@index') }}" class="nav-link {{ request()->is('utilityinfo/utilitydashboard') ? 'active' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('Utility Dashboard')}}</p>
                        </a>
                    </li>
                    @endif

                    @can('List Roadlines')
                    <li class="nav-item">
                        <a href="{{ action('UtilityInfo\RoadlineController@index') }}" class="nav-link {{ request()->is('utilityinfo/roadlines','utilityinfo/roadlines/*') ? 'active' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('Road Network')}} </p>
                        </a>
                    </li>
                    @endcan
                    @can('List Sewers')
                    <li class="nav-item">
                        <a href="{{ action('UtilityInfo\SewerLineController@index') }}" class="nav-link {{ request()->is('utilityinfo/sewerlines/*','utilityinfo/sewerlines') ? 'active' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('Sewer Network')}} </p>
                        </a>
                    </li>
                    @endcan
                    @can('List WaterSupply Network')
                    <li class="nav-item">
                        <a href="{{ action('UtilityInfo\WaterSupplysController@index') }}" class="nav-link {{ request()->is('utilityinfo/watersupplys/*','utilityinfo/watersupplys') ? 'active' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('Water Supply Network')}} </p>
                        </a>
                    </li>
                    @endcan
                    @can('List Drains')
                    <li class="nav-item">
                        <a href="{{ action('UtilityInfo\DrainController@index') }}" class="nav-link {{ request()->is('utilityinfo/drains/*','utilityinfo/drains') ? 'active' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('Drain Network')}} </p>
                        </a>
                    </li>
                    @endcan

                </ul>
            </li>
            @endif

            @if(Auth::user()->hasanyPermissionInGroup(['Sw Service Payment', 'SW Service Provider Organizations', 'SW Service Provider Work Types', 'SW Waste Bin Types', 'SW Service Provider Workers', 'SW Service Provider Vehicle Types', 'SW Service Provider Waste Types', 'SW Service Provider Landfill Types', 'SW Service Provider Vehicles', 'SW Service Facility Landfills', 'SW Service Facility STS', 'SW Bill Collection Payments', 'SW Billing Status', 'SW Complaints', 'SW Attendance Logs', 'SW STS Logs', 'SW Landfill Logs', 'SW Waste Processing', 'SW Dashboard and KPIs']) || Auth::user()->hasRole('Super Admin'))
            <li class="nav-item {{ request()->is('swm-payment', 'swm-payment/*', 'swm/dashboard-kpis', 'swm/dashboard-kpis/*', 'swm/service-providers/*', 'swm/service-facilities/*', 'swm/service-coverage/*', 'swm/complaints', 'swm/complaints/*', 'swm/service-management/*') ? 'menu-is-opening menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->is('swm-payment', 'swm-payment/*', 'swm/dashboard-kpis', 'swm/dashboard-kpis/*', 'swm/service-providers/*', 'swm/service-facilities/*', 'swm/service-coverage/*', 'swm/complaints', 'swm/complaints/*', 'swm/service-management/*') ? 'active' : '' }}">
                    <img src="{{ asset('img/svg/imis-icons/swmPaymentStatus.svg')}}" class="nav-icon">
                    <p>
                        {{__('Solid Waste IMS')}} <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    {{-- @can('List SW Service Payment Collection')
                    <li class="nav-item">
                        <a href="{{ route('swm-payment.index') }}" class="nav-link {{ request()->is('swm-payment', 'swm-payment/*') ? 'active' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('SW Service Payment')}}</p>
                        </a>
                    </li>
                    @endcan --}}
                    @can('List SW Dashboard and KPIs')
                    <li class="nav-item">
                        <a href="{{ route('swm.dashboard-kpis.index') }}" class="nav-link {{ request()->is('swm/dashboard-kpis', 'swm/dashboard-kpis/*') ? 'active' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{ __('Dashboard and KPIs') }}</p>
                        </a>
                    </li>
                    @endcan
                    @if(Auth::user()->can('List SW Organizations') || Auth::user()->can('List SW Workers'))
                    <li class="nav-item {{ request()->is('swm/service-providers/organizations*', 'swm/service-providers/workers*') ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('swm/service-providers/organizations*', 'swm/service-providers/workers*') ? 'active subnav' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('Service Providers')}} <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('List SW Organizations')
                            <li class="nav-item">
                                <a href="{{ action('Swm\OrganizationController@index') }}" class="nav-link {{ request()->is('swm/service-providers/organizations', 'swm/service-providers/organizations/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Organizations')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Workers')
                            <li class="nav-item">
                                <a href="{{ action('Swm\WorkerController@index') }}" class="nav-link {{ request()->is('swm/service-providers/workers', 'swm/service-providers/workers/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Workers')}}</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif
                    
                    @if(Auth::user()->can('List SW Landfills') || Auth::user()->can('List SW STS') || Auth::user()->can('List SW Waste Bins') || Auth::user()->can('List SW Vehicles'))
                    <li class="nav-item {{ request()->is('swm/service-facilities/*', 'swm/service-providers/vehicles', 'swm/service-providers/vehicles/*') ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('swm/service-facilities/*', 'swm/service-providers/vehicles', 'swm/service-providers/vehicles/*') ? 'active subnav' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('Service Facilities')}} <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('List SW Waste Bins')
                            <li class="nav-item">
                                <a href="{{ action('Swm\WasteBinController@index') }}" class="nav-link {{ request()->is('swm/service-facilities/waste-bins', 'swm/service-facilities/waste-bins/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Waste Bins')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Vehicles')
                            <li class="nav-item">
                                <a href="{{ action('Swm\VehicleController@index') }}" class="nav-link {{ request()->is('swm/service-providers/vehicles', 'swm/service-providers/vehicles/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Vehicles')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW STS')
                            <li class="nav-item">
                                <a href="{{ action('Swm\StsController@index') }}" class="nav-link {{ request()->is('swm/service-facilities/sts', 'swm/service-facilities/sts/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('STSs')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Landfills')
                            <li class="nav-item">
                                <a href="{{ action('Swm\LandfillController@index') }}" class="nav-link {{ request()->is('swm/service-facilities/landfills', 'swm/service-facilities/landfills/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Landfills')}}</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif
                    @if(Auth::user()->can('List SW Attendance Logs') || Auth::user()->can('List SW STS Logs') || Auth::user()->can('List SW Landfill Logs') || Auth::user()->can('List SW Waste Processing'))
                    <li class="nav-item {{ request()->is('swm/service-management/*') ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('swm/service-management/*') ? 'active subnav' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('Service Management')}} <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('List SW Attendance Logs')
                            <li class="nav-item">
                                <a href="{{ route('swm.attendance-logs.index') }}" class="nav-link {{ request()->is('swm/service-management/attendance-logs', 'swm/service-management/attendance-logs/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Attendance')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW STS Logs')
                            <li class="nav-item">
                                <a href="{{ route('swm.sts-logs.index') }}" class="nav-link {{ request()->is('swm/service-management/sts-logs', 'swm/service-management/sts-logs/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('STS Loading')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Landfill Logs')
                            <li class="nav-item">
                                <a href="{{ route('swm.landfill-logs.index') }}" class="nav-link {{ request()->is('swm/service-management/landfill-logs', 'swm/service-management/landfill-logs/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Landfill Loading')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Waste Processing')
                            <li class="nav-item">
                                <a href="{{ route('swm.waste-processing.index') }}" class="nav-link {{ request()->is('swm/service-management/waste-processing', 'swm/service-management/waste-processing/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Waste Processing')}}</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif
                    @if(Auth::user()->can('List SW Bill Collection Payments') || Auth::user()->can('List SW Billing Status'))
                    <li class="nav-item {{ request()->is('swm/service-coverage/bill-collection', 'swm/service-coverage/bill-collection/*') ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('swm/service-coverage/bill-collection', 'swm/service-coverage/bill-collection/*') ? 'active subnav' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{ __('Billing') }} <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('List SW Bill Collection Payments')
                            <li class="nav-item">
                                <a href="{{ route('swm.bill-collection-payments.index') }}" class="nav-link {{ request()->is('swm/service-coverage/bill-collection/payments', 'swm/service-coverage/bill-collection/payments/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{ __('Payments') }}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Billing Status')
                            <li class="nav-item">
                                <a href="{{ route('swm.billing-status.index') }}" class="nav-link {{ request()->is('swm/service-coverage/bill-collection/billing-status', 'swm/service-coverage/bill-collection/billing-status/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{ __('Billing Status') }}</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif
                    @can('List SW Complaints')
                    <li class="nav-item">
                        <a href="{{ route('swm.complaints.index') }}" class="nav-link {{ request()->is('swm/complaints', 'swm/complaints/*') ? 'active' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{ __('Complaints') }}</p>
                        </a>
                    </li>
                    @endcan
                    @if(Auth::user()->can('List SW Work Types') || Auth::user()->can('List SW Vehicle Types') || Auth::user()->can('List SW Waste Types') || Auth::user()->can('List SW Landfill Types') || Auth::user()->can('List SW Waste Bin Types') || Auth::user()->can('List SW Organization Types') || Auth::user()->can('View SW Per Capita Generation Setting'))
                    <li class="nav-item {{ request()->is('swm/service-providers/work-types*', 'swm/service-providers/vehicle-types*', 'swm/service-providers/waste-types*', 'swm/service-providers/landfill-types*', 'swm/service-providers/waste-bin-types*', 'swm/service-providers/organization-types*', 'swm/settings/per-capita-sw-generation*') ? 'menu-is-opening menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('swm/service-providers/work-types*', 'swm/service-providers/vehicle-types*', 'swm/service-providers/waste-types*', 'swm/service-providers/landfill-types*', 'swm/service-providers/waste-bin-types*', 'swm/service-providers/organization-types*', 'swm/settings/per-capita-sw-generation*') ? 'active subnav' : '' }}">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('SW Module Settings')}} <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('List SW Work Types')
                            <li class="nav-item">
                                <a href="{{ action('Swm\WorkTypeController@index') }}" class="nav-link {{ request()->is('swm/service-providers/work-types', 'swm/service-providers/work-types/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Worker Type')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Vehicle Types')
                            <li class="nav-item">
                                <a href="{{ action('Swm\VehicleTypeController@index') }}" class="nav-link {{ request()->is('swm/service-providers/vehicle-types', 'swm/service-providers/vehicle-types/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Vehicle Type')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Waste Types')
                            <li class="nav-item">
                                <a href="{{ action('Swm\WasteTypeController@index') }}" class="nav-link {{ request()->is('swm/service-providers/waste-types', 'swm/service-providers/waste-types/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Waste Type')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Landfill Types')
                            <li class="nav-item">
                                <a href="{{ action('Swm\LandfillTypeController@index') }}" class="nav-link {{ request()->is('swm/service-providers/landfill-types', 'swm/service-providers/landfill-types/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Landfill Type')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Waste Bin Types')
                            <li class="nav-item">
                                <a href="{{ action('Swm\WasteBinTypeController@index') }}" class="nav-link {{ request()->is('swm/service-providers/waste-bin-types', 'swm/service-providers/waste-bin-types/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Waste Bin Type')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List SW Organization Types')
                            <li class="nav-item">
                                <a href="{{ action('Swm\OrganizationTypeController@index') }}" class="nav-link {{ request()->is('swm/service-providers/organization-types', 'swm/service-providers/organization-types/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Organization Type')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('Waste Generation Setting')
                            <li class="nav-item">
                                <a href="{{ route('swm.settings.per-capita-sw-generation.edit') }}" class="nav-link {{ request()->is('swm/settings/per-capita-sw-generation', 'swm/settings/per-capita-sw-generation/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{ __('Waste Generation') }}</p>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            @if(Auth::user()->hasanyPermissionInGroup(['Property Tax Collection ISS']) || Auth::user()->hasRole('Super Admin'))
            <li class="nav-item">
                <a href="{{ route('tax-payment.index') }}" class="nav-link {{ request()->is('tax-payment') ? 'active' : '' }}">
                    <img src="{{ asset('img/svg/imis-icons/propertyTaxCollectionIMS.svg')}}" class="nav-icon">
                    <p>
                        {{__('Property Tax Collection ISS')}}
                    </p>
                </a>
            </li>
            @endif

            @if(Auth::user()->hasanyPermissionInGroup(['Water Supply ISS']) || Auth::user()->hasRole('Super Admin'))
            <li class="nav-item">
                <a href="{{ route('watersupply-payment.index') }}" class="nav-link {{ request()->is('watersupply-payment') ? 'active' : '' }}">
                    <img src="{{ asset('img/svg/imis-icons/watersupplyISS.svg')}}" class="nav-icon" alt="Water Supply ISS Icon">
                    <p>
                        {{__('Water Supply ISS')}}
                    </p>
                </a>

            </li>
            @endif

            @if(Auth::user()->hasanyPermissionInGroup(['Data Export','Maps']) || Auth::user()->hasRole('Super Admin'))
            <li class="nav-item  {{ request()->is('export-shp-kml', 'maps') ? 'menu-is-opening menu-open' : '' }}">
                <a href="#" class="nav-link  {{ request()->is('export-shp-kml', 'maps') ? 'active' : '' }}">
                    <img src="{{ asset('img/svg/imis-icons/urbanManagementDSS.svg')}}" class="nav-icon" alt="Urban Management DSS">
                    <p>
                        {{__('Urban Management DSS')}} <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    @can('Export Data in Urban Management')
                    <li class="nav-item">
                        <a href="{{ action('ExportShpKmlController@index') }}" class="nav-link {{ request()->is('export-shp-kml') ? 'active' : '' }}">
                            <i class="nav-icon far fa-circle nav-icon"></i>
                            <p>{{__('Export Data')}} </p>
                        </a>
                    </li>
                    @endcan

                    <li class="nav-item">
                        <a href="{{ action('MapsController@index') }}" class="nav-link {{ request()->is('maps') ? 'active' : '' }}">
                            <i class="nav-icon far fa-circle nav-icon"></i>
                            <p>{{__('Map Feature')}} </p>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            @if(Auth::user()->hasanyPermissionInGroup(['Water Samples','Hotspots','Yearly Waterborne Cases']) || Auth::user()->hasRole('Super Admin'))
            <li class="nav-item {{ request()->is('publichealth/hotspots/*','publichealth/hotspots', 'publichealth/waterborne/*','publichealth/waterborne','publichealth/water-samples/*','publichealth/water-samples') ? 'menu-is-opening menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->is('publichealth/hotspots/*','publichealth/hotspots','publichealth/waterborne/*','publichealth/waterborne' ,'publichealth/water-samples/*','publichealth/water-samples') ? 'active' : '' }}">
                    <img src="{{ asset('img/svg/imis-icons/publicHealthISS.svg')}}" class="nav-icon" alt="Fecal Sludge Icon">
                    <p>
                        {{__('Public Health ISS')}} <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
            @can('List Water Samples')
            <li class="nav-item">
                <a href="{{ action('PublicHealth\WaterSamplesController@index') }}" class="nav-link {{ request()->is('publichealth/water-samples/*','publichealth/water-samples') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>{{__('Water Samples')}}</p>
                </a>
            </li>
            @endcan
            @can('List Hotspot Identifications')
            <li class="nav-item">
                <a href="{{ action('PublicHealth\HotspotController@index') }}" class="nav-link {{ request()->is('publichealth/hotspots/*','publichealth/hotspots') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>{{__('Waterborne Hotspot')}} </p>
                </a>
            </li>
            @endcan
            @can('List Yearly Waterborne Cases')
            <li class="nav-item">
                <a href="{{ action('PublicHealth\YearlyWaterborneController@index') }}" class="nav-link {{ request()->is('publichealth/waterborne/*','publichealth/waterborne') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>{{__('Waterborne Cases Information')}} </p>
                </a>
            </li>
            @endcan

            </ul>
            </li>
            @endif

            @if(Auth::user()->hasanyPermissionInGroup(['Users','Roles','Language']) || Auth::user()->hasRole('Super Admin'))
            <li class="nav-item {{ request()->is('auth/*','language/*') ? 'menu-is-opening menu-open' : '' }}">
                <a href="#" class="nav-link {{ request()->is('auth/*','language/*') ? 'active' : '' }}">
                    <i class="nav-icon fa-solid fa-gear"></i>
                    <p>
                        {{__('Settings')}}<i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                                      <li class="nav-item {{ request()->is('auth/*') ? 'menu-is-opening menu-open' : '' }}"><a href="#" class="nav-link {{ request()->is('auth/*') ? 'active subnav' : '' }}">
                            <i class="fa-solid fa-users"></i>
                            <p>
                                {{__('User Information Management')}}<i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('List Users')
                            <li class="nav-item">
                                <a href="{{ action('Auth\UserController@index') }}" class="nav-link {{ request()->is('auth/users','auth/users/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Users')}}</p>
                                </a>
                            </li>
                            @endcan
                            @can('List Roles')
                            <li class="nav-item">
                                <a href="{{ action('Auth\RoleController@index') }}" class="nav-link {{ request()->is('auth/roles','auth/roles/*') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>{{__('Roles')}}</p>
                                </a>
                            </li>
                            @endcan

                        </ul>
                    </li>

                </ul>
                <ul class="nav nav-treeview">

                    @can('List Languages')
                    <li class="nav-item">
                        <a href="{{ action('Language\LanguageController@index') }}" class="nav-link {{ request()->is('language/*') ? 'active' : '' }}">
                            <i class="fa-solid fa-language"></i>
                            <p>{{__('Languages')}}</p>
                        </a>
                    </li>
                    @endcan
                </ul>

            </li>
            @endif

            </ul>
        </nav>

    </div>

</aside>
<script>
    var SIDEBAR_STATE_KEY = 'imis_sidebar_collapsed';

    function isDesktopSidebarLayout() {
        return window.matchMedia('(min-width: 1200px)').matches;
    }

    function normalizeMobileSidebar() {
        if (isDesktopSidebarLayout()) {
            return;
        }
        document.body.classList.add('sidebar-collapse');
        document.body.classList.remove('sidebar-open');
    }

    function syncSidebarVisualState() {
        var logo = document.getElementById('sidebar-logo');
        var helloText = document.getElementById('hello-text');
        var isCollapsed = document.body.classList.contains('sidebar-collapse');

        if (!logo || !helloText) {
            return;
        }

        if (isCollapsed) {
            // Collapsed sidebar: keep compact logo visible.
            logo.style.display = 'inline';
            helloText.style.display = 'none';
        } else {
            // Expanded sidebar: show the larger branding mark.
            logo.style.display = 'none';
            helloText.style.display = 'inline';
        }
    }

    function persistSidebarState() {
        if (isDesktopSidebarLayout()) {
            var isCollapsed = document.body.classList.contains('sidebar-collapse');
            localStorage.setItem(SIDEBAR_STATE_KEY, isCollapsed ? '1' : '0');
        }
        syncSidebarVisualState();
    }

    function restoreSidebarState() {
        if (!isDesktopSidebarLayout()) {
            return;
        }
        var saved = localStorage.getItem(SIDEBAR_STATE_KEY);
        if (saved === '1') {
            document.body.classList.add('sidebar-collapse');
        } else if (saved === '0') {
            document.body.classList.remove('sidebar-collapse');
        }
        syncSidebarVisualState();
    }

    function toggleElements() {
        // Wait until AdminLTE toggles the sidebar class.
        setTimeout(persistSidebarState, 0);
    }

    document.addEventListener('DOMContentLoaded', function () {
        restoreSidebarState();
        normalizeMobileSidebar();
        syncSidebarVisualState();
        document.addEventListener('collapsed.lte.pushmenu', persistSidebarState);
        document.addEventListener('shown.lte.pushmenu', persistSidebarState);
    });

    window.addEventListener('resize', function () {
        if (!isDesktopSidebarLayout()) {
            normalizeMobileSidebar();
        } else {
            restoreSidebarState();
        }
        syncSidebarVisualState();
    });
</script>
