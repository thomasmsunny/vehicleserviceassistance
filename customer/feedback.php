<?php
session_start();

// Database connection
require_once 'includes/db_config.php';

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: customerlogin.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$success = $error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'] ?? '';
    $feedback_text = $_POST['feedback_text'] ?? '';
    
    if (!empty($rating) && !empty($feedback_text)) {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("INSERT INTO feedback (customer_id, rating, feedback_text) VALUES (?, ?, ?)");
            $stmt->execute([$customer_id, $rating, $feedback_text]);
            
            $success = "Thank you for your feedback! We appreciate your input.";
        } catch (PDOException $e) {
            $error = "Failed to submit feedback. Please try again.";
        }
    } else {
        $error = "Please provide both rating and feedback.";
    }
}

// Fetch recent feedback
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM feedback WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$customer_id]);
    $feedbacks = $stmt->fetchAll();
} catch (PDOException $e) {
    $feedbacks = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - AutoFix</title>
    <style>
        :root {
            --primary: #ff4a17;
            --primary-dark: #e04010;
            --secondary: #2c3e50;
            --accent: #3498db;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
            --border-radius: 8px;
            --box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }
        
        /* Header Styles */
        .header {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 5%;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .logo-icon {
            font-size: 28px;
            color: var(--primary);
            margin-right: 10px;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: var(--secondary);
            letter-spacing: -0.5px;
        }
        
        .logo-text span {
            color: var(--primary);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
        }
        
        .nav-menu li {
            margin-left: 25px;
        }
        
        .nav-menu a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: var(--transition);
            position: relative;
            padding: 5px 0;
        }
        
        .nav-menu a:hover {
            color: var(--primary);
        }
        
        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }
        
        .nav-menu a:hover::after {
            width: 100%;
        }
        
        .cta-button {
            background: var(--primary);
            color: white;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            border: 2px solid var(--primary);
        }
        
        .cta-button:hover {
            background: transparent;
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 74, 23, 0.3);
        }
        
        .main {
            margin-top: 80px;
            padding: 30px 5%;
        }
        
        .feedback-container {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 992px) {
            .feedback-container {
                grid-template-columns: 1fr;
            }
        }
        
        .feedback-form-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .feedback-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .feedback-header h1 {
            color: var(--secondary);
            font-size: 2.2rem;
            margin-bottom: 15px;
        }
        
        .feedback-header p {
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary);
            font-size: 16px;
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 74, 23, 0.1);
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .rating-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .rating-option {
            flex: 1;
            min-width: 60px;
            text-align: center;
        }
        
        .rating-input {
            display: none;
        }
        
        .rating-label {
            display: block;
            padding: 15px;
            background: var(--light);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            font-size: 18px;
        }
        
        .rating-input:checked + .rating-label {
            background: var(--primary);
            color: white;
            transform: scale(1.05);
        }
        
        .rating-label:hover {
            background: #e9ecef;
        }
        
        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 18px;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 74, 23, 0.4);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--secondary);
            border: 2px solid var(--secondary);
        }
        
        .btn-secondary:hover {
            background: var(--secondary);
            color: white;
        }
        
        .alert {
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .feedback-history-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .section-header h2 {
            color: var(--secondary);
            font-size: 1.8rem;
        }
        
        .feedback-item {
            background: var(--light);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .feedback-header-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .feedback-rating {
            display: flex;
            gap: 5px;
        }
        
        .star {
            color: #ffc107;
            font-size: 20px;
        }
        
        .feedback-date {
            color: var(--gray);
            font-size: 14px;
        }
        
        .feedback-text {
            color: var(--dark);
            line-height: 1.7;
        }
        
        .no-feedback {
            text-align: center;
            padding: 30px;
            color: var(--gray);
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .actions a {
            flex: 1;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .feedback-container {
                padding: 15px;
            }
            
            .feedback-form-section, .feedback-history-section {
                padding: 20px;
            }
            
            .feedback-header h1 {
                font-size: 1.8rem;
            }
            
            .rating-option {
                min-width: 50px;
            }
            
            .rating-label {
                padding: 12px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="customerpage/index.php" class="logo">
                <div class="logo-icon">🔧</div>
                <div class="logo-text">Auto<span>Fix</span></div>
            </a>
            
            <ul class="nav-menu">
                <li><a href="customerpage/index.php">Home</a></li>
                <li><a href="customerpage/index.php#about">About</a></li>
                <li><a href="customerpage/index.php#services">Services</a></li>
                <li><a href="customerpage/index.php#contact">Contact</a></li>
            </ul>
            
            <a href="profile.php" class="cta-button">My Profile</a>
        </div>
    </header>

    <main class="main">
        <div class="feedback-container">
            <div class="feedback-form-section">
                <div class="feedback-header">
                    <h1>Share Your Feedback</h1>
                    <p>We value your opinion and would love to hear about your experience</p>
                </div>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>How would you rate our service?</label>
                        <div class="rating-group">
                            <div class="rating-option">
                                <input type="radio" id="rating-1" name="rating" value="1" class="rating-input">
                                <label for="rating-1" class="rating-label">1</label>
                            </div>
                            <div class="rating-option">
                                <input type="radio" id="rating-2" name="rating" value="2" class="rating-input">
                                <label for="rating-2" class="rating-label">2</label>
                            </div>
                            <div class="rating-option">
                                <input type="radio" id="rating-3" name="rating" value="3" class="rating-input">
                                <label for="rating-3" class="rating-label">3</label>
                            </div>
                            <div class="rating-option">
                                <input type="radio" id="rating-4" name="rating" value="4" class="rating-input">
                                <label for="rating-4" class="rating-label">4</label>
                            </div>
                            <div class="rating-option">
                                <input type="radio" id="rating-5" name="rating" value="5" class="rating-input" checked>
                                <label for="rating-5" class="rating-label">5</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="feedback_text">Your Feedback *</label>
                        <textarea id="feedback_text" name="feedback_text" class="form-control" placeholder="Please share your experience with our service..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Submit Feedback</button>
                </form>
                
                <div class="actions">
                    <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
                </div>
            </div>
            
            <div class="feedback-history-section">
                <div class="section-header">
                    <h2>Your Feedback History</h2>
                </div>
                
                <?php if (!empty($feedbacks)): ?>
                    <?php foreach ($feedbacks as $feedback): ?>
                        <div class="feedback-item">
                            <div class="feedback-header-item">
                                <div class="feedback-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star"><?php echo $i <= $feedback['rating'] ? '★' : '☆'; ?></span>
                                    <?php endfor; ?>
                                </div>
                                <div class="feedback-date">
                                    <?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
                                </div>
                            </div>
                            <div class="feedback-text">
                                <?php echo htmlspecialchars($feedback['feedback_text']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-feedback">
                        <p>You haven't submitted any feedback yet.</p>
                        <p>Share your experience with us using the form!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>