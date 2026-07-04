<?php
class SubscriptionManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getSubscriptionPlans() {
        $plans = [];
        try {
            $query = "SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY price ASC";
            $result = $this->conn->query($query);
            while ($row = $result->fetch_assoc()) {
                $plans[] = $row;
            }
            return $plans;
        } catch (Exception $e) {
            error_log("Subscription plans error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUserSubscription($user_id) {
        try {
            $query = "SELECT us.*, sp.name as plan_name, sp.price, sp.video_quality, sp.max_screens, sp.ad_free, sp.download_limit 
                     FROM user_subscriptions us 
                     JOIN subscription_plans sp ON us.plan_id = sp.id 
                     WHERE us.user_id = ? AND us.status = 'active' 
                     ORDER BY us.end_date DESC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("User subscription error: " . $e->getMessage());
            return null;
        }
    }
    
    public function createSubscription($user_id, $plan_id, $payment_id = null) {
        try {
            $this->conn->begin_transaction();
            
            // Get plan details
            $plan_query = "SELECT * FROM subscription_plans WHERE id = ?";
            $plan_stmt = $this->conn->prepare($plan_query);
            $plan_stmt->bind_param("i", $plan_id);
            $plan_stmt->execute();
            $plan = $plan_stmt->get_result()->fetch_assoc();
            $plan_stmt->close();
            
            if (!$plan) {
                throw new Exception("Invalid subscription plan");
            }
            
            // Cancel any existing active subscription
            $cancel_query = "UPDATE user_subscriptions SET status = 'canceled' WHERE user_id = ? AND status = 'active'";
            $cancel_stmt = $this->conn->prepare($cancel_query);
            $cancel_stmt->bind_param("i", $user_id);
            $cancel_stmt->execute();
            $cancel_stmt->close();
            
            // Create new subscription
            $start_date = date('Y-m-d H:i:s');
            $end_date = date('Y-m-d H:i:s', strtotime("+{$plan['duration_days']} days"));
            
            $insert_query = "INSERT INTO user_subscriptions (user_id, plan_id, status, start_date, end_date, auto_renew) 
                           VALUES (?, ?, 'active', ?, ?, 1)";
            $insert_stmt = $this->conn->prepare($insert_query);
            $insert_stmt->bind_param("iiss", $user_id, $plan_id, $start_date, $end_date);
            
            if ($insert_stmt->execute()) {
                $subscription_id = $this->conn->insert_id;
                $this->conn->commit();
                return $subscription_id;
            } else {
                throw new Exception("Failed to create subscription");
            }
            
            $insert_stmt->close();
            
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Create subscription error: " . $e->getMessage());
            return false;
        }
    }
}
?>