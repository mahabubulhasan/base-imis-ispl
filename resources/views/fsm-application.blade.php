<!-- Last Modified Date: 31-08-2025
Developed By: Streamstech Ltd.   -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>IMIS-Homepage</title>
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Roboto:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i&display=swap"
        rel="stylesheet" />

    <!-- Vendor CSS Files -->
    <!-- <link href="{{ asset('landingpage/vendor/aos/aos.css') }}" rel="stylesheet" /> -->
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">

    <style>
        .animated-success {
            animation: successPulse 2s ease-in-out;
        }

        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .alert-success {
            font-weight: 500;
        }

        .alert-success i {
            color: #155724;
        }
    </style>

</head>

<body>
    <!-- ======= Top Bar ======= -->
    <section id="topbar" class="d-flex align-items-center">
        <div class="container d-flex justify-content-center justify-content-md-between">
            <div class="contact-info d-flex align-items-center">
                <div class="d-flex p-4">
                    <i class="fas fa-envelope d-flex align-items-center"></i>
                    <span class="p-2">imis@ait.asia</span>
                </div>

                <div class="d-flex">
                    <i class="fas fa-phone d-flex align-items-center "></i>
                    <span class="p-2"> </span>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= Header ======= -->
    <header id="header" class="d-flex align-items-center">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{URL::to('/')}}" class="logo"><img src="{{ asset('img/logo-imis.png') }}" alt="IMIS LOGO" /></a>

            <nav id="navbar" class="navbar-landing">
                <ul>
                    <li><a class="nav-link scrollto" href="/#hero">Home</a></li>
                    <li><a class="nav-link scrollto" href="/#about">About</a></li>
                    <li><a class="nav-link scrollto" href="/#cwis">CWIS</a></li>
                    <li><a class="nav-link scrollto" href="/#features">Features</a></li>
                    <li><a class="nav-link scrollto" href="/#services">Functional Modules</a></li>
                    <li><a class="nav-link scrollto" href="/#contact">Contact</a></li>
                    <li><a class="nav-link scrollto active" href="#fsm-application">FSM Application</a></li>
                    <li>
                        <button type="button" class="btn btn-get-started" data-toggle="modal" data-target="#loginModal">
                            LOG In
                        </button>
                    </li>
                </ul>
                <i class="fa fa-bars  mobile-nav-toggle"></i>
            </nav>
            <!--navbar -->
        </div>

    </header>
    <!-- End Header -->

    <main id="main">
        <!-- ======= Contact Section ======= -->
        <section class="contact " id="fsm-application">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <!--<h2>Contact</h2>-->
                    <h3>FSM <span>Application Form</span></h3>
                </div>

                <div class="row ">

                    <div class="col-lg-4">
                        <div class="info">

                            <div class="address">
                                <i class="fas fa-map-marker-alt"></i>
                                <h4>Location:</h4>
                                <p></p>
                            </div>

                            <div class="email">
                                <i class=" icon far fa-envelope"></i>
                                <h4>Email:</h4>
                                <p>imis@ait.asia</p>
                            </div>

                            <div class="phone">
                                <i class="fas fa-mobile-alt"></i>
                                <h4>Call:</h4>
                                <p></p>
                            </div>

                        </div>

                    </div>

                    <div class="col-lg-8 mt-5 mt-lg-0">

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #28a745; background-color: #d4edda; border-color: #c3e6cb;">
                                <i class="fas fa-check-circle mr-2"></i>
                                <strong>Success!</strong> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #dc3545;">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Error!</strong> {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" style="border-left: 4px solid #dc3545;">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <form action="{{ route('client-fsm-application.submit') }}" method="POST">
                            @csrf

                            <div class="form-row">
                                <div class="form-group col-12 col-md-6">
                                    <label for="customer_name">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" id="customer_name"
                                        placeholder="Customer Name" value="{{ old('customer_name') }}" required aria-required="true">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-12 col-md-6">
                                    <label for="customer_contact">Contact No. <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('customer_contact') is-invalid @enderror" name="customer_contact" id="customer_contact"
                                        placeholder="01#########" value="{{ old('customer_contact') }}" required aria-required="true">
                                    @error('customer_contact')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-12 col-md-6">
                                    <label for="holding_owner_name">Holding Owner Name</label>
                                    <input type="text" name="holding_owner_name" class="form-control @error('holding_owner_name') is-invalid @enderror" id="holding_owner_name"
                                        placeholder="Holding Owner Name" value="{{ old('holding_owner_name') }}">
                                    @error('holding_owner_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-12 col-md-6">
                                    <label for="ward">Ward <span class="text-danger">*</span></label>
                                    <select name="ward" class="form-control @error('ward') is-invalid @enderror" id="ward" required aria-required="true">
                                        <option value="">Select Ward</option>
                                        @foreach($wards as $ward)
                                            <option value="{{ $ward }}" {{ old('ward') == $ward ? 'selected' : '' }}>{{ $ward }}</option>
                                        @endforeach
                                    </select>
                                    @error('ward')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-12 col-md-6">
                                    <label for="road_code">Road Name <small class="text-muted">(Optional)</small></label>
                                    <select name="road_code" class="form-control @error('road_code') is-invalid @enderror" id="road_code">
                                        <option value="">Select Road</option>
                                    </select>
                                    @error('road_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-12 col-md-6">
                                    <label for="tax_id">Tax ID <span class="text-danger">*</span></label>
                                    <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" id="tax_id"
                                        placeholder="##-###-####-##" value="{{ old('tax_id') }}" required aria-required="true">
                                    @error('tax_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="3" placeholder="Address" required aria-required="true">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-row">
                                <!-- Smaller date picker column -->
                                <div class="form-group col-12 col-md-4">
                                    <label for="proposed_emptying_date">Proposed Emptying Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-sm @error('proposed_emptying_date') is-invalid @enderror" name="proposed_emptying_date" id="proposed_emptying_date"
                                        value="{{ old('proposed_emptying_date') }}" required aria-required="true">
                                    @error('proposed_emptying_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Joined coordinates + button as a single input-group (sm) -->
                                <div class="form-group col-12 col-md-8">
                                    <label class="d-block mb-2">Coordinates <small class="text-muted">(Optional)</small></label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control @error('latitude') is-invalid @enderror" name="latitude" id="latitude"
                                            placeholder="Latitude" value="{{ old('latitude') }}" aria-label="Latitude">
                                        <input type="text" class="form-control @error('longitude') is-invalid @enderror" name="longitude" id="longitude"
                                            placeholder="Longitude" value="{{ old('longitude') }}" aria-label="Longitude">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-get-location" title="Use browser to detect location">
                                                <i class="fa fa-map-marker-alt" aria-hidden="true"></i>
                                                <span id="btn-get-location-text" class="text-nowrap ml-1">Use my location</span>
                                            </button>
                                        </div>
                                    </div>
                                    @error('latitude')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('longitude')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary btn-block">Send Now!</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </section>
        <!-- End Contact Section -->
    </main>
    <!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer" class="section-bg">
        <div class="container py-4">
            <div class="copyright">
                <strong> Base IMIS <i class="fa-regular fa-copyright"> </i> 2022-{{ \Carbon\Carbon::now()->format('Y')
                    }} by <a href="http://www.innovativesolution.com.np">
                        ISPL</a> & <a href="https://www.gwsc.ait.ac.th/">GWSC-AIT</a> is licensed under <a
                        href="https://creativecommons.org/licenses/by-nc-sa/4.0/?ref=chooser-v1">CC BY-NC-SA 4.0 </a>
                </strong>
            </div>
            <div class="credits">
                Developed by
                <a href="https://innovativesolution.com.np/">Innovative Solution Pvt. Ltd.</a>
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <div id="preloader"></div>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="fa fa-arrow-circle-up"></i></a>
    <div class="modal" id="loginModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header d-block">
                    <h5 class=" text-center "> Integrated Municipal Information System</h5>
                </div>
                <div class="modal-body">
                    @include('auth.login')
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor JS Files -->
    <script src="{{asset('js/app.js')}}"></script>
    <!-- Template Main JS File -->
    <script src="{{ asset('js/main.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.0.2/cleave.min.js" integrity="sha512-SvgzybymTn9KvnNGu0HxXiGoNeOi0TTK7viiG0EGn2Qbeu/NFi3JdWrJs2JHiGA1Lph+dxiDv5F9gDlcgBzjfA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        function myFunction() {
            var x = document.getElementById("password");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }

        $(document).ready(function() {
            var error = @js($errors->messages());
            var hasSuccess = @js(session('success') ? true : false);

            if (error.length > 0) {
                $('#loginModal').modal('show');
            }

            // Scroll to and highlight success message if present
            if (hasSuccess) {
                setTimeout(function() {
                    var successAlert = $('.alert-success');
                    if (successAlert.length) {
                        $('html, body').animate({
                            scrollTop: successAlert.offset().top - 100
                        }, 800);

                        // Add a subtle pulse effect
                        successAlert.addClass('animated-success');
                    }
                }, 300);
            }

            function initSelect2() {
                $('#road_code').select2({
                    ajax: {
                        url: "{{ route('client-fsm-application.get-road-names') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                search: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.results,
                                pagination: {
                                    more: data.pagination && data.pagination.more
                                }
                            };
                        },
                        cache: true
                    },
                    placeholder: 'Street Name / Street Code',
                    allowClear: true,
                    closeOnSelect: true,
                    width: '100%',
                });
            }
            initSelect2();

            /**
             * Pre-select the road code if it exists
             */
            function preSelect()
            {
                var preselectedRoadCode = "{{ old('road_code') }}";
                if (preselectedRoadCode) {
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('client-fsm-application.get-road-names') }}",
                        data: { search: preselectedRoadCode }
                    }).then(function (data) {
                        if (data.results && data.results.length) {
                            var road = data.results[0];
                            var option = new Option(road.text, road.id, true, true);
                            $('#road_code').append(option).trigger('change');

                            $('#road_code').trigger({
                                type: 'select2:select',
                                params: {
                                    data: data
                                }
                            });
                        }
                    });
                }
            }
            preSelect();

            new Cleave('#tax_id', {
                numericOnly: true,
                delimiter: '-',
                blocks: [2, 3, 4, 2],
                delimiterLazyShow: true
            });

            new Cleave('#customer_contact', {
                numericOnly: true,
                blocks: [11],
                numericOnly: true
            });

        })

        // --- Geolocation: fill latitude/longitude from browser ---
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('btn-get-location');
            const btnText = document.getElementById('btn-get-location-text');
            const latInput = document.getElementById('latitude');
            const lonInput = document.getElementById('longitude');

            function formatCoord(v) {
                if (!isFinite(v)) return '';
                return parseFloat(v).toFixed(6); // 6 decimal places
            }

            function setButtonLoading(loading) {
                if (!btn) return;
                btn.disabled = loading;
                btn.classList.toggle('loading', loading);
                btnText.textContent = loading ? 'Detecting...' : 'Use my location';
            }

            function success(pos) {
                const coords = pos.coords;
                if (latInput) latInput.value = formatCoord(coords.latitude);
                if (lonInput) lonInput.value = formatCoord(coords.longitude);
                setButtonLoading(false);
            }

            function error(err) {
                setButtonLoading(false);
                let msg = 'Unable to retrieve your location.';
                if (err && err.code) {
                    switch (err.code) {
                        case 1: msg = 'Permission denied. Please allow location access in your browser.'; break;
                        case 2: msg = 'Position unavailable.'; break;
                        case 3: msg = 'Location request timed out.'; break;
                    }
                }
                // Minimal UI feedback
                try { alert(msg); } catch(e){}
            }

            if (btn) {
                btn.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        alert('Geolocation is not supported by your browser.');
                        return;
                    }
                    setButtonLoading(true);
                    navigator.geolocation.getCurrentPosition(success, error, {
                        enableHighAccuracy: false,
                        timeout: 10000,
                        maximumAge: 60000
                    });
                });
            }
        });
    </script>
</body>

</html>