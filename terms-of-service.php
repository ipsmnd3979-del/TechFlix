<?php
$page_title = "Terms of Service - TechFlix";
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

        /* Terms Content Styles */
        .terms-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 20px;
        }

        .terms-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .terms-header h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #ff00cc, #3333ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .terms-header p {
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

        .terms-content {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 50px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .terms-section {
            margin-bottom: 40px;
        }

        .terms-section h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #fff;
            border-left: 4px solid;
            border-image: var(--gradient) 1;
            padding-left: 15px;
        }

        .terms-section h3 {
            font-size: 1.3rem;
            margin: 25px 0 15px 0;
            color: #fff;
        }

        .terms-section p {
            margin-bottom: 15px;
            color: var(--gray);
        }

        .terms-section ul {
            margin: 15px 0;
            padding-left: 30px;
        }

        .terms-section li {
            margin-bottom: 10px;
            color: var(--gray);
        }

        .terms-section strong {
            color: #fff;
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

            .terms-header h1 {
                font-size: 2.2rem;
            }

            .terms-content {
                padding: 30px 20px;
            }

            .terms-section h2 {
                font-size: 1.5rem;
            }

            .terms-container {
                padding: 30px 15px;
            }
        }

        @media (max-width: 480px) {
            .terms-header h1 {
                font-size: 1.8rem;
            }

            .terms-content {
                padding: 20px 15px;
            }

            .terms-section h2 {
                font-size: 1.3rem;
            }

            .toc {
                padding: 20px;
            }
        }

        /* Print Styles */
        @media print {
            header, footer, .toc, .contact-info {
                display: none;
            }

            body {
                background: white;
                color: black;
            }

            .terms-content {
                background: white;
                color: black;
            }
        }
    </style>
</head>

<body>
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

    <!-- Terms of Service Content -->
    <div class="terms-container">
        <div class="terms-header">
            <h1>Terms of Service</h1>
            <p>Please read these terms carefully before using TechFlix streaming services</p>
            <div class="last-updated">
                Last Updated: December 1, 2024
            </div>
        </div>

        <div class="toc">
            <h3>Table of Contents</h3>
            <ul>
                <li><a href="#acceptance"><i class="fas fa-chevron-right"></i> Acceptance of Terms</a></li>
                <li><a href="#subscription"><i class="fas fa-chevron-right"></i> Subscription and Billing</a></li>
                <li><a href="#account"><i class="fas fa-chevron-right"></i> Account Registration</a></li>
                <li><a href="#content"><i class="fas fa-chevron-right"></i> Content Usage</a></li>
                <li><a href="#prohibited"><i class="fas fa-chevron-right"></i> Prohibited Activities</a></li>
                <li><a href="#intellectual"><i class="fas fa-chevron-right"></i> Intellectual Property</a></li>
                <li><a href="#termination"><i class="fas fa-chevron-right"></i> Termination</a></li>
                <li><a href="#disclaimer"><i class="fas fa-chevron-right"></i> Disclaimer</a></li>
                <li><a href="#limitation"><i class="fas fa-chevron-right"></i> Limitation of Liability</a></li>
                <li><a href="#changes"><i class="fas fa-chevron-right"></i> Changes to Terms</a></li>
                <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact Information</a></li>
            </ul>
        </div>

        <div class="terms-content">
            <div class="terms-section" id="acceptance">
                <h2>1. Acceptance of Terms</h2>
                <p>By accessing or using TechFlix streaming services, websites, applications, and software (collectively "the Service"), you agree to be bound by these Terms of Service and all applicable laws and regulations.</p>
                <p>If you do not agree with any of these terms, you are prohibited from using or accessing the Service. The materials contained in the Service are protected by applicable copyright and trademark law.</p>
                
                <div class="highlight-box">
                    <p><strong>Important:</strong> These terms include an arbitration agreement and class action waiver that apply to all claims brought against TechFlix. Please read them carefully as they affect your legal rights.</p>
                </div>
            </div>

            <div class="terms-section" id="subscription">
                <h2>2. Subscription and Billing</h2>
                <h3>2.1 Subscription Plans</h3>
                <p>TechFlix offers various subscription plans with different features and pricing. Your subscription will continue month-to-month or year-to-year until terminated.</p>
                
                <h3>2.2 Billing Cycle</h3>
                <p>The subscription fee will be charged to your Payment Method on the specific billing date indicated. The length of your billing cycle will depend on the type of subscription you choose.</p>
                
                <h3>2.3 Payment Methods</h3>
                <p>To use the Service you must provide one or more Payment Methods. You authorize us to charge any Payment Method associated to your account.</p>
                
                <h3>2.4 Cancellation</h3>
                <p>You can cancel your subscription at any time, and you will continue to have access to the Service through the end of your billing period.</p>
            </div>

            <div class="terms-section" id="account">
                <h2>3. Account Registration</h2>
                <p>You must be at least 18 years of age, or the age of majority in your jurisdiction, to create an account. Individuals under the applicable age may utilize the Service only with the involvement of a parent or legal guardian.</p>
                
                <h3>3.1 Account Security</h3>
                <p>You are responsible for safeguarding your account and for all activities that occur under your account. You agree to notify us immediately of any unauthorized use of your account.</p>
                
                <h3>3.2 Account Information</h3>
                <p>You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete.</p>
            </div>

            <div class="terms-section" id="content">
                <h2>4. Content Usage</h2>
                <h3>4.1 License Grant</h3>
                <p>TechFlix grants you a limited, non-exclusive, non-transferable license to access and view the content for personal, non-commercial purposes.</p>
                
                <h3>4.2 Geographic Restrictions</h3>
                <p>The content available for streaming may vary by geographic location. We may use technologies to verify your geographic location.</p>
                
                <h3>4.3 Downloading Content</h3>
                <p>Some content may be available for temporary download. Downloaded content is subject to usage rules and may expire after a certain period.</p>
            </div>

            <div class="terms-section" id="prohibited">
                <h2>5. Prohibited Activities</h2>
                <p>You agree not to engage in any of the following prohibited activities:</p>
                <ul>
                    <li>Copying, distributing, or disclosing any part of the Service in any medium</li>
                    <li>Using any automated system to access the Service</li>
                    <li>Attempting to interfere with, compromise the system integrity or security of the Service</li>
                    <li>Taking any action that imposes an unreasonable load on our infrastructure</li>
                    <li>Uploading invalid data, viruses, worms, or other software agents</li>
                    <li>Collecting or harvesting any personally identifiable information</li>
                    <li>Using the Service for any commercial solicitation purposes</li>
                    <li>Impersonating another person or otherwise misrepresenting your affiliation</li>
                    <li>Interfering with the proper working of the Service</li>
                    <li>Accessing content through any technology or means other than those provided by the Service</li>
                </ul>
            </div>

            <div class="terms-section" id="intellectual">
                <h2>6. Intellectual Property</h2>
                <p>The Service and its original content, features, and functionality are owned by TechFlix and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.</p>
                
                <h3>6.1 Trademarks</h3>
                <p>The TechFlix name and logos are trademarks and service marks of TechFlix. Our trademarks may not be used in connection with any product or service without our prior written consent.</p>
                
                <h3>6.2 Third-Party Content</h3>
                <p>The Service may contain links to third-party websites or services that are not owned or controlled by TechFlix. We have no control over, and assume no responsibility for, the content, privacy policies, or practices of any third-party websites or services.</p>
            </div>

            <div class="terms-section" id="termination">
                <h2>7. Termination</h2>
                <p>We may terminate or suspend your account and bar access to the Service immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever, including but not limited to a breach of the Terms.</p>
                
                <p>Upon termination, your right to use the Service will immediately cease. If you wish to terminate your account, you may simply discontinue using the Service or use the account termination feature.</p>
            </div>

            <div class="terms-section" id="disclaimer">
                <h2>8. Disclaimer</h2>
                <p>Your use of the Service is at your sole risk. The Service is provided on an "AS IS" and "AS AVAILABLE" basis. The Service is provided without warranties of any kind, whether express or implied, including, but not limited to, implied warranties of merchantability, fitness for a particular purpose, non-infringement, or course of performance.</p>
                
                <p>TechFlix, its subsidiaries, affiliates, and its licensors do not warrant that:</p>
                <ul>
                    <li>The Service will function uninterrupted, secure, or available at any particular time or location</li>
                    <li>Any errors or defects will be corrected</li>
                    <li>The Service is free of viruses or other harmful components</li>
                    <li>The results of using the Service will meet your requirements</li>
                </ul>
            </div>

            <div class="terms-section" id="limitation">
                <h2>9. Limitation of Liability</h2>
                <p>In no event shall TechFlix, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from:</p>
                <ul>
                    <li>Your access to or use of or inability to access or use the Service</li>
                    <li>Any conduct or content of any third party on the Service</li>
                    <li>Any content obtained from the Service</li>
                    <li>Unauthorized access, use, or alteration of your transmissions or content</li>
                </ul>
            </div>

            <div class="terms-section" id="changes">
                <h2>10. Changes to Terms</h2>
                <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days' notice prior to any new terms taking effect.</p>
                
                <p>What constitutes a material change will be determined at our sole discretion. By continuing to access or use our Service after those revisions become effective, you agree to be bound by the revised terms.</p>
            </div>

            <div class="contact-info" id="contact">
                <h3>Contact Us</h3>
                <p>If you have any questions about these Terms of Service, please contact us:</p>
                <p>Email: <a href="mailto:legal@techflix.com">legal@techflix.com</a></p>
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
                    <li><a href="terms-of-service.php" style="color: #fff;">Terms of Service</a></li>
                    <li><a href="privacy-policy.php">Privacy Policy</a></li>
                    <li><a href="cookie-policy.php">Cookie Policy</a></li>
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
        // Smooth scrolling for table of contents links
        document.addEventListener('DOMContentLoaded', function() {
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
    </script>
</body>
</html>