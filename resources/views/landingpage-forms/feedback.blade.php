<!-- FEEDBACK TAB -->
<div id="feedback" class="tab-content hidden p-5 md:p-10 bg-transparent min-h-[calc(100vh-100px)] animate-fadeIn">
    <div class="bg-white p-5 md:p-8 rounded-2xl shadow-2xl w-full max-w-6xl mx-auto">
        <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-center text-[#1f3b7d] mb-6 md:mb-8">Feedback Form</h1>

        <form id="feedback-form" class="needs-validation" novalidate>
            @csrf

            <!-- Application Information Section -->
            <fieldset class="app_fieldset">
                <legend>Application Information</legend>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="application_id" class="block text-gray-800 font-semibold mb-2 text-base">Application Number <span class="text-red-500">*</span></label>
                        <input type="text" id="application_id" name="application_id" placeholder="Enter your application number" required
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10">
                        <span class="error-message" id="error-application_id"></span>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="customer_name" class="block text-gray-800 font-semibold mb-2 text-base">Service Receiver Name <span class="text-red-500">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" placeholder="Will be filled automatically" required
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10">
                        <span class="error-message" id="error-customer_name"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="customer_number" class="block text-gray-800 font-semibold mb-2 text-base">Service Receiver Contact <span class="text-red-500">*</span></label>
                        <input type="text" id="customer_number" name="customer_number" placeholder="Will be filled automatically" required
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10">
                        <span class="error-message" id="error-customer_number"></span>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="service_provider_name" class="block text-gray-800 font-semibold mb-2 text-base">Service Provider Name</label>
                        <input type="text" id="service_provider_name" name="service_provider_name" readonly
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-200 bg-gray-50 text-base cursor-not-allowed">
                        <span class="error-message" id="error-service_provider_name"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="service_provider_contact" class="block text-gray-800 font-semibold mb-2 text-base">Service Provider Contact</label>
                        <input type="text" id="service_provider_contact" name="service_provider_contact" readonly
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-200 bg-gray-50 text-base cursor-not-allowed">
                        <span class="error-message" id="error-service_provider_contact"></span>
                    </div>
                </div>
            </fieldset>

            <!-- Safety & Service Quality Section -->
            <fieldset class="app_fieldset">
                <legend>Safety & Service Quality</legend>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">1. Safety of emptiers and customer is very important. Please indicate which measures the emptiers followed during the service: <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="safety_measures[]" value="Used hand gloves" class="w-5 h-5 text-[#0056b3] border-gray-300 rounded focus:ring-[#0056b3]">
                                <span class="text-gray-700">Used hand gloves</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="safety_measures[]" value="Used mask" class="w-5 h-5 text-[#0056b3] border-gray-300 rounded focus:ring-[#0056b3]">
                                <span class="text-gray-700">Used mask</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="safety_measures[]" value="Used apron" class="w-5 h-5 text-[#0056b3] border-gray-300 rounded focus:ring-[#0056b3]">
                                <span class="text-gray-700">Used apron</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="safety_measures[]" value="Did not wear the glovespic tank" class="w-5 h-5 text-[#0056b3] border-gray-300 rounded focus:ring-[#0056b3]">
                                <span class="text-gray-700">Did not wear the glovespic tank</span>
                            </label>
                        </div>
                        <span class="error-message" id="error-safety_measures"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">2. How would you rate the attitude of the emptiers during service? <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="fsm_quality_level" value="4" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Very Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="fsm_quality_level" value="3" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="fsm_quality_level" value="2" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Not Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="fsm_quality_level" value="1" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Disappointed</span>
                            </label>
                        </div>
                        <span class="error-message" id="error-fsm_quality_level"></span>
                    </div>
                </div>

                <div class="row" id="dissatisfaction_q2" style="display: none;">
                    <div class="col-12 mb-3">
                        <textarea id="dissatisfaction_comment_q2" name="dissatisfaction_comment_q2" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span class="error-message" id="error-dissatisfaction_comment_q2"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">3. How do you assess the response time of the emptying service? <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_delivery_efficiency" value="4" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Very Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_delivery_efficiency" value="3" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_delivery_efficiency" value="2" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Not Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_delivery_efficiency" value="1" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Disappointed</span>
                            </label>
                        </div>
                        <span class="error-message" id="error-service_delivery_efficiency"></span>
                    </div>
                </div>

                <div class="row" id="dissatisfaction_q3" style="display: none;">
                    <div class="col-12 mb-3">
                        <textarea id="dissatisfaction_comment_q3" name="dissatisfaction_comment_q3" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span class="error-message" id="error-dissatisfaction_comment_q3"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">4. How satisfied are you with the overall emptying service? <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="overall_satisfaction" value="4" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Very Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="overall_satisfaction" value="3" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="overall_satisfaction" value="2" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Not Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="overall_satisfaction" value="1" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Disappointed</span>
                            </label>
                        </div>
                        <span class="error-message" id="error-overall_satisfaction"></span>
                    </div>
                </div>

                <div class="row" id="dissatisfaction_q4" style="display: none;">
                    <div class="col-12 mb-3">
                        <textarea id="dissatisfaction_comment_q4" name="dissatisfaction_comment_q4" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span class="error-message" id="error-dissatisfaction_comment_q4"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">5. How satisfied are you with the price of this service? <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_quality_price" value="4" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Very Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_quality_price" value="3" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_quality_price" value="2" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Not Satisfied</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_quality_price" value="1" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Disappointed</span>
                            </label>
                        </div>
                        <span class="error-message" id="error-service_quality_price"></span>
                    </div>
                </div>

                <div class="row" id="dissatisfaction_q5" style="display: none;">
                    <div class="col-12 mb-3">
                        <textarea id="dissatisfaction_comment_q5" name="dissatisfaction_comment_q5" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span class="error-message" id="error-dissatisfaction_comment_q5"></span>
                    </div>
                </div>
            </fieldset>

            <!-- Payment & Future Service Section -->
            <fieldset class="app_fieldset">
                <legend>Payment & Future Service</legend>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">7. Are you satisfied with the payment mechanism? <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="payment_mechanism_satisfied" value="1" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Yes</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="payment_mechanism_satisfied" value="0" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">No</span>
                            </label>
                        </div>
                        <span class="error-message" id="error-payment_mechanism_satisfied"></span>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">8. Are you willing to apply again in the future? <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="apply_in_future" value="1" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Yes</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="apply_in_future" value="0" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">No</span>
                            </label>
                        </div>
                        <span class="error-message" id="error-apply_in_future"></span>
                    </div>
                </div>

                <div class="row" id="payment_comments_section" style="display: none;">
                    <div class="col-12 mb-3">
                        <label for="payment_mechanism_comments" class="block text-gray-700 mb-2 text-base italic">If no, why?</label>
                        <textarea id="payment_mechanism_comments" name="payment_mechanism_comments" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span class="error-message" id="error-payment_mechanism_comments"></span>
                    </div>
                </div>

                <div class="row" id="apply_future_comments_section" style="display: none;">
                    <div class="col-12 mb-3">
                        <label for="apply_in_future_comments" class="block text-gray-700 mb-2 text-base italic">If no, why?</label>
                        <textarea id="apply_in_future_comments" name="apply_in_future_comments" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span class="error-message" id="error-apply_in_future_comments"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">9. Would you recommend this FSM service to others? <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="recommend_service" value="1" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">Yes</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="recommend_service" value="0" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">No</span>
                            </label>
                        </div>
                        <span class="error-message" id="error-recommend_service"></span>
                    </div>
                </div>

                <div class="row" id="recommend_comments_section" style="display: none;">
                    <div class="col-12 mb-3">
                        <label for="recommend_service_comments" class="block text-gray-700 mb-2 text-base italic">If no, why?</label>
                        <textarea id="recommend_service_comments" name="recommend_service_comments" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span class="error-message" id="error-recommend_service_comments"></span>
                    </div>
                </div>
            </fieldset>

            <!-- Additional Information Section -->
            <fieldset class="app_fieldset">
                <legend>Additional Information</legend>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">10. How did you hear about the FSM service? <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="advertising_media[]" value="Social Media" class="w-5 h-5 text-[#0056b3] border-gray-300 rounded focus:ring-[#0056b3]">
                                <span class="text-gray-700">Social Media</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="advertising_media[]" value="Neighbours" class="w-5 h-5 text-[#0056b3] border-gray-300 rounded focus:ring-[#0056b3]">
                                <span class="text-gray-700">Neighbours</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="advertising_media[]" value="Campaign" class="w-5 h-5 text-[#0056b3] border-gray-300 rounded focus:ring-[#0056b3]">
                                <span class="text-gray-700">Campaign</span>
                            </label>
                        </div>
                        <span class="error-message" id="error-advertising_media"></span>
                    </div>
                </div>
            </fieldset>

            <!-- Hidden fields for anti-spam -->
            <input type="text" name="website" id="website" style="display:none" tabindex="-1" autocomplete="off">
            <input type="hidden" name="form_loaded_at" id="form_loaded_at">
            <input type="hidden" name="customer_gender" id="customer_gender">

            <!-- Error Message Display -->
            <div id="feedback-error" class="hidden mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg"></div>

            <div class="text-center mt-4">
                <button type="submit" id="feedback-submit-btn"
                    class="px-8 py-3 bg-gradient-to-br from-[#007bff] to-[#0056b3] text-white border-none rounded-lg text-base md:text-lg font-semibold cursor-pointer transition-all duration-300 hover:from-[#0056b3] hover:to-[#003d82] hover:-translate-y-0.5 hover:shadow-xl hover:shadow-[#0056b3]/30">
                    Submit Feedback
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Feedback Success Modal -->
<div id="feedback-success-modal" class="fsm-modal-overlay" style="display: none;">
    <div class="fsm-modal-content">
        <div class="fsm-modal-icon">
            <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-center text-gray-800 mb-3">Success!</h3>
        <p class="text-center text-gray-600 mb-4" id="feedback-success-message">Your feedback has been submitted successfully!</p>
        <p class="text-center text-sm text-gray-500 mb-4">This modal will close in <span id="feedback-countdown">5</span> seconds...</p>
        <div class="text-center">
            <button onclick="closeFeedbackModal()" class="px-6 py-2 bg-gradient-to-br from-[#007bff] to-[#0056b3] text-white rounded-lg font-semibold hover:from-[#0056b3] hover:to-[#003d82]">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
  // ===================================
  // FEEDBACK FORM JAVASCRIPT
  // ===================================

  // Set form loaded timestamp
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('form_loaded_at').value = Math.floor(Date.now() / 1000);
  });

  // Application number lookup
  document.getElementById('application_id').addEventListener('blur', function() {
    var applicationId = this.value.trim();
    if (applicationId) {
      fetchApplicationData(applicationId);
    }
  });

  function fetchApplicationData(applicationId) {
    fetch('/feedback-application-data?application_id=' + applicationId)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Auto-populate fields
          document.getElementById('customer_name').value = data.data.customer_name || '';
          document.getElementById('customer_number').value = data.data.customer_contact || '';
          document.getElementById('customer_gender').value = data.data.customer_gender || '';
          document.getElementById('service_provider_name').value = data.data.service_provider_name || '';
          document.getElementById('service_provider_contact').value = data.data.service_provider_contact || '';
        } else {
          showFeedbackError(data.message);
          // Clear fields
          document.getElementById('customer_name').value = '';
          document.getElementById('customer_number').value = '';
          document.getElementById('customer_gender').value = '';
          document.getElementById('service_provider_name').value = '';
          document.getElementById('service_provider_contact').value = '';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showFeedbackError('Error fetching application data. Please try again.');
      });
  }

  // Conditional display logic for comment fields
  function setupConditionalFields() {
    // Per-question dissatisfaction handling for questions 2-5
    const questionMappings = [
      { name: 'fsm_quality_level', id: 'dissatisfaction_q2' },
      { name: 'service_delivery_efficiency', id: 'dissatisfaction_q3' },
      { name: 'overall_satisfaction', id: 'dissatisfaction_q4' },
      { name: 'service_quality_price', id: 'dissatisfaction_q5' }
    ];

    questionMappings.forEach(mapping => {
      const radios = document.querySelectorAll(`input[name="${mapping.name}"]`);
      radios.forEach(radio => {
        radio.addEventListener('change', function() {
          document.getElementById(mapping.id).style.display = this.value === '1' ? 'block' : 'none';
        });
      });
    });

    // Payment mechanism comments
    document.querySelectorAll('input[name="payment_mechanism_satisfied"]').forEach(radio => {
      radio.addEventListener('change', function() {
        document.getElementById('payment_comments_section').style.display = this.value === '0' ? 'block' : 'none';
      });
    });

    // Apply in future comments
    document.querySelectorAll('input[name="apply_in_future"]').forEach(radio => {
      radio.addEventListener('change', function() {
        document.getElementById('apply_future_comments_section').style.display = this.value === '0' ? 'block' : 'none';
      });
    });

    // Recommend service comments
    document.querySelectorAll('input[name="recommend_service"]').forEach(radio => {
      radio.addEventListener('change', function() {
        document.getElementById('recommend_comments_section').style.display = this.value === '0' ? 'block' : 'none';
      });
    });
  }

  // Initialize conditional fields
  document.addEventListener('DOMContentLoaded', setupConditionalFields);

  // Form submission handler
  document.getElementById('feedback-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const submitBtn = document.getElementById('feedback-submit-btn');
    const formData = new FormData(form);

    // Convert checkbox arrays to comma-separated strings
    const safetyMeasures = [];
    document.querySelectorAll('input[name="safety_measures[]"]:checked').forEach(cb => {
      safetyMeasures.push(cb.value);
    });
    formData.delete('safety_measures[]');
    formData.append('safety_measures', safetyMeasures.join(', '));

    const advertisingMedia = [];
    document.querySelectorAll('input[name="advertising_media[]"]:checked').forEach(cb => {
      advertisingMedia.push(cb.value);
    });
    formData.delete('advertising_media[]');
    formData.append('advertising_media', advertisingMedia.join(', '));

    // Validation
    if (!formData.get('application_id')) {
      showFeedbackError('Please enter Application Number');
      return;
    }
    if (!formData.get('customer_name')) {
      showFeedbackError('Please enter Service Receiver Name');
      return;
    }
    if (!formData.get('customer_number')) {
      showFeedbackError('Please enter Service Receiver Contact');
      return;
    }
    if (!safetyMeasures.length) {
      showFeedbackError('Please select at least one safety measure');
      return;
    }
    if (!advertisingMedia.length) {
      showFeedbackError('Please select how you heard about the service');
      return;
    }

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    // Clear error message
    hideFeedbackError();

    // Submit via AJAX
    fetch('/public-feedback', {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Show success modal
        document.getElementById('feedback-success-message').textContent = data.message;
        showFeedbackSuccessModal();

        // Reset form
        form.reset();
        document.getElementById('form_loaded_at').value = Math.floor(Date.now() / 1000);
      } else {
        showFeedbackError(data.message || 'An error occurred. Please try again.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showFeedbackError('An error occurred while submitting feedback. Please try again.');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit Feedback';
    });
  });

  function showFeedbackError(message) {
    const errorDiv = document.getElementById('feedback-error');
    errorDiv.textContent = message;
    errorDiv.classList.remove('hidden');
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function hideFeedbackError() {
    const errorDiv = document.getElementById('feedback-error');
    errorDiv.classList.add('hidden');
  }

  function showFeedbackSuccessModal() {
    const modal = document.getElementById('feedback-success-modal');
    modal.style.display = 'flex';

    // Countdown timer
    let countdown = 5;
    const countdownSpan = document.getElementById('feedback-countdown');

    const timer = setInterval(function() {
      countdown--;
      countdownSpan.textContent = countdown;

      if (countdown <= 0) {
        clearInterval(timer);
        closeFeedbackModal();
      }
    }, 1000);
  }

  function closeFeedbackModal() {
    document.getElementById('feedback-success-modal').style.display = 'none';
  }

  // Make closeFeedbackModal globally accessible
  window.closeFeedbackModal = closeFeedbackModal;
</script>
@endpush
