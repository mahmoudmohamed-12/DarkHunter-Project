<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/includes/score_manager.php'); 
session_start();

if (!isset($_SESSION['dom_med_attempts'])) {
    $_SESSION['dom_med_attempts'] = 0;
}

if (isset($_GET['check']) && $_GET['check'] === 'true') {
    $isSolved = isset($_GET['solved']) && $_GET['solved'] === '1';
    if ($isSolved && isset($_SESSION['user_id'])) {
        $success = solveLab($pdo, 6);
        
        if ($success) {
            $success_msg = "🎉 DOM XSS Medium Completed! +50 pts";
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
  <title>Eval Sink - DOM XSS Medium</title>
  <link
    href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="css/eval-sink-case-3.css">



</head>

<body><?php include_once __DIR__ . '/../../../includes/navbar.php';
  ?><div class="container"><a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i>Back</a>
    <div class="lab-header">
      <div class="difficulty-badge"><i class="fas fa-fire"></i>Medium</div>
      <h1 class="lab-title">Eval() Calculator Sink</h1>
      <div class="lab-tags"><span class="tag">DOM XSS</span><span class="tag">eval()</span><span class="tag">JavaScript
          Injection</span><span class="tag">Code Execution</span><span class="tag">Expression Context</span></div>
      <p>This calculator uses eval() to compute results. Can you inject JavaScript code instead of numbers?</p>
    </div>
    <div class="challenge-box">
      <div class="calculator">
        <div class="calc-display" id="display">0</div>
        <div class="calc-buttons"><button class="calc-btn" onclick="append('7')">7</button><button class="calc-btn"
            onclick="append('8')">8</button><button class="calc-btn" onclick="append('9')">9</button><button
            class="calc-btn" onclick="append('/')">/</button><button class="calc-btn"
            onclick="append('4')">4</button><button class="calc-btn" onclick="append('5')">5</button><button
            class="calc-btn" onclick="append('6')">6</button><button class="calc-btn"
            onclick="append('*')">*</button><button class="calc-btn" onclick="append('1')">1</button><button
            class="calc-btn" onclick="append('2')">2</button><button class="calc-btn"
            onclick="append('3')">3</button><button class="calc-btn" onclick="append('-')">-</button><button
            class="calc-btn" onclick="append('0')">0</button><button class="calc-btn"
            onclick="append('.')">.</button><button class="calc-btn" onclick="calculate()">=</button><button
            class="calc-btn" onclick="append('+')">+</button><button class="calc-btn" onclick="clearDisplay()"
            style="grid-column: span 4; background: var(--neon-red);">C</button></div>
      </div>
      <div class="hint-box"><strong><i class="fas fa-lightbulb"></i>Hint:</strong>The calculator uses
        <code>eval(display.value)</code>. What if you enter JavaScript instead of math? Try: <code>alert(1)</code>or
        <code>1+1; alert(1)</code>
      </div>
    </div>
  </div>
  <form id="success-form" method="GET" style="display: none;"><input type="hidden" name="check" value="true"><input
      type="hidden" name="solved" value="0" id="solved-flag"></form>
  <script>
  let expression = '';

  function append(char) {
    expression += char;
    document.getElementById('display').textContent = expression;
  }

  function clearDisplay() {
    expression = '';
    document.getElementById('display').textContent = '0';
  }

  function calculate() {
    try {
      // VULNERABLE: Using eval on user input!
      var result = eval(expression);
      document.getElementById('display').textContent = result;
    } catch (e) {
      document.getElementById('display').textContent = 'Error';
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