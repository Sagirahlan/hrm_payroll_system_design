
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            padding: 10px 0;
            border-bottom: 2px solid #007bff;
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #007bff;
        }
        .header .logo {
            float: left;
            height: 50px;
            width: auto;
        }
        .header .company-details {
            text-align: right;
        }
        .content {
            margin-bottom: 40px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .footer .page-number:before {
            content: "Page " counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        {{-- <img src="" alt="Logo" class="logo"> --}}
        <div class="company-details">
            <h2>Your Company Name</h2>
            <p>123 Company Address, City, State, ZIP</p>
            <p>Email: contact@yourcompany.com | Phone: (123) 456-7890</p>
        </div>
        <h1>@yield('title')</h1>
    </div>

    <div class="container">
        <div class="content">
            @yield('content')
        </div>
    </div>

    <div class="footer">
        <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
        <p class="page-number"></p>
    </div>
</body>
</html>
