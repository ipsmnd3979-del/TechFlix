<?php
require_once 'includes/header.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact_submit'])) {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');
    $department = sanitize_input($_POST['department'] ?? 'general');
    
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($subject)) $errors[] = "Subject is required";
    if (empty($message)) $errors[] = "Message is required";
    
    if (empty($errors)) {
        $success_message = "Thank you for your message! We'll get back to you within 24 hours.";
        $name = $email = $subject = $message = $department = '';
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>

<div class="page-content">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div class="page-header" style="text-align: center; margin-bottom: 50px; padding: 40px 0;">
            <h1>Contact Us</h1>
        </div>

        <div class="contact-form-section" style="background: var(--card-bg); padding: 40px; border-radius: 15px;">
            <form method="POST" action="">
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" style="width: 100%; padding: 12px; background: rgba(255,255,255,0.1); color: white;" required>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Email Address *</label>
                    <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" style="width: 100%; padding: 12px; background: rgba(255,255,255,0.1); color: white;" required>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Subject *</label>
                    <input type="text" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" style="width: 100%; padding: 12px; background: rgba(255,255,255,0.1); color: white;" required>
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Message *</label>
                    <textarea name="message" rows="6" style="width: 100%; padding: 12px; background: rgba(255,255,255,0.1); color: white;" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                </div>
                <button type="submit" name="contact_submit" class="btn btn-primary" style="width:100%;">Send Message</button>
            </form>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>