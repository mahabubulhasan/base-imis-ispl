{{-- Last Modified: 2026-07-05 --}}
{{-- Developed By: Streams Tech Ltd. --}}
{{-- Description: Payment success page that redirects to receipt --}}

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পেমেন্ট সফল</title>
    <style>
        body {
            font-family: 'Kalpurush', 'Vrinda', 'SolaimanLipi', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            text-align: center;
            padding: 40px 30px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-out;
        }

        .success-icon::after {
            content: "✓";
            color: white;
            font-size: 50px;
            font-weight: bold;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        h1 {
            color: #28a745;
            font-size: 28px;
            margin-bottom: 15px;
        }

        p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .btn-receipt {
            background-color: #2d5f3f;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-receipt:hover {
            background-color: #1e4029;
        }

        .redirect-message {
            font-size: 14px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon"></div>
        <h1>পেমেন্ট সফল হয়েছে!</h1>
        <p>আপনার পেমেন্ট সফলভাবে সম্পন্ন হয়েছে। আপনার রসিদ দেখতে নিচের বাটনে ক্লিক করুন।</p>

        @php
            // In a real scenario, you'd get the payment ID from the session or URL parameter
            $transaction_id = request()->query('transId');
        @endphp

        <a href="{{ route('payment.receipt', $transaction_id) }}" class="btn-receipt">
            📄 রসিদ দেখুন
        </a>

        <div class="redirect-message">
            <script>
                // Auto-redirect to receipt after 3 seconds
                setTimeout(function() {
                    window.location.href = "{{ route('payment.receipt', $transaction_id) }}";
                }, 3000);
            </script>
            ৩ সেকেন্ডে স্বয়ংক্রিয়ভাবে রসিদে নিয়ে যাওয়া হবে...
        </div>
    </div>
</body>
</html>
