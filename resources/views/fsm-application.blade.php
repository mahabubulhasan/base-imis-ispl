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
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />

    <style>
        .animated-success {
            animation: successPulse 2s ease-in-out;
        }

        @keyframes successPulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }

            100% {
                transform: scale(1);
            }
        }

        .alert-success {
            font-weight: 500;
        }

        .alert-success i {
            color: #155724;
        }

        /* Fieldset / legend styling for grouped form sections */
        .app_fieldset {
            border: 1px solid #e2e8f0;
            padding: 1rem;
            border-radius: 0.4rem;
            margin-bottom: 1.25rem;
            background: #ffffff;
        }

        .app_fieldset>legend {
            padding: 0 .5rem;
            font-weight: 600;
            color: #0f394c;
            font-size: 1rem;
            width: auto;
        }

        /* Keep small screens comfortable */
        @media (max-width: 576px) {
            .app_fieldset {
                padding: 0.75rem;
            }

            .app_fieldset>legend {
                font-size: 0.95rem;
            }
        }

        /* Map container height */
        #map {
            height: 360px;
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

                            <fieldset class="app_fieldset">
                                <legend>Tax Information</legend>

                                <div class="form-row">
                                    <div class="form-group row col-12">
                                        {!! Form::label('has_tax_id', 'Do you have a Tax ID? ', ['class' => 'col-sm-3 control-label']) !!}
                                        <div class="col-sm-5">
                                            <select name="has_tax_id" class="form-control @error('has_tax_id') is-invalid @enderror" id="has_tax_id" required>
                                                <option value="">Please select</option>
                                                <option value="yes" {{ old('has_tax_id') == 'yes' ? 'selected' : '' }}>Yes</option>
                                                <option value="no" {{ old('has_tax_id') == 'no' ? 'selected' : '' }}>No</option>
                                            </select>
                                            @error('has_tax_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Tax ID field: initially shown only when old('has_tax_id') == 'yes' -->
                                <div class="form-row">
                                    <div class="form-group col-12 col-md-6" id="tax_id_group" style="{{ old('has_tax_id') == 'yes' ? '' : 'display:none;' }}">
                                        <label for="tax_id">Tax ID <span class="text-danger tax-required-star" style="{{ old('has_tax_id') == 'yes' ? '' : 'display:none;' }}">*</span></label>
                                        <input type="text" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" id="tax_id"
                                            placeholder="##-###-####-##" value="{{ old('tax_id') }}" {{ old('has_tax_id') == 'yes' ? 'required aria-required=true' : '' }}>
                                        @error('tax_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </fieldset>

                            <!-- Customer Information Section -->
                            <fieldset class="app_fieldset">
                                <legend>Customer Information</legend>

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
                                </div>

                                <div class="form-group">
                                    <label for="address">Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="3" placeholder="Address" required aria-required="true">{{ old('address') }}</textarea>
                                    @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </fieldset>

                            <fieldset class="app_fieldset">
                                <legend>Service Info</legend>

                                <div class="form-row">
                                    <div class="form-group col-12 col-md-6">
                                        <label for="proposed_emptying_date">Proposed Emptying Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('proposed_emptying_date') is-invalid @enderror" name="proposed_emptying_date" id="proposed_emptying_date"
                                            value="{{ old('proposed_emptying_date') }}" required aria-required="true">
                                        @error('proposed_emptying_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="notes">Notes / Comments <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" id="notes" rows="3" placeholder="Additional Notes/Comments" required aria-required="true">{{ old('notes') }}</textarea>
                                        @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                            </fieldset>

                            <fieldset class="app_fieldset">
                                <legend>Location</legend>

                                <div class="form-group">
                                    <div id="map" class="w-100 rounded-lg shadow-sm border border-gray-300"></div>
                                </div>

                                <!-- Hidden inputs updated by the map -->
                                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                                @error('latitude')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('longitude')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                            </fieldset>

                            <div class="text-center mt-3">
                                 <button type="submit" class="btn btn-primary btn-block">Submit</button>
                             </div>
                        </form>
                    </div>

                </div>
            </div>
        </section>
        <!-- End Contact Section -->
    </main>
    <!-- End #main -->

    <!-- Branding -->
    <div style="background: white">
        <div class="container">
            @include('includes.branding')
        </div>
    </div>
    <!-- End Branding -->

    <!-- ======= Footer ======= -->
    <footer id="footer" class="section-bg">
        <div class="container py-4">
            <div class="copyright">
                <strong> Base IMIS <i class="fa-regular fa-copyright"> </i>  2022-{{ \Carbon\Carbon::now()->format('Y') }} by <a href="http://www.innovativesolution.com.np">
	ISPL</a> & <a href="https://www.gwsc.ait.ac.th/">GWSC-AIT</a> is licensed under <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/?ref=chooser-v1">CC BY-NC-SA 4.0 </a>
</strong>
            </div>
            <div class="credits">
                Implemented by
                <a href="https://streamstech.com">Streams Tech Ltd.</a>
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
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
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
            // Safely serialize server-side errors and success flag for the client
            var errors = @json($errors->messages());
            var hasSuccess = @json(session('success') ? true : false);

            // If there are validation errors, open the login modal.
            if (errors && Object.keys(errors).length > 0) {
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

            // Toggle Tax ID visibility based on selection
            function setTaxVisibility(show) {
                var $group = $('#tax_id_group');
                var $input = $('#tax_id');
                var $star = $('.tax-required-star');

                if (show) {
                    $group.slideDown(150);
                    $input.prop('required', true).attr('aria-required', 'true');
                    $star.show();
                } else {
                    $group.slideUp(150);
                    $input.prop('required', false).removeAttr('aria-required');
                    $star.hide();
                }
            }

            // initialize select2 and other things
            function initSelect2() {
                $('#road_code').select2({
                    ajax: {
                        url: "{{ route('client-fsm-application.get-road-names') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                search: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
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

            // ensure Tax ID visibility matches current selection on load
            var initialHasTax = @json(old('has_tax_id', ''));
            setTaxVisibility(initialHasTax === 'yes');

            // listen for changes
            $('#has_tax_id').on('change', function() {
                setTaxVisibility($(this).val() === 'yes');
            });

            /**
             * Pre-select the road code if it exists
             */
            function preSelect() {
                var preselectedRoadCode = "{{ old('road_code') }}";
                if (preselectedRoadCode) {
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('client-fsm-application.get-road-names') }}",
                        data: {
                            search: preselectedRoadCode
                        }
                    }).then(function(data) {
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

            /**
             * Auto-fill form fields based on Tax ID
             * Uses debounced input event for real-time auto-population
             */
            var taxIdInput = $('#tax_id');
            var isLoadingData = false;
            var debounceTimeout = null;
            var minTaxIdLength = 10; // Minimum characters (including dashes) before making request
            // Format: ##-###-####-## = 13 characters with dashes, 11 digits
            // We'll check for at least 10 characters to ensure we're close to complete

            // Add loading indicator styling
            function showTaxIdLoading(show) {
                if (show) {
                    taxIdInput.css('background-image', 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23007bff\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpath d=\'M21 12a9 9 0 1 1-6.219-8.56\'/%3E%3C/svg%3E")');
                    taxIdInput.css('background-repeat', 'no-repeat');
                    taxIdInput.css('background-position', 'right 0.75rem center');
                    taxIdInput.css('background-size', '16px 16px');
                } else {
                    taxIdInput.css('background-image', '');
                }
            }

            // Function to check if tax_id is valid (format: ##-###-####-##)
            function isValidTaxId(taxId) {
                // Tax ID format: ##-###-####-## (11 digits total with dashes)
                var taxIdPattern = /^\d{2}-\d{3}-\d{4}-\d{2}$/;
                return taxIdPattern.test(taxId.trim());
            }

            // Function to get minimum length check (digits only, minimum 8)
            function hasMinimumLength(taxId) {
                var digitsOnly = taxId.replace(/[^0-9]/g, '');
                return digitsOnly.length >= 8; // At least 8 digits
            }

            // Function to clear all auto-populated fields
            function clearAutoPopulatedFields() {
                $('#customer_name').val('').trigger('input');
                $('#customer_contact').val('').trigger('input');
                $('#holding_owner_name').val('').trigger('input');
                $('#ward').val('').trigger('change');
                $('#road_code').val(null).trigger('change'); // Clear Select2
                $('#address').val('').trigger('input');
            }

            // Auto-fill function
            function autoFillFromTaxId() {
                var taxId = taxIdInput.val().trim();

                // Clear fields if tax_id is empty or too short
                if (!taxId || taxId.length < minTaxIdLength) {
                    clearAutoPopulatedFields();
                    return;
                }

                // Check minimum length (at least 8 digits)
                if (!hasMinimumLength(taxId)) {
                    clearAutoPopulatedFields();
                    return;
                }

                // Don't fetch if tax_id format is invalid (but only check format if we have enough length)
                if (!isValidTaxId(taxId)) {
                    // If we have minimum length but invalid format, still try to fetch
                    // (in case user is still typing)
                    if (taxId.length >= 13) {
                        // Only clear if we're sure the format is wrong and it's complete
                        clearAutoPopulatedFields();
                        return;
                    }
                }

                // Prevent multiple simultaneous requests
                if (isLoadingData) {
                    return;
                }

                isLoadingData = true;
                showTaxIdLoading(true);

                // Send tax_id with dashes (database stores it in format: "11-080-0319-00")
                var url = "{{ route('client-fsm-application.get-building-data') }}";

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        tax_id: taxId.trim()
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            var data = response.data;

                            // Populate customer name
                            if (data.customer_name) {
                                $('#customer_name').val(data.customer_name).trigger('input');
                            } else {
                                $('#customer_name').val('').trigger('input');
                            }

                            // Populate customer contact
                            if (data.customer_contact) {
                                $('#customer_contact').val(data.customer_contact).trigger('input');
                            } else {
                                $('#customer_contact').val('').trigger('input');
                            }

                            // Populate holding owner name
                            if (data.holding_owner_name) {
                                $('#holding_owner_name').val(data.holding_owner_name).trigger('input');
                            } else {
                                $('#holding_owner_name').val('').trigger('input');
                            }

                            // Populate ward
                            if (data.ward) {
                                $('#ward').val(data.ward).trigger('change');
                            } else {
                                $('#ward').val('').trigger('change');
                            }

                            // Populate road_code (Select2 dropdown)
                            if (data.road_code && data.road_name_text) {
                                // Check if option already exists
                                var $roadCode = $('#road_code');
                                var optionExists = $roadCode.find('option[value="' + data.road_code + '"]').length > 0;

                                if (!optionExists) {
                                    // Create and append new option
                                    var newOption = new Option(data.road_name_text, data.road_code, true, true);
                                    $roadCode.append(newOption);
                                }

                                // Set the value and trigger change
                                $roadCode.val(data.road_code).trigger('change');
                            } else {
                                $('#road_code').val(null).trigger('change');
                            }

                            // Populate address
                            if (data.address) {
                                $('#address').val(data.address).trigger('input');
                            } else {
                                $('#address').val('').trigger('input');
                            }

                            // Show success message (optional, subtle notification)
                            console.log('Building data loaded successfully');
                        } else {
                            // No data found - clear fields
                            clearAutoPopulatedFields();
                            console.log(response.message || 'No building data found');
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = 'Unable to fetch building data.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        console.error('Error:', errorMessage);
                        // Clear fields on error to avoid stale data
                        clearAutoPopulatedFields();
                    },
                    complete: function() {
                        isLoadingData = false;
                        showTaxIdLoading(false);
                    }
                });
            }

            // Debounce function
            function debounceAutoFill(delay) {
                // Clear any existing timeout
                if (debounceTimeout) {
                    clearTimeout(debounceTimeout);
                }

                // Set new timeout
                debounceTimeout = setTimeout(function() {
                    autoFillFromTaxId();
                    debounceTimeout = null;
                }, delay);
            }

            // Add input event listener with debounce (400ms delay)
            taxIdInput.on('input', function() {
                var taxId = taxIdInput.val().trim();

                // If tax_id is cleared or too short, clear fields immediately
                if (!taxId || taxId.length < minTaxIdLength || !hasMinimumLength(taxId)) {
                    // Cancel any pending debounced request
                    if (debounceTimeout) {
                        clearTimeout(debounceTimeout);
                        debounceTimeout = null;
                    }
                    clearAutoPopulatedFields();
                    return;
                }

                // Debounce the AJAX call (400ms delay)
                debounceAutoFill(400);
            });

            // Also handle blur event to ensure data is fetched if user leaves field
            taxIdInput.on('blur', function() {
                // If there's a pending debounce, cancel it and fetch immediately
                if (debounceTimeout) {
                    clearTimeout(debounceTimeout);
                    debounceTimeout = null;
                }
                autoFillFromTaxId();
            });

        })

        // --- Leaflet Map Initialization ---
        document.addEventListener('DOMContentLoaded', function() {
            var latInput = document.getElementById('latitude');
            var lonInput = document.getElementById('longitude');

            function toFloatOrNull(v) {
                var f = parseFloat(v);
                return isFinite(f) ? f : null;
            }

            var defaultLat = 23.780887; // fallback center
            var defaultLon = 90.279237;

            var startLat = toFloatOrNull(latInput && latInput.value) ?? defaultLat;
            var startLon = toFloatOrNull(lonInput && lonInput.value) ?? defaultLon;

            var mapEl = document.getElementById('map');
            if (!mapEl) return;

            var map = L.map('map');
            map.setView([startLat, startLon], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            var marker = L.marker([startLat, startLon], { draggable: true }).addTo(map);

            function updateLocation(lat, lon) {
                if (latInput) latInput.value = Number(lat).toFixed(6);
                if (lonInput) lonInput.value = Number(lon).toFixed(6);
            }

            marker.on('dragend', function(e) {
                var p = e.target.getLatLng();
                updateLocation(p.lat, p.lng);
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                updateLocation(e.latlng.lat, e.latlng.lng);
            });
        });
    </script>
</body>

</html>