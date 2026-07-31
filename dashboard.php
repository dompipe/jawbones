<?php
// dashboard.php - Main page with UI updates

include 'wallet_logic.php';

// Add to form / controls
echo '
<div style="margin:15px 0; padding:10px; background:#1e2937; border-radius:8px;">
    <label>Profit Target Multiplier: </label>
    <input type="number" name="profit_multiplier" value="' . $profit_multiplier . '" step="0.1" min="1.0" style="width:80px;">
    
    <button onclick="toggleHardStop()" id="hardStopBtn" style="margin-left:20px; padding:10px 20px; font-weight:bold; background:#ef4444; color:white;">
        ' . ($hard_stop ? 'CONTINUE TRADING' : 'HARD STOP') . '
    </button>
</div>
';

?>

<script>
// Hard Stop Toggle
function toggleHardStop() {
    const btn = document.getElementById('hardStopBtn');
    const current = btn.textContent.trim() === 'HARD STOP';
    
    fetch(location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'hard_stop=' + (current ? '1' : '0')
    }).then(() => location.reload());
}
</script>