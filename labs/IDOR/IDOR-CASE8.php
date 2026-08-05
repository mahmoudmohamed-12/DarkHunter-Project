<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['IDOR']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['idor_hard4_attempts'])) {
  $_SESSION['idor_hard4_attempts'] = 0;
}
if (!isset($_SESSION['idor_hard4_solved'])) {
  $_SESSION['idor_hard4_solved'] = false;
}

// ─── Simulated GraphQL Data Store ──────────────────────────────────────────
$users = [
  'u1' => ['id' => 'u1', 'username' => 'admin', 'email' => 'admin@darkhunter.local', 'role' => 'admin', 'ssn' => '123-45-6789', 'salary' => 150000],
  'u2' => ['id' => 'u2', 'username' => 'alice', 'email' => 'alice@darkhunter.local', 'role' => 'user', 'ssn' => '987-65-4321', 'salary' => 75000],
  'u3' => ['id' => 'u3', 'username' => 'bob', 'email' => 'bob@darkhunter.local', 'role' => 'user', 'ssn' => '456-78-9012', 'salary' => 82000],
  'u4' => ['id' => 'u4', 'username' => 'charlie', 'email' => 'charlie@darkhunter.local', 'role' => 'user', 'ssn' => '789-01-2345', 'salary' => 68000],
];

// ─── Vulnerable GraphQL Resolver Logic ─────────────────────────────────────
// The "query" parameter simulates a GraphQL query string
$query = isset($_GET['query']) ? $_GET['query'] : '{ user(id: "u2") { username email } }';

// Simple GraphQL parser simulation
$extracted_id = 'u2';
if (preg_match('/user\(id:\s*["\']?(\w+)["\']?\)/', $query, $matches)) {
  $extracted_id = $matches[1];
}

$requested_fields = ['username', 'email'];
if (preg_match_all('/(\w+)/', $query, $field_matches)) {
  $requested_fields = array_unique($field_matches[1]);
}

$user = isset($users[$extracted_id]) ? $users[$extracted_id] : $users['u2'];
$is_admin = ($user['role'] === 'admin');

// Build response (vulnerable: returns ALL requested fields without auth check)
$response_data = [];
foreach ($requested_fields as $field) {
  if (isset($user[$field])) {
    $response_data[$field] = $user[$field];
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['idor_hard4_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['idor_hard4_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['idor_hard4_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've exploited a GraphQL BOLA vulnerability! You used nested queries to access unauthorized user data through resolver-level IDOR.";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
  $_SESSION['idor_hard4_attempts']++;
}

$attempts = $_SESSION['idor_hard4_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GraphQL Explorer - IDOR Hard 4</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/idor-vuln-case-8.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to IDOR Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-project-diagram"></i> GraphQL API Explorer</h1>
      <p class="lab-description">Query user data through our GraphQL API. This challenge demonstrates <strong>GraphQL
          BOLA</strong> (Broken Object Level Authorization). Resolvers don't check ownership before returning sensitive
        fields. <strong>No field-level authorization!</strong></p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this GraphQL BOLA vulnerability. You can continue exploring, but no additional
            points will be awarded.</p>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($success_msg): ?>
      <div class="success-alert">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Completed!</h3>
          <p><?php echo $success_msg; ?></p>
        </div>
      </div>
    <?php endif; ?>

    <!-- GraphQL Query Editor -->
    <div class="graphql-panel">
      <div class="graphql-header">
        <i class="fas fa-terminal"></i>
        <span>GraphQL Query</span>
        <span class="graphql-badge">POST /graphql</span>
      </div>
      <form method="GET" action="" class="graphql-form">
        <div class="query-editor">
          <textarea name="query" class="query-input" rows="6"
            spellcheck="false"><?php echo htmlspecialchars($query); ?></textarea>
        </div>
        <button type="submit" class="execute-btn">
          <i class="fas fa-play"></i> Execute Query
        </button>
      </form>
    </div>

    <!-- Response Panel -->
    <div class="response-panel">
      <div class="response-header">
        <i class="fas fa-code"></i>
        <span>Response</span>
        <span class="response-status">200 OK</span>
      </div>
      <div class="response-body">
        <pre><?php echo json_encode([
                'data' => [
                  'user' => $response_data
                ]
              ], JSON_PRETTY_PRINT); ?></pre>
      </div>
    </div>

    <!-- Schema Documentation -->
    <div class="schema-panel">
      <h3><i class="fas fa-book"></i> Schema Documentation</h3>
      <div class="schema-type">
        <code class="type-name">type User</code>
        <div class="type-fields">
          <div class="field-item">
            <code class="field-name">id</code>
            <span class="field-type">ID!</span>
            <span class="field-desc">Unique user identifier</span>
          </div>
          <div class="field-item">
            <code class="field-name">username</code>
            <span class="field-type">String!</span>
            <span class="field-desc">User login name</span>
          </div>
          <div class="field-item">
            <code class="field-name">email</code>
            <span class="field-type">String!</span>
            <span class="field-desc">User email address</span>
          </div>
          <div class="field-item sensitive">
            <code class="field-name">role</code>
            <span class="field-type">String!</span>
            <span class="field-desc">User role (admin/user)</span>
            <i class="fas fa-eye-slash"></i>
          </div>
          <div class="field-item sensitive">
            <code class="field-name">ssn</code>
            <span class="field-type">String!</span>
            <span class="field-desc">Social Security Number</span>
            <i class="fas fa-eye-slash"></i>
          </div>
          <div class="field-item sensitive">
            <code class="field-name">salary</code>
            <span class="field-type">Int!</span>
            <span class="field-desc">Annual salary in USD</span>
            <i class="fas fa-eye-slash"></i>
          </div>
        </div>
      </div>
      <div class="schema-query">
        <code class="type-name">type Query</code>
        <div class="type-fields">
          <div class="field-item">
            <code class="field-name">user(id: ID!)</code>
            <span class="field-type">User</span>
            <span class="field-desc">Get user by ID</span>
          </div>
        </div>
      </div>
    </div>

    <!-- User IDs Reference -->
    <div class="users-ref">
      <h3><i class="fas fa-users"></i> User IDs</h3>
      <div class="users-grid">
        <?php foreach ($users as $uid => $u): ?>
          <div class="user-chip <?php echo $u['role'] === 'admin' ? 'admin-chip' : ''; ?>">
            <span class="chip-id"><?php echo htmlspecialchars($uid); ?></span>
            <span class="chip-name"><?php echo htmlspecialchars($u['username']); ?></span>
            <?php if ($u['role'] === 'admin'): ?>
              <i class="fas fa-crown"></i>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">GraphQL allows clients to request any fields they want. The schema shows sensitive fields
          like <code>ssn</code> and <code>salary</code> marked as hidden, but the resolver might still return them.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Try changing the user ID in the query to access other users. Then request sensitive fields
          like <code>ssn</code>, <code>salary</code>, or <code>role</code>. The resolver doesn't check if you should see
          these fields.</div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Query the admin user with
          <code>{ user(id: "u1") { username email role ssn salary } }</code>. The resolver returns ALL fields without
          authorization checks, exposing the admin's sensitive data including the flag!
        </div>
      </div>
    <?php endif; ?>

    <!-- Attempts Counter -->
    <div class="attempts-bar">
      <i class="fas fa-crosshairs"></i>
      <span>Attempts: <strong><?php echo $attempts; ?></strong></span>
    </div>
  </div>

  <!-- Hidden form for solve detection -->
  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
    window.addEventListener('load', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const query = urlParams.get('query') || '';

      // Check if query contains admin id and sensitive fields
      const hasAdminId = query.includes('u1');
      const hasSensitiveField = query.includes('ssn') || query.includes('salary') || query.includes('role');

      if (hasAdminId && hasSensitiveField && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>