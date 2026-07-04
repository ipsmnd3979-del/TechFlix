<?php
require_once 'includes/header.php';
?>

<div class="page-content">
    <div class="container" style="max-width: 1000px; margin: 0 auto;">
        <!-- Header Section -->
        <div class="page-header" style="text-align: center; margin-bottom: 50px; padding: 40px 0;">
            <h1 style="background: linear-gradient(to right, #fff, #d9e3f0); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 20px;">
                Frequently Asked Questions
            </h1>
            <p style="color: #bbb; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                Find quick answers to the most common questions about TechFlix.
            </p>
        </div>

        <!-- Search Bar -->
        <div class="search-section" style="margin-bottom: 40px;">
            <div class="search-container" style="max-width: 600px; margin: 0 auto;">
                <div class="search-box" style="position: relative;">
                    <input type="text" id="faqSearch" placeholder="Search FAQs..." 
                           style="width: 100%; padding: 15px 50px 15px 20px; border-radius: 25px; border: 2px solid rgba(138, 43, 226, 0.3); background: rgba(255, 255, 255, 0.1); color: white; font-size: 1rem;">
                    <i class="fas fa-search" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); color: var(--primary);"></i>
                </div>
            </div>
        </div>

        <!-- FAQ Categories -->
        <div class="faq-categories" style="display: flex; justify-content: center; gap: 15px; margin-bottom: 40px; flex-wrap: wrap;">
            <button class="category-btn active" data-category="all" style="background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; transition: all 0.3s ease;">
                All Questions
            </button>
            <button class="category-btn" data-category="account" style="background: rgba(255, 255, 255, 0.1); color: #ddd; border: 1px solid rgba(138, 43, 226, 0.3); padding: 10px 20px; border-radius: 20px; cursor: pointer; transition: all 0.3s ease;">
                Account & Billing
            </button>
            <button class="category-btn" data-category="technical" style="background: rgba(255, 255, 255, 0.1); color: #ddd; border: 1px solid rgba(138, 43, 226, 0.3); padding: 10px 20px; border-radius: 20px; cursor: pointer; transition: all 0.3s ease;">
                Technical Issues
            </button>
            <button class="category-btn" data-category="content" style="background: rgba(255, 255, 255, 0.1); color: #ddd; border: 1px solid rgba(138, 43, 226, 0.3); padding: 10px 20px; border-radius: 20px; cursor: pointer; transition: all 0.3s ease;">
                Content & Streaming
            </button>
            <button class="category-btn" data-category="subscription" style="background: rgba(255, 255, 255, 0.1); color: #ddd; border: 1px solid rgba(138, 43, 226, 0.3); padding: 10px 20px; border-radius: 20px; cursor: pointer; transition: all 0.3s ease;">
                Subscription Plans
            </button>
        </div>

        <!-- FAQ Items -->
        <div class="faq-container">
            <?php
            $faqs = [
                'account' => [
                    [
                        'question' => 'How do I create a TechFlix account?',
                        'answer' => 'Click the "Sign Up" button in the top right corner of our website. You\'ll need to provide your email address, create a password, and choose a username. After verifying your email, your account will be ready to use.'
                    ],
                    [
                        'question' => 'How do I reset my password?',
                        'answer' => 'Click "Forgot Password" on the login page, enter your email address, and we\'ll send you a password reset link. Follow the instructions in the email to create a new password.'
                    ],
                    [
                        'question' => 'Can I change my email address?',
                        'answer' => 'Yes, you can change your email address in your account settings. Go to Profile > Account Settings and update your email. You\'ll need to verify the new email address.'
                    ],
                    [
                        'question' => 'How do I update my payment method?',
                        'answer' => 'Go to your Account Settings, select "Billing & Payments", and click "Update Payment Method". You can add, remove, or change your payment information there.'
                    ]
                ],
                'technical' => [
                    [
                        'question' => 'Why is my video buffering or loading slowly?',
                        'answer' => 'Buffering can be caused by several factors: slow internet connection, network congestion, or device performance. Try these solutions: check your internet speed, lower video quality in settings, restart your router, or try watching on a different device.'
                    ],
                    [
                        'question' => 'The video won\'t play. What should I do?',
                        'answer' => 'First, check your internet connection. If that\'s working, try clearing your browser cache and cookies, or try using a different browser. If the problem persists, contact our support team with details about your device and browser.'
                    ],
                    [
                        'question' => 'How do I enable subtitles or closed captions?',
                        'answer' => 'While watching content, click the "CC" icon in the video player. You can choose from available languages and customize the appearance of subtitles in your account settings.'
                    ],
                    [
                        'question' => 'Why is the video quality poor?',
                        'answer' => 'Video quality automatically adjusts based on your internet speed. You can manually set the quality in the video player settings. For best quality, ensure you have a stable internet connection of at least 5 Mbps for HD content.'
                    ]
                ],
                'content' => [
                    [
                        'question' => 'Can I download content to watch offline?',
                        'answer' => 'Yes! Look for the download icon (downward arrow) on content details pages. You can download movies and TV shows to watch without an internet connection. Downloads are available for 30 days once you start watching.'
                    ],
                    [
                        'question' => 'Why can\'t I find a specific movie or show?',
                        'answer' => 'Content availability varies by region due to licensing agreements. Some content may also be temporarily removed. Use our search function and check different categories. If you still can\'t find it, the content may not be available in your region.'
                    ],
                    [
                        'question' => 'How often is new content added?',
                        'answer' => 'We add new content weekly! Check the "New Releases" section regularly. You can also enable notifications to be alerted when new content from your favorite genres or creators is added.'
                    ],
                    [
                        'question' => 'Can I request specific movies or shows?',
                        'answer' => 'Yes! We welcome content suggestions. Use our contact form and select "Content Request" to suggest titles you\'d like to see on TechFlix. While we can\'t guarantee all requests, we consider user suggestions when acquiring new content.'
                    ]
                ],
                'subscription' => [
                    [
                        'question' => 'What subscription plans are available?',
                        'answer' => 'We offer three main plans: Basic (1 screen, SD quality), Standard (2 screens, HD quality), and Premium (4 screens, 4K Ultra HD quality). All plans include access to our full content library and can be canceled anytime.'
                    ],
                    [
                        'question' => 'How do I cancel my subscription?',
                        'answer' => 'Go to your Account Settings, click on "Subscription", and select "Cancel Subscription". Your access will continue until the end of your current billing period. You can resubscribe at any time.'
                    ],
                    [
                        'question' => 'Do you offer student or family discounts?',
                        'answer' => 'Yes! We offer a 50% discount for verified students and a family plan that allows up to 6 profiles. Contact our support team with proof of student status to activate the student discount.'
                    ],
                    [
                        'question' => 'Can I share my account with family?',
                        'answer' => 'Yes! You can create up to 5 profiles on one account. However, the number of simultaneous streams depends on your subscription plan: Basic (1), Standard (2), Premium (4).'
                    ],
                    [
                        'question' => 'Is there a free trial available?',
                        'answer' => 'We offer a 7-day free trial for new users. You\'ll need to provide payment information, but you won\'t be charged until the trial ends. You can cancel anytime during the trial period.'
                    ]
                ]
            ];
            
            foreach ($faqs as $category => $category_faqs):
                foreach ($category_faqs as $index => $faq):
            ?>
            <div class="faq-item" data-category="<?php echo $category; ?>" style="margin-bottom: 15px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; overflow: hidden; background: var(--card-bg);">
                <button class="faq-question" style="width: 100%; padding: 20px; background: rgba(255, 255, 255, 0.05); border: none; color: #fff; text-align: left; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600; font-size: 1.1rem;"><?php echo $faq['question']; ?></span>
                    <i class="fas fa-chevron-down" style="transition: transform 0.3s ease; color: var(--primary);"></i>
                </button>
                <div class="faq-answer" style="padding: 0 20px; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                    <div style="padding: 20px 0; color: #bbb; line-height: 1.6; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                        <?php echo $faq['answer']; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; endforeach; ?>
        </div>

        <!-- No Results Message -->
        <div id="noResults" style="display: none; text-align: center; padding: 40px; color: #bbb;">
            <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
            <h3>No matching questions found</h3>
            <p>Try searching with different keywords or browse the categories above.</p>
        </div>

        <!-- Contact CTA -->
        <div class="contact-cta" style="text-align: center; margin-top: 60px; padding: 40px; background: var(--card-bg); border-radius: 15px;">
            <h2 style="color: var(--primary); margin-bottom: 15px;">Still need help?</h2>
            <p style="color: #bbb; margin-bottom: 25px; max-width: 500px; margin-left: auto; margin-right: auto;">
                Can't find the answer you're looking for? Our support team is here to help you.
            </p>
            <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                <a href="contact.php" class="btn btn-primary">
                    <i class="fas fa-envelope"></i> Contact Support
                </a>
                <a href="help.php" class="btn btn-outline">
                    <i class="fas fa-life-ring"></i> Visit Help Center
                </a>
            </div>
        </div>
    </div>
</div>

<style>
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

    .category-btn.active {
        background: var(--primary) !important;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(138, 43, 226, 0.3);
    }

    .category-btn:hover:not(.active) {
        background: rgba(138, 43, 226, 0.1) !important;
        transform: translateY(-2px);
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

    .faq-item {
        transition: all 0.3s ease;
    }

    .faq-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const faqItems = document.querySelectorAll('.faq-item');
        const categoryButtons = document.querySelectorAll('.category-btn');
        const searchInput = document.getElementById('faqSearch');
        const noResults = document.getElementById('noResults');

        // FAQ Accordion
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

        // Category Filtering
        categoryButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Update active button
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                const category = button.dataset.category;
                filterFAQs(category, searchInput.value);
            });
        });

        // Search Functionality
        searchInput.addEventListener('input', () => {
            const activeCategory = document.querySelector('.category-btn.active').dataset.category;
            filterFAQs(activeCategory, searchInput.value);
        });

        function filterFAQs(category, searchTerm) {
            let visibleCount = 0;
            
            faqItems.forEach(item => {
                const itemCategory = item.dataset.category;
                const question = item.querySelector('.faq-question span').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                const searchLower = searchTerm.toLowerCase();
                
                const matchesCategory = category === 'all' || itemCategory === category;
                const matchesSearch = !searchTerm || question.includes(searchLower) || answer.includes(searchLower);
                
                if (matchesCategory && matchesSearch) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            if (visibleCount === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }

        // Open first FAQ item by default
        if (faqItems.length > 0) {
            faqItems[0].classList.add('active');
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>