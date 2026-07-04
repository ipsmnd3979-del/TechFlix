<?php
require_once 'includes/header.php';

// Handle contact form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact_submit'])) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    $category = sanitize_input($_POST['category']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    if (empty($errors)) {
        // Here you would typically:
        // 1. Save to database
        // 2. Send email notification
        // 3. Trigger support ticket
        
        // For now, we'll just show a success message
        $success_message = "Thank you for your message! We'll get back to you within 24 hours.";
        
        // Clear form fields
        $name = $email = $subject = $message = $category = '';
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<div class="page-content">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <!-- Header Section -->
        <div class="page-header" style="text-align: center; margin-bottom: 50px; padding: 40px 0;">
            <h1 style="background: linear-gradient(to right, #fff, #d9e3f0); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 20px;">
                Help & Support
            </h1>
            <p style="color: #bbb; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                We're here to help! Find answers to common questions or contact our support team.
            </p>
        </div>

        <!-- Quick Help Cards -->
        <div class="help-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 50px;">
            <div class="help-card" style="background: var(--card-bg); padding: 30px; border-radius: 15px; text-align: center; border: 1px solid rgba(138, 43, 226, 0.3); transition: all 0.3s ease;">
                <div class="card-icon" style="background: rgba(138, 43, 226, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fas fa-question-circle" style="font-size: 2rem; color: var(--primary);"></i>
                </div>
                <h3 style="color: #fff; margin-bottom: 15px;">FAQ</h3>
                <p style="color: #bbb; margin-bottom: 20px;">Find quick answers to frequently asked questions</p>
                <a href="#faq" class="btn btn-outline" style="display: inline-block;">Browse FAQ</a>
            </div>

            <div class="help-card" style="background: var(--card-bg); padding: 30px; border-radius: 15px; text-align: center; border: 1px solid rgba(138, 43, 226, 0.3); transition: all 0.3s ease;">
                <div class="card-icon" style="background: rgba(138, 43, 226, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fas fa-book" style="font-size: 2rem; color: var(--primary);"></i>
                </div>
                <h3 style="color: #fff; margin-bottom: 15px;">Guides</h3>
                <p style="color: #bbb; margin-bottom: 20px;">Step-by-step guides for using TechFlix</p>
                <a href="#guides" class="btn btn-outline" style="display: inline-block;">View Guides</a>
            </div>

            <div class="help-card" style="background: var(--card-bg); padding: 30px; border-radius: 15px; text-align: center; border: 1px solid rgba(138, 43, 226, 0.3); transition: all 0.3s ease;">
                <div class="card-icon" style="background: rgba(138, 43, 226, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fas fa-headset" style="font-size: 2rem; color: var(--primary);"></i>
                </div>
                <h3 style="color: #fff; margin-bottom: 15px;">Contact Support</h3>
                <p style="color: #bbb; margin-bottom: 20px;">Get help from our support team</p>
                <a href="#contact" class="btn btn-primary" style="display: inline-block;">Contact Us</a>
            </div>
        </div>

      

        <!-- Guides Section -->
        <section id="guides" class="guides-section" style="margin-bottom: 50px;">
            <h2 style="color: var(--primary); margin-bottom: 30px; text-align: center;">Helpful Guides</h2>
            
            <div class="guides-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
                <div class="guide-card" style="background: var(--card-bg); padding: 25px; border-radius: 15px; border: 1px solid rgba(138, 43, 226, 0.3);">
                    <h3 style="color: #fff; margin-bottom: 15px;"><i class="fas fa-play-circle" style="color: var(--primary); margin-right: 10px;"></i>Getting Started</h3>
                    <p style="color: #bbb; margin-bottom: 20px;">Learn how to set up your account and start streaming in minutes.</p>
                    <a href="#" class="btn btn-outline" style="display: inline-block;">Read Guide</a>
                </div>

                <div class="guide-card" style="background: var(--card-bg); padding: 25px; border-radius: 15px; border: 1px solid rgba(138, 43, 226, 0.3);">
                    <h3 style="color: #fff; margin-bottom: 15px;"><i class="fas fa-mobile-alt" style="color: var(--primary); margin-right: 10px;"></i>Mobile App Guide</h3>
                    <p style="color: #bbb; margin-bottom: 20px;">Complete guide to using TechFlix on your mobile devices.</p>
                    <a href="#" class="btn btn-outline" style="display: inline-block;">Read Guide</a>
                </div>

                <div class="guide-card" style="background: var(--card-bg); padding: 25px; border-radius: 15px; border: 1px solid rgba(138, 43, 226, 0.3);">
                    <h3 style="color: #fff; margin-bottom: 15px;"><i class="fas fa-tv" style="color: var(--primary); margin-right: 10px;"></i>Smart TV Setup</h3>
                    <p style="color: #bbb; margin-bottom: 20px;">How to install and use TechFlix on your smart TV.</p>
                    <a href="#" class="btn btn-outline" style="display: inline-block;">Read Guide</a>
                </div>
            </div>
        </section>

       
    </div>
</div>

<style>
    .help-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .guide-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }

    .faq-item.active .faq-question {
        background: rgba(138, 43, 226, 0.2);
    }

    .faq-item.active .faq-question i {
        transform: rotate(180deg);
    }

    .faq-item.active .faq-answer {
        max-height: 500px;
        padding: 0 20px;
    }

    .btn-primary {
        background: linear-gradient(45deg, #ff00cc, #3333ff);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(138, 43, 226, 0.4);
    }

    .btn-outline {
        background: transparent;
        border: 2px solid var(--primary);
        color: var(--primary);
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-outline:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .contact-container {
            grid-template-columns: 1fr !important;
        }
        
        .help-cards {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
    // FAQ Accordion
    document.addEventListener('DOMContentLoaded', function() {
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            question.addEventListener('click', () => {
                // Close all other items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle current item
                item.classList.toggle('active');
            });
        });

        // Smooth scrolling for anchor links
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        anchorLinks.forEach(link => {
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

        // Auto-fill form for logged-in users
        <?php if ($isLoggedIn && isset($userData)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const nameField = document.querySelector('input[name="name"]');
            const emailField = document.querySelector('input[name="email"]');
            
            if (nameField && !nameField.value) {
                const fullName = '<?php echo $userData["first_name"] . " " . $userData["last_name"]; ?>'.trim();
                if (fullName) {
                    nameField.value = fullName;
                } else {
                    nameField.value = '<?php echo $userData["username"]; ?>';
                }
            }
            
            if (emailField && !emailField.value) {
                emailField.value = '<?php echo $userData["email"]; ?>';
            }
        });
        <?php endif; ?>
    });
</script>

<?php require_once 'includes/footer.php'; ?>