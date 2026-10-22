<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect('index.php');

$storeTypes = [
    'general'      => 'General store / sari-sari store',
    'clothing'     => 'Clothing / apparel',
    'food_grocery' => 'Food / grocery',
    'restaurant'   => 'Restaurant / food shop',
    'electronics'  => 'Electronics',
    'beauty'       => 'Beauty / personal care',
    'hardware'     => 'Hardware / home supplies',
    'other'        => 'Other'
];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim((string)($_POST['name'] ?? ''));
    $store     = trim((string)($_POST['store_name'] ?? ''));
    $storeType = (string)($_POST['store_type'] ?? '');
    $email     = strtolower(trim((string)($_POST['email'] ?? '')));
    $password  = (string)($_POST['password'] ?? '');

    if ($name===''||$store===''||!array_key_exists($storeType,$storeTypes)||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<8) {
        $error = 'Enter your name, store name, store type, a valid email, and a password of at least 8 characters.';
    } else {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO stores (name, store_type) VALUES (?, ?)')->execute([$store, $storeType]);
            $storeId = (int)$pdo->lastInsertId();
            $pdo->prepare("INSERT INTO users (store_id,name,email,password_hash,role) VALUES (?,?,?,?,'owner')")
                ->execute([$storeId,$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
            $userId = (int)$pdo->lastInsertId();
            $pdo->commit();
            session_regenerate_id(true);
            $_SESSION['user'] = ['id'=>$userId,'store_id'=>$storeId,'name'=>$name,'email'=>$email,'role'=>'owner','store_name'=>$store,'store_type'=>$storeType];
            redirect('index.php');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e->getCode()==='23000' ? 'That email address already has an account.' : 'Could not create the account. Please check your database setup.';
        }
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create account · TindaHub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800;900&display=swap">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:Manrope,system-ui,sans-serif}

body{
  min-height:100vh;
  display:flex;
  align-items:stretch;
  background:#f2f2f2;
}

/* ─── LEFT DARK PANEL ─── */
.al{
  flex:1.05;
  position:relative;
  overflow:hidden;
  background:#060c09;
  display:flex;
  flex-direction:column;
  padding:28px 32px;
}
.al-bg{
  position:absolute;inset:0;
  background:
    radial-gradient(ellipse 80% 60% at 15% 10%, rgba(16,185,129,.14), transparent 55%),
    radial-gradient(ellipse 60% 50% at 90% 85%, rgba(5,150,105,.18), transparent 55%),
    radial-gradient(ellipse 50% 40% at 50% 50%, rgba(255,90,20,.06), transparent 60%),
    linear-gradient(170deg,#040a07 0%,#08130d 45%,#050a07 100%);
}
.al-grid{
  position:absolute;inset:0;pointer-events:none;
  background-image:
    linear-gradient(rgba(52,211,153,.04) 1px,transparent 1px),
    linear-gradient(90deg,rgba(52,211,153,.04) 1px,transparent 1px);
  background-size:52px 52px;
  animation:gridDrift 40s linear infinite;
}
@keyframes gridDrift{to{background-position:52px 52px}}
.al-aurora{
  position:absolute;top:8%;left:-10%;right:-10%;height:35%;
  background:
    radial-gradient(ellipse 70% 100% at 30% 50%, rgba(16,185,129,.18), transparent 70%),
    radial-gradient(ellipse 50% 100% at 75% 50%, rgba(52,211,153,.12), transparent 70%);
  filter:blur(28px);
  animation:auroraPulse 8s ease-in-out infinite alternate;
  pointer-events:none;
}
@keyframes auroraPulse{
  0%{opacity:.5;transform:scaleX(1) translateY(0)}
  50%{opacity:.9;transform:scaleX(1.06) translateY(8px)}
  100%{opacity:.6;transform:scaleX(.96) translateY(-4px)}
}
.al-shoot{
  position:absolute;top:0;left:0;width:120px;height:1.5px;
  background:linear-gradient(90deg,rgba(255,255,255,.9),transparent);
  border-radius:1px;opacity:0;pointer-events:none;
}
.al-shoot1{animation:shoot1 6s ease-in-out 1s infinite}
.al-shoot2{animation:shoot2 9s ease-in-out 4.5s infinite}
@keyframes shoot1{
  0%,85%,100%{opacity:0;transform:translate(5vw,8vh) rotate(-28deg)}
  87%{opacity:1}
  95%{opacity:0;transform:translate(30vw,22vh) rotate(-28deg)}
}
@keyframes shoot2{
  0%,80%,100%{opacity:0;transform:translate(50vw,4vh) rotate(-22deg)}
  82%{opacity:.8}
  92%{opacity:0;transform:translate(72vw,16vh) rotate(-22deg)}
}

/* big framed card */
.al-frame{
  position:absolute;
  top:50%;left:50%;
  transform:translate(-50%,-50%);
  width:min(76%,400px);
  aspect-ratio:3/4;
  border-radius:28px;
  overflow:hidden;
  border:1px solid rgba(255,255,255,.14);
  box-shadow:0 0 0 1px rgba(52,211,153,.08),0 24px 70px rgba(0,0,0,.65),0 0 60px rgba(16,185,129,.08) inset;
  background:linear-gradient(160deg,#0e1a0e 0%,#0a1410 50%,#060c08 100%);
  animation:frameFloat 10s ease-in-out infinite alternate;
}
.al-sky{
  position:absolute;inset:0;
  background:linear-gradient(180deg,#04100a 0%,#091a10 30%,#0e1e12 55%,#1a0c04 80%,#0a0804 100%);
}
.al-frame-aurora{
  position:absolute;top:5%;left:-20%;right:-20%;height:30%;
  background:radial-gradient(ellipse 60% 100% at 40% 50%, rgba(16,185,129,.22), transparent 65%),
             radial-gradient(ellipse 40% 100% at 80% 50%, rgba(52,211,153,.14), transparent 65%);
  filter:blur(18px);
  animation:auroraPulse 7s ease-in-out 1s infinite alternate;
}
.al-stardots{position:absolute;inset:0;pointer-events:none}
.sd{
  position:absolute;border-radius:50%;background:#fff;
  animation:twinkle var(--d,3s) ease-in-out var(--dl,0s) infinite alternate;
}
@keyframes twinkle{0%{opacity:.15;transform:scale(.8)}100%{opacity:.9;transform:scale(1.2)}}
.al-mtn{position:absolute;bottom:0;left:0;right:0}
.al-mtn svg{width:100%;height:auto;display:block}
.al-planet{
  position:absolute;top:14%;right:16%;
  width:clamp(42px,8%,68px);aspect-ratio:1;border-radius:50%;
  background:radial-gradient(circle at 32% 32%,#daeeff,#8ab8dc 38%,#3f6e96 70%,#1e3a52);
  box-shadow:0 0 0 2px rgba(180,220,255,.15),0 6px 28px rgba(80,160,220,.35),inset -4px -4px 12px rgba(0,0,30,.4);
  animation:planetPulse 5s ease-in-out infinite alternate,planetOrbit 30s linear infinite;
}
@keyframes planetPulse{
  0%{box-shadow:0 0 0 2px rgba(180,220,255,.15),0 6px 28px rgba(80,160,220,.35),inset -4px -4px 12px rgba(0,0,30,.4)}
  100%{box-shadow:0 0 0 5px rgba(180,220,255,.1),0 8px 40px rgba(80,160,220,.55),inset -4px -4px 12px rgba(0,0,30,.4)}
}
@keyframes planetOrbit{
  0%{transform:translate(0,0)}25%{transform:translate(4px,-5px)}
  50%{transform:translate(0,-8px)}75%{transform:translate(-4px,-5px)}100%{transform:translate(0,0)}
}
.al-glow{
  position:absolute;bottom:-15%;left:10%;right:10%;height:50%;border-radius:50%;
  background:radial-gradient(ellipse,rgba(255,90,20,.3) 0%,rgba(200,50,5,.15) 40%,transparent 70%);
  filter:blur(22px);
  animation:glowBreath 6s ease-in-out infinite alternate;
}
@keyframes glowBreath{0%{opacity:.7;transform:scaleX(1)}100%{opacity:1;transform:scaleX(1.08)}}
.al-dust .dp{
  position:absolute;bottom:-6px;border-radius:50%;
  background:rgba(52,211,153,.55);
  animation:dustFloat linear infinite;
}
@keyframes dustFloat{
  0%{transform:translateY(0);opacity:0}10%{opacity:.7}90%{opacity:.3}100%{transform:translateY(-100%);opacity:0}
}
.al-orb{position:absolute;border-radius:50%;filter:blur(70px);pointer-events:none}
.al-orb1{
  width:300px;height:300px;top:-100px;left:-80px;
  background:rgba(16,185,129,.12);
  animation:orbDrift1 12s ease-in-out infinite alternate;
}
.al-orb2{
  width:220px;height:220px;bottom:-60px;right:-50px;
  background:rgba(5,150,105,.14);
  animation:orbDrift2 15s ease-in-out infinite alternate;
}
@keyframes orbDrift1{from{transform:translate(0,0)}to{transform:translate(30px,-40px)}}
@keyframes orbDrift2{from{transform:translate(0,0)}to{transform:translate(-20px,30px)}}
.al-user{
  position:absolute;bottom:22px;left:22px;
  display:flex;align-items:center;gap:10px;
  background:rgba(10,20,14,.6);border:1px solid rgba(52,211,153,.2);
  border-radius:40px;padding:7px 14px 7px 8px;backdrop-filter:blur(14px);z-index:4;
  animation:chipSlide .8s cubic-bezier(.2,.8,.3,1) .2s both;
}
@keyframes chipSlide{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.al-user-avatar{
  width:34px;height:34px;border-radius:50%;
  background:linear-gradient(135deg,#34d399,#059669);
  display:grid;place-items:center;color:#fff;font-weight:900;font-size:15px;flex-shrink:0;
  animation:avatarGlow 3s ease-in-out infinite alternate;
}
@keyframes avatarGlow{
  0%{box-shadow:0 0 8px rgba(52,211,153,.35)}100%{box-shadow:0 0 18px rgba(52,211,153,.65)}
}
.al-user-name{color:#fff;font-weight:700;font-size:13px;line-height:1.2}
.al-user-role{color:rgba(255,255,255,.5);font-size:11px}
.al-brand{
  position:relative;z-index:4;display:flex;align-items:center;gap:9px;
  animation:chipSlide .6s cubic-bezier(.2,.8,.3,1) both;
}
.al-brand-mark{
  width:34px;height:34px;border-radius:11px;
  background:linear-gradient(135deg,#34d399,#059669);
  display:grid;place-items:center;color:#fff;font-weight:900;font-size:16px;
  box-shadow:0 4px 14px rgba(16,185,129,.4);
}
.al-brand-text{color:#fff;font-weight:800;font-size:16px;letter-spacing:-.2px}
.al-nav{
  position:absolute;top:28px;right:28px;z-index:4;
  display:flex;align-items:center;gap:10px;
  animation:chipSlide .6s cubic-bezier(.2,.8,.3,1) .1s both;
}
.al-nav-link{color:rgba(255,255,255,.55);font-size:13px;font-weight:600;text-decoration:none;transition:color .2s}
.al-nav-link:hover{color:#fff}
.al-nav-pill{
  padding:6px 16px;border-radius:999px;
  background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);
  color:#a7f3d0;font-size:13px;font-weight:700;text-decoration:none;transition:.2s;
}
.al-nav-pill:hover{background:rgba(52,211,153,.2);color:#fff}

@keyframes frameFloat{
  from{transform:translate(-50%,-50%) translateY(0) rotate(-.5deg)}
  to{transform:translate(-50%,-50%) translateY(-12px) rotate(.5deg)}
}

/* ─── RIGHT WHITE PANEL ─── */
.ar{
  flex:1;
  display:flex;align-items:center;justify-content:center;
  padding:32px 32px;
  background:#fff;
  position:relative;overflow:hidden;
  overflow-y:auto;
}
.ar::before{
  content:'';position:absolute;top:-80px;right:-80px;
  width:260px;height:260px;border-radius:50%;
  background:radial-gradient(circle,rgba(16,185,129,.06),transparent 70%);
  pointer-events:none;
}
.ar-wrap{width:100%;max-width:440px}

.ar-brand{display:flex;align-items:center;gap:9px;margin-bottom:24px}
.ar-brand-mark{
  width:36px;height:36px;border-radius:11px;
  background:linear-gradient(135deg,#34d399,#059669);
  display:grid;place-items:center;color:#fff;font-weight:900;font-size:16px;
}
.ar-brand-text{font-weight:900;font-size:16px;color:#0f172a;letter-spacing:-.3px}
.ar-brand-text small{display:block;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px}

.ar-title{
  font-size:clamp(28px,3vw,38px);font-weight:900;
  color:#0f172a;letter-spacing:-1.2px;line-height:1.1;margin-bottom:5px;
}
.ar-sub{font-size:14px;color:#94a3b8;font-weight:500;margin-bottom:22px}

.lux-alert{
  background:#fef2f2;color:#7f1d1d;border:1px solid #fca5a5;
  border-radius:10px;padding:10px 14px;font-size:13px;font-weight:700;
  margin-bottom:16px;text-align:center;
}

/* floating-label fields */
.fl-row{display:flex;gap:12px}
.fl-row .fl-field{flex:1}
.fl-field{position:relative;margin-bottom:12px}
.fl-input{
  width:100%;padding:15px 16px 8px;
  border:1.5px solid #e8ecef;border-radius:12px;
  background:#f8fafb;color:#0f172a;
  font-size:14px;font-family:inherit;outline:none;
  transition:border-color .2s,box-shadow .2s,background .2s;
}
.fl-input::placeholder{color:transparent}
.fl-label{
  position:absolute;top:50%;left:16px;
  transform:translateY(-50%);
  font-size:14px;font-weight:500;color:#94a3b8;
  pointer-events:none;
  transition:top .18s ease,font-size .18s ease,color .18s ease,transform .18s ease;
}
.fl-input:focus+.fl-label,
.fl-input:not(:placeholder-shown)+.fl-label{
  top:8px;transform:translateY(0);
  font-size:10px;font-weight:700;
  color:#059669;letter-spacing:.4px;text-transform:uppercase;
}
.fl-input:focus{border-color:#10b981;background:#fff;box-shadow:0 0 0 4px rgba(16,185,129,.1)}
/* select needs always-shown label */
.fl-input-sel{
  width:100%;padding:22px 16px 8px;
  border:1.5px solid #e8ecef;border-radius:12px;
  background:#f8fafb;color:#0f172a;
  font-size:14px;font-family:inherit;outline:none;
  transition:border-color .2s,box-shadow .2s;
  appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 14px center;
}
.fl-input-sel:focus{border-color:#10b981;background-color:#fff;box-shadow:0 0 0 4px rgba(16,185,129,.1)}
.fl-label-sel{
  position:absolute;top:8px;left:16px;
  font-size:10px;font-weight:700;color:#059669;letter-spacing:.4px;text-transform:uppercase;
  pointer-events:none;
}
.fl-pw-btn{
  position:absolute;right:14px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;font-size:17px;color:#94a3b8;line-height:1;padding:4px;
}

.lux-btn{
  width:100%;padding:15px;border-radius:12px;border:none;
  background:linear-gradient(135deg,#10b981,#059669);
  color:#fff;font-size:15px;font-weight:800;cursor:pointer;letter-spacing:.2px;
  box-shadow:0 8px 24px rgba(5,150,105,.3);
  transition:transform .2s,box-shadow .2s,filter .2s;
  margin-top:6px;
}
.lux-btn:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(5,150,105,.38);filter:brightness(1.04)}
.lux-btn:active{transform:scale(.98)}

.ar-sub-link{text-align:center;font-size:13px;color:#64748b;margin-top:14px}
.ar-sub-link a{color:#059669;font-weight:700;text-decoration:none}
.ar-sub-link a:hover{text-decoration:underline}

.ar-notice{
  text-align:center;font-size:11.5px;color:#cbd5e1;
  margin-top:14px;padding-top:14px;border-top:1px solid #f1f5f9;
}

@media(max-width:820px){
  .al{display:none}
  .ar{width:100%}
}
@media(max-width:480px){
  .ar{padding:24px 18px}
  .ar-title{font-size:26px}
  .fl-row{flex-direction:column;gap:0}
}
@media(prefers-reduced-motion:reduce){
  *,*::before,*::after{animation-duration:.01ms !important;transition-duration:.01ms !important}
}
</style>
</head>
<body>

<!-- LEFT DARK PANEL -->
<div class="al">
  <div class="al-bg"></div>
  <div class="al-grid"></div>
  <div class="al-aurora"></div>
  <div class="al-orb al-orb1"></div>
  <div class="al-orb al-orb2"></div>
  <div class="al-shoot al-shoot1"></div>
  <div class="al-shoot al-shoot2"></div>

  <div class="al-brand">
    <div class="al-brand-mark">T</div>
    <div class="al-brand-text">TindaHub</div>
  </div>

  <div class="al-nav">
    <a class="al-nav-link" href="admin-login.php">Already have an account?</a>
    <a class="al-nav-pill" href="admin-login.php">Sign In</a>
  </div>

  <div class="al-frame">
    <div class="al-sky"></div>
    <div class="al-frame-aurora"></div>
    <div class="al-stardots">
      <div class="sd" style="width:2px;height:2px;top:6%;left:12%;--d:2.8s;--dl:0s"></div>
      <div class="sd" style="width:1.5px;height:1.5px;top:9%;left:38%;--d:3.4s;--dl:.6s"></div>
      <div class="sd" style="width:2px;height:2px;top:5%;left:62%;--d:2.1s;--dl:1.2s"></div>
      <div class="sd" style="width:1px;height:1px;top:12%;left:78%;--d:4s;--dl:.3s"></div>
      <div class="sd" style="width:2px;height:2px;top:18%;left:22%;--d:2.6s;--dl:.9s"></div>
      <div class="sd" style="width:1.5px;height:1.5px;top:8%;left:50%;--d:3.8s;--dl:1.5s"></div>
      <div class="sd" style="width:1px;height:1px;top:15%;left:86%;--d:2.3s;--dl:.4s"></div>
      <div class="sd" style="width:2px;height:2px;top:22%;left:7%;--d:3.1s;--dl:2s"></div>
      <div class="sd" style="width:1.5px;height:1.5px;top:4%;left:92%;--d:2.9s;--dl:.7s"></div>
      <div class="sd" style="width:1px;height:1px;top:26%;left:55%;--d:3.6s;--dl:1.8s"></div>
      <div class="sd" style="width:2px;height:2px;top:3%;left:28%;--d:2.4s;--dl:2.4s"></div>
      <div class="sd" style="width:1px;height:1px;top:19%;left:70%;--d:4.2s;--dl:1.1s"></div>
    </div>
    <div class="al-planet"></div>
    <div class="al-glow"></div>
    <div class="al-dust">
      <div class="dp" style="width:2px;height:2px;left:15%;animation-duration:8s;animation-delay:-2s"></div>
      <div class="dp" style="width:1.5px;height:1.5px;left:30%;animation-duration:11s;animation-delay:-5s"></div>
      <div class="dp" style="width:2.5px;height:2.5px;left:48%;animation-duration:9s;animation-delay:-1s"></div>
      <div class="dp" style="width:1px;height:1px;left:65%;animation-duration:12s;animation-delay:-7s"></div>
      <div class="dp" style="width:2px;height:2px;left:80%;animation-duration:7s;animation-delay:-3s"></div>
    </div>
    <div class="al-mtn">
      <svg viewBox="0 0 420 280" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
        <defs>
          <linearGradient id="mg1" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#1a3020" stop-opacity=".85"/>
            <stop offset="100%" stop-color="#0a1810"/>
          </linearGradient>
          <linearGradient id="mg2" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#102016" stop-opacity=".95"/>
            <stop offset="100%" stop-color="#060e08"/>
          </linearGradient>
          <linearGradient id="mg3" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#ff6420" stop-opacity=".22"/>
            <stop offset="100%" stop-color="#ff3a00" stop-opacity=".04"/>
          </linearGradient>
          <linearGradient id="mg4" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#050a06" stop-opacity="1"/>
            <stop offset="100%" stop-color="#020604" stop-opacity="1"/>
          </linearGradient>
          <linearGradient id="mgRidge" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#34d399" stop-opacity=".18"/>
            <stop offset="60%" stop-color="#10b981" stop-opacity=".04"/>
            <stop offset="100%" stop-color="transparent" stop-opacity="0"/>
          </linearGradient>
        </defs>
        <polygon points="0,200 50,130 100,165 160,95 210,130 260,80 310,115 360,75 420,100 420,280 0,280" fill="url(#mg1)"/>
        <polygon points="0,230 35,170 70,195 115,140 155,168 200,115 245,148 290,100 335,138 380,108 420,125 420,280 0,280" fill="url(#mg2)"/>
        <polygon points="0,230 35,170 70,195 115,140 155,168 200,115 245,148 290,100 335,138 380,108 420,125 420,280 0,280" fill="url(#mg3)"/>
        <polygon points="0,230 35,170 70,195 115,140 155,168 200,115 245,148 290,100 335,138 380,108 420,125 420,145 380,128 335,158 290,120 245,168 200,135 155,188 115,160 70,215 35,190 0,250" fill="url(#mgRidge)"/>
        <polygon points="0,250 420,250 420,280 0,280" fill="url(#mg4)"/>
      </svg>
    </div>
  </div>

  <div class="al-user">
    <div class="al-user-avatar">T</div>
    <div>
      <div class="al-user-name">TindaHub POS</div>
      <div class="al-user-role">Store Management System</div>
    </div>
  </div>
</div>

<!-- RIGHT WHITE PANEL -->
<div class="ar">
  <div class="ar-wrap">
    <div class="ar-brand">
      <div class="ar-brand-mark">T</div>
      <div class="ar-brand-text">TindaHub<small>Store Management</small></div>
    </div>

    <div class="ar-title">Set up your store 🏪</div>
    <div class="ar-sub">Create your owner account and get started.</div>

    <?php if ($error): ?>
      <div class="lux-alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>

      <div class="fl-row">
        <div class="fl-field">
          <input class="fl-input" id="name" name="name" type="text" required maxlength="120"
                 autocomplete="name" placeholder="Your name"
                 value="<?= e($_POST['name'] ?? '') ?>">
          <label class="fl-label" for="name">Your name</label>
        </div>
        <div class="fl-field">
          <input class="fl-input" id="store_name" name="store_name" type="text" required maxlength="150"
                 placeholder="Store name"
                 value="<?= e($_POST['store_name'] ?? '') ?>">
          <label class="fl-label" for="store_name">Store name</label>
        </div>
      </div>

      <div class="fl-field">
        <label class="fl-label-sel" for="store_type">Store type</label>
        <select class="fl-input-sel" id="store_type" name="store_type" required>
          <option value="">Choose your store type</option>
          <?php foreach ($storeTypes as $value => $label): ?>
          <option value="<?= e($value) ?>" <?= (($_POST['store_type'] ?? '') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="fl-field">
        <input class="fl-input" id="email" name="email" type="email" required maxlength="190"
               autocomplete="email" placeholder="Email address"
               value="<?= e($_POST['email'] ?? '') ?>">
        <label class="fl-label" for="email">Email address</label>
      </div>

      <div class="fl-field">
        <input class="fl-input" id="password" name="password" type="password" minlength="8" required
               autocomplete="new-password" placeholder="Password"
               style="padding-right:44px">
        <label class="fl-label" for="password">Password (min. 8 characters)</label>
        <button type="button" class="fl-pw-btn" aria-label="Toggle password"
          onclick="const i=document.getElementById('password');i.type=i.type==='password'?'text':'password';this.textContent=i.type==='password'?'👁':'🙈'">👁</button>
      </div>

      <button class="lux-btn" type="submit">Create my store →</button>
    </form>

    <div class="ar-sub-link">Already have an account? <a href="admin-login.php">Sign in</a></div>
    <div class="ar-sub-link" style="margin-top:6px">Working as a cashier? <a href="cashier-login.php">Cashier sign in</a></div>
    <div class="ar-notice">🔒 Your data stays private — runs securely on your own machine.</div>
  </div>
</div>

</body>
</html>
