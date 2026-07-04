<?php
require_once 'includes/header.php';
require_once 'includes/SubscriptionManager.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: auth/login.php");
    exit();
}

$subscriptionManager = new SubscriptionManager($conn);
$plans = $subscriptionManager->getSubscriptionPlans();
$current_subscription = $subscriptionManager->getUserSubscription($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFlix - Subscription Plans</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #fff;
            overflow-x: hidden;
            position: relative;
        }

        .galaxy {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .star {
            position: absolute;
            background: #fff;
            border-radius: 50%;
            animation: twinkle var(--duration) infinite ease-in-out;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.1; }
            50% { opacity: 1; }
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .page-title {
            font-size: 3rem;
            margin-bottom: 15px;
            background: linear-gradient(45deg, #6a5af9, #d66efd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-subtitle {
            font-size: 1.2rem;
            color: #bbb;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .alert-success {
            background: rgba(46, 213, 115, 0.2);
            border: 1px solid #2ed573;
            color: #2ed573;
        }

        .current-subscription {
            margin-bottom: 40px;
        }

        .subscription-card {
            background: rgba(106, 90, 249, 0.1);
            border: 2px solid #6a5af9;
            border-radius: 15px;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        .subscription-card h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #6a5af9;
        }

        .plan-details h4 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .plan-details .price {
            font-size: 1.8rem;
            font-weight: 700;
            color: #6a5af9;
            margin-bottom: 5px;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .plan-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            backdrop-filter: blur(10px);
        }

        .plan-card:hover {
            transform: translateY(-10px);
            border-color: rgba(106, 90, 249, 0.3);
            background: rgba(255, 255, 255, 0.08);
        }

        .plan-card.current {
            border-color: #6a5af9;
            background: rgba(106, 90, 249, 0.15);
        }

        .plan-card.popular {
            border-color: #ffd700;
            background: rgba(255, 215, 0, 0.1);
        }

        .plan-card.popular::before {
            content: 'Most Popular';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background: #ffd700;
            color: #000;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .plan-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .plan-header h3 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #fff;
        }

        .price {
            font-size: 3rem;
            font-weight: 700;
            color: #6a5af9;
        }

        .price .period {
            font-size: 1.2rem;
            color: #ccc;
        }

        .plan-features ul {
            list-style: none;
            margin-bottom: 30px;
        }

        .plan-features li {
            padding: 12px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .plan-features li:last-child {
            border-bottom: none;
        }

        .plan-features i {
            color: #6a5af9;
            width: 20px;
        }

        .plan-actions {
            text-align: center;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 25px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, #6a5af9, #d66efd);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(106, 90, 249, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #6a5af9;
            color: #6a5af9;
        }

        .btn-outline:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .features-comparison {
            margin-top: 60px;
        }

        .features-comparison h3 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
            color: #fff;
        }

        .comparison-table {
            overflow-x: auto;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(10px);
        }

        .comparison-table table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .comparison-table th,
        .comparison-table td {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .comparison-table th {
            background: rgba(106, 90, 249, 0.2);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .comparison-table th:first-child {
            text-align: left;
            background: transparent;
        }

        .comparison-table td:first-child {
            text-align: left;
            font-weight: 600;
        }

        .comparison-table tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .text-success {
            color: #2ed573;
        }

        .text-muted {
            color: #666;
        }

        @media (max-width: 768px) {
            .plans-grid {
                grid-template-columns: 1fr;
            }
            
            .subscription-card {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .page-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="galaxy" id="galaxy"></div>
    
    

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-crown"></i> Choose Your Plan
            </h1>
            <p class="page-subtitle">Select the perfect plan for your entertainment needs</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Subscription activated successfully! Enjoy your premium experience.
            </div>
        <?php endif; ?>

        <?php if ($current_subscription): ?>
            <div class="current-subscription">
                <div class="subscription-card active">
                    <div>
                        <h3><i class="fas fa-star"></i> Current Plan</h3>
                        <div class="plan-details">
                            <h4><?php echo htmlspecialchars($current_subscription['plan_name']); ?></h4>
                            <p class="price">$<?php echo $current_subscription['price']; ?>/month</p>
                            <p>Renews: <?php echo date('M j, Y', strtotime($current_subscription['end_date'])); ?></p>
                        </div>
                    </div>
                    <div>
                        <a href="profile.php" class="btn btn-outline">Manage Subscription</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="plans-grid">
            <?php foreach ($plans as $index => $plan): 
                $is_current = $current_subscription && $current_subscription['plan_id'] == $plan['id'];
                $is_popular = $plan['name'] == 'Standard' || $plan['name'] == 'Premium';
            ?>
                <div class="plan-card <?php echo $is_current ? 'current' : ''; ?> <?php echo $is_popular ? 'popular' : ''; ?>">
                    <div class="plan-header">
                        <h3><?php echo htmlspecialchars($plan['name']); ?></h3>
                        <div class="price">
                            <span class="amount">$<?php echo $plan['price']; ?></span>
                            <span class="period">/month</span>
                        </div>
                    </div>
                    
                    <div class="plan-features">
                        <ul>
                            <li><i class="fas fa-check"></i> <?php echo $plan['video_quality']; ?> Quality</li>
                            <li><i class="fas fa-check"></i> <?php echo $plan['max_screens']; ?> Simultaneous Screens</li>
                            <li><i class="fas fa-check"></i> <?php echo $plan['ad_free'] ? 'Ad-Free Streaming' : 'With Ads'; ?></li>
                            <li><i class="fas fa-check"></i> <?php echo $plan['download_limit'] ? $plan['download_limit'] . ' Downloads/Month' : 'No Downloads'; ?></li>
                            <li><i class="fas fa-check"></i> Cancel Anytime</li>
                        </ul>
                    </div>
                    
                    <div class="plan-actions">
                        <?php if ($is_current): ?>
                            <button class="btn btn-outline" disabled>Current Plan</button>
                        <?php else: ?>
                            <a href="payment.php?plan_id=<?php echo $plan['id']; ?>" class="btn btn-primary">
                                <?php echo $current_subscription ? 'Switch Plan' : 'Get Started'; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="features-comparison">
            <h3>Plan Comparison</h3>
            <div class="comparison-table">
                <table>
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <?php foreach ($plans as $plan): ?>
                                <th><?php echo htmlspecialchars($plan['name']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Video Quality</td>
                            <?php foreach ($plans as $plan): ?>
                                <td><?php echo $plan['video_quality']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td>Simultaneous Screens</td>
                            <?php foreach ($plans as $plan): ?>
                                <td><?php echo $plan['max_screens']; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td>Ad-Free</td>
                            <?php foreach ($plans as $plan): ?>
                                <td><?php echo $plan['ad_free'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td>Downloads</td>
                            <?php foreach ($plans as $plan): ?>
                                <td><?php echo $plan['download_limit'] ? $plan['download_limit'] . '/month' : 'None'; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td>Monthly Price</td>
                            <?php foreach ($plans as $plan): ?>
                                <td><strong>$<?php echo $plan['price']; ?></strong></td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Galaxy background animation
        function createGalaxy() {
            const galaxy = document.getElementById('galaxy');
            if (!galaxy) return;
            
            const starsCount = 200;

            for (let i = 0; i < starsCount; i++) {
                const star = document.createElement('div');
                star.classList.add('star');

                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                const size = Math.random() * 2.5 + 0.5;
                const duration = Math.random() * 5 + 3;

                star.style.left = `${posX}%`;
                star.style.top = `${posY}%`;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.setProperty('--duration', `${duration}s`);

                galaxy.appendChild(star);
            }
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function () {
            createGalaxy();
        });
    </script>

    <?php if (file_exists('includes/footer.php')) include 'includes/footer.php'; ?>
</body>
</html>