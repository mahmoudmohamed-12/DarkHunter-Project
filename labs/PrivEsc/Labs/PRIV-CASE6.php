<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Privilege-Escalation']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['priv_case6_attempts'])) {
  $_SESSION['priv_case6_attempts'] = 0;
}
if (!isset($_SESSION['priv_case6_solved'])) {
  $_SESSION['priv_case6_solved'] = false;
}

// ─── Simulated GraphQL Schema ────────────────────────────────────────────
$graphql_schema = '
type Query {
  me: User
  users: [User]        # Admin only
  adminStats: Stats    # Admin only
  systemLogs: [Log]    # Super Admin only
}

type Mutation {
  updateProfile(input: ProfileInput!): User
  deleteUser(id: ID!): Boolean      # Admin only
  promoteUser(id: ID!, role: String!): User  # Super Admin only
}

type User {
  id: ID!
  username: String!
  email: String!
  role: String!
  isAdmin: Boolean
  isSuperUser: Boolean
}

type Stats {
  totalUsers: Int
  activeSessions: Int
  systemHealth: String
}

type Log {
  id: ID!
  action: String!
  timestamp: String!
  userId: ID!
}

input ProfileInput {
  username: String
  email: String
  role: String        # VULNERABLE: Should not be user-editable
  isAdmin: Boolean    # VULNERABLE: Should not be user-editable
}
';

// ─── Handle GraphQL Queries ──────────────────────────────────────────────
$query_submitted = false;
$introspection_detected = false;
$admin_query_detected = false;
$flag_triggered = false;
$query_result = null;
$query_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute_query'])) {
  $query = $_POST['graphql_query'] ?? '';
  $query_submitted = true;

  // Check for introspection
  if (
    stripos($query, '__schema') !== false ||
    stripos($query, '__type') !== false ||
    stripos($query, 'IntrospectionQuery') !== false
  ) {
    $introspection_detected = true;
  }

  // Check for admin queries
  if (
    stripos($query, 'users') !== false ||
    stripos($query, 'adminStats') !== false ||
    stripos($query, 'systemLogs') !== false ||
    stripos($query, 'deleteUser') !== false ||
    stripos($query, 'promoteUser') !== false
  ) {
    $admin_query_detected = true;
  }

  // Check for escalation in mutations
  if (stripos($query, 'role') !== false && stripos($query, 'admin') !== false) {
    $admin_query_detected = true;
  }

  // Simulate query execution
  if ($introspection_detected || $admin_query_detected) {
    $flag_triggered = true;
    $query_result = [
      'data' => [
        '__schema' => ['types' => ['User', 'Admin', 'SuperUser']],
        'users' => [
          ['id' => '1', 'username' => 'admin', 'role' => 'administrator', 'isAdmin' => true],
          ['id' => '2', 'username' => 'superuser', 'role' => 'superuser', 'isSuperUser' => true]
        ],
        'adminStats' => ['totalUsers' => 42, 'activeSessions' => 15, 'systemHealth' => 'Good']
      ]
    ];
  } else {
    $query_result = [
      'data' => [
        'me' => ['id' => '3', 'username' => 'user123', 'role' => 'user', 'isAdmin' => false]
      ]
    ];
  }
}

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['priv_case6_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['priv_case6_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['priv_case6_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've successfully exploited a GraphQL Introspection vulnerability. By querying the __schema or accessing admin-only fields, you bypassed field-level authorization and escalated your privileges!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['execute_query'])) {
  $_SESSION['priv_case6_attempts']++;
}

$attempts = $_SESSION['priv_case6_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GraphQL Explorer - PrivEsc Case 6 (GraphQL Introspection)</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/PRIV-CASE6.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to PrivEsc Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-project-diagram"></i> GraphQL Explorer</h1>
      <p class="lab-description">Explore our GraphQL API. This hard Privilege Escalation challenge has
        <strong>introspection enabled</strong> and weak field-level authorization. <strong>Access admin-only queries
          and mutations!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner">
        <i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this GraphQL Introspection vulnerability. You can continue exploring, but no
            additional points will be awarded.</p>
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

    <!-- GraphQL Grid -->
    <div class="graphql-grid">

      <!-- Query Editor -->
      <div class="graphql-card editor-card">
        <div class="card-header">
          <i class="fas fa-code"></i>
          <h3>Query Editor</h3>
          <span class="vuln-badge"><i class="fas fa-unlock"></i> Introspection Enabled</span>
        </div>

        <form method="POST" action="" class="graphql-form" id="graphql-form">
          <input type="hidden" name="execute_query" value="1">

          <div class="form-group">
            <label><i class="fas fa-terminal"></i> GraphQL Query</label>
            <textarea name="graphql_query" rows="10" class="form-textarea"
              placeholder="Enter your GraphQL query here..."><?php echo isset($_POST['graphql_query']) ? htmlspecialchars($_POST['graphql_query']) : '{ me { id username role } }'; ?></textarea>
          </div>

          <button type="submit" class="btn-execute">
            <i class="fas fa-play"></i> Execute Query
          </button>
        </form>
      </div>

      <!-- Query Result -->
      <div class="graphql-card result-card">
        <div class="card-header">
          <i class="fas fa-reply"></i>
          <h3>Query Result</h3>
        </div>

        <?php if ($query_submitted && $query_result): ?>
          <div class="result-content">
            <pre class="result-code"><code><?php echo json_encode($query_result, JSON_PRETTY_PRINT); ?></code></pre>
          </div>

          <?php if ($introspection_detected): ?>
            <div class="escalation-alert">
              <i class="fas fa-exclamation-triangle"></i>
              <span>Introspection query detected! Schema exposed!</span>
            </div>
          <?php endif; ?>

          <?php if ($admin_query_detected): ?>
            <div class="escalation-alert">
              <i class="fas fa-exclamation-triangle"></i>
              <span>Admin-only query/mutation detected! Authorization bypassed!</span>
            </div>
          <?php endif; ?>
        <?php else: ?>
          <div class="result-placeholder">
            <i class="fas fa-brackets-curly"></i>
            <p>Execute a query to see the results</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Schema Viewer -->
      <div class="graphql-card schema-card">
        <div class="card-header">
          <i class="fas fa-sitemap"></i>
          <h3>Schema Overview</h3>
        </div>
        <div class="schema-content">
          <pre class="schema-code"><code><?php echo htmlspecialchars($graphql_schema); ?></code></pre>
        </div>
      </div>

      <!-- Authorization Analysis -->
      <div class="graphql-card analysis-card">
        <div class="card-header">
          <i class="fas fa-microscope"></i>
          <h3>Authorization Analysis</h3>
        </div>
        <div class="analysis-content">
          <div class="analysis-item">
            <span class="analysis-label">Introspection:</span>
            <code class="analysis-code">__schema { types { name } } // ENABLED</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Enabled</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Field Auth:</span>
            <code class="analysis-code">// No field-level checks</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
          <div class="analysis-item">
            <span class="analysis-label">Mutation Auth:</span>
            <code class="analysis-code">// No role validation</code>
            <span class="analysis-status vuln"><i class="fas fa-times-circle"></i> Missing</span>
          </div>
        </div>
      </div>

      <!-- Attack Vectors -->
      <div class="graphql-card vectors-card">
        <div class="card-header">
          <i class="fas fa-skull-crossbones"></i>
          <h3>GraphQL Attack Vectors</h3>
        </div>
        <div class="vectors-list">
          <div class="vector-item">
            <div class="vector-name">Introspection Query</div>
            <code class="vector-code">{ __schema { types { name fields { name } } } }</code>
            <span class="vector-desc">Dump entire schema structure</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Admin User Query</div>
            <code class="vector-code">{ users { id username role isAdmin } }</code>
            <span class="vector-desc">Access admin-only user list</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Privilege Escalation Mutation</div>
            <code class="vector-code">mutation { updateProfile(input: {role: "admin"}) { role } }</code>
            <span class="vector-desc">Change role via mutation</span>
          </div>
          <div class="vector-item">
            <div class="vector-name">Nested Resolver Abuse</div>
            <code class="vector-code">{ me { ... on Admin { systemLogs { action } } } }</code>
            <span class="vector-desc">Bypass via type conditions</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Debug Panel -->
    <div class="debug-panel">
      <div class="debug-header">
        <i class="fas fa-code"></i>
        <span>Current Request</span>
      </div>
      <div class="debug-body">
        <code><?php echo $_SERVER['REQUEST_METHOD']; ?> <?php echo $_SERVER['REQUEST_URI']; ?></code>
        <div class="request-details">
          <span>Query Submitted: <?php echo $query_submitted ? 'YES' : 'NO'; ?></span>
          <span>Introspection: <?php echo $introspection_detected ? 'YES' : 'NO'; ?></span>
          <span>Admin Query: <?php echo $admin_query_detected ? 'YES' : 'NO'; ?></span>
          <span>Flag Triggered: <?php echo $flag_triggered ? 'YES' : 'NO'; ?></span>
        </div>
      </div>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">GraphQL introspection allows you to query the schema itself. Try running
          <code>{ __schema { types { name } } }</code> to discover all available types and fields.
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">Look for admin-only queries like <code>users</code>, <code>adminStats</code>, or
          <code>systemLogs</code>. The server doesn't check if you're authorized to access these fields!
        </div>
      </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Try this query to dump everything:
          <code>{ __schema { types { name fields { name type { name } } } } }</code>. Then access admin fields directly
          like <code>{ users { id username role isAdmin } }</code> or escalate via mutation:
          <code>mutation { updateProfile(input: {role: "admin"}) { role } }</code>.
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
      const escalationAlert = document.querySelector('.escalation-alert');
      const alreadySolved = document.querySelector('.solved-banner');

      if (escalationAlert && !alreadySolved) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>