<?php

function generateUUID() {
    return uniqid("log_", true); 
}


function logTransaction($pdo, $user_id, $fullname, $description, $transaction_user) {
    $transaction_id = generateUUID();
    $stmt = $pdo->prepare("INSERT INTO transaction_logs (
        transaction_id, user_id, fullname, transaction_date, transaction_description, transaction_user
    ) VALUES (?, ?, ?, NOW(), ?, ?)");
    $stmt->execute([$transaction_id, $user_id, $fullname, $description, $transaction_user]);
}


function logAudit($pdo, $user_id, $activity, $new_value, $old_value, $sys_user, $success_yn = 'Y') {
    $audit_id = generateUUID();
    $stmt = $pdo->prepare("INSERT INTO audit_logs (
        audit_id, user_id, log_date, activity, new_value, old_value, sys_user, success_yn
    ) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?)");
    $stmt->execute([$audit_id, $user_id, $activity, $new_value, $old_value, $sys_user, $success_yn]);
}
?>
