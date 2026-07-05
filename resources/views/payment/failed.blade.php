{{-- Last Modified: 2026-07-05 --}}
{{-- Developed By: Streams Tech Ltd. --}}
{{-- Description: Payment failed page --}}

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পেমেন্ট ব্যর্থ</title>
    <style>
        body {
            font-family: 'Kalpurush', 'Vrinda', 'SolaimanLipi', Arial, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .failed-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            text-align: center;
            padding: 40px 30px;
        }

        .failed-icon {
            width: 80px;
            height: 80px;
            background: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .failed-icon::after {
            content: "!";
            color: white;
            font-size: 50px;
            font-weight: bold;
        }

        h1 {
            color: #dc3545;
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
            margin: 5px;
        }

        .btn-retry:hover {
            background-color: #1e4029;
        }

        .btn-home {
            background-color: #6c757d;
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }

        .btn-home:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="failed-container">
        <div class="failed-icon"></div>
        <h1>পেমেন্ট ব্যর্থ হয়েছে!</h1>
        <p>দুঃখিত! আপনার পেমেন্ট প্রক্রিয়া সফল হয়নি। অনুগ্রহ করে আবার চেষ্টা করুন অথবা সহায়তার জন্য যোগাযোগ করুন।</p>

        <div>
            <a href="{{ url('/') }}" class="btn-home">
                🏠 হোমে ফিরে যান
            </a>
        </div>
    </div>
</body>
</html>
