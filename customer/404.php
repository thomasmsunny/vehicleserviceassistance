<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - AutoFix</title>
    
    <!-- Main CSS File -->
    <link href="customerpage/error/css/main.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: var(--background-color);
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .error-container {
            text-align: center;
            padding: 40px;
            background: var(--surface-color);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
        }
        .error-code {
            font-size: 72px;
            font-weight: 700;
            color: var(--accent-color);
            margin: 0;
        }
        .error-title {
            font-size: 32px;
            margin: 20px 0;
            color: var(--heading-color);
        }
        .error-message {
            font-size: 18px;
            color: var(--default-color);
            margin-bottom: 30px;
        }
        .btn-home {
            background-color: var(--accent-color);
            color: var(--contrast-color);
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-home:hover {
            background-color: color-mix(in srgb, var(--accent-color), transparent 20%);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page Not Found</h2>
        <p class="error-message">Sorry, the page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        <a href="customerpage/index.php" class="btn-home">Go to Homepage</a>
    </div>
</body>
</html>