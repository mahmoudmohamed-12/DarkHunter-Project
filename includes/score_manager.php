<?php
// includes/score_manager.php

/**

 * @param PDO $pdo 
 * @param int $lab_id 
 */
function solveLab($pdo, $lab_id) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $user_id = $_SESSION['user_id'];

    try {
        $sql_update = "UPDATE users SET score = score + 50, total_xp = total_xp + 25 WHERE id = ?";
        $pdo->prepare($sql_update)->execute([$user_id]);

    $sql_progress = "
INSERT INTO user_progress (user_id, lab_id, status, completed_at) 
VALUES (?, ?, 'completed', NOW())
ON DUPLICATE KEY UPDATE status='completed'
";

    $pdo->prepare($sql_progress)->execute([$user_id, $lab_id]);

        return true;
    } catch (PDOException $e) {
        return false;
    }
}
?>