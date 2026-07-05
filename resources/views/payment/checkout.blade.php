{{-- Last Modified: 2026-07-05 --}}
{{-- Developed By: Streams Tech Ltd. --}}
{{-- Description: Payment checkout page for FSM service application --}}

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পেমেন্ট চেকআউট</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Kalpurush', 'Vrinda', 'SolaimanLipi', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .checkout-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .checkout-header {
            background: linear-gradient(135deg, #2d5f3f 0%, #1e4029 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .checkout-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .checkout-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .checkout-content {
            padding: 30px;
        }

        .section-title {
            background-color: #f0f0f0;
            border-left: 4px solid #2d5f3f;
            padding: 12px 15px;
            font-weight: bold;
            font-size: 18px;
            margin: 25px 0 15px 0;
            color: #2d5f3f;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-grid.full {
            grid-template-columns: 1fr;
        }

        .info-item {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .info-label {
            font-weight: bold;
            color: #2d5f3f;
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }

        .info-value {
            color: #333;
            font-size: 15px;
            line-height: 1.5;
            word-break: break-word;
        }

        .payment-summary {
            background: #f0f8ff;
            border: 2px solid #2d5f3f;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }

        .payment-summary h3 {
            color: #2d5f3f;
            font-size: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #ddd;
            font-size: 15px;
        }

        .payment-row:last-child {
            border-bottom: none;
        }

        .payment-row.total {
            border-top: 2px solid #2d5f3f;
            border-bottom: none;
            padding: 15px 0;
            font-weight: bold;
            font-size: 18px;
            color: #2d5f3f;
        }

        .payment-label {
            color: #666;
        }

        .payment-amount {
            color: #2d5f3f;
            font-weight: 600;
        }

        .checkbox-group {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #333;
            cursor: pointer;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
            cursor: pointer;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2d5f3f 0%, #1e4029 100%);
            color: white;
        }

        .btn-primary:hover {
            box-shadow: 0 5px 20px rgba(45, 95, 63, 0.4);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-disabled {
            background: #ccc;
            color: #999;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .warning-message {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #856404;
            font-size: 14px;
        }

        .success-check {
            color: #28a745;
            font-weight: bold;
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .checkout-header h1 {
                font-size: 24px;
            }

            .checkout-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <!-- Header -->
        <div class="checkout-header">
            <h1>পেমেন্ট চেকআউট</h1>
            <p>পয়ঃবর্জ্য সংগ্রহ সেবা</p>
        </div>

        <!-- Content -->
        <div class="checkout-content">
            <!-- Warning Message -->
            <div class="warning-message">
                <strong>⚠️ গুরুত্বপূর্ণ:</strong> নিম্নলিখিত তথ্য যাচাই করে নিশ্চিত করুন যে সবকিছু সঠিক আছে।
            </div>

            <!-- Applicant Information Section -->
            <div class="section-title">
                আবেদনকারীর তথ্য
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">আবেদনকারীর নাম</span>
                    <span class="info-value">{{ $application->applicant_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">মোবাইল নম্বর</span>
                    <span class="info-value">
                        @php
                            $contactNumber = $application->applicant_contact;
                            if (strlen($contactNumber) === 10) {
                                $contactNumber = '0' . $contactNumber;
                            }
                        @endphp
                        {{ $contactNumber }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">ট্যাক্স কোড</span>
                    <span class="info-value">{{ $application->tax_code }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">ওয়ার্ড</span>
                    <span class="info-value">{{ $application->ward ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Address Information -->
            <div class="section-title">
                ঠিকানা তথ্য
            </div>
            <div class="info-grid full">
                <div class="info-item">
                    <span class="info-label">সম্পূর্ণ ঠিকানা</span>
                    <span class="info-value">{{ $application->address }}</span>
                </div>
            </div>

            <!-- Service Information -->
            <div class="section-title">
                সেবা তথ্য
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">প্রস্তাবিত খালি করার তারিখ</span>
                    <span class="info-value">
                        @if($application->proposed_emptying_date)
                            {{ \Carbon\Carbon::parse($application->proposed_emptying_date)->locale('bn')->isoFormat('DD MMMM YYYY') }}
                        @else
                            N/A
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">BIN (বিল্ডিং আইডেন্টিফাই নম্বর)</span>
                    <span class="info-value">{{ $application->bin ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="payment-summary">
                <h3>💰 পেমেন্ট সারসংক্ষেপ</h3>

                <div class="payment-row">
                    <span class="payment-label">সেবা</span>
                    <span class="payment-amount">পয়ঃবর্জ্য সংগ্রহ সেবা</span>
                </div>

                <div class="payment-row">
                    <span class="payment-label">সেবা ফি</span>
                    <span class="payment-amount">১,৫০০.০০ টাকা</span>
                </div>

                <div class="payment-row">
                    <span class="payment-label">পেমেন্ট গেটওয়ে</span>
                    <span class="payment-amount">অনলাইন (Ekpay)</span>
                </div>

                <div class="payment-row total">
                    <span class="payment-label">মোট পরিমাণ</span>
                    <span class="payment-amount">১,৫০০.০০ টাকা</span>
                </div>
            </div>

            <!-- Terms and Conditions -->
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" id="terms-checkbox" required>
                    আমি শর্তাবলী এবং গোপনীয়তা নীতি সম্মত করছি
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="button-group">
                <form id="payment-form" method="POST" action="{{ route('payment.store', $application->id) }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="application_id" value="{{ $application->id }}">
                    <input type="hidden" name="amount" value="1500">
                    <button type="submit" class="btn btn-primary" id="checkout-btn" disabled>
                        🔒 নিরাপদে পেমেন্ট করুন
                    </button>
                </form>

                <a href="/#/fsm" class="btn btn-secondary">
                    ← ফিরে যান
                </a>
            </div>

            <!-- Security Note -->
            <div style="text-align: center; margin-top: 30px; color: #999; font-size: 13px;">
                <p>
                    <span class="success-check">🔒</span> আপনার পেমেন্ট সুরক্ষিত এবং এনক্রিপ্ট করা হয়।<br>
                    আমরা Ekpay গেটওয়ে ব্যবহার করি সর্বোচ্চ নিরাপত্তার জন্য।
                </p>
            </div>
        </div>
    </div>

    <script>
        // Enable button only when checkbox is checked
        const checkbox = document.getElementById('terms-checkbox');
        const checkoutBtn = document.getElementById('checkout-btn');

        checkbox.addEventListener('change', function() {
            checkoutBtn.disabled = !this.checked;
            if (this.checked) {
                checkoutBtn.classList.remove('btn-disabled');
            } else {
                checkoutBtn.classList.add('btn-disabled');
            }
        });
    </script>
</body>
</html>
