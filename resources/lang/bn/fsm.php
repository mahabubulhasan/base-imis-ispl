<?php
// Last Modified: 2026-08-04
// Developed By: Streams Tech Ltd.
// Description: এফএসএম আবেদন ফরমের অনুবাদ স্ট্রিং

return [
    'form_title' => 'FSM আবেদন ফরম',

    // Tax Information Section
    'tax_section' => 'কর সংক্রান্ত তথ্য',
    'do_you_have_tax_code' => 'আপনার কি ট্যাক্স কোড আছে?',
    'yes' => 'হ্যাঁ',
    'no' => 'না',
    'tax_code' => 'ট্যাক্স কোড',
    'tax_code_placeholder' => 'ww-rrr-hhhh-xx',

    // Applicant Information Section
    'applicant_section' => 'আবেদনকারীর তথ্য',
    'applicant_name' => 'নাম',
    'applicant_name_placeholder' => 'নাম লিখুন',
    'contact_no' => 'যোগাযোগ নম্বর',
    'contact_placeholder' => '01#########',
    'holding_owner_name' => 'হোল্ডিং মালিকের নাম',
    'holding_owner_placeholder' => 'হোল্ডিং মালিকের নাম লিখুন',
    'ward' => 'ওয়ার্ড',
    'select_ward' => 'ওয়ার্ড নির্বাচন করুন',

    // Location Information Section
    'location_section' => 'অবস্থানের তথ্য',
    'address' => 'ঠিকানা',
    'address_placeholder' => 'ঠিকানা লিখুন',
    'map_instructions' => 'অবস্থান নির্বাচন করতে ম্যাপে ক্লিক করুন',

    // Service Information Section
    'service_section' => 'সেবার তথ্য',
    'proposed_emptying_date' => 'প্রস্তাবিত খালি করার তারিখ',
    'notes' => 'নোট',
    'notes_placeholder' => 'অতিরিক্ত কোনো তথ্য বা নোট থাকলে লিখুন',

    // Validation Messages
    'validation_tax_id_required' => 'ট্যাক্স কোড দিতে হবে।',
    'validation_tax_id_incomplete' => 'ট্যাক্স কোডটি অসম্পূর্ণ।',
    'validation_tax_id_invalid' => 'ট্যাক্স কোড XX-XXX-XXXX-XX ফরম্যাটে হতে হবে!',
    'validation_customer_name_required' => 'আবেদনকারীর নাম দিতে হবে।',
    'validation_customer_contact_required' => 'যোগাযোগ নম্বর দিতে হবে।',
    'validation_ward_required' => 'ওয়ার্ড দিতে হবে।',
    'validation_address_required' => 'ঠিকানা দিতে হবে।',
    'validation_emptying_date_required' => 'প্রস্তাবিত খালি করার তারিখ দিতে হবে।',

    // Success Messages
    'submission_success' => 'আবেদন সফলভাবে জমা হয়েছে!',
    'submission_success_message' => 'আপনার FSM আবেদন সফলভাবে জমা হয়েছে। রেফারেন্স নম্বর: :reference_number',
    'redirect_message' => ':seconds সেকেন্ড পর আপনাকে পরবর্তী পৃষ্ঠায় নেওয়া হচ্ছে...',
    'submit_another' => 'আরেকটি আবেদন করুন',
    'back_to_home' => 'হোমে ফিরে যান',

    // Loading Messages
    'loading_wards' => 'ওয়ার্ডের তালিকা লোড হচ্ছে...',
    'loading_owner_data' => 'মালিকের তথ্য লোড হচ্ছে...',
];
