<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();


$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['IDOR']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['idor_medium1_attempts'])) {
  $_SESSION['idor_medium1_attempts'] = 0;
}
if (!isset($_SESSION['idor_medium1_solved'])) {
  $_SESSION['idor_medium1_solved'] = false;
}

// ─── Simulated API Data ────────────────────────────────────────────────────
$api_docs = [
  'DOC-2024-001' => ['title' => 'Employee Handbook 2024', 'owner' => 'hr_dept', 'confidential' => false, 'content' => 'Welcome to DarkHunter. This handbook covers company policies.'],
  'DOC-2024-002' => ['title' => 'Q1 Financial Report', 'owner' => 'finance', 'confidential' => true, 'content' => 'Revenue: $2.4M | Expenses: $1.8M | Net: $600K. Flag: DH{idor_api_bola_pwned}'],
  'DOC-2024-003' => ['title' => 'Security Audit Results', 'owner' => 'security', 'confidential' => true, 'content' => 'Critical: 3 | High: 7 | Medium: 12. Remediation in progress.'],
  'DOC-2024-004' => ['title' => 'Product Roadmap', 'owner' => 'product', 'confidential' => false, 'content' => 'Q2: API v2 | Q3: Mobile App | Q4: AI Integration.'],
  'DOC-2024-005' => ['title' => 'Salary Bands', 'owner' => 'hr_dept', 'confidential' => true, 'content' => 'Engineer L1: $80K-$110K | Engineer L2: $110K-$150K | Staff: $150K-$200K'],
];

// ─── Vulnerable API Logic ────────────────────────────────────────────────────
$doc_id = isset($_GET['doc_id']) ? $_GET['doc_id'] : 'DOC-2024-001';
$doc = isset($api_docs[$doc_id]) ? $api_docs[$doc_id] : null;

if (!$doc) {
  $doc_id = 'DOC-2024-001';
  $doc = $api_docs[$doc_id];
}

// ─── Solve Detection ───────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['idor_medium1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['idor_medium1_attempts']++;

  if (!$already_solved && isset($_SESSION['user_id'])) {
    solveLab($pdo, $lab['id']);
  }

  $_SESSION['idor_medium1_solved'] = true;
  $already_solved = true;
  $success_msg = "Excellent! You've exploited an API IDOR vulnerability via JSON body manipulation. You accessed confidential documents by changing the doc_id parameter!";
}

// ─── Attempt Tracking ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['doc_id'])) {
  $_SESSION['idor_medium1_attempts']++;
}

$attempts = $_SESSION['idor_medium1_attempts'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Document Viewer - IDOR Medium 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/idor-vuln-case-3.css">
  <link rel="stylesheet" type="text/css" href="idor-common.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>

  <div class="container">
    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to IDOR Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-file-code"></i> API Document Viewer</h1>
      <p class="lab-description">View public company documents via our REST API. This challenge demonstrates IDOR
        through API endpoints. Try to access confidential documents by manipulating the <code>doc_id</code> parameter.
        <strong>No authorization checks on API resources!</strong>
      </p>
    </div>

    <?php if ($already_solved): ?>
    <div class="success-alert solved-banner">
      <i class="fas fa-trophy"></i>
      <div class="success-content">
        <h3>Challenge Already Solved!</h3>
        <p>You have already exploited this API IDOR vulnerability. You can continue exploring, but no additional points
          will be awarded.</p>
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

    <!-- API Request Panel -->
    <div class="api-panel">
      <div class="api-header">
        <i class="fas fa-server"></i>
        <span>API Request</span>
        <span class="api-method">GET</span>
      </div>
      <div class="api-body">
        <code>GET /api/documents?doc_id=<?php echo htmlspecialchars($doc_id); ?></code>
        <div class="api-response">
          <pre><?php echo json_encode([
                  'status' => 'success',
                  'document_id' => $doc_id,
                  'title' => $doc['title'],
                  'owner' => $doc['owner'],
                  'confidential' => $doc['confidential'],
                  'content' => $doc['content']
                ], JSON_PRETTY_PRINT); ?></pre>
        </div>
      </div>
    </div>

    <!-- Document Card -->
    <div class="document-card <?php echo $doc['confidential'] ? 'confidential' : ''; ?>">
      <div class="doc-header">
        <div class="doc-icon">
          <i class="fas fa-<?php echo $doc['confidential'] ? 'lock' : 'file-alt'; ?>"></i>
        </div>
        <div class="doc-meta">
          <h2 class="doc-title"><?php echo htmlspecialchars($doc['title']); ?></h2>
          <span class="doc-owner"><i class="fas fa-user"></i> <?php echo htmlspecialchars($doc['owner']); ?></span>
          <?php if ($doc['confidential']): ?>
          <span class="confidential-badge"><i class="fas fa-exclamation-triangle"></i> CONFIDENTIAL</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="doc-content">
        <p><?php echo htmlspecialchars($doc['content']); ?></p>
      </div>
    </div>

    <!-- Available Documents List -->
    <div class="docs-list">
      <h3><i class="fas fa-list"></i> Available Documents</h3>
      <?php foreach ($api_docs as $id => $d): ?>
      <a href="?doc_id=<?php echo urlencode($id); ?>"
        class="doc-item <?php echo $id === $doc_id ? 'active' : ''; ?> <?php echo $d['confidential'] ? 'confidential-item' : ''; ?>">
        <span class="doc-id"><?php echo htmlspecialchars($id); ?></span>
        <span class="doc-name"><?php echo htmlspecialchars($d['title']); ?></span>
        <?php if ($d['confidential']): ?>
        <span class="doc-lock"><i class="fas fa-lock"></i></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Hints -->
    <?php if ($attempts >= 2): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
      <div class="hint-text">Look at the URL parameter <code>doc_id</code>. The documents follow a predictable pattern:
        <code>DOC-2024-00X</code>. Try changing the number to access other documents.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 4): ?>
    <div class="hint-box">
      <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
      <div class="hint-text">Some documents are marked as confidential but the API returns them anyway. Try accessing
        <code>DOC-2024-002</code> or <code>DOC-2024-005</code> — they contain sensitive data.
      </div>
    </div>
    <?php endif; ?>

    <?php if ($attempts >= 6): ?>
    <div class="hint-box hint-final">
      <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
      <div class="hint-text">Access the confidential financial report by changing the URL to
        <code>?doc_id=DOC-2024-002</code>. The document content contains the flag!
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
    const docId = urlParams.get('doc_id');

    if (docId === 'DOC-2024-002' && !document.querySelector('.solved-banner')) {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }
  });
  </script>
</body>

</html>