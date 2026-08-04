<?php
// Last Modified: 2026-08-04
// Developed By: Streams Tech Ltd.
// Description: FSM Application form translation strings

return [
    'form_title' => 'FSM Application Form',

    // Tax Information Section
    'tax_section' => 'Tax Information',
    'do_you_have_tax_code' => 'Do you have a Tax Code?',
    'yes' => 'Yes',
    'no' => 'No',
    'tax_code' => 'Tax Code',
    'tax_code_placeholder' => 'ww-rrr-hhhh-xx',

    // Applicant Information Section
    'applicant_section' => 'Applicant Information',
    'applicant_name' => 'Applicant Name',
    'applicant_name_placeholder' => 'Applicant Name',
    'contact_no' => 'Contact No.',
    'contact_placeholder' => '01#########',
    'holding_owner_name' => 'Holding Owner Name',
    'holding_owner_placeholder' => 'Holding Owner Name',
    'ward' => 'Ward',
    'select_ward' => 'Select a Ward',

    // Location Information Section
    'location_section' => 'Location Information',
    'address' => 'Address',
    'address_placeholder' => 'Enter the address',
    'map_instructions' => 'Click on the map to select location',

    // Service Information Section
    'service_section' => 'Service Information',
    'proposed_emptying_date' => 'Proposed Emptying Date',
    'notes' => 'Notes',
    'notes_placeholder' => 'Any additional notes or comments',

    // Validation Messages
    'validation_tax_id_required' => 'Tax Code is required',
    'validation_tax_id_incomplete' => 'Tax Code is incomplete',
    'validation_tax_id_invalid' => 'Tax Code format is invalid',
    'validation_customer_name_required' => 'Applicant Name is required',
    'validation_customer_contact_required' => 'Contact Number is required',
    'validation_ward_required' => 'Ward is required',
    'validation_address_required' => 'Address is required',
    'validation_emptying_date_required' => 'Proposed Emptying Date is required',

    // Success Messages
    'submission_success' => 'Application submitted successfully!',
    'submission_success_message' => 'Your FSM application has been submitted successfully. Reference number: :reference_number',
    'redirect_message' => 'Redirecting in :seconds seconds...',
    'submit_another' => 'Submit Another Application',
    'back_to_home' => 'Back to Home',

    // Loading Messages
    'loading_wards' => 'Loading wards...',
    'loading_owner_data' => 'Loading owner information...',
];
