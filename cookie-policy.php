<?php
$page_title = "Cookie Policy - TechFlix";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary: linear-gradient(45deg, #ff00cc, #3333ff);
            --dark: #141414;
            --light: #f5f5f5;
            --gray: #808080;
            --dark-gray: #2d2d2d;
            --gradient: linear-gradient(45deg, #ff00cc, #3333ff);
        }

        body {
            background-color: var(--dark);
            color: var(--light);
            overflow-x: hidden;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(91, 36, 173, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(91, 36, 173, 0.15) 0%, transparent 40%);
            line-height: 1.6;
        }

        /* Header Styles */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 5%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            font-size: 32px;
            background: linear-gradient(45deg, #ff00cc, #3333ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(45deg, #ff00cc, #3333ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        nav ul {
            display: flex;
            gap: 25px;
            list-style: none;
        }

        nav a {
            color: #ddd;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            padding: 8px 12px;
            border-radius: 20px;
        }

        nav a:hover {
            background: rgba(106, 90, 249, 0.3);
            color: #fff;
        }

        .user-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: var(--gradient);
            color: white;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            backdrop-filter: blur(10px);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        /* Cookie Banner */
        .cookie-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(20, 20, 20, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1000;
            display: none;
        }

        .cookie-banner.active {
            display: block;
        }

        .cookie-banner-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .cookie-banner-text {
            flex: 1;
            min-width: 300px;
        }

        .cookie-banner-text h3 {
            margin-bottom: 10px;
            color: #fff;
        }

        .cookie-banner-text p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .cookie-banner-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        .cookie-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .cookie-btn.accept {
            background: var(--gradient);
            color: white;
        }

        .cookie-btn.reject {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Policy Content Styles */
        .policy-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 20px;
        }

        .policy-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .policy-header h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #ff00cc, #3333ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .policy-header p {
            color: var(--gray);
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .last-updated {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin-top: 15px;
            font-size: 0.9rem;
        }

        .policy-content {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 50px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .policy-section {
            margin-bottom: 40px;
        }

        .policy-section h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #fff;
            border-left: 4px solid;
            border-image: var(--gradient) 1;
            padding-left: 15px;
        }

        .policy-section h3 {
            font-size: 1.3rem;
            margin: 25px 0 15px 0;
            color: #fff;
        }

        .policy-section p {
            margin-bottom: 15px;
            color: var(--gray);
        }

        .policy-section ul {
            margin: 15px 0;
            padding-left: 30px;
        }

        .policy-section li {
            margin-bottom: 10px;
            color: var(--gray);
        }

        .policy-section strong {
            color: #fff;
        }

        /* Cookie Table */
        .cookie-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .cookie-table th,
        .cookie-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .cookie-table th {
            background: rgba(106, 90, 249, 0.2);
            color: #fff;
            font-weight: 600;
        }

        .cookie-table tr:last-child td {
            border-bottom: none;
        }

        .cookie-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .cookie-type.necessary {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }

        .cookie-type.preferences {
            background: rgba(33, 150, 243, 0.2);
            color: #2196f3;
        }

        .cookie-type.analytics {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }

        .cookie-type.marketing {
            background: rgba(233, 30, 99, 0.2);
            color: #e91e63;
        }

        /* Cookie Settings */
        .cookie-settings {
            background: rgba(106, 90, 249, 0.1);
            border-radius: 10px;
            padding: 30px;
            margin: 30px 0;
        }

        .cookie-settings h3 {
            margin-bottom: 20px;
            color: #fff;
        }

        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .setting-info h4 {
            color: #fff;
            margin-bottom: 5px;
        }

        .setting-info p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.2);
            transition: .4s;
            border-radius: 24px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .toggle-slider {
            background: var(--gradient);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        .highlight-box {
            background: rgba(106, 90, 249, 0.1);
            border-left: 4px solid #6a5af9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .contact-info {
            background: var(--gradient);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-top: 40px;
        }

        .contact-info h3 {
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .contact-info p {
            margin-bottom: 10px;
        }

        .contact-info a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        /* Table of Contents */
        .toc {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .toc h3 {
            margin-bottom: 20px;
            color: #fff;
        }

        .toc ul {
            list-style: none;
            padding: 0;
        }

        .toc li {
            margin-bottom: 10px;
        }

        .toc a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toc a:hover {
            color: #fff;
        }

        .toc a i {
            color: #6a5af9;
            font-size: 0.8rem;
        }

        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.8);
            padding: 50px 5% 20px;
            margin-top: 50px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: #fff;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: var(--gray);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: #fff;
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 15px;
                padding: 15px 5%;
            }

            nav ul {
                gap: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .policy-header h1 {
                font-size: 2.2rem;
            }

            .policy-content {
                padding: 30px 20px;
            }

            .policy-section h2 {
                font-size: 1.5rem;
            }

            .policy-container {
                padding: 30px 15px;
            }

            .cookie-table {
                display: block;
                overflow-x: auto;
            }

            .cookie-banner-content {
                flex-direction: column;
                text-align: center;
            }

            .setting-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .policy-header h1 {
                font-size: 1.8rem;
            }

            .policy-content {
                padding: 20px 15px;
            }

            .policy-section h2 {
                font-size: 1.3rem;
            }

            .toc {
                padding: 20px;
            }

            .cookie-settings {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Cookie Consent Banner -->
    <div class="cookie-banner" id="cookieBanner">
        <div class="cookie-banner-content">
            <div class="cookie-banner-text">
                <h3>We Use Cookies</h3>
                <p>We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies. You can manage your preferences in our Cookie Policy.</p>
            </div>
            <div class="cookie-banner-actions">
                <button class="cookie-btn reject" onclick="rejectCookies()">Reject All</button>
                <button class="cookie-btn accept" onclick="acceptCookies()">Accept All</button>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="logo">
            <a href="index.php" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                <div class="logo-icon">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="logo-text">TECHFLIX</div>
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#trending">Movies</a></li>
                <li><a href="index.php#trending">TV Shows</a></li>
                <li><a href="privacy-policy.php">Privacy</a></li>
            </ul>
        </nav>
        <div class="user-actions">
            <a href="auth/login.php" class="btn btn-secondary">Sign In</a>
            <a href="auth/register.php" class="btn btn-primary">Sign Up</a>
        </div>
    </header>

    <!-- Cookie Policy Content -->
    <div class="policy-container">
        <div class="policy-header">
            <h1>Cookie Policy</h1>
            <p>Learn how TechFlix uses cookies and similar technologies to enhance your streaming experience</p>
            <div class="last-updated">
                Last Updated: December 1, 2024
            </div>
        </div>

        <div class="toc">
            <h3>Table of Contents</h3>
            <ul>
                <li><a href="#what-are-cookies"><i class="fas fa-chevron-right"></i> What Are Cookies?</a></li>
                <li><a href="#how-we-use"><i class="fas fa-chevron-right"></i> How We Use Cookies</a></li>
                <li><a href="#types-of-cookies"><i class="fas fa-chevron-right"></i> Types of Cookies We Use</a></li>
                <li><a href="#cookie-table"><i class="fas fa-chevron-right"></i> Detailed Cookie Information</a></li>
                <li><a href="#third-party"><i class="fas fa-chevron-right"></i> Third-Party Cookies</a></li>
                <li><a href="#manage-cookies"><i class="fas fa-chevron-right"></i> Managing Cookies</a></li>
                <li><a href="#changes"><i class="fas fa-chevron-right"></i> Changes to Cookie Policy</a></li>
                <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact Information</a></li>
            </ul>
        </div>

        <div class="policy-content">
            <div class="policy-section" id="what-are-cookies">
                <h2>1. What Are Cookies?</h2>
                <p>Cookies are small text files that are stored on your device (computer, tablet, or mobile) when you visit our website. They are widely used to make websites work more efficiently and provide information to the website owners.</p>
                
                <p>Cookies help us understand how you interact with our service, remember your preferences, and provide you with a personalized streaming experience.</p>
                
                <div class="highlight-box">
                    <p><strong>Note:</strong> Cookies cannot harm your computer and do not contain viruses. We do not use cookies to collect personally identifiable information without your permission.</p>
                </div>
            </div>

            <div class="policy-section" id="how-we-use">
                <h2>2. How We Use Cookies</h2>
                <p>TechFlix uses cookies and similar tracking technologies for the following purposes:</p>
                
                <ul>
                    <li><strong>Essential Operation:</strong> To enable basic functions like page navigation and access to secure areas</li>
                    <li><strong>Preferences:</strong> To remember your settings and preferences</li>
                    <li><strong>Authentication:</strong> To keep you signed in and maintain your session</li>
                    <li><strong>Analytics:</strong> To understand how visitors interact with our website</li>
                    <li><strong>Personalization:</strong> To provide personalized content recommendations</li>
                    <li><strong>Advertising:</strong> To deliver relevant advertisements and measure their effectiveness</li>
                </ul>
            </div>

            <div class="policy-section" id="types-of-cookies">
                <h2>3. Types of Cookies We Use</h2>
                
                <h3>3.1 Essential Cookies</h3>
                <p>These cookies are necessary for the website to function and cannot be switched off. They are usually set in response to actions made by you, such as setting your privacy preferences, logging in, or filling in forms.</p>
                
                <h3>3.2 Preference Cookies</h3>
                <p>These cookies enable the website to provide enhanced functionality and personalization. They may be set by us or by third-party providers whose services we have added to our pages.</p>
                
                <h3>3.3 Analytics Cookies</h3>
                <p>These cookies allow us to count visits and traffic sources so we can measure and improve the performance of our site. They help us know which pages are the most and least popular and see how visitors move around the site.</p>
                
                <h3>3.4 Marketing Cookies</h3>
                <p>These cookies may be set through our site by our advertising partners. They may be used by those companies to build a profile of your interests and show you relevant advertisements on other sites.</p>
            </div>

            <div class="policy-section" id="cookie-table">
                <h2>4. Detailed Cookie Information</h2>
                <p>The table below provides more information about the cookies we use and why:</p>
                
                <table class="cookie-table">
                    <thead>
                        <tr>
                            <th>Cookie Name</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>session_id</td>
                            <td><span class="cookie-type necessary">Essential</span></td>
                            <td>Maintains your login session and preferences</td>
                            <td>Session</td>
                        </tr>
                        <tr>
                            <td>user_preferences</td>
                            <td><span class="cookie-type preferences">Preferences</span></td>
                            <td>Stores your language, theme, and playback settings</td>
                            <td>1 year</td>
                        </tr>
                        <tr>
                            <td>watch_history</td>
                            <td><span class="cookie-type preferences">Preferences</span></td>
                            <td>Remembers your viewing history for recommendations</td>
                            <td>2 years</td>
                        </tr>
                        <tr>
                            <td>_ga</td>
                            <td><span class="cookie-type analytics">Analytics</span></td>
                            <td>Google Analytics - distinguishes unique users</td>
                            <td>2 years</td>
                        </tr>
                        <tr>
                            <td>_gid</td>
                            <td><span class="cookie-type analytics">Analytics</span></td>
                            <td>Google Analytics - distinguishes unique users</td>
                            <td>24 hours</td>
                        </tr>
                        <tr>
                            <td>_fbp</td>
                            <td><span class="cookie-type marketing">Marketing</span></td>
                            <td>Facebook Pixel - tracks conversions from Facebook ads</td>
                            <td>3 months</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="policy-section" id="third-party">
                <h2>5. Third-Party Cookies</h2>
                <p>In addition to our own cookies, we may also use various third-party cookies to report usage statistics of the service, deliver advertisements on and through the service, and so on.</p>
                
                <p><strong>Third-party services we use include:</strong></p>
                <ul>
                    <li>Google Analytics for website analytics</li>
                    <li>Facebook Pixel for advertising measurement</li>
                    <li>Stripe for payment processing</li>
                    <li>Cloudflare for security and performance</li>
                </ul>
                
                <div class="highlight-box">
                    <p><strong>Important:</strong> These third-party services have their own privacy policies and cookie practices. We recommend you review their policies to understand how they handle your data.</p>
                </div>
            </div>

            <div class="policy-section" id="manage-cookies">
                <h2>6. Managing Cookies</h2>
                <p>You have the right to decide whether to accept or reject cookies. You can exercise your cookie preferences by clicking on the appropriate opt-out links provided in the cookie table or by modifying your browser settings.</p>

                <div class="cookie-settings">
                    <h3>Cookie Preferences</h3>
                    <p>Manage your cookie preferences below:</p>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Essential Cookies</h4>
                            <p>Required for the website to function properly. Cannot be disabled.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked disabled>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Preference Cookies</h4>
                            <p>Remember your settings and preferences for a better experience.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="prefCookies" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Analytics Cookies</h4>
                            <p>Help us improve our website by collecting anonymous usage data.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="analyticsCookies" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Marketing Cookies</h4>
                            <p>Used to deliver relevant advertisements and measure campaign effectiveness.</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="marketingCookies">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <button class="btn btn-primary" onclick="saveCookiePreferences()" style="margin-top: 20px;">Save Preferences</button>
                </div>

                <h3>Browser Settings</h3>
                <p>You can also manage cookies through your web browser settings. Most web browsers allow you to control cookies through their settings preferences. However, limiting cookies may affect your experience on our website.</p>
                
                <p><strong>Instructions for popular browsers:</strong></p>
                <ul>
                    <li><a href="#" style="color: #6a5af9;">Google Chrome</a></li>
                    <li><a href="#" style="color: #6a5af9;">Mozilla Firefox</a></li>
                    <li><a href="#" style="color: #6a5af9;">Safari</a></li>
                    <li><a href="#" style="color: #6a5af9;">Microsoft Edge</a></li>
                </ul>
            </div>

            <div class="policy-section" id="changes">
                <h2>7. Changes to Cookie Policy</h2>
                <p>We may update this Cookie Policy from time to time to reflect changes in technology, legislation, or our operations. We will notify you of any material changes by posting the new Cookie Policy on this page and updating the "Last Updated" date.</p>
                
                <p>We encourage you to review this Cookie Policy periodically to stay informed about how we use cookies.</p>
            </div>

            <div class="contact-info" id="contact">
                <h3>Contact Us</h3>
                <p>If you have any questions about our use of cookies, please contact us:</p>
                <p>Email: <a href="mailto:privacy@techflix.com">privacy@techflix.com</a></p>
                <p>Address: 123 Streaming Street, Digital City, DC 12345</p>
                <p>Phone: +1 (555) 123-TECH</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div class="footer-column">
                <h3>Navigation</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#trending">Movies</a></li>
                    <li><a href="index.php#trending">TV Shows</a></li>
                    <li><a href="index.php#features">Features</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Legal</h3>
                <ul class="footer-links">
                    <li><a href="terms-of-service.php">Terms of Service</a></li>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="cookie-policy.php" style="color: #fff;">Cookie Policy</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Support</h3>
                <ul class="footer-links">
                    <li><a href="help-center.php">Help Center</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="faq.php">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Connect</h3>
                <ul class="footer-links">
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">Twitter</a></li>
                    <li><a href="#">Instagram</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            &copy; 2025 TechFlix. All rights reserved.
        </div>
    </footer>

    <script>
        // Cookie Management Functions
        document.addEventListener('DOMContentLoaded', function() {
            // Check if user has already made cookie choices
            const cookieConsent = getCookie('cookie_consent');
            
            if (!cookieConsent) {
                // Show cookie banner if no choice has been made
                setTimeout(() => {
                    document.getElementById('cookieBanner').classList.add('active');
                }, 1000);
            } else {
                // Apply saved preferences
                applyCookiePreferences();
            }

            // Smooth scrolling for table of contents links
            const tocLinks = document.querySelectorAll('.toc a');
            
            tocLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Header scroll effect
            window.addEventListener('scroll', () => {
                const header = document.querySelector('header');
                if (window.scrollY > 50) {
                    header.style.background = 'rgba(0, 0, 0, 0.9)';
                } else {
                    header.style.background = 'rgba(0, 0, 0, 0.8)';
                }
            });
        });

        function acceptCookies() {
            setCookie('cookie_consent', 'all', 365);
            setCookie('pref_cookies', 'true', 365);
            setCookie('analytics_cookies', 'true', 365);
            setCookie('marketing_cookies', 'true', 365);
            document.getElementById('cookieBanner').classList.remove('active');
            showNotification('Cookie preferences saved!');
        }

        function rejectCookies() {
            setCookie('cookie_consent', 'necessary', 365);
            setCookie('pref_cookies', 'false', 365);
            setCookie('analytics_cookies', 'false', 365);
            setCookie('marketing_cookies', 'false', 365);
            document.getElementById('cookieBanner').classList.remove('active');
            showNotification('Only essential cookies will be used.');
        }

        function saveCookiePreferences() {
            const prefCookies = document.getElementById('prefCookies').checked;
            const analyticsCookies = document.getElementById('analyticsCookies').checked;
            const marketingCookies = document.getElementById('marketingCookies').checked;

            setCookie('cookie_consent', 'custom', 365);
            setCookie('pref_cookies', prefCookies.toString(), 365);
            setCookie('analytics_cookies', analyticsCookies.toString(), 365);
            setCookie('marketing_cookies', marketingCookies.toString(), 365);

            showNotification('Cookie preferences saved successfully!');
        }

        function applyCookiePreferences() {
            const prefCookies = getCookie('pref_cookies') === 'true';
            const analyticsCookies = getCookie('analytics_cookies') === 'true';
            const marketingCookies = getCookie('marketing_cookies') === 'true';

            document.getElementById('prefCookies').checked = prefCookies;
            document.getElementById('analyticsCookies').checked = analyticsCookies;
            document.getElementById('marketingCookies').checked = marketingCookies;
        }

        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toUTCString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        function showNotification(message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--gradient);
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 10000;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>