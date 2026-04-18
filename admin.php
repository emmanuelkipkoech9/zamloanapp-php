<?php
// Simple password protection
$ADMIN_PASS = 'zamloans2024';
session_start();

if (isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASS) {
        $_SESSION['admin'] = true;
    } else {
        $error = 'Wrong password';
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!isset($_SESSION['admin'])) { ?>
<!DOCTYPE html>
<html><head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>ZamLoans Admin</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&display=swap" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:#0d3d22;min-height:100vh;display:flex;align-items:center;justify-content:center}
.login{background:white;border-radius:16px;padding:40px 36px;width:100%;max-width:360px;text-align:center}
.login h2{font-size:1.4rem;font-weight:700;margin-bottom:6px}
.login p{color:#888;font-size:0.85rem;margin-bottom:28px}
.login input{width:100%;padding:12px 14px;border:1.5px solid #e0e0e0;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:0.92rem;outline:none;margin-bottom:14px}
.login input:focus{border-color:#1a6b3c}
.login button{width:100%;padding:13px;background:#1a6b3c;color:white;border:none;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:0.95rem;font-weight:600;cursor:pointer}
.error{background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px;font-size:0.83rem;margin-bottom:14px}
</style>
</head><body>
<div class="login">
  <h2>ZamLoans Admin</h2>
  <p>Enter password to view dashboard</p>
  <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
  <form method="POST">
    <input type="password" name="password" placeholder="Password" autofocus/>
    <button type="submit">Login</button>
  </form>
</div>
</body></html>
<?php exit; } ?>
<?php
// Load data
$data_dir = __DIR__ . '/data';
function read_json($f){ if(!file_exists($f)) return []; return json_decode(file_get_contents($f),true)?:[]; }

$visits   = read_json($data_dir.'/visits.json');
$payments = read_json($data_dir.'/payments.json');
$clicks   = read_json($data_dir.'/clicks.json');

// Stats
$total_visits   = count($visits);
$unique_ips     = count(array_unique(array_column($visits, 'ip')));
$total_clicks   = count($clicks);
$total_attempts = count($payments);
$successful     = count(array_filter($payments, fn($p) => $p['status']==='success'));
$failed         = count(array_filter($payments, fn($p) => $p['status']==='error'));
$pending        = count(array_filter($payments, fn($p) => $p['status']==='pending'));
$total_fees     = array_sum(array_column(array_filter($payments, fn($p)=>$p['status']==='success'), 'fee'));
$total_loans    = array_sum(array_column(array_filter($payments, fn($p)=>$p['status']==='success'), 'amount'));

// Most clicked loan
$click_amounts = array_count_values(array_column($clicks, 'amount'));
arsort($click_amounts);
$top_loan = key($click_amounts) ?? 'N/A';

// Today stats
$today = date('Y-m-d');
$today_visits   = count(array_filter($visits,   fn($v) => str_starts_with($v['time'], $today)));
$today_payments = count(array_filter($payments, fn($p) => str_starts_with($p['time'], $today) && $p['status']==='success'));

// Recent activity (last 20)
$recent = array_reverse(array_slice(array_merge(
    array_map(fn($v) => ['type'=>'visit',   'time'=>$v['time'], 'detail'=>'New visitor', 'ip'=>$v['ip']], $visits),
    array_map(fn($c) => ['type'=>'click',   'time'=>$c['time'], 'detail'=>'Selected ZMW '.number_format($c['amount']), 'ip'=>$c['ip']], $clicks),
    array_map(fn($p) => ['type'=>$p['status']==='success'?'success':($p['status']==='error'?'failed':'payment'),
        'time'=>$p['time'], 'detail'=>'Payment ZMW '.number_format($p['fee']).' for loan ZMW '.number_format($p['amount']), 'ip'=>$p['ip']], $payments)
), -40));
usort($recent, fn($a,$b) => strcmp($b['time'], $a['time']));
$recent = array_slice($recent, 0, 25);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>ZamLoans Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:#f4f6f4;color:#1a1a1a}
.topbar{background:linear-gradient(135deg,#1a6b3c,#0d3d22);padding:16px 28px;display:flex;align-items:center;justify-content:space-between}
.topbar h1{color:white;font-size:1.2rem;font-weight:700}
.topbar a{color:rgba(255,255,255,0.75);font-size:0.82rem;text-decoration:none;background:rgba(255,255,255,0.15);padding:6px 14px;border-radius:20px}
.topbar a:hover{background:rgba(255,255,255,0.25)}
.main{max-width:1100px;margin:0 auto;padding:28px 20px}
.section-title{font-size:0.75rem;font-weight:600;color:#888;letter-spacing:0.08em;text-transform:uppercase;margin:28px 0 14px}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
.stat-card{background:white;border-radius:14px;padding:20px;box-shadow:0 1px 8px rgba(0,0,0,0.05)}
.stat-card .s-label{font-size:0.78rem;color:#888;margin-bottom:8px}
.stat-card .s-value{font-size:1.9rem;font-weight:700;color:#111}
.stat-card .s-value.green{color:#1a6b3c}
.stat-card .s-value.amber{color:#d97706}
.stat-card .s-value.red{color:#dc2626}
.stat-card .s-sub{font-size:0.75rem;color:#aaa;margin-top:4px}
.table-card{background:white;border-radius:14px;padding:0;box-shadow:0 1px 8px rgba(0,0,0,0.05);overflow:hidden}
.table-card .tc-head{padding:16px 20px;border-bottom:1px solid #f0f0f0;font-size:0.9rem;font-weight:600}
table{width:100%;border-collapse:collapse}
table th{font-size:0.72rem;color:#999;font-weight:600;text-align:left;padding:10px 16px;background:#fafafa;border-bottom:1px solid #f0f0f0;text-transform:uppercase;letter-spacing:0.05em}
table td{font-size:0.83rem;padding:11px 16px;border-bottom:1px solid #f8f8f8;color:#333}
table tr:last-child td{border-bottom:none}
table tr:hover td{background:#fafff9}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:0.72rem;font-weight:600}
.badge.visit{background:#eff6ff;color:#1d4ed8}
.badge.click{background:#fef9e3;color:#92400e}
.badge.payment{background:#f0fdf4;color:#166534}
.badge.success{background:#d1fae5;color:#065f46}
.badge.failed{background:#fee2e2;color:#991b1b}
.badge.pending{background:#fef3c7;color:#92400e}
.refresh{font-size:0.8rem;color:#888;text-align:right;margin-bottom:8px}
.empty{text-align:center;padding:40px;color:#bbb;font-size:0.88rem}
@media(max-width:700px){
  .grid-4{grid-template-columns:repeat(2,1fr)}
  .grid-2{grid-template-columns:1fr}
  .stat-card .s-value{font-size:1.5rem}
  table th, table td{padding:9px 10px;font-size:0.78rem}
}
</style>
</head>
<body>
<div class="topbar">
  <h1>🌿 ZamLoans Dashboard</h1>
  <a href="?logout=1">Logout</a>
</div>

<div class="main">
  <div class="refresh">Auto-refreshes every 60s &nbsp;|&nbsp; Last updated: <?= date('H:i:s') ?></div>

  <div class="section-title">Overview</div>
  <div class="grid-4">
    <div class="stat-card">
      <div class="s-label">Total Visitors</div>
      <div class="s-value"><?= number_format($total_visits) ?></div>
      <div class="s-sub"><?= $today_visits ?> today</div>
    </div>
    <div class="stat-card">
      <div class="s-label">Unique Visitors</div>
      <div class="s-value"><?= number_format($unique_ips) ?></div>
    </div>
    <div class="stat-card">
      <div class="s-label">Loan Selections</div>
      <div class="s-value amber"><?= number_format($total_clicks) ?></div>
      <div class="s-sub">Most picked: ZMW <?= number_format($top_loan) ?></div>
    </div>
    <div class="stat-card">
      <div class="s-label">Payment Attempts</div>
      <div class="s-value"><?= number_format($total_attempts) ?></div>
      <div class="s-sub"><?= $today_payments ?> successful today</div>
    </div>
  </div>

  <div class="section-title">Revenue</div>
  <div class="grid-4">
    <div class="stat-card">
      <div class="s-label">Fees Collected</div>
      <div class="s-value green">ZMW <?= number_format($total_fees) ?></div>
      <div class="s-sub">From successful payments</div>
    </div>
    <div class="stat-card">
      <div class="s-label">Loans Disbursed Value</div>
      <div class="s-value green">ZMW <?= number_format($total_loans) ?></div>
    </div>
    <div class="stat-card">
      <div class="s-label">Successful Payments</div>
      <div class="s-value green"><?= $successful ?></div>
    </div>
    <div class="stat-card">
      <div class="s-label">Failed / Pending</div>
      <div class="s-value red"><?= $failed ?></div>
      <div class="s-sub"><?= $pending ?> pending</div>
    </div>
  </div>

  <div class="section-title">Recent Activity</div>
  <div class="table-card">
    <div class="tc-head">Last 25 Events</div>
    <?php if(empty($recent)): ?>
      <div class="empty">No activity yet — share your link to get started!</div>
    <?php else: ?>
    <table>
      <thead><tr>
        <th>Time</th><th>Event</th><th>Detail</th><th>IP</th>
      </tr></thead>
      <tbody>
      <?php foreach($recent as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['time']) ?></td>
          <td><span class="badge <?= $r['type'] ?>"><?= ucfirst($r['type']) ?></span></td>
          <td><?= htmlspecialchars($r['detail']) ?></td>
          <td style="color:#aaa;font-size:0.76rem"><?= htmlspecialchars(substr($r['ip'],0,12)).'...' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <div class="section-title">Payment Log</div>
  <div class="table-card">
    <div class="tc-head">All Payments</div>
    <?php if(empty($payments)): ?>
      <div class="empty">No payments yet</div>
    <?php else: ?>
    <table>
      <thead><tr>
        <th>Time</th><th>Loan</th><th>Fee</th><th>Phone</th><th>Status</th>
      </tr></thead>
      <tbody>
      <?php foreach(array_reverse($payments) as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['time']) ?></td>
          <td>ZMW <?= number_format($p['amount']) ?></td>
          <td>ZMW <?= number_format($p['fee']) ?></td>
          <td style="font-family:monospace"><?= htmlspecialchars($p['phone']) ?></td>
          <td><span class="badge <?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

</div>
<script>setTimeout(()=>location.reload(), 60000);</script>
</body>
</html>
