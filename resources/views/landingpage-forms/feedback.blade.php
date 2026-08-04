{{--
// Last Modified: 2026-08-04
// Developed By: Streams Tech Ltd.
// Description: Public feedback landing page form with language support
--}}
<!-- FEEDBACK TAB -->
<div id="feedback" class="tab-content p-5 md:p-10 min-h-[calc(100vh-100px)] animate-fadeIn">
    <div class="bg-white p-5 md:p-8 rounded-xl shadow-lg border border-slate-200 w-full max-w-6xl mx-auto">
        <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-center text-slate-900 mb-6 md:mb-8">{{ __('feedback.form_title') }}</h1>

        <form id="feedback-form" class="needs-validation" novalidate @submit.prevent="handleSubmit">
            @csrf

            <!-- Error Message Display -->
            <div v-if="errorMessage" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                @{{ errorMessage }}
            </div>
            <!-- Application Information Section -->
            <fieldset class="app_fieldset">
                <legend>{{ __('feedback.application_section') }}</legend>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="application_id" class="block text-gray-800 font-semibold mb-2 text-base">{{ __('feedback.application_number') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="application_id" name="application_id" placeholder="{{ __('feedback.application_number_placeholder') }}" required
                            v-model="applicationId" @blur="fetchApplicationData"
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10">
                        <span v-if="fieldErrors.application_id" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.application_id }}</span>
                        <div v-if="isFetchingData" class="mt-2 flex items-center text-blue-600 text-sm">
                            <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ __('feedback.loading_application_details') }}</span>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="customer_name" class="block text-gray-800 font-semibold mb-2 text-base">{{ __('feedback.service_receiver_name') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" placeholder="{{ __('feedback.service_receiver_placeholder') }}" required
                            v-model="customerName"
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10">
                        <span v-if="fieldErrors.customer_name" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.customer_name }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="customer_number" class="block text-gray-800 font-semibold mb-2 text-base">{{ __('feedback.service_receiver_contact') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="customer_number" name="customer_number" placeholder="{{ __('feedback.service_receiver_placeholder') }}" required
                            v-model="customerNumber"
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10">
                        <span v-if="fieldErrors.customer_number" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.customer_number }}</span>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="service_provider_name" class="block text-gray-800 font-semibold mb-2 text-base">{{ __('feedback.service_provider_name') }}</label>
                        <input type="text" id="service_provider_name" name="service_provider_name" readonly
                            v-model="serviceProviderName"
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-200 bg-gray-50 text-base cursor-not-allowed">
                        <span v-if="fieldErrors.service_provider_name" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.service_provider_name }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="service_provider_contact" class="block text-gray-800 font-semibold mb-2 text-base">{{ __('feedback.service_provider_contact') }}</label>
                        <input type="text" id="service_provider_contact" name="service_provider_contact" readonly
                            v-model="serviceProviderContact"
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-200 bg-gray-50 text-base cursor-not-allowed">
                        <span v-if="fieldErrors.service_provider_contact" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.service_provider_contact }}</span>
                    </div>
                </div>
            </fieldset>

            <!-- Service Feedback Section -->
            <fieldset class="app_fieldset">
                <legend>{{ __('feedback.feedback_section') }}</legend>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">{{ __('feedback.safety_equipment_question') }} <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="safety_measures" value="Yes" v-model="safetyMeasures" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.yes') }}</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="safety_measures" value="No" v-model="safetyMeasures" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.no') }}</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="safety_measures" value="Unknown" v-model="safetyMeasures" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.unknown') }}</span>
                            </label>
                        </div>
                        <span v-if="fieldErrors.safety_measures" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.safety_measures }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">{{ __('feedback.q2_emptier_attitude') }} <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="fsm_quality_level" value="3" v-model="fsmQualityLevel" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.satisfied') }}</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="fsm_quality_level" value="2" v-model="fsmQualityLevel" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.neutral') }}</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="fsm_quality_level" value="1" v-model="fsmQualityLevel" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.dissatisfied') }}</span>
                            </label>
                        </div>
                        <span v-if="fieldErrors.fsm_quality_level" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.fsm_quality_level }}</span>
                    </div>
                </div>

                <div class="row" v-if="fsmQualityLevel === '1'">
                    <div class="col-12 mb-3">
                        <textarea id="dissatisfaction_comment_q2" name="dissatisfaction_comment_q2" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span v-if="fieldErrors.dissatisfaction_comment_q2" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.dissatisfaction_comment_q2 }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">{{ __('feedback.q3_response_time') }} <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_delivery_efficiency" value="3" v-model="serviceDeliveryEfficiency" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.satisfied') }}</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_delivery_efficiency" value="2" v-model="serviceDeliveryEfficiency" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.neutral') }}</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_delivery_efficiency" value="1" v-model="serviceDeliveryEfficiency" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.dissatisfied') }}</span>
                            </label>
                        </div>
                        <span v-if="fieldErrors.service_delivery_efficiency" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.service_delivery_efficiency }}</span>
                    </div>
                </div>

                <div class="row" v-if="serviceDeliveryEfficiency === '1'">
                    <div class="col-12 mb-3">
                        <textarea id="dissatisfaction_comment_q3" name="dissatisfaction_comment_q3" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span v-if="fieldErrors.dissatisfaction_comment_q3" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.dissatisfaction_comment_q3 }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">{{ __('feedback.q4_overall_satisfaction') }} <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="overall_satisfaction" value="3" v-model="overallSatisfaction" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.satisfied') }}</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="overall_satisfaction" value="2" v-model="overallSatisfaction" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.neutral') }}</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="overall_satisfaction" value="1" v-model="overallSatisfaction" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.dissatisfied') }}</span>
                            </label>
                        </div>
                        <span v-if="fieldErrors.overall_satisfaction" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.overall_satisfaction }}</span>
                    </div>
                </div>

                <div class="row" v-if="overallSatisfaction === '1'">
                    <div class="col-12 mb-3">
                        <textarea id="dissatisfaction_comment_q4" name="dissatisfaction_comment_q4" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span v-if="fieldErrors.dissatisfaction_comment_q4" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.dissatisfaction_comment_q4 }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">{{ __('feedback.q5_price_satisfaction') }} <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_quality_price" value="3" v-model="serviceQualityPrice" required class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.satisfied') }}</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_quality_price" value="2" v-model="serviceQualityPrice" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.neutral') }}</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="radio" name="service_quality_price" value="1" v-model="serviceQualityPrice" class="w-5 h-5 text-[#0056b3] border-gray-300 focus:ring-[#0056b3]">
                                <span class="text-gray-700">{{ __('feedback.dissatisfied') }}</span>
                            </label>
                        </div>
                        <span v-if="fieldErrors.service_quality_price" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.service_quality_price }}</span>
                    </div>
                </div>

                <div class="row" v-if="serviceQualityPrice === '1'">
                    <div class="col-12 mb-3">
                        <textarea id="dissatisfaction_comment_q5" name="dissatisfaction_comment_q5" rows="3" placeholder="Please explain..."
                            class="form-control w-full px-4 py-3 rounded-lg border-2 border-gray-300 text-base resize-y transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10"></textarea>
                        <span v-if="fieldErrors.dissatisfaction_comment_q5" class="text-red-600 text-sm mt-1 block">@{{ fieldErrors.dissatisfaction_comment_q5 }}</span>
                    </div>
                </div>
            </fieldset>

            <!-- Hidden fields for anti-spam -->
            <input type="text" name="website" id="website" style="display:none" tabindex="-1" autocomplete="off" v-model="website">
            <input type="hidden" name="form_loaded_at" id="form_loaded_at" v-model="formLoadedAt">
            <input type="hidden" name="customer_gender" id="customer_gender" v-model="customerGender">

            <div class="text-center mt-4">
                <button type="submit" id="feedback-submit-btn" :disabled="isSubmitting" class="mx-auto bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:shadow-lg hover:shadow-primary/30 active:scale-[0.98] transition-all uppercase tracking-wider px-8">
                    @{{ isSubmitting ? 'Submitting...' : 'Submit Feedback' }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Feedback Success Modal -->
<div v-if="showModal" class="fsm-modal-overlay">
    <div class="fsm-modal-content">
        <div class="fsm-modal-icon">
            <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-center text-gray-800 mb-3">Success!</h3>
        <p class="text-center text-gray-600 mb-4" id="feedback-success-message">Your feedback has been submitted successfully!</p>
        <p class="text-center text-sm text-gray-500 mb-4">This modal will close in <span id="feedback-countdown">@{{ countdown }}</span> seconds...</p>
        <div class="text-center">
            <button onclick="closeFeedbackModal()" class="px-6 py-2 bg-gradient-to-br from-[#007bff] to-[#0056b3] text-white rounded-lg font-semibold hover:from-[#0056b3] hover:to-[#003d82]">Close</button>
        </div>
    </div>
</div>
