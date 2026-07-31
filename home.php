<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('UTC');

// ==================== NEW WALLET LOGIC ====================
$profit_multiplier = isset($_REQUEST['profit_multiplier']) && is_numeric($_REQUEST['profit_multiplier'])
    ? max(1.01, min(10.0, (float)$_REQUEST['profit_multiplier'])) : 1.50;

$hard_stop = isset($_REQUEST['hard_stop']) && $_REQUEST['hard_stop'] == '1';

function performSecureCashOut(array $state, float $multiplier = 1.5): array {
    $initialSeed = 10000.0;
    $currentEquity = (float)($state['equity_value'] ?? 0);
    $currentCash = (float)($state['cash_left'] ?? 0);
    $assetValue = (float)($state['holding_value'] ?? 0);

    $profit = max(0.0, $currentEquity - $initialSeed);
    if ($profit <= 50) {
        return ['success' => false, 'message' => 'No sufficient profit above initial seed.'];
    }

    $sellAmount = min($assetValue, $profit);
    $fees = $sellAmount * 0.0025;
    $slippage = $sellAmount * 0.003;
    $net = $sellAmount - $fees - $slippage;

    $state['cash_left'] = $currentCash + $net;
    $state['holding_value'] = $assetValue - $sellAmount;

    return [
        'success' => true,
        'net_cashed' => round($net, 2),
        'new_cash' => round($state['cash_left'], 2),
        'message' => "Cashed out $$net (fees & slippage deducted)"
    ];
}

function isTradingAllowed(): bool {
    global $hard_stop;
    return !$hard_stop;
}

// ==================== ORIGINAL CNGN CLASS ====================
include 'core_cngn.php';

include 'dashboard.php';

// Add this control panel
echo '
<div style="background:#1e2937; padding:15px; margin:10px 0; border-radius:8px; color:white;">
    <strong>Profit Multiplier:</strong> 
    <input type="number" name="profit_multiplier" value="' . htmlspecialchars($profit_multiplier) . '" step="0.1" min="1.0" style="width:80px;">
    
    <button onclick="toggleHardStop()" id="hardStopBtn" style="margin-left:20px;padding:10px 20px;font-weight:bold;background:' 
    . ($hard_stop ? '#22c55e' : '#ef4444') . ';color:white;">
        ' . ($hard_stop ? 'CONTINUE TRADING' : 'HARD STOP') . '
    </button>
    
    <button onclick="document.getElementById(\'cashoutForm\').submit()" style="margin-left:10px;">Cash Out Profits</button>
</div>
<form id="cashoutForm" method="post" style="display:none;"><input type="hidden" name="cash_out" value="1"></form>
';

// Rest of your original code...

// Example: Before any trade
if (!isTradingAllowed()) {
    $executionAction = 'NO TRADE';
}

// At the end of the file, before </body>, add this JS:
?>
<script>
function toggleHardStop() {
    const btn = document.getElementById('hardStopBtn');
    const isStop = btn.textContent.includes('HARD STOP');
    fetch(location.href, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'hard_stop=' + (isStop ? '1' : '0')
    }).then(() => location.reload());
}
</script>
</body>
</html>