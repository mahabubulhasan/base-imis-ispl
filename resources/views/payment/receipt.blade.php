{{-- Last Modified: 2026-07-05 --}}
{{-- Developed By: Streams Tech Ltd. --}}
{{-- Description: Printable payment receipt for Lakshmipur Pourashava --}}

<?php
// Localize numbers to Bengali using PHP's NumberFormatter
function toBengaliNumber($number) {
    $formatter = new \NumberFormatter('bn_BD', \NumberFormatter::DECIMAL);
    return $formatter->format($number);
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রসিদ - {{ $payment->receipt_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kalpurush', 'Vrinda', 'SolaimanLipi', Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2d5f3f;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header img {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 32px;
            color: #2d5f3f;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 18px;
            color: #555;
        }

        .receipt-title {
            background-color: #2d5f3f;
            color: white;
            text-align: center;
            padding: 12px;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }

        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 20px;
        }

        .receipt-info-left {
            flex: 1;
            border: 2px solid #2d5f3f;
            padding: 15px;
        }

        .receipt-info-right {
            flex: 1;
            border: 2px solid #ddd;
            padding: 15px;
            background-color: #f9f9f9;
        }

        .info-row {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 120px;
        }

        .section-title {
            background-color: #f0f0f0;
            border-left: 4px solid #2d5f3f;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0 10px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .info-table td:first-child {
            background-color: #f9f9f9;
            font-weight: 600;
            width: 40%;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .payment-table th {
            background-color: #2d5f3f;
            color: white;
            padding: 12px;
            text-align: center;
            font-size: 16px;
            border: 1px solid #2d5f3f;
        }

        .payment-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .payment-table td:first-child {
            text-align: left;
        }

        .warning-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }

        .warning-box .warning-icon {
            color: #dc3545;
            font-size: 20px;
            margin-right: 10px;
        }

        .warning-text {
            font-size: 14px;
            line-height: 1.6;
        }

        .footer-info {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
            color: #555;
        }

        .print-button {
            text-align: center;
            margin: 30px 0 20px 0;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-print,
        .btn-download {
            background-color: #2d5f3f;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print:hover,
        .btn-download:hover {
            background-color: #1e4029;
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                padding: 20px;
                max-width: 100%;
            }

            @media print {
                .print-button {
                    display: none;
                }

                .warning-box {
                border: 2px solid #ffc107 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .header {
                border-bottom: 3px solid #2d5f3f !important;
            }

            .receipt-title,
            .payment-table th {
                background-color: #2d5f3f !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .section-title {
                border-left: 4px solid #2d5f3f !important;
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                margin: 1cm;
                size: A4;
            }
        }

        .bengali-number {
            font-family: 'Kalpurush', 'Vrinda', 'SolaimanLipi', Arial, sans-serif;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Receipt Content -->
        <div class="receipt-content">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('layout/img/logo-Lakshmipur.png') }}" alt="Lakshmipur Logo">
            <h1>লক্ষ্মীপুর পৌরসভা কার্যালয়</h1>
            <div class="subtitle">লক্ষ্মীপুর।</div>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">
            পয়ঃবর্জ্য সংগ্রহ সেবার ফি পরিশোধ রসিদ
        </div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div class="receipt-info-left">
                <div class="info-row">
                    <span class="info-label">রসিদ নম্বর:</span>
                    <strong>{{ $payment->receipt_no }}</strong>
                </div>
                <div class="info-row">
                    <span class="info-label">ট্রাজেকশন নম্বর:</span>
                    <strong>{{ $payment->transaction_id }}</strong>
                </div>
            </div>
            <div class="receipt-info-right">
                <div class="info-row">
                    <span class="info-label">ফি পরিশোধের তারিখ:</span>
                    {{ $payment->payment_timestamp }}
                </div>
                <div class="info-row">
                    <span class="info-label">ফি পরিশোধের সময়:</span>
                    {{ $payment->payment_timestamp }}
                </div>
            </div>
        </div>

        <!-- Applicant Information -->
        <div class="section-title">
            আবেদনকারীর তথ্য (Applicant Information)
        </div>
        <table class="info-table">
            <tr>
                <td>আবেদনকারীর নাম</td>
                <td>{{ $payment->applicant_name }}</td>
            </tr>
            <tr>
                <td>মোবাইল নম্বর</td>
                <td>{{ $payment->applicant_contact }}</td>
            </tr>
            <tr>
                <td>হোল্ডিং মালিকের নাম</td>
                <td>{{ $payment->holding_owner_name }}</td>
            </tr>
            <tr>
                <td>ঠিকানা</td>
                <td>{{ $payment->address }}</td>
            </tr>
            <tr>
                <td>ট্যাক্স কোড</td>
                <td>{{ $payment->tax_code }}</td>
            </tr>
        </table>

        <!-- Service Information -->
        <div class="section-title">
            সেবার তথ্য (Service Information)
        </div>
        <table class="info-table">
            <tr>
                <td>সেবার ধরন</td>
                <td>পয়ঃবর্জ্য সংগ্রহ সেবা</td>
            </tr>
            <tr>
                <td>প্রস্তাবিত খালি করার তারিখ</td>
                <td>{{ $payment->proposed_service_date }}</td>
            </tr>
        </table>

        <!-- Payment Details -->
        <div class="section-title">
            বিল পরিশোধের তথ্য (Payment Details)
        </div>
        <table class="payment-table">
            <thead>
                <tr>
                    <th>বিবরণ</th>
                    <th>পরিমাণ</th>
                    <th>ফি পরিশোধের ধরন</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>পয়ঃবর্জ্য সংগ্রহ সেবা ফি</td>
                    <td><span class="bengali-number">{{ toBengaliNumber($payment->amount) }}/-</span></td>
                    <td>অনলাইন পেমেন্ট</td>
                </tr>
            </tbody>
        </table>

        <!-- Warning Box -->
        <div class="warning-box">
            <div class="warning-text">
                <span class="warning-icon">⚠</span>
                <strong>গুরুত্বপূর্ণ সেবা বিষয়ক মতামত বা অভিযোগের জন্য ট্রাজেকশন নম্বরটি সংরক্ষণ করুন</strong>
                <div style="margin-top: 10px;">
                    <strong>আপনার ট্রাজেকশন নম্বর:</strong> {{ $payment->transaction_id }}
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-info">
            সেবা গ্রহনের পর এই ট্রাজেকশন নম্বর দ্বারা করে <strong>imislxp-new.streamstech.com</strong> ওয়েবসাইটে <strong>Feedback</strong> দিয়ে<br>
            গিয়ে আপনার মতামত প্রদান করুন।
        </div>
        </div>
        <!-- End Receipt Content -->

        <!-- Print and Download Buttons at Bottom -->
        <div class="print-button">
            <button class="btn-print" onclick="window.print()">
                <span>🖨️</span>
                <span>প্রিন্ট করুন</span>
            </button>
            <a href="{{ route('payment.download-receipt', $payment->id) }}" class="btn-download">
                <span>📥</span>
                <span>PDF ডাউনলোড করুন</span>
            </a>
        </div>
    </div>
</body>
</html>
