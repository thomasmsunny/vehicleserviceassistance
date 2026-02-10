<?php
session_start();
$logged_in = isset($_SESSION['customer_id']);

// Database connection
require_once '../includes/db_config.php';

// Initialize variables
$name = $email = $subject = $message = '';
$form_success = $form_error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'], $_POST['email'], $_POST['subject'], $_POST['message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $form_error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = "Please enter a valid email address.";
    } else {
        // Save to database
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, 'new', NOW())");
            $stmt->execute([$name, $email, $subject, $message]);
            
            if ($stmt->rowCount() > 0) {
                $form_success = "Thank you for contacting us! We'll get back to you soon.";
                // Clear form fields
                $name = $email = $subject = $message = '';
            } else {
                $form_error = "Sorry, there was an error sending your message. Please try again.";
            }
        } catch (PDOException $e) {
            $form_error = "Sorry, there was an error sending your message. Please try again.";
        }
    }
}

// Function to get services from database
function getServices() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM services WHERE status = 'active' OR status = '1' ORDER BY servicename");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get services for display
$services = getServices();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>AutoFix - Premium Vehicle Services</title>
  <meta name="description" content="Professional vehicle maintenance and repair services with 25 years of excellence">
  <meta name="keywords" content="vehicle service, car repair, maintenance, denting, painting, auto service">
  
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
    
    /* Main Content */
    .main {
      margin-top: 80px;
    }
    
    /* Hero Section */
    .hero {
      background: linear-gradient(rgba(44, 62, 80, 0.85), rgba(44, 62, 80, 0.9)), url('../images/frontimage.png');
      background-size: cover;
      background-position: center;
      color: white;
      text-align: center;
      padding: 120px 20px;
      position: relative;
    }
    
    .hero-content {
      max-width: 800px;
      margin: 0 auto;
    }
    
    .hero h1 {
      font-size: 3.5rem;
      font-weight: 800;
      margin-bottom: 20px;
      line-height: 1.2;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .hero p {
      font-size: 1.5rem;
      margin-bottom: 30px;
      opacity: 0.9;
    }
    
    .hero-buttons {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 30px;
      flex-wrap: wrap;
    }
    
    .btn-primary {
      background: var(--primary);
      color: white;
      padding: 15px 35px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      font-size: 18px;
      transition: var(--transition);
      border: 2px solid var(--primary);
      text-align: center; /* Center the text */
    }
    
    .btn-primary:hover {
      background: transparent;
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(255, 74, 23, 0.4);
    }
    
    .btn-secondary {
      background: transparent;
      color: white;
      padding: 15px 35px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      font-size: 18px;
      transition: var(--transition);
      border: 2px solid white;
      text-align: center; /* Center the text */
    }
    
    .btn-secondary:hover {
      background: white;
      color: var(--secondary);
      transform: translateY(-3px);
    }
    
    /* Section Styles */
    .section {
      padding: 80px 5%;
    }
    
    .section-title {
      text-align: center;
      margin-bottom: 60px;
    }
    
    .section-title h2 {
      font-size: 2.5rem;
      color: var(--secondary);
      margin-bottom: 15px;
      position: relative;
      display: inline-block;
    }
    
    .section-title h2::after {
      content: '';
      position: absolute;
      bottom: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: var(--primary);
      border-radius: 2px;
    }
    
    .section-title p {
      font-size: 1.2rem;
      color: var(--gray);
      max-width: 700px;
      margin: 30px auto 0;
    }
    
    /* About Section */
    .about {
      background: white;
    }
    
    .about-content {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
      align-items: center;
      max-width: 1400px;
      margin: 0 auto;
    }
    
    .about-image {
      flex: 1;
      min-width: 300px;
    }
    
    .about-image img {
      width: 100%;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
    }
    
    .about-text {
      flex: 1;
      min-width: 300px;
    }
    
    .about-text h3 {
      font-size: 2rem;
      color: var(--secondary);
      margin-bottom: 20px;
    }
    
    .about-text p {
      margin-bottom: 20px;
      font-size: 1.1rem;
      color: var(--gray);
    }
    
    .highlight {
      background: linear-gradient(120deg, #ff4a17 0%, #ff7b54 100%);
      color: white;
      padding: 30px;
      border-radius: var(--border-radius);
      margin: 30px 0;
    }
    
    .highlight p {
      margin: 0;
      font-size: 1.2rem;
      font-weight: 500;
    }
    
    /* Services Section */
    .services {
      background: #f8f9fa;
    }
    
    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      max-width: 1400px;
      margin: 0 auto;
    }
    
    .service-card {
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
    }
    
    .service-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .service-image {
      height: 200px;
      overflow: hidden;
    }
    
    .service-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: var(--transition);
    }
    
    .service-card:hover .service-image img {
      transform: scale(1.05);
    }
    
    .service-content {
      padding: 25px;
    }
    
    .service-content .icon {
      font-size: 2.5rem;
      color: var(--primary);
      margin-bottom: 15px;
    }
    
    .service-content h3 {
      font-size: 1.5rem;
      color: var(--secondary);
      margin-bottom: 15px;
    }
    
    .service-content p {
      color: var(--gray);
      margin-bottom: 20px;
    }
    
    .service-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
      display: flex;
      align-items: center;
      transition: var(--transition);
    }
    
    .service-link:hover {
      color: var(--primary-dark);
    }
    
    .service-link i {
      margin-left: 5px;
      transition: var(--transition);
    }
    
    .service-link:hover i {
      transform: translateX(3px);
    }
    
    /* Contact Section */
    .contact {
      background: white;
    }
    
    .contact-container {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
      max-width: 1400px;
      margin: 0 auto;
    }
    
    .contact-info {
      flex: 1;
      min-width: 300px;
    }
    
    .contact-form {
      flex: 1;
      min-width: 300px;
    }
    
    .info-item {
      display: flex;
      margin-bottom: 30px;
    }
    
    .info-icon {
      font-size: 2rem;
      color: var(--primary);
      margin-right: 20px;
      min-width: 50px;
    }
    
    .info-content h3 {
      color: var(--secondary);
      margin-bottom: 5px;
    }
    
    .info-content p {
      color: var(--gray);
      font-size: 1.1rem;
    }
    
    .form-group {
      margin-bottom: 20px;
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
    
    /* Alert Styles */
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid transparent;
      border-radius: var(--border-radius);
    }
    
    .alert-success {
      color: #155724;
      background-color: #d4edda;
      border-color: #c3e6cb;
    }
    
    .alert-danger {
      color: #721c24;
      background-color: #f8d7da;
      border-color: #f5c6cb;
    }
    
    /* Footer */
    .footer {
      background: var(--secondary);
      color: white;
      padding: 60px 5% 30px;
    }
    
    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 40px;
      max-width: 1400px;
      margin: 0 auto 40px;
    }
    
    .footer-col h4 {
      font-size: 1.5rem;
      margin-bottom: 25px;
      position: relative;
      padding-bottom: 10px;
    }
    
    .footer-col h4::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      background: var(--primary);
    }
    
    .footer-about p {
      margin-bottom: 20px;
      opacity: 0.8;
    }
    
    .social-links {
      display: flex;
      gap: 15px;
    }
    
    .social-links a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: rgba(255,255,255,0.1);
      border-radius: 50%;
      color: white;
      text-decoration: none;
      transition: var(--transition);
    }
    
    .social-links a:hover {
      background: var(--primary);
      transform: translateY(-3px);
    }
    
    .footer-links ul {
      list-style: none;
    }
    
    .footer-links li {
      margin-bottom: 12px;
    }
    
    .footer-links a {
      color: rgba(255,255,255,0.7);
      text-decoration: none;
      transition: var(--transition);
      display: flex;
      align-items: center;
    }
    
    .footer-links a:hover {
      color: var(--primary);
      transform: translateX(3px);
    }
    
    .footer-links i {
      margin-right: 10px;
      color: var(--primary);
    }
    
    .copyright {
      text-align: center;
      padding-top: 30px;
      border-top: 1px solid rgba(255,255,255,0.1);
      max-width: 1400px;
      margin: 0 auto;
      opacity: 0.7;
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
      .hero h1 {
        font-size: 2.8rem;
      }
      
      .hero p {
        font-size: 1.3rem;
      }
    }
    
    @media (max-width: 768px) {
      .header-content {
        flex-direction: column;
        padding: 15px;
      }
      
      .nav-menu {
        margin: 15px 0;
        flex-wrap: wrap;
        justify-content: center;
      }
      
      .nav-menu li {
        margin: 5px 10px;
      }
      
      .hero {
        padding: 80px 20px;
      }
      
      .hero h1 {
        font-size: 2.3rem;
      }
      
      .hero p {
        font-size: 1.1rem;
      }
      
      .hero-buttons {
        flex-direction: column;
        gap: 15px;
      }
      
      .section {
        padding: 60px 5%;
      }
      
      .section-title h2 {
        font-size: 2rem;
      }
    }
    
    @media (max-width: 576px) {
      .logo-text {
        font-size: 20px;
      }
      
      .nav-menu a {
        font-size: 14px;
      }
      
      .btn-primary, .btn-secondary {
        padding: 12px 25px;
        font-size: 16px;
      }
    }
  </style>
</head>

<body>
  <!-- Header -->
  <header class="header">
    <div class="header-content">
      <a href="index.php" class="logo">
        <div class="logo-icon">🔧</div>
        <div class="logo-text">Auto<span>Fix</span></div>
      </a>
      
      <ul class="nav-menu">
        <li><a href="#hero">Home</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
      
      <?php if ($logged_in): ?>
        <a href="../profile.php" class="cta-button">My Profile</a>
      <?php else: ?>
        <a href="../customerlogin.php" class="cta-button">Login</a>
      <?php endif; ?>
    </div>
  </header>

  <main class="main">
    <!-- Hero Section -->
    <section id="hero" class="hero">
      <div class="hero-content">
        <h1>Premium Vehicle Service & Maintenance</h1>
        <p>We Take Care of Your Wheels with 25 Years of Excellence</p>
        <?php if ($logged_in): ?>
          <div class="hero-buttons">
            <a href="../bookingfor.php" class="btn-primary">Book Service Now</a>
          </div>
        <?php else: ?>
          <div class="hero-buttons">
            <a href="../customerlogin.php" class="btn-primary">Login to Book</a>
            <a href="../registeration2.php" class="btn-secondary">Register Now</a>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section about">
      <div class="section-title">
        <h2>About Our Service</h2>
        <p>A Reputation of 25 Years in the Automotive Industry</p>
      </div>
      
      <div class="about-content">
        <div class="about-image">
          <img src="../images/ABOUT1.jpg" alt="AutoFix Workshop">
        </div>
        
        <div class="about-text">
          <h3>Our Mission</h3>
          <p>Our mission is to enable premium quality care for your luxury car service at affordable pricing. We ensure real time updates for complete car care needs with a fair and transparent pricing mechanism.</p>
          
          <div class="highlight">
            <p>Skilled technicians, working at our state of the art German technology workshop, ensure that only genuine OEM parts are used for your car care needs.</p>
          </div>
          
          <h3>Perfect Service Partner</h3>
          <p>Customer satisfaction is the core of all initiatives at AutoFix. Online appointment scheduling with doorstep, same day pickup and drop anywhere in Pune is our constant endeavor to maximize customer convenience.</p>
          
          <p>Our commitment stands for reliability and unequalled professionalism to provide dealer quality auto-service at a fair price.</p>
          
          <div class="about-image" style="margin-top: 30px;">
            <img src="../images/about 2.jpg" alt="Our Team">
          </div>
        </div>
      </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="section services">
      <div class="section-title">
        <h2>Our Services</h2>
        <p>Comprehensive Vehicle Care Solutions</p>
      </div>
      
      <div class="services-grid">
        <?php if (!empty($services)): ?>
          <?php foreach ($services as $service): ?>
            <div class="service-card">
              <div class="service-image">
                <?php
                // Check if service has an image in the database
                if (!empty($service['image']) && file_exists('../../uploads/services/' . $service['image'])) {
                    $image = '../../uploads/services/' . $service['image'];
                } else {
                    // Determine which default image to use based on service name
                    $serviceName = strtolower($service['servicename']);
                    
                    if (strpos($serviceName, 'paint') !== false || strpos($serviceName, 'dent') !== false) {
                        $image = "../images/car-painting-spray.jpg";
                    } elseif (strpos($serviceName, 'major') !== false) {
                        $image = "../images/major.jpeg";
                    } elseif (strpos($serviceName, 'detail') !== false || strpos($serviceName, 'wash') !== false) {
                        $image = "../images/carwash.jpg";
                    } elseif (strpos($serviceName, 'ac') !== false || strpos($serviceName, 'air') !== false) {
                        $image = "../images/car ac.jpg";
                    } elseif (strpos($serviceName, 'tire') !== false) {
                        $image = "../images/tire-maintenance.jpg";
                    } elseif (strpos($serviceName, 'ceramic') !== false || strpos($serviceName, 'coat') !== false) {
                        $image = "../images/ceramic coating.jpeg";
                    } else {
                        $image = "../images/periodic.jpeg"; // default image
                    }
                }
                ?>
                <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($service['servicename']); ?>">
              </div>
              <div class="service-content">
                <div class="icon">
                  <?php
                  // Determine which icon to use based on service name
                  $serviceName = strtolower($service['servicename']);
                  if (strpos($serviceName, 'paint') !== false || strpos($serviceName, 'dent') !== false) {
                      echo "🎨";
                  } elseif (strpos($serviceName, 'major') !== false) {
                      echo "⚙️";
                  } elseif (strpos($serviceName, 'detail') !== false) {
                      echo "✨";
                  } elseif (strpos($serviceName, 'ac') !== false || strpos($serviceName, 'air') !== false) {
                      echo "❄️";
                  } elseif (strpos($serviceName, 'tire') !== false) {
                      echo "🛞";
                  } elseif (strpos($serviceName, 'ceramic') !== false || strpos($serviceName, 'coat') !== false) {
                      echo "💎";
                  } else {
                      echo "🔧";
                  }
                  ?>
                </div>
                <h3><?php echo htmlspecialchars($service['servicename']); ?></h3>
                <?php if (!empty($service['description'])): ?>
                  <p><?php echo htmlspecialchars($service['description']); ?></p>
                <?php else: ?>
                  <p>We provide professional <?php echo htmlspecialchars(strtolower($service['servicename'])); ?> to keep your vehicle in perfect condition. Our skilled technicians use the latest equipment and genuine parts for reliable results.</p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Default services if none found in database -->
          <div class="service-card">
            <div class="service-image">
              <img src="../images/periodic.jpeg" alt="Periodic Services">
            </div>
            <div class="service-content">
              <div class="icon">🔧</div>
              <h3>Periodic Services</h3>
              <p>Stop waiting for a problem to happen. Be proactive with your vehicle's health. Book your next periodic service with us and experience the difference that quality, convenience, and transparency can make.</p>
            </div>
          </div>
          
          <div class="service-card">
            <div class="service-image">
              <img src="../images/car-painting-spray.jpg" alt="Denting & Painting">
            </div>
            <div class="service-content">
              <div class="icon">🎨</div>
              <h3>Denting & Painting</h3>
              <p>Our professional car painting services restore your vehicle's finish to its original, flawless, showroom-quality appearance.</p>
            </div>
          </div>
          
          <div class="service-card">
            <div class="service-image">
              <img src="../images/major.jpeg" alt="Major Services">
            </div>
            <div class="service-content">
              <div class="icon">⚙️</div>
              <h3>Major Services</h3>
              <p>Our major service package is the most extensive maintenance option available, providing a thorough "head-to-tail" inspection and tune-up for your vehicle.</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section contact">
      <div class="section-title">
        <h2>Contact Us</h2>
        <p>Get in Touch with Our Team</p>
      </div>
      
      <div class="contact-container">
        <div class="contact-info">
          <div class="info-item">
            <div class="info-icon">📍</div>
            <div class="info-content">
              <h3>Our Location</h3>
              <p>Aluva To Nedumbassery Airport Road<br>Opposite Federal Bank<br>PIN: 535022</p>
            </div>
          </div>
          
          <div class="info-item">
            <div class="info-icon">📞</div>
            <div class="info-content">
              <h3>Call Us</h3>
              <p>+91 9961248888</p>
            </div>
          </div>
          
          <div class="info-item">
            <div class="info-icon">✉️</div>
            <div class="info-content">
              <h3>Email Us</h3>
              <p>autofixaluava@gmail.com</p>
            </div>
          </div>
        </div>
        
        <div class="contact-form">
          <?php if ($form_success): ?>
            <div class="alert alert-success"><?php echo $form_success; ?></div>
          <?php endif; ?>
          
          <?php if ($form_error): ?>
            <div class="alert alert-danger"><?php echo $form_error; ?></div>
          <?php endif; ?>
          
          <form action="#contact" method="post">
            <div class="form-group">
              <input type="text" name="name" class="form-control" placeholder="Your Name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            
            <div class="form-group">
              <input type="email" name="email" class="form-control" placeholder="Your Email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            
            <div class="form-group">
              <input type="text" name="subject" class="form-control" placeholder="Subject" value="<?php echo htmlspecialchars($subject); ?>" required>
            </div>
            
            <div class="form-group">
              <textarea name="message" class="form-control" placeholder="Your Message" required><?php echo htmlspecialchars($message); ?></textarea>
            </div>
            
            <button type="submit" class="btn-primary" style="border: none; cursor: pointer; width: 100%;">Send Message</button>
          </form>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-content">
      <div class="footer-col footer-about">
        <h4>AutoFix</h4>
        <p>Providing premium vehicle service for over 25 years with a commitment to quality and customer satisfaction.</p>
        <div class="social-links">
          <a href="#"><i>f</i></a>
          <a href="#"><i>t</i></a>
          <a href="#"><i>in</i></a>
          <a href="#"><i>ig</i></a>
        </div>
      </div>
      
      <div class="footer-col footer-links">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="#hero"><i>→</i> Home</a></li>
          <li><a href="#about"><i>→</i> About Us</a></li>
          <li><a href="#services"><i>→</i> Services</a></li>
          <li><a href="#contact"><i>→</i> Contact</a></li>
          <?php if ($logged_in): ?>
            <li><a href="../profile.php"><i>→</i> My Profile</a></li>
          <?php else: ?>
            <li><a href="../customerlogin.php"><i>→</i> Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
      
      <div class="footer-col footer-links">
        <h4>Our Services</h4>
        <ul>
          <?php if (!empty($services)): ?>
            <?php foreach ($services as $service): ?>
              <li><?php echo htmlspecialchars($service['servicename']); ?></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li>Periodic Services</li>
            <li>Denting & Painting</li>
            <li>Major Services</li>
            <li>Car Detailing</li>
            <li>AC Services</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    
    <div class="copyright">
      <p>&copy; 2025 AutoFix Vehicle Service. All Rights Reserved.</p>
    </div>
  </footer>
</body>
</html>