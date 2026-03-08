<!-- Last Modified: March 8, 2026
Developed By: Streams Tech Ltd.
Description: FSM Application Form - Vue 3 Implementation -->

<!-- FSM APPLICATION TAB -->
<div class="tab-content p-5 md:p-10 min-h-[calc(100vh-100px)] animate-fadeIn">
    <div class="bg-white p-5 md:p-8 rounded-xl shadow-lg border border-slate-200 w-full max-w-6xl mx-auto">
        <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold text-center text-slate-900 mb-6 md:mb-8">FSM Application Form</h1>

        <form @submit.prevent="handleSubmit" class="needs-validation" novalidate>
            @csrf

            <!-- Tax Information Section -->
            <fieldset class="app_fieldset">
                <legend>Tax Information</legend>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">
                            Do you have a Tax ID? <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="hasTaxId"
                            :class="['form-control w-full px-4 py-3 rounded-lg border-2 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10', fieldErrors.has_tax_id ? 'border-red-500' : 'border-gray-300']"
                        >
                            <option value="">Please select</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                        <span v-if="fieldErrors.has_tax_id" class="text-red-500 text-sm mt-1 block">@{{ fieldErrors.has_tax_id }}</span>
                    </div>
                </div>

                <div v-show="showTaxIdField" class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">
                            Tax ID <span v-if="showTaxIdField" class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="taxId"
                            @input="taxId = formatTaxId($event.target.value)"
                            :class="['form-control w-full px-4 py-3 rounded-lg border-2 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10', fieldErrors.tax_id ? 'border-red-500' : 'border-gray-300']"
                            placeholder="##-###-####-##"
                        >
                        <span v-if="fieldErrors.tax_id" class="text-red-500 text-sm mt-1 block">@{{ fieldErrors.tax_id }}</span>
                    </div>
                </div>
            </fieldset>

            <!-- Customer Information Section -->
            <fieldset class="app_fieldset">
                <legend>Customer Information</legend>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">
                            Customer Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="customerName"
                            :class="['form-control w-full px-4 py-3 rounded-lg border-2 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10', fieldErrors.customer_name ? 'border-red-500' : 'border-gray-300']"
                            placeholder="Customer Name"
                        >
                        <span v-if="fieldErrors.customer_name" class="text-red-500 text-sm mt-1 block">@{{ fieldErrors.customer_name }}</span>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">
                            Contact No. <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="tel"
                            v-model="customerContact"
                            @input="customerContact = formatPhone($event.target.value)"
                            :class="['form-control w-full px-4 py-3 rounded-lg border-2 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10', fieldErrors.customer_contact ? 'border-red-500' : 'border-gray-300']"
                            placeholder="01#########"
                        >
                        <span v-if="fieldErrors.customer_contact" class="text-red-500 text-sm mt-1 block">@{{ fieldErrors.customer_contact }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">
                            Holding Owner Name
                        </label>
                        <input
                            type="text"
                            v-model="holdingOwnerName"
                            :class="['form-control w-full px-4 py-3 rounded-lg border-2 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10', fieldErrors.holding_owner_name ? 'border-red-500' : 'border-gray-300']"
                            placeholder="Holding Owner Name"
                        >
                        <span v-if="fieldErrors.holding_owner_name" class="text-red-500 text-sm mt-1 block">@{{ fieldErrors.holding_owner_name }}</span>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">
                            Ward <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="ward"
                            :class="['form-control w-full px-4 py-3 rounded-lg border-2 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10', fieldErrors.ward ? 'border-red-500' : 'border-gray-300']"
                        >
                            <option value="">Select Ward</option>
                            <option v-for="wardItem in wards" :key="wardItem" :value="wardItem">@{{ wardItem }}</option>
                        </select>
                        <span v-if="fieldErrors.ward" class="text-red-500 text-sm mt-1 block">@{{ fieldErrors.ward }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">
                            Road Name <small class="text-gray-500">(Optional)</small>
                        </label>
                        <select
                            v-model="roadCode"
                            :class="['form-control w-full px-4 py-3 rounded-lg border-2 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10', fieldErrors.road_code ? 'border-red-500' : 'border-gray-300']"
                        >
                            <option value="">Select Road</option>
                            <option v-for="road in roadOptions" :key="road.id" :value="road.id">@{{ road.text }}</option>
                        </select>
                        <span v-if="fieldErrors.road_code" class="text-red-500 text-sm mt-1 block">@{{ fieldErrors.road_code }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">
                            Address <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="address"
                            :class="['form-control w-full px-4 py-3 rounded-lg border-2 text-base resize-y min-h-[100px] transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10', fieldErrors.address ? 'border-red-500' : 'border-gray-300']"
                            rows="3"
                            placeholder="Address"
                        ></textarea>
                        <span v-if="fieldErrors.address" class="text-red-500 text-sm mt-1 block">@{{ fieldErrors.address }}</span>
                    </div>
                </div>
            </fieldset>

            <!-- Service Info Section -->
            <fieldset class="app_fieldset">
                <legend>Service Info</legend>

                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">
                            Proposed Emptying Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            v-model="proposedEmptyingDate"
                            :class="['form-control w-full px-4 py-3 rounded-lg border-2 text-base transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10', fieldErrors.proposed_emptying_date ? 'border-red-500' : 'border-gray-300']"
                        >
                        <span v-if="fieldErrors.proposed_emptying_date" class="text-red-500 text-sm mt-1 block">@{{ fieldErrors.proposed_emptying_date }}</span>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="block text-gray-800 font-semibold mb-2 text-base">
                            Notes / Comments <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="notes"
                            :class="['form-control w-full px-4 py-3 rounded-lg border-2 text-base resize-y min-h-[100px] transition-all duration-300 focus:outline-none focus:border-[#0056b3] focus:ring-4 focus:ring-[#0056b3]/10', fieldErrors.notes ? 'border-red-500' : 'border-gray-300']"
                            rows="3"
                            placeholder="Additional Notes/Comments"
                        ></textarea>
                        <span v-if="fieldErrors.notes" class="text-red-500 text-sm mt-1 block">@{{ fieldErrors.notes }}</span>
                    </div>
                </div>
            </fieldset>

            <div class="text-center mt-4">
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="mx-auto bg-primary text-white py-4 rounded-2xl font-bold text-lg hover:shadow-lg hover:shadow-primary/30 active:scale-[0.98] transition-all uppercase tracking-wider px-8 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    @{{ isSubmitting ? 'Submitting...' : 'Submit Application' }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div v-if="showModal" class="fsm-modal-overlay">
    <div class="fsm-modal-content">
        <div class="fsm-modal-icon">
            <span class="material-icons text-6xl text-white">check_circle</span>
        </div>
        <h3 class="text-2xl font-bold text-center text-gray-800 mb-3">Success!</h3>
        <p class="text-center text-gray-600 mb-4">@{{ successMessage }}</p>
        <p class="text-center text-sm text-gray-500 mb-4">This modal will close in <span>@{{ countdown }}</span> seconds...</p>
        <div class="text-center">
            <button
                @click="closeModal"
                class="px-6 py-2 bg-gradient-to-br from-[#007bff] to-[#0056b3] text-white border-none rounded-lg font-semibold cursor-pointer transition-all duration-300 hover:from-[#0056b3] hover:to-[#003d82]"
            >
                Close
            </button>
        </div>
    </div>
</div>
