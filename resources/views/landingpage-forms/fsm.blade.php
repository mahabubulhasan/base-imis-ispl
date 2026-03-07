<!-- FSM APPLICATION TAB -->
<div id="fsm" class="tab-content p-5 md:p-10 bg-background-light min-h-[calc(100vh-100px)] animate-fadeIn">
    <div class="bg-white p-5 md:p-8 rounded-xl shadow-lg border border-slate-200 w-full max-w-6xl mx-auto">
        <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-center text-slate-900 mb-6 md:mb-8">FSM Application Form</h1>

        <!-- Alert Container for general errors -->
        <div id="fsm-alert-container"></div>

        <form id="fsm-application-form" class="needs-validation" novalidate>
            @csrf

            <!-- Tax Information Section -->
            <fieldset class="app_fieldset">
                <legend>Tax Information</legend>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="has_tax_id" class="block text-gray-800 font-semibold mb-2 text-base">Do you have a Tax ID? <span class="text-red-500">*</span></label>
                        <select name="has_tax_id" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="has_tax_id" required>
                            <option value="">Please select</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                        <span class="error-message" id="error-has_tax_id"></span>
                    </div>
                </div>

                <div class="row" id="tax_id_group" style="display:none;">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="tax_id" class="block text-gray-800 font-semibold mb-2 text-base">Tax ID <span class="text-red-500 tax-required-star" style="display:none;">*</span></label>
                        <input type="text" name="tax_id" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="tax_id" placeholder="##-###-####-##">
                        <span class="error-message" id="error-tax_id"></span>
                    </div>
                </div>
            </fieldset>

            <!-- Customer Information Section -->
            <fieldset class="app_fieldset">
                <legend>Customer Information</legend>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="customer_name" class="block text-gray-800 font-semibold mb-2 text-base">Customer Name <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="customer_name" placeholder="Customer Name" required>
                        <span class="error-message" id="error-customer_name"></span>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="customer_contact" class="block text-gray-800 font-semibold mb-2 text-base">Contact No. <span class="text-red-500">*</span></label>
                        <input type="tel" name="customer_contact" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="customer_contact" placeholder="01#########" required>
                        <span class="error-message" id="error-customer_contact"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="holding_owner_name" class="block text-gray-800 font-semibold mb-2 text-base">Holding Owner Name</label>
                        <input type="text" name="holding_owner_name" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="holding_owner_name" placeholder="Holding Owner Name">
                        <span class="error-message" id="error-holding_owner_name"></span>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="ward" class="block text-gray-800 font-semibold mb-2 text-base">Ward <span class="text-red-500">*</span></label>
                        <select name="ward" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="ward" required>
                            <option value="">Loading wards...</option>
                        </select>
                        <span class="error-message" id="error-ward"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="road_code" class="block text-gray-800 font-semibold mb-2 text-base">Road Name <small class="text-gray-500">(Optional)</small></label>
                        <select name="road_code" class="form-control w-full" id="road_code">
                            <option value="">Select Road</option>
                        </select>
                        <span class="error-message" id="error-road_code"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="address" class="block text-gray-800 font-semibold mb-2 text-base">Address <span class="text-red-500">*</span></label>
                        <textarea name="address" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y min-h-[100px] transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="address" rows="3" placeholder="Address" required></textarea>
                        <span class="error-message" id="error-address"></span>
                    </div>
                </div>
            </fieldset>

            <!-- Service Info Section -->
            <fieldset class="app_fieldset">
                <legend>Service Info</legend>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="proposed_emptying_date" class="block text-gray-800 font-semibold mb-2 text-base">Proposed Emptying Date <span class="text-red-500">*</span></label>
                        <input type="date" name="proposed_emptying_date" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="proposed_emptying_date" required>
                        <span class="error-message" id="error-proposed_emptying_date"></span>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="notes" class="block text-gray-800 font-semibold mb-2 text-base">Notes / Comments <span class="text-red-500">*</span></label>
                        <textarea name="notes" class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y min-h-[100px] transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10" id="notes" rows="3" placeholder="Additional Notes/Comments" required></textarea>
                        <span class="error-message" id="error-notes"></span>
                    </div>
                </div>
            </fieldset>

            <div class="text-center mt-4">
                <button type="submit" id="fsm-submit-btn" class="mx-auto bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:shadow-lg hover:shadow-primary/30 active:scale-[0.98] transition-all uppercase tracking-wider px-8">
                    Submit Application
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="fsm-success-modal" class="fsm-modal-overlay" style="display: none;">
    <div class="fsm-modal-content">
        <div class="fsm-modal-icon">
            <i class="fas fa-check"></i>
        </div>
        <h3 class="text-2xl font-bold text-center text-gray-800 mb-3">Success!</h3>
        <p class="text-center text-gray-600 mb-4" id="fsm-success-message">Your FSM application has been submitted successfully!</p>
        <p class="text-center text-sm text-gray-500 mb-4">This modal will close in <span id="fsm-countdown">5</span> seconds...</p>
        <div class="text-center">
            <button onclick="closeFsmSuccessModal()" class="px-6 py-2 bg-gradient-to-br from-[#007bff] to-[#0056b3] text-white border-none rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:from-[#0056b3] hover:to-[#003d82]">
                Close
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
  // ========== FSM APPLICATION FORM FUNCTIONALITY ==========

  // Global variables for FSM form
  var fsmWardsLoaded = false;
  var fsmFormInitialized = false;
  var fsmAutoDismissTimeout = null;

  // Function to close success modal
  function closeFsmSuccessModal() {
    $('#fsm-success-modal').fadeOut(300);
    if (fsmAutoDismissTimeout) {
      clearTimeout(fsmAutoDismissTimeout);
      fsmAutoDismissTimeout = null;
    }
  }

  // Function to show success modal with auto-dismiss
  function showFsmSuccessModal(message) {
    $('#fsm-success-message').text(message || 'Your FSM application has been submitted successfully!');
    $('#fsm-success-modal').fadeIn(300);

    // Auto-dismiss countdown
    var countdown = 5;
    $('#fsm-countdown').text(countdown);

    var countdownInterval = setInterval(function() {
      countdown--;
      $('#fsm-countdown').text(countdown);
      if (countdown <= 0) {
        clearInterval(countdownInterval);
      }
    }, 1000);

    // Auto-dismiss after 5 seconds
    fsmAutoDismissTimeout = setTimeout(function() {
      closeFsmSuccessModal();
    }, 5000);
  }

  // Function to load wards data
  function loadFsmWards() {
    if (fsmWardsLoaded) return;

    $.ajax({
      url: '{{ route("client-fsm-application.get-wards") }}',
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response.success && response.wards) {
          var $wardSelect = $('#ward');
          $wardSelect.empty();
          $wardSelect.append('<option value="">Select Ward</option>');

          response.wards.forEach(function(ward) {
            $wardSelect.append('<option value="' + ward + '">' + ward + '</option>');
          });

          fsmWardsLoaded = true;
        }
      },
      error: function() {
        var $wardSelect = $('#ward');
        $wardSelect.empty();
        $wardSelect.append('<option value="">Failed to load wards</option>');
        console.error('Failed to load wards data');
      }
    });
  }

  // Function to clear all form errors
  function clearFsmFormErrors() {
    $('.error-message').text('').hide();
    $('.field-error').removeClass('field-error');
    $('#fsm-alert-container').empty();
  }

  // Function to display validation errors
  function displayFsmErrors(errors) {
    clearFsmFormErrors();

    $.each(errors, function(field, messages) {
      var $field = $('#' + field);
      var $errorSpan = $('#error-' + field);

      if ($field.length) {
        $field.addClass('field-error');

        // Clear error when user starts typing
        $field.one('input change', function() {
          $(this).removeClass('field-error');
          $errorSpan.text('').hide();
        });
      }

      if ($errorSpan.length && messages.length > 0) {
        $errorSpan.text(messages[0]).show();
      }
    });
  }

  // Function to reset FSM form
  function resetFsmForm() {
    $('#fsm-application-form')[0].reset();
    clearFsmFormErrors();

    // Reset Tax ID visibility
    $('#tax_id_group').slideUp(150);
    $('#tax_id').prop('required', false).removeAttr('aria-required');
    $('.tax-required-star').hide();

    // Reset Select2
    if ($('#road_code').data('select2')) {
      $('#road_code').val(null).trigger('change');
    }


  }

  // Initialize FSM form functionality
  function initializeFsmForm() {
    if (fsmFormInitialized) return;

    // Tax ID visibility toggle
    $('#has_tax_id').on('change', function() {
      var $group = $('#tax_id_group');
      var $input = $('#tax_id');
      var $star = $('.tax-required-star');

      if ($(this).val() === 'yes') {
        $group.slideDown(150);
        $input.prop('required', true).attr('aria-required', 'true');
        $star.show();
      } else {
        $group.slideUp(150);
        $input.prop('required', false).removeAttr('aria-required');
        $star.hide();
        $input.val('');
      }
    });

    // Initialize Select2 for road names
    $('#road_code').select2({
      ajax: {
        url: '{{ route("client-fsm-application.get-road-names") }}',
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
      width: '100%'
    });

    // Initialize Cleave.js for Tax ID
    new Cleave('#tax_id', {
      numericOnly: true,
      delimiter: '-',
      blocks: [2, 3, 4, 2],
      delimiterLazyShow: true
    });

    // Initialize Cleave.js for Contact
    new Cleave('#customer_contact', {
      numericOnly: true,
      blocks: [11]
    });

    // Auto-fill from Tax ID
    var taxIdInput = $('#tax_id');
    var debounceTimeout = null;
    var isLoadingData = false;

    function clearAutoPopulatedFields() {
      $('#customer_name').val('');
      $('#customer_contact').val('');
      $('#holding_owner_name').val('');
      $('#ward').val('');
      $('#road_code').val(null).trigger('change');
      $('#address').val('');
    }

    function hasMinimumLength(taxId) {
      var digitsOnly = taxId.replace(/[^0-9]/g, '');
      return digitsOnly.length >= 8;
    }

    function isValidTaxId(taxId) {
      var taxIdPattern = /^\d{2}-\d{3}-\d{4}-\d{2}$/;
      return taxIdPattern.test(taxId.trim());
    }

    function autoFillFromTaxId() {
      var taxId = taxIdInput.val().trim();

      if (!taxId || taxId.length < 10 || !hasMinimumLength(taxId)) {
        clearAutoPopulatedFields();
        return;
      }

      if (!isValidTaxId(taxId)) {
        if (taxId.length >= 13) {
          clearAutoPopulatedFields();
        }
        return;
      }

      if (isLoadingData) return;

      isLoadingData = true;

      $.ajax({
        url: '{{ route("client-fsm-application.get-building-data") }}',
        type: 'GET',
        data: { tax_id: taxId.trim() },
        success: function(response) {
          if (response.success && response.data) {
            var data = response.data;

            $('#customer_name').val(data.customer_name || '');
            $('#customer_contact').val(data.customer_contact || '');
            $('#holding_owner_name').val(data.holding_owner_name || '');
            $('#ward').val(data.ward || '');
            $('#address').val(data.address || '');

            if (data.road_code && data.road_name_text) {
              var $roadCode = $('#road_code');
              if (!$roadCode.find('option[value="' + data.road_code + '"]').length) {
                var newOption = new Option(data.road_name_text, data.road_code, true, true);
                $roadCode.append(newOption);
              }
              $roadCode.val(data.road_code).trigger('change');
            }
          } else {
            clearAutoPopulatedFields();
          }
        },
        error: function() {
          clearAutoPopulatedFields();
        },
        complete: function() {
          isLoadingData = false;
        }
      });
    }

    taxIdInput.on('input', function() {
      var taxId = taxIdInput.val().trim();

      if (!taxId || taxId.length < 10 || !hasMinimumLength(taxId)) {
        if (debounceTimeout) {
          clearTimeout(debounceTimeout);
          debounceTimeout = null;
        }
        clearAutoPopulatedFields();
        return;
      }

      if (debounceTimeout) {
        clearTimeout(debounceTimeout);
      }

      debounceTimeout = setTimeout(function() {
        autoFillFromTaxId();
        debounceTimeout = null;
      }, 400);
    });

    taxIdInput.on('blur', function() {
      if (debounceTimeout) {
        clearTimeout(debounceTimeout);
        debounceTimeout = null;
      }
      autoFillFromTaxId();
    });



    // Form submission
    $('#fsm-application-form').on('submit', function(e) {
      e.preventDefault();

      clearFsmFormErrors();

      var $submitBtn = $('#fsm-submit-btn');
      var originalText = $submitBtn.html();

      // Disable button and show loading state
      $submitBtn.prop('disabled', true).addClass('btn-loading').html('Submitting...');

      var formData = $(this).serialize();

      $.ajax({
        url: '{{ route("client-fsm-application.submit") }}',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            // Show success modal
            showFsmSuccessModal(response.message);

            // Reset form
            resetFsmForm();
          } else {
            // Show error message
            if (response.message) {
              $('#fsm-alert-container').html(
                '<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">' +
                '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                '<strong>Error!</strong> ' + response.message +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span></button></div>'
              );
            }
          }
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            // Validation errors
            var errors = xhr.responseJSON.errors;
            displayFsmErrors(errors);

            // Scroll to first error
            var firstError = $('.field-error').first();
            if (firstError.length) {
              $('html, body').animate({
                scrollTop: firstError.offset().top - 100
              }, 500);
            }
          } else {
            // General error
            $('#fsm-alert-container').html(
              '<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">' +
              '<i class="fas fa-exclamation-triangle mr-2"></i>' +
              '<strong>Error!</strong> An unexpected error occurred. Please try again.' +
              '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
              '<span aria-hidden="true">&times;</span></button></div>'
            );
          }
        },
        complete: function() {
          // Re-enable button
          $submitBtn.prop('disabled', false).removeClass('btn-loading').html(originalText);
        }
      });
    });

    fsmFormInitialized = true;
  }

  // Document ready
  $(document).ready(function() {
    // Load wards data on page load
    loadFsmWards();

    // Initialize FSM form when FSM tab is opened
    var originalOpenTab = window.openTab;
    window.openTab = function(tabId, el) {
      originalOpenTab(tabId, el);

      if (tabId === 'fsm') {
        // Initialize form on first open
        if (!fsmFormInitialized) {
          setTimeout(function() {
            initializeFsmForm();
          }, 100);
        } else {
          // Form already initialized
          setTimeout(function() {
            // no map to invalidate
          }, 100);
        }
      }
    };
  });
</script>
@endpush
