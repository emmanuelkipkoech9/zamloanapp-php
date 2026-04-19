<?php
$ADMIN_PASS = 'zamloans2024';
session_start();
if (isset($_POST['password'])) {
    if ($_POST['password'] === $ADMIN_PASS) $_SESSION['admin'] = true;
    else $error = 'Wrong password';
}
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }

if (!isset($_SESSION['admin'])) { ?>
<!DOCTYPE html><html><head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>ZamLoans Admin</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&display=swap" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:#0d3d22;min-height:100vh;display:flex;align-items:center;justify-content:center}
.box{background:white;border-radius:16px;padding:40px 36px;width:100%;max-width:360px;text-align:center}
.box h2{font-size:1.4rem;font-weight:700;margin-bottom:6px}.box p{color:#888;font-size:0.85rem;margin-bottom:28px}
input{width:100%;padding:12px 14px;border:1.5px solid #e0e0e0;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:0.92rem;outline:none;margin-bottom:14px}
input:focus{border-color:#1a6b3c}
button{width:100%;padding:13px;background:#1a6b3c;color:white;border:none;border-radius:10px;font-family:'DM Sans',sans-serif;font-size:0.95rem;font-weight:600;cursor:pointer}
.err{background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px;font-size:0.83rem;margin-bottom:14px}
</style></head><body>
<div class="box"><h2>ZamLoans Admin</h2><p>Enter password to view dashboard</p>
<?php if(isset($error)) echo "<div class='err'>$error</div>"; ?>
<form method="POST"><input type="password" name="password" placeholder="Password" autofocus/><button type="submit">Login</button></form>
</div></body></html>
<?php exit; }

// Load data
$data_dir = __DIR__ . '/data';
function rj($f){ if(!file_exists($f)) return []; return json_decode(file_get_contents($f),true)?:[]; }
$visits   = rj($data_dir.'/visits.json');
$payments = rj($data_dir.'/payments.json');
$clicks   = rj($data_dir.'/clicks.json');
$funnel   = rj($data_dir.'/funnel.json');

// Core stats
$total_visits   = count($visits);
$unique_ips     = count(array_unique(array_column($visits,'ip')));
$total_clicks   = count($clicks);
$total_attempts = count($payments);
$successful     = array_filter($payments, fn($p)=>$p['status']==='success');
$failed         = array_filter($payments, fn($p)=>$p['status']==='error');
$pending        = array_filter($payments, fn($p)=>$p['status']==='pending');
$total_fees     = array_sum(array_column($successful,'fee'));
$total_loans    = array_sum(array_column($successful,'amount'));
$conv_rate      = $unique_ips > 0 ? round((count($successful)/$unique_ips)*100,1) : 0;

// Today
$today = date('Y-m-d');
$today_visits   = count(array_filter($visits,   fn($v)=>str_starts_with($v['time'],$today)));
$today_success  = count(array_filter($payments, fn($p)=>str_starts_with($p['time'],$today)&&$p['status']==='success'));
$today_fees     = array_sum(array_column(array_filter($payments,fn($p)=>str_starts_with($p['time'],$today)&&$p['status']==='success'),'fee'));

// Traffic sources
$sources = array_count_values(array_column($visits,'source'));
arsort($sources);

// Device breakdown
$devices = array_count_values(array_column($visits,'device'));

// Most clicked loans
$click_amounts = array_count_values(array_map(fn($c)=>'ZMW '.number_format($c['amount']), $clicks));
arsort($click_amounts);
$top_loans = array_slice($click_amounts, 0, 5, true);

// Funnel
$f_visited  = count($funnel);
$f_clicked  = count(array_filter($funnel, fn($f)=>isset($f['clicked'])));
$f_form     = count(array_filter($funnel, fn($f)=>isset($f['form_submitted'])));
$f_paid     = count(array_filter($funnel, fn($f)=>isset($f['payment_attempted'])));
$f_success  = count(array_filter($funnel, fn($f)=>isset($f['payment_success'])));

// Daily visits chart (last 7 days)
$daily = [];
for($i=6;$i>=0;$i--){
    $d = date('Y-m-d', strtotime("-$i days"));
    $daily[$d] = ['date'=>date('D', strtotime($d)), 'visits'=>0, 'payments'=>0];
}
foreach($visits as $v){ $d=substr($v['time'],0,10); if(isset($daily[$d])) $daily[$d]['visits']++; }
foreach($payments as $p){ if($p['status']==='success'){ $d=substr($p['time'],0,10); if(isset($daily[$d])) $daily[$d]['payments']++; } }
$max_daily = max(1, max(array_column($daily,'visits')));

// Recent activity
$all_events = [];
foreach($visits   as $v) $all_events[] = ['type'=>'visit',   'time'=>$v['time'], 'detail'=>'Visitor from '.($v['source']??'Unknown').' · '.($v['device']??''), 'ip'=>$v['ip']];
foreach($clicks   as $c) $all_events[] = ['type'=>'click',   'time'=>$c['time'], 'detail'=>'Selected ZMW '.number_format($c['amount']).' loan', 'ip'=>$c['ip']];
foreach($payments as $p) $all_events[] = ['type'=>$p['status']==='success'?'success':($p['status']==='error'?'failed':'pending'),
    'time'=>$p['time'], 'detail'=>'Payment ZMW '.number_format($p['fee']).' · loan ZMW '.number_format($p['amount']).' · '.$p['phone'], 'ip'=>$p['ip']];
usort($all_events, fn($a,$b)=>strcmp($b['time'],$a['time']));
$recent = array_slice($all_events, 0, 30);
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>ZamLoans Admin</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;background:#f0f4f0;color:#1a1a1a}
.topbar{background:linear-gradient(135deg,#1a6b3c,#0d3d22);padding:14px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:99}
.topbar h1{color:white;font-size:1.1rem;font-weight:700}
.topbar-right{display:flex;align-items:center;gap:12px}
.topbar .live{background:rgba(255,255,255,0.15);color:white;font-size:0.75rem;padding:4px 10px;border-radius:20px;display:flex;align-items:center;gap:5px}
.dot{width:7px;height:7px;border-radius:50%;background:#4ade80;animation:pulse 1.5s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.4}}
.topbar a{color:rgba(255,255,255,0.75);font-size:0.8rem;text-decoration:none;background:rgba(255,255,255,0.15);padding:5px 12px;border-radius:20px}
.main{max-width:1140px;margin:0 auto;padding:24px 18px 60px}
.sec-title{font-size:0.72rem;font-weight:700;color:#888;letter-spacing:0.1em;text-transform:uppercase;margin:28px 0 12px}
.g4{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.g3{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.g2{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.card{background:white;border-radius:14px;padding:18px;box-shadow:0 1px 6px rgba(0,0,0,0.05)}
.card .lbl{font-size:0.75rem;color:#999;margin-bottom:6px}
.card .val{font-size:1.85rem;font-weight:700;color:#111;line-height:1}
.card .sub{font-size:0.73rem;color:#bbb;margin-top:5px}
.green{color:#1a6b3c!important}.amber{color:#d97706!important}.red{color:#dc2626!important}.blue{color:#2563eb!important}

/* funnel */
.funnel{display:flex;flex-direction:column;gap:6px}
.f-row{display:flex;align-items:center;gap:10px}
.f-label{font-size:0.8rem;color:#555;width:130px;flex-shrink:0}
.f-bar-wrap{flex:1;background:#f0f4f0;border-radius:20px;height:22px;overflow:hidden}
.f-bar{height:100%;border-radius:20px;background:linear-gradient(90deg,#1a6b3c,#2d9e5f);transition:width 0.6s}
.f-count{font-size:0.8rem;font-weight:600;color:#333;width:40px;text-align:right;flex-shrink:0}
.f-pct{font-size:0.72rem;color:#aaa;width:36px;flex-shrink:0}

/* bar chart */
.bar-chart{display:flex;align-items:flex-end;gap:8px;height:100px;padding-top:8px}
.bar-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:4px}
.bar{width:100%;border-radius:4px 4px 0 0;background:linear-gradient(180deg,#2d9e5f,#1a6b3c);min-height:2px;transition:height 0.5s}
.bar-lbl{font-size:0.68rem;color:#aaa}
.bar-val{font-size:0.68rem;color:#666;font-weight:600}

/* source pills */
.pills{display:flex;flex-wrap:wrap;gap:8px;margin-top:4px}
.pill{display:flex;align-items:center;gap:6px;background:#f4f6f4;border-radius:20px;padding:6px 12px;font-size:0.8rem}
.pill .pct{font-weight:700;color:#1a6b3c}
.pill .src{color:#555}

/* table */
.tcard{background:white;border-radius:14px;box-shadow:0 1px 6px rgba(0,0,0,0.05);overflow:hidden}
.thead{padding:14px 18px;font-size:0.88rem;font-weight:600;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center}
.thead span{font-size:0.75rem;color:#aaa;font-weight:400}
table{width:100%;border-collapse:collapse}
th{font-size:0.7rem;color:#aaa;font-weight:600;text-align:left;padding:9px 14px;background:#fafafa;border-bottom:1px solid #f0f0f0;text-transform:uppercase;letter-spacing:0.05em}
td{font-size:0.82rem;padding:10px 14px;border-bottom:1px solid #f8f8f8;color:#333}
tr:last-child td{border-bottom:none}
tr:hover td{background:#f9fdf9}
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:0.7rem;font-weight:600}
.badge.visit{background:#eff6ff;color:#1d4ed8}
.badge.click{background:#fef9e3;color:#92400e}
.badge.success{background:#d1fae5;color:#065f46}
.badge.failed{background:#fee2e2;color:#991b1b}
.badge.pending{background:#fef3c7;color:#92400e}
.empty{text-align:center;padding:40px;color:#ccc;font-size:0.85rem}
.refresh-bar{font-size:0.75rem;color:#aaa;text-align:right;margin-bottom:10px}

/* today highlight */
.today-banner{background:linear-gradient(135deg,#1a6b3c,#0d3d22);border-radius:14px;padding:18px 22px;color:white;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.today-banner .t-item{text-align:center}
.today-banner .t-val{font-size:1.6rem;font-weight:700}
.today-banner .t-lbl{font-size:0.75rem;opacity:0.75;margin-top:2px}

@media(max-width:720px){
  .g4{grid-template-columns:repeat(2,1fr)}
  .g3{grid-template-columns:repeat(2,1fr)}
  .g2{grid-template-columns:1fr}
  .card .val{font-size:1.4rem}
  .today-banner{flex-direction:column;text-align:center}
  th,td{padding:8px 10px;font-size:0.76rem}
}
</style></head><body>

<div class="topbar">
  <h1>🌿 ZamLoans Admin</h1>
  <div class="topbar-right">
    <div class="live"><span class="dot"></span> Live</div>
    <a href="?logout=1">Logout</a>
  </div>
</div>

<div class="main">
<div class="refresh-bar">Auto-refreshes every 60s &nbsp;·&nbsp; <?= date('D d M Y, H:i:s') ?> (GMT+3)</div>

<!-- TODAY -->
<div class="sec-title">Today</div>
<div class="today-banner">
  <div class="t-item"><div class="t-val"><?= $today_visits ?></div><div class="t-lbl">Visitors Today</div></div>
  <div class="t-item"><div class="t-val"><?= $today_success ?></div><div class="t-lbl">Payments Today</div></div>
  <div class="t-item"><div class="t-val">ZMW <?= number_format($today_fees) ?></div><div class="t-lbl">Fees Collected Today</div></div>
  <div class="t-item"><div class="t-val"><?= $conv_rate ?>%</div><div class="t-lbl">Conversion Rate</div></div>
</div>

<!-- OVERVIEW -->
<div class="sec-title">All Time</div>
<div class="g4">
  <div class="card"><div class="lbl">Total Visitors</div><div class="val"><?= number_format($total_visits) ?></div><div class="sub"><?= number_format($unique_ips) ?> unique IPs</div></div>
  <div class="card"><div class="lbl">Loan Selections</div><div class="val amber"><?= number_format($total_clicks) ?></div><div class="sub">Cards clicked</div></div>
  <div class="card"><div class="lbl">Payment Attempts</div><div class="val blue"><?= number_format($total_attempts) ?></div><div class="sub"><?= count($successful) ?> successful</div></div>
  <div class="card"><div class="lbl">Total Fees Earned</div><div class="val green">ZMW <?= number_format($total_fees) ?></div><div class="sub">ZMW <?= number_format($total_loans) ?> disbursed</div></div>
</div>

<!-- PAYMENT BREAKDOWN -->
<div class="sec-title">Payment Breakdown</div>
<div class="g3">
  <div class="card"><div class="lbl">✅ Successful</div><div class="val green"><?= count($successful) ?></div></div>
  <div class="card"><div class="lbl">❌ Failed</div><div class="val red"><?= count($failed) ?></div></div>
  <div class="card"><div class="lbl">⏳ Pending</div><div class="val amber"><?= count($pending) ?></div></div>
</div>

<!-- 7-DAY CHART + TRAFFIC SOURCES -->
<div class="sec-title">Last 7 Days & Traffic Sources</div>
<div class="g2">
  <div class="card">
    <div class="lbl" style="margin-bottom:12px">Daily Visitors</div>
    <div class="bar-chart">
      <?php foreach($daily as $d): $h = round(($d['visits']/$max_daily)*90); ?>
      <div class="bar-col">
        <div class="bar-val"><?= $d['visits'] ?></div>
        <div class="bar" style="height:<?= max(2,$h) ?>px"></div>
        <div class="bar-lbl"><?= $d['date'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card">
    <div class="lbl" style="margin-bottom:12px">Traffic Sources</div>
    <?php if(empty($sources)): ?>
      <div style="color:#ccc;font-size:0.85rem;text-align:center;padding:30px 0">No data yet</div>
    <?php else: ?>
    <div class="pills">
      <?php foreach($sources as $src=>$cnt):
        $pct = $total_visits > 0 ? round(($cnt/$total_visits)*100) : 0;
        $icons = ['TikTok'=>'🎵','Facebook'=>'📘','Instagram'=>'📸','WhatsApp'=>'💬','Google'=>'🔍','Direct'=>'🔗','Twitter/X'=>'🐦','YouTube'=>'▶️','Other'=>'🌐'];
        $icon = $icons[$src] ?? '🌐';
      ?>
      <div class="pill"><?= $icon ?> <span class="src"><?= htmlspecialchars($src) ?></span> <span class="pct"><?= $pct ?>%</span> <span style="color:#bbb;font-size:0.72rem">(<?= $cnt ?>)</span></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- FUNNEL + DEVICES -->
<div class="sec-title">Conversion Funnel & Devices</div>
<div class="g2">
  <div class="card">
    <div class="lbl" style="margin-bottom:14px">User Journey</div>
    <div class="funnel">
      <?php
      $steps = [['Visited Site',$f_visited],['Selected Loan',$f_clicked],['Submitted Form',$f_form],['Attempted Payment',$f_paid],['Payment Success',$f_success]];
      $max_f = max(1,$f_visited);
      foreach($steps as [$lbl,$cnt]):
        $pct = round(($cnt/$max_f)*100);
      ?>
      <div class="f-row">
        <div class="f-label"><?= $lbl ?></div>
        <div class="f-bar-wrap"><div class="f-bar" style="width:<?= $pct ?>%"></div></div>
        <div class="f-count"><?= $cnt ?></div>
        <div class="f-pct"><?= $pct ?>%</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card">
    <div class="lbl" style="margin-bottom:14px">Device Types</div>
    <?php if(empty($devices)): ?>
      <div style="color:#ccc;font-size:0.85rem;text-align:center;padding:30px 0">No data yet</div>
    <?php else: ?>
    <div class="funnel">
      <?php $max_d = max(1,max($devices)); foreach($devices as $dev=>$cnt):
        $pct = round(($cnt/$max_d)*100);
        $icons = ['Mobile'=>'📱','Desktop'=>'💻','Tablet'=>'📟'];
      ?>
      <div class="f-row">
        <div class="f-label"><?= ($icons[$dev]??'📱').' '.$dev ?></div>
        <div class="f-bar-wrap"><div class="f-bar" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#d97706,#f59e0b)"></div></div>
        <div class="f-count"><?= $cnt ?></div>
        <div class="f-pct"><?= $pct ?>%</div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- TOP LOANS -->
<div class="sec-title">Most Selected Loans</div>
<div class="card">
  <?php if(empty($top_loans)): ?>
    <div class="empty">No loan selections yet</div>
  <?php else: ?>
  <div class="funnel">
    <?php $max_l = max(1,max($top_loans)); foreach($top_loans as $loan=>$cnt):
      $pct = round(($cnt/$max_l)*100);
    ?>
    <div class="f-row">
      <div class="f-label" style="width:150px"><?= htmlspecialchars($loan) ?></div>
      <div class="f-bar-wrap"><div class="f-bar" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#2563eb,#3b82f6)"></div></div>
      <div class="f-count"><?= $cnt ?></div>
      <div class="f-pct"><?= $pct ?>%</div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- RECENT ACTIVITY -->
<div class="sec-title">Recent Activity</div>
<div class="tcard">
  <div class="thead">Last 30 Events <span>Updates every 60s</span></div>
  <?php if(empty($recent)): ?>
    <div class="empty">No activity yet — share your link!</div>
  <?php else: ?>
  <table>
    <thead><tr><th>Time</th><th>Event</th><th>Detail</th><th>IP</th></tr></thead>
    <tbody>
    <?php foreach($recent as $r): ?>
    <tr>
      <td style="color:#aaa;font-size:0.76rem;white-space:nowrap"><?= htmlspecialchars($r['time']) ?></td>
      <td><span class="badge <?= $r['type'] ?>"><?= ucfirst($r['type']) ?></span></td>
      <td><?= htmlspecialchars($r['detail']) ?></td>
      <td style="color:#ddd;font-size:0.72rem;font-family:monospace"><?= htmlspecialchars(substr($r['ip'],0,10)).'...' ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- FULL PAYMENT LOG -->
<div class="sec-title">All Payments</div>
<div class="tcard">
  <div class="thead">Payment Log <span><?= count($payments) ?> total</span></div>
  <?php if(empty($payments)): ?>
    <div class="empty">No payments yet</div>
  <?php else: ?>
  <table>
    <thead><tr><th>Time</th><th>Loan</th><th>Fee</th><th>Phone</th><th>Source</th><th>Device</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach(array_reverse($payments) as $p): ?>
    <tr>
      <td style="white-space:nowrap;color:#aaa;font-size:0.76rem"><?= htmlspecialchars($p['time']) ?></td>
      <td>ZMW <?= number_format($p['amount']) ?></td>
      <td style="font-weight:600;color:#1a6b3c">ZMW <?= number_format($p['fee']) ?></td>
      <td style="font-family:monospace;font-size:0.78rem"><?= htmlspecialchars($p['phone']??'—') ?></td>
      <td><?= htmlspecialchars($p['source']??'—') ?></td>
      <td><?= htmlspecialchars($p['device']??'—') ?></td>
      <td><span class="badge <?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

</div>
<script>setTimeout(()=>location.reload(),60000);</script>
</body></html>
