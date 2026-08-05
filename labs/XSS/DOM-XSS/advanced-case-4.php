<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

if (!isset($_SESSION['dom_hard_attempts'])) {
    $_SESSION['dom_hard_attempts'] = 0;
}

if (isset($_GET['check']) && $_GET['check'] === 'true') {
    $isSolved = isset($_GET['solved']) && $_GET['solved'] === '1';
    if ($isSolved && isset($_SESSION['user_id'])) {
        $success = solveLab($pdo, 4);
        
        if ($success) {
            $success_msg = "🏆 DOM XSS Hard Completed! +50 pts";
        } else {
            $error_msg = "Error saving progress!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Advanced DOM - Prototype Pollution</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/advanced-case-4.css">


</head>

<body><?php include_once __DIR__ . '/../../../includes/navbar.php';

  ?><div class="container"><a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i>Back</a>
    <div class="warning-banner"><i class="fas fa-biohazard" style="font-size: 3rem; color: var(--neon-red);"></i>
      <div>
        <h2 style="color: var(--neon-red); font-family: 'Orbitron'; margin-bottom: 10px;">EXTREME DIFFICULTY</h2>
        <p>Prototype Pollution+DOM Clobbering+Client-Side Filter Bypass</p>
      </div>
    </div>
    <div class="lab-card">
      <div class="difficulty-badge"><i class="fas fa-skull"></i>Hard / Expert</div>
      <h1 class="lab-title">Prototype Pollution & DOM Clobbering</h1>
      <div class="lab-tags"><span class="tag">DOM Clobbering</span><span class="tag">Prototype Pollution</span><span
          class="tag">__proto__</span><span class="tag">constructor</span><span class="tag">Client-Side
          Filter</span><span class="tag">JSON Merge</span><span class="tag">Gadget Chains</span><span
          class="tag">jQuery</span></div>
      <p style="color: rgba(255,255,255,0.8); line-height: 1.8; margin-bottom: 30px;">This application merges user
        configuration with default settings. The merge function is vulnerable to Prototype Pollution. Combine this with
        DOM Clobbering to bypass the client-side filter and execute arbitrary code. </p>
      <div class="config-section">
        <h3><i class="fas fa-cogs"></i>User Configuration</h3>
        <p style="color: rgba(255,255,255,0.6); margin-bottom: 15px;">Enter JSON configuration (e.g., {
          "theme": "dark", "lang": "en"
          }

          ) </p><textarea class="config-input" id="configInput" rows="4"
          placeholder='{"theme": "dark"}'></textarea><button class="action-btn" onclick="applyConfig()"><i
            class="fas fa-save"></i>Apply Configuration </button>
        <div id="output"
          style="margin-top: 20px; padding: 20px; background: rgba(0,0,0,0.3); border-radius: 10px; font-family: monospace;">
          Waiting for configuration... </div>
      </div>
      <div class="hint-card">
        <h4 style="color: var(--neon-yellow); margin-bottom: 15px;"><i class="fas fa-book"></i>Learning Resources</h4>
        <ul style="color: rgba(255,255,255,0.8); line-height: 2; margin-left: 20px;">
          <li>Research: <code>__proto__</code>pollution in JavaScript</li>
          <li>Learn about DOM Clobbering with <code>&lt;
  img name="config"&gt;
  </code></li>
          <li>Study how <code>Object.assign</code>merges objects recursively</li>
          <li>Find the "gadget"that leads to code execution</li>
        </ul>
      </div>
    </div>
  </div>
  <form id="success-form" method="GET" style="display: none;"><input type="hidden" name="check" value="true"><input
      type="hidden" name="solved" value="0" id="solved-flag"></form>
  <script>
  // Default configuration

  var config = {

    theme: 'light',
    lang: 'en',
    features: {
      analytics: true,
      debug: false
    }
  }

  ;

  // VULNERABLE: Recursive merge without prototype check
  function merge(target, source) {
    for (let key in source) {
      if (typeof source[key] === 'object' && source[key] !== null) {
        if (!target[key]) target[key] = {}

        ;
        merge(target[key], source[key]);
      } else {
        target[key] = source[key];
      }
    }

    return target;
  }

  // Client-side "WAF" - blocks dangerous strings
  function waf(input) {
    var blocked = ['script',
      'alert',
      'eval',
      'Function',
      'constructor',
      'proto',
      'prototype'
    ];

    for (let word of blocked) {
      if (input.toLowerCase().includes(word)) {
        return false;
      }
    }

    return true;
  }

  function applyConfig() {
    var input = document.getElementById('configInput').value;

    if (!waf(input)) {
      document.getElementById('output').innerHTML =
        '<span style="color: #ff0040;">[WAF] Dangerous content detected!</span>';
      return;
    }

    try {
      var userConfig = JSON.parse(input);
      // VULNERABLE: Merge pollutes prototype!
      merge(config, userConfig);

      document.getElementById('output').innerHTML = '<span style="color: #00ff88;">Config applied!</span><br>' +
        '<pre>' + JSON.stringify(config, null, 2) + '</pre>';

      // Check if we can trigger the gadget
      checkGadget();
    } catch (e) {
      document.getElementById('output').innerHTML = '<span style="color: #ff6600;">Error: ' + e.message + '</span>';
    }
  }

  // Gadget that can be triggered via polluted prototype
  function checkGadget() {

    // This simulates a real gadget chain
    if (Object.prototype.polluted) {
      // In real scenario, this would lead to XSS
      console.log('Prototype polluted!');
    }
  }

  const originalAlert = window.alert;

  window.alert = function(msg) {
    if (msg == '1') {
      document.getElementById('solved-flag').value = '1';
      document.getElementById('success-form').submit();
    }

    return originalAlert.apply(this, arguments);
  }

  ;
  </script>
</body>

</html>