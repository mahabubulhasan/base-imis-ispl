<?php

// ─── Command Execution ───────────────────────────────────────────────────────

$output   = '';
$error    = '';
$command  = '';
$executed = false;

function is_allowed_command(string $cmd): bool
{
    $cmd = trim($cmd);

    // Allow: git pull (optionally with remote/branch args)
    if (preg_match('/^git\s+pull(\s+[\w\.\-\/]+)*\s*$/', $cmd)) {
        return true;
    }

    // Allow: php artisan <anything> — but NOT php artisan serve
    if (preg_match('/^php\s+artisan\s+(?!serve(\s|$))[\w\:\-]+(\s+.*)?$/i', $cmd)) {
        return true;
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $command  = trim($_POST['command'] ?? '');
    $executed = true;

    if ($command === '') {
        $error = 'Please enter a command.';
    } elseif (!is_allowed_command($command)) {
        $error = 'Command not allowed. Only <code>git pull</code> and <code>php artisan &lt;command&gt;</code> (except <code>serve</code>) are permitted.';
    } else {
        // Change to Laravel root (one level up from public/)
        $laravelRoot = dirname(__DIR__);
        chdir($laravelRoot);

        // Run the command, merge stderr into stdout
        $escaped = escapeshellcmd($command);
        $output  = shell_exec($escaped . ' 2>&1');
        if ($output === null) {
            $error = 'Command execution failed or returned no output.';
        }
    }
}

// ─── HTML ────────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Deploy Console</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Syne:wght@700;800&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:        #0d0f14;
    --panel:     #13161e;
    --border:    #1f2330;
    --accent:    #00e5a0;
    --accent2:   #0099ff;
    --danger:    #ff4d6d;
    --text:      #c8d0e0;
    --text-dim:  #556070;
    --mono:      'JetBrains Mono', monospace;
    --sans:      'Syne', sans-serif;
  }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--mono);
    min-height: 100vh;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 48px 16px;
  }

  /* subtle grid background */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
      linear-gradient(rgba(0,229,160,.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,229,160,.03) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
    z-index: 0;
  }

  .wrap {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 780px;
  }

  /* ── Header ── */
  header {
    margin-bottom: 36px;
  }
  .badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--accent);
    border: 1px solid var(--accent);
    padding: 3px 10px;
    border-radius: 2px;
    margin-bottom: 12px;
    opacity: .8;
  }
  h1 {
    font-family: var(--sans);
    font-size: clamp(28px, 5vw, 42px);
    font-weight: 800;
    letter-spacing: -.02em;
    line-height: 1.1;
    color: #fff;
  }
  h1 span { color: var(--accent); }
  .subtitle {
    margin-top: 8px;
    font-size: 12px;
    color: var(--text-dim);
    letter-spacing: .04em;
  }

  /* ── Card ── */
  .card {
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
  }

  .card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    background: rgba(255,255,255,.02);
  }
  .dot { width: 10px; height: 10px; border-radius: 50%; }
  .dot-r { background: #ff5f57; }
  .dot-y { background: #febc2e; }
  .dot-g { background: #28c840; }
  .card-title {
    margin-left: 6px;
    font-size: 11px;
    color: var(--text-dim);
    letter-spacing: .1em;
  }

  .card-body { padding: 24px; }

  /* ── Input group ── */
  .input-group {
    display: flex;
    gap: 10px;
  }

  .cmd-wrap {
    flex: 1;
    position: relative;
  }
  .prompt {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--accent);
    font-size: 14px;
    pointer-events: none;
    user-select: none;
  }
  input[type="text"] {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 13px 14px 13px 30px;
    font-family: var(--mono);
    font-size: 14px;
    color: #fff;
    outline: none;
    transition: border-color .2s, box-shadow .2s;
    caret-color: var(--accent);
  }
  input[type="text"]:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(0,229,160,.08);
  }
  input[type="text"]::placeholder { color: var(--text-dim); }

  button[type="submit"] {
    padding: 13px 24px;
    background: var(--accent);
    color: #0d0f14;
    border: none;
    border-radius: 6px;
    font-family: var(--mono);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .06em;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s, transform .1s, box-shadow .15s;
  }
  button[type="submit"]:hover {
    background: #00ffb3;
    box-shadow: 0 0 18px rgba(0,229,160,.35);
  }
  button[type="submit"]:active { transform: scale(.97); }

  /* ── Hints ── */
  .hints {
    margin-top: 12px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }
  .hint-chip {
    font-size: 11px;
    color: var(--text-dim);
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 3px 10px;
    cursor: pointer;
    transition: color .15s, border-color .15s;
    user-select: none;
  }
  .hint-chip:hover { color: var(--accent); border-color: var(--accent); }

  /* ── Divider ── */
  .divider {
    height: 1px;
    background: var(--border);
    margin: 24px 0;
  }

  /* ── Output ── */
  .output-label {
    font-size: 10px;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--text-dim);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .output-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
  }

  .output-box {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 18px;
    font-size: 12.5px;
    line-height: 1.7;
    white-space: pre-wrap;
    word-break: break-all;
    max-height: 480px;
    overflow-y: auto;
    color: #a8ffcc;
  }

  .output-box::-webkit-scrollbar { width: 6px; }
  .output-box::-webkit-scrollbar-track { background: transparent; }
  .output-box::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

  .output-empty {
    color: var(--text-dim);
    font-style: italic;
    font-size: 12px;
  }

  /* ── Error ── */
  .alert-error {
    background: rgba(255,77,109,.07);
    border: 1px solid rgba(255,77,109,.25);
    border-radius: 6px;
    padding: 13px 16px;
    font-size: 13px;
    color: var(--danger);
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-top: 20px;
  }
  .alert-error svg { flex-shrink: 0; margin-top: 1px; }

  /* ── Executed cmd echo ── */
  .cmd-echo {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-size: 11px;
    color: var(--text-dim);
  }
  .cmd-echo code {
    color: var(--accent2);
    font-size: 12px;
  }

  /* ── Footer ── */
  footer {
    margin-top: 28px;
    font-size: 11px;
    color: var(--text-dim);
    text-align: center;
    letter-spacing: .04em;
  }
</style>
</head>
<body>
<div class="wrap">

  <header>
    <div class="badge">&#x25CF; Deploy Console</div>
    <h1>Run <span>Commands</span></h1>
    <p class="subtitle">Allowed: &nbsp;<code>git pull</code> &nbsp;&bull;&nbsp; <code>php artisan &lt;cmd&gt;</code> (except serve)</p>
  </header>

  <div class="card">
    <div class="card-header">
      <span class="dot dot-r"></span>
      <span class="dot dot-y"></span>
      <span class="dot dot-g"></span>
      <span class="card-title">terminal &mdash; bash</span>
    </div>

    <div class="card-body">
      <form method="POST" autocomplete="off">
        <div class="input-group">
          <div class="cmd-wrap">
            <span class="prompt">$</span>
            <input
              type="text"
              name="command"
              id="command"
              placeholder="git pull  /  php artisan migrate"
              value="<?= htmlspecialchars($command) ?>"
              spellcheck="false"
              autofocus
            >
          </div>
          <button type="submit">Run &rsaquo;</button>
        </div>

        <div class="hints">
          <span class="hint-chip" onclick="fill('git pull')">git pull</span>
          <span class="hint-chip" onclick="fill('php artisan migrate')">artisan migrate</span>
          <span class="hint-chip" onclick="fill('php artisan migrate:rollback')">migrate:rollback</span>
          <span class="hint-chip" onclick="fill('php artisan cache:clear')">cache:clear</span>
          <span class="hint-chip" onclick="fill('php artisan config:cache')">config:cache</span>
          <span class="hint-chip" onclick="fill('php artisan queue:restart')">queue:restart</span>
          <span class="hint-chip" onclick="fill('php artisan optimize')">optimize</span>
          <span class="hint-chip" onclick="fill('php artisan route:clear')">route:clear</span>
        </div>
      </form>

      <?php if ($executed): ?>
        <div class="divider"></div>

        <?php if ($error): ?>
          <div class="alert-error">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span><?= $error ?></span>
          </div>

        <?php else: ?>
          <div class="output-label">output</div>

          <?php if ($command): ?>
            <div class="cmd-echo">
              <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
              Executed: <code><?= htmlspecialchars($command) ?></code>
            </div>
          <?php endif; ?>

          <div class="output-box"><?php
            if ($output !== null && $output !== '') {
                echo htmlspecialchars($output);
            } else {
                echo '<span class="output-empty">(no output)</span>';
            }
          ?></div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <footer>Deploy Console &bull; Allowed commands only &bull; <?= date('Y') ?></footer>
</div>

<script>
function fill(cmd) {
  document.getElementById('command').value = cmd;
  document.getElementById('command').focus();
}
</script>
</body>
</html>