<!DOCTYPE html>
<html>
<head>
    <title>Waitlist Reminder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            height: 80px;
        }
        h1 {
            font-size: 20px;
            color: #333;
        }
        p {
            font-size: 16px;
            color: #555;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #1976D2;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover {
            background-color: #1565C0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ url('images/Saplings_Logo_Linear_For_White.png') }}" alt="Saplings Logo">
        </div>
        <h1>You're Still on the Waitlist!</h1>
        <p>We wanted to let you know that you're still on the Saplings Early Learning Centres Waitlist. Please click the link below to update your information or to remove yourself from the list.x</p>
        <a href="{{ $url }}" target="_blank" style="display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #1976D2; color: #ffffff; text-decoration: none; border-radius: 4px;">Update Information or Opt Out</a>
        <p style="margin-top: 30px;"></p>
        <p>If you no longer wish to remain on the waitlist, you can opt out directly from the update page.</p>
    </div>
</body>
</html>
