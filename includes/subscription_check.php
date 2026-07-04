<?php
require_once 'SubscriptionManager.php';

function checkSubscriptionAccess($user_id, $conn) {
    $subscriptionManager = new SubscriptionManager($conn);
    
    if (!$subscriptionManager->isValidSubscription($user_id)) {
        // Redirect to subscription page if user doesn't have valid subscription
        $_SESSION['redirect_message'] = 'Please subscribe to access premium content';
        header('Location: subscription_plans.php');
        exit();
    }
    
    return true;
}

// Function to get subscription features
function getSubscriptionFeatures($user_id, $conn) {
    $subscriptionManager = new SubscriptionManager($conn);
    $subscription = $subscriptionManager->getUserSubscription($user_id);
    
    if (!$subscription) {
        return [
            'video_quality' => 'SD',
            'max_screens' => 1,
            'ad_free' => false,
            'download_enabled' => false
        ];
    }
    
    return [
        'video_quality' => $subscription['video_quality'],
        'max_screens' => $subscription['max_screens'],
        'ad_free' => (bool)$subscription['ad_free'],
        'download_enabled' => $subscription['download_limit'] > 0
    ];
}
?>