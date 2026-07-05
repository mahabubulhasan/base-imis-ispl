{{-- Last Modified: 2026-07-05 --}}
{{-- Developed By: Streams Tech Ltd. --}}
{{-- Description: Payment cancelled page --}}

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পেমেন্ট বাতিল</title>
    <style>
        body {
            font-family: 'Kalpurush', 'Vrinda', 'SolaimanLipi', Arial, sans-serif;
            background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .cancel-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            text-align: center;
            padding: 40px 30px;
        }

        .cancel-icon {
            width: 80px;
            height: 80px;
            background: #ffc107;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .cancel-icon::after {
            content: "✕";
            color: white;
            font-size: 50px;
            font-weight: bold;
        }

        h1 {
            color: #ffc107;
            font-size: 28px;
            margin-bottom: 15px;
        }

        p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .btn-retry {
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
        }

        .btn-retry:hover {
            background-color: #1e4029;
        }
    </style>
</head>
<body>
    <div class="cancel-container">
        <div class="cancel-icon"></div>
        <h1>পেমেন্ট বাতিল হয়েছে</h1>
        <p>আপনি পেমেন্ট প্রক্রিয়া বাতিল করেছেন। আবার চেষ্টা করতে চাইলে নিচের বাটনে ক্লিক করুন।</p>

        <a href="{{ url('/') }}" class="btn-retry">
            🏠 হোমে ফিরে যান
        </a>
    </div>
</body>
</html>
