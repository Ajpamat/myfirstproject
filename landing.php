<!--?php
// landing.php - TindaHub Haute Luxe Light Edition
// Ready for XAMPP / Apache. No build tools or npm required.
?--><!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>TindaHub — Simple, Fast POS &amp; Inventory System for Philippine Retailers</title>
<!-- Primary Font: Manrope & Space Grotesk display -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&amp;family=Space+Grotesk:wght@500;600;700&amp;display=swap" rel="stylesheet"/>
<!-- Three.js (r128) with reliable fallback -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
    if (typeof THREE === 'undefined') {
      document.write('<script src="https://unpkg.com/three@0.128.0/build/three.min.js"><\/script>');
    }
</script>
<style>
    /* ==========================================================================
       1. HAUTE LUXE LIGHT PALETTE & BESPOKE TOKENS
       ========================================================================== */
    :root {
      --bg-alabaster: #FDFBF7;
      --bg-ivory: #F9F6F0;
      --bg-porcelain: #F4EFEB;
      --bg-surface: #FFFFFF;
      --bg-card: rgba(255, 255, 255, 0.88);
      --bg-card-hover: rgba(255, 255, 255, 0.98);
      
      --emerald-jewel: #047857;
      --emerald-deep: #065F46;
      --emerald-vibrant: #059669;
      --emerald-accent: #10B981;
      --emerald-subtle: rgba(4, 120, 87, 0.08);
      
      --gold-hallmark: #B8860B;
      --gold-champagne: #C5A059;
      --gold-light: #E9D7B3;
      --gold-glow: rgba(197, 160, 89, 0.28);
      --gold-border: rgba(197, 160, 89, 0.35);

      --text-espresso: #18181B;
      --text-onyx: #0F172A;
      --text-primary: #27272A;
      --text-muted: #52525B;
      --text-faint: #71717A;

      --border-micro: rgba(197, 160, 89, 0.2);
      --border-soft: rgba(0, 0, 0, 0.07);
      --border-gold: rgba(197, 160, 89, 0.4);
      --border-emerald: rgba(4, 120, 87, 0.25);
      
      --shadow-prestige: 0 20px 45px -12px rgba(28, 25, 23, 0.07), 0 4px 16px -2px rgba(197, 160, 89, 0.06);
      --shadow-elevated: 0 32px 70px -15px rgba(28, 25, 23, 0.12), 0 8px 24px -4px rgba(197, 160, 89, 0.12);
      --radius-sm: 8px;
      --radius-md: 14px;
      --radius-lg: 24px;
      --radius-full: 9999px;
      --transition-silk: all 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    }

    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
      font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background-color: var(--bg-alabaster);
      color: var(--text-primary);
      overflow-x: hidden;
      color-scheme: light;
    }

    body {
      background-color: var(--bg-alabaster);
      color: var(--text-primary);
      overflow-x: hidden;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
    }

    ::selection {
      background: rgba(197, 160, 89, 0.32);
      color: #18181B;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    /* ==========================================================================
       KEYFRAMES: IMMERSIVE LUXURY ANIMATIONS
       ========================================================================== */
    @keyframes floatSlow1 {
      0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
      50% { transform: translate3d(40px, -35px, 0) scale(1.08); }
    }

    @keyframes floatSlow2 {
      0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
      50% { transform: translate3d(-35px, 30px, 0) scale(1.12); }
    }

    @keyframes floatSlow3 {
      0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
      50% { transform: translate3d(30px, 45px, 0) scale(0.95); }
    }

    @keyframes shimmerSweep {
      0% { transform: translateX(-150%) skewX(-20deg); }
      100% { transform: translateX(250%) skewX(-20deg); }
    }

    @keyframes pulseAura {
      0%, 100% { transform: scale(1); opacity: 0.85; filter: drop-shadow(0 0 10px rgba(197, 160, 89, 0.25)); }
      50% { transform: scale(1.025); opacity: 1; filter: drop-shadow(0 0 20px rgba(197, 160, 89, 0.45)); }
    }

    @keyframes radarPulse {
      0% { transform: scale(0.8); opacity: 1; }
      100% { transform: scale(2.8); opacity: 0; }
    }

    @keyframes subtleGlowBreath {
      0%, 100% { box-shadow: 0 10px 24px -4px rgba(4, 120, 87, 0.28), 0 0 0 0 rgba(16, 185, 129, 0.3); }
      50% { box-shadow: 0 14px 32px -4px rgba(4, 120, 87, 0.45), 0 0 20px 4px rgba(197, 160, 89, 0.3); }
    }

    @keyframes barGlowThrob {
      0%, 100% { filter: brightness(1); }
      50% { filter: brightness(1.12) drop-shadow(0 0 8px rgba(16, 185, 129, 0.4)); }
    }

    /* Ambient Haute Luxe Luminous Lighting */
    .luxury-ambient {
      position: fixed;
      inset: 0;
      pointer-events: none;
      z-index: 0;
      overflow: hidden;
      will-change: transform;
    }

    .ambient-radial-top {
      position: absolute;
      top: -20%;
      left: 15%;
      width: 72vw;
      height: 72vw;
      background: radial-gradient(circle, rgba(233, 215, 179, 0.48) 0%, rgba(244, 239, 235, 0.28) 50%, transparent 75%);
      filter: blur(90px);
      border-radius: 50%;
      animation: floatSlow1 18s ease-in-out infinite alternate;
      will-change: transform;
    }

    .ambient-radial-gold {
      position: absolute;
      top: 35%;
      right: -10%;
      width: 58vw;
      height: 58vw;
      background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, rgba(197, 160, 89, 0.18) 50%, transparent 80%);
      filter: blur(100px);
      border-radius: 50%;
      animation: floatSlow2 22s ease-in-out infinite alternate;
      will-change: transform;
    }

    .ambient-radial-bottom {
      position: absolute;
      bottom: -15%;
      left: -10%;
      width: 62vw;
      height: 62vw;
      background: radial-gradient(circle, rgba(197, 160, 89, 0.22) 0%, rgba(249, 246, 240, 0.32) 60%, transparent 80%);
      filter: blur(100px);
      border-radius: 50%;
      animation: floatSlow3 20s ease-in-out infinite alternate;
      will-change: transform;
    }

    .container {
      width: 100%;
      max-width: 1260px;
      margin: 0 auto;
      padding: 0 28px;
      position: relative;
    }

    /* ==========================================================================
       2. ULTRA-PREMIUM NAVIGATION BAR (LUMINOUS IVORY / CERAMIC)
       ========================================================================== */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 86px;
      display: flex;
      align-items: center;
      z-index: 100;
      background: rgba(253, 251, 247, 0.85);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(197, 160, 89, 0.22);
      transition: var(--transition-silk);
    }

    .navbar.scrolled {
      height: 74px;
      background: rgba(255, 255, 255, 0.95);
      border-bottom: 1px solid rgba(197, 160, 89, 0.34);
      box-shadow: 0 12px 35px rgba(44, 39, 34, 0.08);
    }

    .nav-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
    }

    .brand-logo {
      display: flex;
      align-items: center;
      gap: 14px;
      font-weight: 800;
      font-size: 1.35rem;
      color: var(--text-espresso);
      letter-spacing: -0.02em;
      position: relative;
    }

    .logo-badge {
      width: 42px;
      height: 42px;
      background: linear-gradient(135deg, #059669 0%, #047857 55%, #064E3B 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ffffff;
      font-weight: 800;
      font-size: 1.3rem;
      border: 1px solid rgba(197, 160, 89, 0.5);
      box-shadow: 0 4px 16px rgba(4, 120, 87, 0.25);
      position: relative;
      overflow: hidden;
      transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
    }

    .brand-logo:hover .logo-badge {
      transform: scale(1.06) rotate(-3deg);
      box-shadow: 0 6px 22px rgba(4, 120, 87, 0.38), 0 0 15px rgba(197, 160, 89, 0.4);
    }

    .logo-badge::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
      animation: shimmerSweep 4s infinite ease-in-out;
      pointer-events: none;
    }

    .brand-badge-tag {
      font-size: 0.65rem;
      text-transform: uppercase;
      letter-spacing: 0.16em;
      font-weight: 800;
      color: var(--gold-hallmark);
      border: 1px solid rgba(197, 160, 89, 0.45);
      padding: 3px 8px;
      border-radius: 4px;
      background: rgba(197, 160, 89, 0.1);
      margin-left: 6px;
      position: relative;
      overflow: hidden;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 36px;
      list-style: none;
    }

    .nav-link {
      font-size: 0.92rem;
      font-weight: 600;
      color: var(--text-muted);
      letter-spacing: -0.01em;
      transition: color 0.25s ease;
      position: relative;
    }

    .nav-link:hover {
      color: var(--emerald-jewel);
    }

    .nav-link::after {
      content: '';
      position: absolute;
      bottom: -6px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--gold-champagne), var(--emerald-accent));
      border-radius: 2px;
      transition: width 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .nav-link:hover::after {
      width: 100%;
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    /* Buttons - Haute Luxe Light Specs */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      font-size: 0.92rem;
      font-weight: 700;
      padding: 11px 24px;
      border-radius: 12px;
      transition: var(--transition-silk);
      cursor: pointer;
      position: relative;
      white-space: nowrap;
      letter-spacing: -0.01em;
      overflow: hidden;
      will-change: transform, box-shadow;
    }

    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 60%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent);
      transform: skewX(-20deg);
      transition: none;
      pointer-events: none;
    }

    .btn:hover::before {
      animation: shimmerSweep 1s ease-in-out forwards;
    }

    .btn-ghost {
      color: var(--text-onyx);
      background: transparent;
      border: 1px solid rgba(197, 160, 89, 0.3);
    }

    .btn-ghost:hover {
      color: var(--emerald-jewel);
      background: rgba(255, 255, 255, 0.9);
      border-color: rgba(197, 160, 89, 0.6);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(197, 160, 89, 0.16);
    }

    .btn-primary {
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      color: #ffffff;
      border: 1px solid rgba(197, 160, 89, 0.45);
      box-shadow: 0 10px 24px -4px rgba(4, 120, 87, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.3);
      animation: subtleGlowBreath 4s infinite ease-in-out;
    }

    .btn-primary:hover {
      transform: translateY(-3px) scale(1.01);
      box-shadow: 0 18px 36px -4px rgba(4, 120, 87, 0.48), 0 0 24px rgba(197, 160, 89, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.5);
      background: linear-gradient(135deg, #10B981 0%, #047857 100%);
    }

    .btn-luxury-white {
      background: #FFFFFF;
      color: var(--text-espresso);
      border: 1px solid rgba(197, 160, 89, 0.45);
      box-shadow: 0 10px 25px rgba(197, 160, 89, 0.14), inset 0 1px 1px #ffffff;
    }

    .btn-luxury-white:hover {
      background: #FDFBF7;
      transform: translateY(-3px) scale(1.01);
      border-color: var(--gold-champagne);
      box-shadow: 0 18px 36px rgba(197, 160, 89, 0.25), 0 0 18px rgba(197, 160, 89, 0.15);
      color: var(--emerald-jewel);
    }

    .btn-lg {
      padding: 15px 32px;
      font-size: 1.02rem;
      border-radius: 14px;
    }

    /* ==========================================================================
       3. HERO SECTION & THREE.JS LUXURY LIGHT CANVAS
       ========================================================================== */
    .hero-section {
      position: relative;
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding: 135px 0 70px;
      z-index: 2;
      overflow: hidden;
    }

    .hero-canvas-container {
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      width: 54%;
      height: 100%;
      min-height: 580px;
      z-index: 3;
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: auto;
    }

    #three-hero-canvas {
      width: 100%;
      height: 100%;
      display: block;
      position: relative;
      z-index: 2;
      outline: none;
    }

    .hero-content {
      position: relative;
      z-index: 10;
      max-width: 630px;
    }

    .luxury-pill-badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: rgba(255, 255, 255, 0.92);
      border: 1px solid var(--border-gold);
      backdrop-filter: blur(12px);
      color: var(--gold-hallmark);
      padding: 7px 20px;
      border-radius: var(--radius-full);
      font-size: 0.82rem;
      font-weight: 800;
      letter-spacing: 0.16em;
      text-transform: uppercase;
      margin-bottom: 24px;
      box-shadow: 0 4px 18px rgba(197, 160, 89, 0.16);
      position: relative;
      overflow: hidden;
      animation: pulseAura 6s ease-in-out infinite;
    }

    .luxury-pill-badge::after {
      content: '';
      position: absolute;
      top: 0;
      left: -120%;
      width: 60%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(197, 160, 89, 0.35), transparent);
      animation: shimmerSweep 4s cubic-bezier(0.16, 1, 0.3, 1) infinite;
      pointer-events: none;
    }

    .luxury-pill-badge .pulse-ring {
      position: relative;
      width: 8px;
      height: 8px;
      background-color: var(--gold-champagne);
      border-radius: 50%;
      box-shadow: 0 0 10px var(--gold-champagne);
    }

    .luxury-pill-badge .pulse-ring::after {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      border: 1.5px solid var(--gold-champagne);
      animation: radarPulse 2.2s cubic-bezier(0.16, 1, 0.3, 1) infinite;
    }

    .hero-headline {
      font-size: 3.8rem;
      line-height: 1.08;
      font-weight: 800;
      letter-spacing: -0.04em;
      color: var(--text-espresso);
      margin-bottom: 24px;
    }

    .hero-headline span.gold-highlight {
      background: linear-gradient(135deg, #18181B 0%, #B8860B 45%, #047857 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      display: inline-block;
      position: relative;
    }

    .hero-subheadline {
      font-size: 1.15rem;
      color: var(--text-muted);
      line-height: 1.7;
      margin-bottom: 38px;
      max-width: 560px;
      font-weight: 400;
    }

    .hero-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      align-items: center;
      margin-bottom: 44px;
    }

    .hero-trust-bar {
      display: flex;
      align-items: center;
      gap: 20px;
      padding-top: 18px;
      border-top: 1px solid rgba(197, 160, 89, 0.22);
    }

    .avatar-stack {
      display: flex;
      align-items: center;
    }

    .avatar-pill {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: linear-gradient(135deg, #FFFFFF, #F4EFEB);
      border: 2px solid #FFFFFF;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.74rem;
      font-weight: 800;
      color: var(--emerald-jewel);
      margin-left: -10px;
      box-shadow: 0 4px 10px rgba(197, 160, 89, 0.25);
      transition: transform 0.3s ease;
    }
    .avatar-pill:first-child { margin-left: 0; }
    .avatar-stack:hover .avatar-pill {
      transform: translateY(-2px);
    }

    .trust-text {
      font-size: 0.86rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    .trust-text strong {
      color: var(--text-espresso);
      font-weight: 700;
    }

    .scene-hint {
      position: absolute;
      right: 36px;
      bottom: 28px;
      z-index: 10;
      font-size: 0.74rem;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      font-weight: 700;
      color: var(--text-muted);
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(12px);
      padding: 8px 20px;
      border-radius: var(--radius-full);
      border: 1px solid var(--border-gold);
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 6px 22px rgba(197, 160, 89, 0.18);
      pointer-events: none;
      animation: pulseAura 4.5s ease-in-out infinite;
    }

    /* ==========================================================================
       4. LUXURY MARQUEE TICKER TAPE (ALABASTER / GOLD FOIL)
       ========================================================================== */
    .marquee-container {
      position: relative;
      z-index: 10;
      background: var(--bg-ivory);
      border-top: 1px solid rgba(197, 160, 89, 0.28);
      border-bottom: 1px solid rgba(197, 160, 89, 0.28);
      padding: 17px 0;
      overflow: hidden;
      white-space: nowrap;
      display: flex;
    }

    .marquee-track {
      display: inline-flex;
      align-items: center;
      animation: marqueeScroll 24s linear infinite;
      will-change: transform;
    }

    .marquee-container:hover .marquee-track {
      animation-play-state: paused;
    }

    .marquee-item {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      padding: 0 32px;
      font-size: 0.84rem;
      font-weight: 700;
      color: var(--text-onyx);
      text-transform: uppercase;
      letter-spacing: 0.14em;
      transition: color 0.3s ease;
    }

    .marquee-item:hover {
      color: var(--emerald-jewel);
    }

    .marquee-gem {
      width: 7px;
      height: 7px;
      background: var(--gold-champagne);
      border-radius: 50%;
      box-shadow: 0 0 10px rgba(197, 160, 89, 0.8);
      animation: radarPulse 2.8s ease-in-out infinite;
    }

    @keyframes marqueeScroll {
      0% { transform: translate3d(0, 0, 0); }
      100% { transform: translate3d(-50%, 0, 0); }
    }

    /* ==========================================================================
       5. SECTION UTILITIES & REVEAL SYSTEM
       ========================================================================== */
    section {
      padding: 120px 0;
      position: relative;
      z-index: 2;
    }

    .section-header {
      text-align: center;
      max-width: 720px;
      margin: 0 auto 70px;
    }

    .section-eyebrow {
      display: inline-block;
      font-size: 0.78rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.16em;
      color: var(--gold-hallmark);
      background: rgba(197, 160, 89, 0.1);
      border: 1px solid rgba(197, 160, 89, 0.35);
      padding: 5px 16px;
      border-radius: var(--radius-full);
      margin-bottom: 18px;
      position: relative;
      overflow: hidden;
    }

    .section-eyebrow::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 60%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(197, 160, 89, 0.4), transparent);
      animation: shimmerSweep 3.6s infinite ease-in-out;
    }

    .section-title {
      font-size: 2.85rem;
      font-weight: 800;
      letter-spacing: -0.035em;
      line-height: 1.18;
      color: var(--text-espresso);
      margin-bottom: 20px;
    }

    .section-description {
      font-size: 1.08rem;
      color: var(--text-muted);
      line-height: 1.7;
    }

    /* Scroll Reveal Stagger System */
    .reveal {
      opacity: 0;
      transform: translate3d(0, 36px, 0);
      transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1), transform 0.85s cubic-bezier(0.16, 1, 0.3, 1);
      will-change: opacity, transform;
    }

    .reveal.active {
      opacity: 1;
      transform: translate3d(0, 0, 0);
    }

    /* ==========================================================================
       6. ENTERPRISE FEATURE CARDS (PORCELAIN WHITE WITH 3D PERSPECTIVE TILT)
       ========================================================================== */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 28px;
      perspective: 1200px;
    }

    .feature-card {
      background: #FFFFFF;
      border: 1px solid rgba(197, 160, 89, 0.26);
      border-radius: var(--radius-lg);
      padding: 38px 32px;
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow-prestige);
      transition: transform 0.25s ease-out, box-shadow 0.4s ease, border-color 0.4s ease;
      transform-style: preserve-3d;
      will-change: transform, box-shadow;
    }

    .feature-card::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at var(--mouse-x, 50%) var(--mouse-y, 0%), rgba(197, 160, 89, 0.16) 0%, transparent 68%);
      opacity: 0;
      transition: opacity 0.4s ease;
      pointer-events: none;
    }

    .feature-card::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(115deg, transparent 40%, rgba(255, 255, 255, 0.6) 48%, rgba(197, 160, 89, 0.2) 52%, transparent 60%);
      transform: translateX(-150%) skewX(-20deg);
      pointer-events: none;
      transition: transform 0.8s ease;
    }

    .feature-card:hover::after {
      transform: translateX(100%) skewX(-20deg);
    }

    .feature-card:hover {
      border-color: rgba(197, 160, 89, 0.65);
      box-shadow: var(--shadow-elevated), 0 0 25px rgba(197, 160, 89, 0.15);
    }

    .feature-card:hover::before {
      opacity: 1;
    }

    .feature-icon-wrapper {
      width: 54px;
      height: 54px;
      border-radius: 14px;
      background: linear-gradient(135deg, #FDFBF7 0%, #F4EFEB 100%);
      border: 1px solid rgba(197, 160, 89, 0.38);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin-bottom: 24px;
      color: var(--emerald-jewel);
      box-shadow: 0 4px 14px rgba(197, 160, 89, 0.15);
      transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease, border-color 0.4s ease;
    }

    .feature-card:hover .feature-icon-wrapper {
      transform: scale(1.12) rotate(-4deg) translateZ(20px);
      border-color: var(--emerald-jewel);
      color: #047857;
      box-shadow: 0 10px 24px rgba(4, 120, 87, 0.28), 0 0 12px rgba(197, 160, 89, 0.3);
    }

    .feature-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--text-espresso);
      margin-bottom: 12px;
      letter-spacing: -0.02em;
    }

    .feature-text {
      font-size: 0.95rem;
      color: var(--text-muted);
      line-height: 1.65;
    }

    .feature-tag-badge {
      display: inline-block;
      margin-top: 22px;
      font-size: 0.72rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.12em;
      color: var(--emerald-jewel);
      background: rgba(4, 120, 87, 0.08);
      border: 1px solid rgba(4, 120, 87, 0.24);
      padding: 4px 12px;
      border-radius: var(--radius-full);
      transition: background 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
    }

    .feature-card:hover .feature-tag-badge {
      background: rgba(4, 120, 87, 0.14);
      border-color: var(--emerald-jewel);
      transform: translateY(-2px);
    }

    /* ==========================================================================
       7. STATS BANNER — MONOLITHIC ENTERPRISE METRICS
       ========================================================================== */
    .stats-section {
      padding: 90px 0;
      background: var(--bg-ivory);
      border-top: 1px solid rgba(197, 160, 89, 0.28);
      border-bottom: 1px solid rgba(197, 160, 89, 0.28);
      position: relative;
    }

    .stats-section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 10%;
      right: 10%;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(197, 160, 89, 0.7), transparent);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 36px;
    }

    .stat-card {
      padding: 24px;
      border-left: 1px solid rgba(197, 160, 89, 0.28);
      position: relative;
      transition: transform 0.35s ease;
    }

    .stat-card:hover {
      transform: translateY(-4px);
    }

    .stat-card:first-child {
      border-left: none;
    }

    .stat-number-wrap {
      font-family: 'Space Grotesk', 'Manrope', sans-serif;
      font-size: 3.2rem;
      font-weight: 700;
      color: var(--text-espresso);
      line-height: 1;
      margin-bottom: 10px;
      letter-spacing: -0.04em;
    }

    .stat-number-wrap span.accent-emerald {
      color: var(--emerald-jewel);
      text-shadow: 0 0 16px rgba(16, 185, 129, 0.3);
    }

    .stat-number-wrap span.accent-gold {
      color: var(--gold-hallmark);
      text-shadow: 0 0 16px rgba(197, 160, 89, 0.3);
    }

    .stat-label {
      font-size: 0.88rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--text-muted);
    }

    /* ==========================================================================
       8. 3D INTERACTIVE EXECUTIVE DASHBOARD (CERAMIC IVORY & SAPPHIRE GLOW)
       ========================================================================== */
    .dashboard-preview-section {
      background: radial-gradient(circle at 50% 30%, rgba(197, 160, 89, 0.14) 0%, rgba(253, 251, 247, 0) 70%);
      padding: 130px 0;
    }

    .dashboard-stage {
      perspective: 1600px;
      max-width: 1060px;
      margin: 0 auto;
    }

    .dashboard-mockup {
      background: #FFFFFF;
      border-radius: 24px;
      border: 1px solid rgba(197, 160, 89, 0.4);
      box-shadow: 0 40px 100px -20px rgba(44, 39, 34, 0.14), 0 0 50px rgba(197, 160, 89, 0.12);
      overflow: hidden;
      transform: rotateX(8deg) rotateY(-3deg) rotateZ(1deg);
      transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease, border-color 0.4s ease;
      will-change: transform;
      position: relative;
    }

    .dashboard-mockup::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.4) 0%, transparent 60%);
      pointer-events: none;
      z-index: 5;
    }

    .dashboard-mockup::after {
      content: '';
      position: absolute;
      top: 0;
      left: -120%;
      width: 80%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.45), rgba(197, 160, 89, 0.15), transparent);
      animation: shimmerSweep 7s infinite ease-in-out;
      pointer-events: none;
      z-index: 6;
    }

    .dashboard-mockup:hover {
      box-shadow: 0 55px 130px -15px rgba(44, 39, 34, 0.2), 0 0 70px rgba(197, 160, 89, 0.25);
      border-color: rgba(197, 160, 89, 0.65);
    }

    .dash-topbar {
      background: #F9F6F0;
      padding: 14px 24px;
      border-bottom: 1px solid rgba(197, 160, 89, 0.25);
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: relative;
      z-index: 7;
    }

    .dash-controls {
      display: flex;
      gap: 8px;
    }

    .dash-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      transition: transform 0.25s ease;
    }
    .dash-controls:hover .dash-dot {
      transform: scale(1.2);
    }
    .dash-dot.red { background-color: #ef4444; }
    .dash-dot.yellow { background-color: #C5A059; }
    .dash-dot.green { background-color: #059669; }

    .dash-title-tab {
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--text-onyx);
      background: #FFFFFF;
      padding: 4px 18px;
      border-radius: 8px;
      border: 1px solid rgba(197, 160, 89, 0.25);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }

    .beacon-dot {
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--emerald-jewel);
      position: relative;
      box-shadow: 0 0 10px var(--emerald-accent);
    }

    .beacon-dot::after {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      border: 1.5px solid var(--emerald-jewel);
      animation: radarPulse 1.8s ease-out infinite;
    }

    .dash-body {
      padding: 32px;
      background: #FFFFFF;
      position: relative;
      z-index: 7;
    }

    .dash-stats-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin-bottom: 24px;
    }

    .dash-mini-card {
      background: #FDFBF7;
      border: 1px solid rgba(197, 160, 89, 0.25);
      border-radius: 14px;
      padding: 20px 22px;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
      transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .dash-mini-card:hover {
      transform: translateY(-3px);
      border-color: rgba(197, 160, 89, 0.5);
      box-shadow: 0 8px 24px rgba(197, 160, 89, 0.12);
    }

    .dash-mini-label {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .dash-mini-val {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 1.65rem;
      font-weight: 700;
      color: var(--text-espresso);
      margin: 6px 0 4px;
    }

    .dash-mini-val span.currency {
      color: var(--gold-hallmark);
    }

    .dash-trend {
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--emerald-jewel);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .dash-chart-section {
      background: #FDFBF7;
      border: 1px solid rgba(197, 160, 89, 0.25);
      border-radius: 16px;
      padding: 24px;
    }

    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 22px;
    }

    .chart-title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--text-espresso);
    }

    .chart-pill {
      font-size: 0.75rem;
      font-weight: 700;
      background: rgba(4, 120, 87, 0.08);
      border: 1px solid rgba(4, 120, 87, 0.25);
      color: var(--emerald-jewel);
      padding: 4px 12px;
      border-radius: var(--radius-full);
      position: relative;
      overflow: hidden;
    }

    .chart-pill::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 50%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.35), transparent);
      animation: shimmerSweep 3s infinite ease-in-out;
    }

    .bars-container {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      height: 160px;
      padding-top: 15px;
      border-bottom: 1px solid rgba(197, 160, 89, 0.2);
      gap: 16px;
    }

    .bar-col {
      display: flex;
      flex-direction: column;
      align-items: center;
      flex: 1;
      height: 100%;
      justify-content: flex-end;
    }

    .bar-fill {
      width: 100%;
      max-width: 44px;
      background: linear-gradient(180deg, #10B981 0%, #047857 100%);
      border-radius: 6px 6px 0 0;
      transition: height 1.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s ease;
      box-shadow: 0 4px 14px rgba(4, 120, 87, 0.25);
      height: 8%;
      position: relative;
      cursor: pointer;
    }

    .bar-fill:hover {
      transform: scaleY(1.05);
      animation: barGlowThrob 1.5s infinite alternate;
      background: linear-gradient(180deg, #34D399 0%, #059669 100%);
    }

    .bar-fill::after {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 6px 6px 0 0;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.4), transparent);
      pointer-events: none;
    }

    .bar-label {
      font-size: 0.76rem;
      color: var(--text-muted);
      margin-top: 10px;
      font-weight: 600;
    }

    /* ==========================================================================
       9. ONBOARDING & ARCHITECTURE (HOW IT WORKS)
       ========================================================================== */
    .steps-container {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 32px;
      position: relative;
    }

    .steps-container::before {
      content: '';
      position: absolute;
      top: 60px;
      left: 15%;
      right: 15%;
      height: 1px;
      background: linear-gradient(90deg, rgba(197, 160, 89, 0.15), rgba(197, 160, 89, 0.6), rgba(197, 160, 89, 0.15));
      z-index: 0;
    }

    .step-card {
      background: #FFFFFF;
      border-radius: var(--radius-lg);
      border: 1px solid rgba(197, 160, 89, 0.25);
      padding: 40px 30px;
      text-align: center;
      position: relative;
      z-index: 1;
      box-shadow: var(--shadow-prestige);
      transition: var(--transition-silk);
    }

    .step-card:hover {
      transform: translateY(-8px);
      border-color: rgba(197, 160, 89, 0.55);
      box-shadow: var(--shadow-elevated), 0 0 20px rgba(197, 160, 89, 0.15);
    }

    .step-number {
      width: 52px;
      height: 52px;
      margin: 0 auto 24px;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
      color: #ffffff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      font-weight: 800;
      box-shadow: 0 4px 16px rgba(4, 120, 87, 0.35);
      border: 2px solid rgba(255, 255, 255, 0.95);
      position: relative;
      transition: transform 0.35s ease;
    }

    .step-card:hover .step-number {
      transform: scale(1.1) rotate(6deg);
    }

    .step-title {
      font-size: 1.25rem;
      font-weight: 700;
      margin-bottom: 14px;
      color: var(--text-espresso);
    }

    .step-desc {
      font-size: 0.94rem;
      color: var(--text-muted);
      line-height: 1.65;
    }

    /* ==========================================================================
       10. LUXURY ENTERPRISE CTA BANNER (SILK CHAMPAGNE & PORCELAIN)
       ========================================================================== */
    .cta-section {
      padding: 120px 0;
    }

    .cta-card {
      background: linear-gradient(135deg, #FFFDF9 0%, #F9F6F0 50%, #F4EFEB 100%);
      border: 1px solid rgba(197, 160, 89, 0.45);
      border-radius: 28px;
      padding: 85px 50px;
      color: var(--text-espresso);
      text-align: center;
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow-elevated);
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }

    .cta-card:hover {
      box-shadow: 0 40px 90px -15px rgba(28, 25, 23, 0.16), 0 0 45px rgba(197, 160, 89, 0.25);
    }

    .cta-mesh-ambient {
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 50% -20%, rgba(197, 160, 89, 0.28) 0%, transparent 68%);
      pointer-events: none;
      animation: floatSlow1 12s ease-in-out infinite alternate;
    }

    .cta-card::after {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(115deg, transparent 42%, rgba(255, 255, 255, 0.5) 50%, transparent 58%);
      animation: shimmerSweep 8s infinite ease-in-out;
      pointer-events: none;
    }

    .cta-inner {
      position: relative;
      z-index: 2;
      max-width: 720px;
      margin: 0 auto;
    }

    .cta-title {
      font-size: 3rem;
      font-weight: 800;
      line-height: 1.15;
      margin-bottom: 20px;
      letter-spacing: -0.035em;
      color: var(--text-espresso);
    }

    .cta-desc {
      font-size: 1.18rem;
      color: var(--text-muted);
      margin-bottom: 40px;
      line-height: 1.65;
    }

    .cta-actions {
      display: flex;
      justify-content: center;
      gap: 18px;
      flex-wrap: wrap;
    }

    /* ==========================================================================
       11. LUXURY LIGHT FOOTER
       ========================================================================== */
    .site-footer {
      background: var(--bg-ivory);
      border-top: 1px solid rgba(197, 160, 89, 0.25);
      padding: 70px 0 40px;
      position: relative;
      z-index: 2;
    }

    .footer-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 44px;
      border-bottom: 1px solid rgba(197, 160, 89, 0.2);
      flex-wrap: wrap;
      gap: 28px;
    }

    .footer-brand {
      max-width: 400px;
    }

    .footer-tagline {
      font-size: 0.92rem;
      color: var(--text-muted);
      margin-top: 10px;
      line-height: 1.6;
    }

    .footer-nav {
      display: flex;
      gap: 28px;
      list-style: none;
      flex-wrap: wrap;
    }

    .footer-link {
      font-size: 0.92rem;
      font-weight: 600;
      color: var(--text-muted);
      transition: color 0.25s ease;
    }

    .footer-link:hover {
      color: var(--emerald-jewel);
    }

    .footer-bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 32px;
      font-size: 0.85rem;
      color: var(--text-muted);
      flex-wrap: wrap;
      gap: 16px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
      .hero-section {
        flex-direction: column;
        text-align: center;
        padding-top: 140px;
      }
      .hero-content {
        max-width: 100%;
        margin-bottom: 30px;
      }
      .hero-actions, .hero-trust-bar {
        justify-content: center;
      }
      .hero-canvas-container {
        position: relative;
        width: 100%;
        height: 480px;
      }
      .features-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      .steps-container {
        grid-template-columns: 1fr;
        gap: 24px;
      }
      .steps-container::before {
        display: none;
      }
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
      }
      .stat-card {
        border-left: none;
        border-bottom: 1px solid rgba(197, 160, 89, 0.25);
      }
    }

    @media (max-width: 768px) {
      .nav-links, .nav-actions .btn-ghost {
        display: none;
      }
      .hero-headline {
        font-size: 2.7rem;
      }
      .hero-canvas-container {
        height: 380px;
      }
      .features-grid {
        grid-template-columns: 1fr;
      }
      .dash-stats-row {
        grid-template-columns: 1fr;
      }
      .dashboard-mockup {
        transform: none !important;
      }
      .cta-title {
        font-size: 2.2rem;
      }
      .cta-card {
        padding: 50px 24px;
      }
      .footer-top {
        flex-direction: column;
        align-items: flex-start;
      }
    }
</style>
</head>
<body>
<!-- Ambient Luxury Lighting Blobs with Smooth Floating Physics -->
<div aria-hidden="true" class="luxury-ambient">
<div class="ambient-radial-top"></div>
<div class="ambient-radial-gold"></div>
<div class="ambient-radial-bottom"></div>
</div>
<!-- ==========================================================================
     SECTION 1: LUXURY NAVBAR
     ========================================================================== -->
<header class="navbar" id="main-navbar">
<div class="container">
<nav class="nav-inner">
<!-- Brand Identity -->
<a aria-label="TindaHub Enterprise Home" class="brand-logo" href="landing.php">
<div class="logo-badge">T</div>
<span>TindaHub</span>
<span class="brand-badge-tag">ALL-IN-ONE STORE SYSTEM · PHILIPPINES</span>
</a>
<!-- Navigation Links -->
<ul class="nav-links">
<li><a class="nav-link" href="#features">Features</a></li>
<li><a class="nav-link" href="#dashboard-preview">Register Screen</a></li>
<li><a class="nav-link" href="#how-it-works">How It Works</a></li>
<li><a class="nav-link" href="stores.php">Stores Network</a></li>
</ul>
<!-- Action Suite -->
<div class="nav-actions">
<a class="btn btn-ghost" href="admin-login.php">Owner / Admin sign in</a>
<a class="btn btn-primary" href="register.php"><span>Get Started Free →</span><svg fill="none" height="15" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" viewbox="0 0 24 24" width="15"><polyline points="9 18 15 12 9 6"></polyline></svg></a>
</div>
</nav>
</div>
</header>
<!-- ==========================================================================
     SECTION 2: 3D EXECUTIVE HERO SECTION
     ========================================================================== -->
<section class="hero-section" id="hero">
<div class="container" style="display: flex; align-items: center; width: 100%;">
<!-- Left Hero Editorial -->
<div class="hero-content">
<div class="luxury-pill-badge"><span class="pulse-ring"></span><span>ALL-IN-ONE STORE SYSTEM · PHILIPPINES</span></div>
<h1 class="hero-headline">Run your store <br/><span class="gold-highlight">smarter, faster, and easier.</span></h1>
<p class="hero-subheadline">Point of sale (POS), real-time inventory tracking, and online customer orders — all in one easy system. Built for sari-sari stores, minimarts, and local retailers across the Philippines.</p>
<div class="hero-actions"><a class="btn btn-primary btn-lg" href="register.php"><span>Get Started Free →</span><svg fill="none" height="18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" viewbox="0 0 24 24" width="18"><line x1="5" x2="19" y1="12" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a><a class="btn btn-luxury-white btn-lg" href="stores.php"><span>View Live Demo</span></a></div>
<div class="hero-trust-bar">
<div class="avatar-stack">
<div class="avatar-pill">MJ</div>
<div class="avatar-pill">RC</div>
<div class="avatar-pill">AL</div>
<div class="avatar-pill">PH</div>
</div>
<div class="trust-text">Trusted by <strong>500+ store owners nationwide</strong>. Runs easily on local XAMPP or live web hosting.</div>
</div>
</div>
</div>
<!-- Right Hero Three.js 3D Interactive Scene -->
<div class="hero-canvas-container" id="canvas-container">
<canvas id="three-hero-canvas"></canvas>
<div class="scene-hint">
<svg fill="none" height="13" stroke="currentColor" stroke-width="2" viewbox="0 0 24 24" width="13"><circle cx="12" cy="12" r="10"></circle><polygon points="12 8 8 12 12 16 16 12 12 8"></polygon></svg>
<span>Interactive Hardware Physics • Tilt to Inspect</span>
</div>
</div>
</section>
<!-- ==========================================================================
     TICKER MARQUEE
     ========================================================================== -->
<div aria-hidden="true" class="marquee-container">
<div class="marquee-track"><div class="marquee-item"><span class="marquee-gem"></span> ⚡ FAST 2-SECOND CHECKOUT</div><div class="marquee-item"><span class="marquee-gem" style="background: var(--emerald-jewel);"></span> 📦 LIVE INVENTORY TRACKING</div><div class="marquee-item"><span class="marquee-gem"></span> 📱 BARCODE SCANNER READY</div><div class="marquee-item"><span class="marquee-gem" style="background: var(--emerald-jewel);"></span> 🛒 ONLINE STORE &amp; FB ORDERS</div><div class="marquee-item"><span class="marquee-gem"></span> 📊 DAILY PROFIT REPORTS</div><div class="marquee-item"><span class="marquee-gem" style="background: var(--emerald-jewel);"></span> ⚡ FAST 2-SECOND CHECKOUT</div><div class="marquee-item"><span class="marquee-gem"></span> 📦 LIVE INVENTORY TRACKING</div><div class="marquee-item"><span class="marquee-gem" style="background: var(--emerald-jewel);"></span> 📱 BARCODE SCANNER READY</div><div class="marquee-item"><span class="marquee-gem"></span> 🛒 ONLINE STORE &amp; FB ORDERS</div><div class="marquee-item"><span class="marquee-gem" style="background: var(--emerald-jewel);"></span> 📊 DAILY PROFIT REPORTS</div></div>
</div>
<!-- ==========================================================================
     SECTION 3: ENTERPRISE FEATURES (WITH 3D TILT EFFECT)
     ========================================================================== -->
<section id="features">
<div class="container">
<div class="section-header reveal"><span class="section-eyebrow">POWERFUL &amp; SIMPLE FEATURES</span><h2 class="section-title">Everything you need to manage your daily sales and inventory</h2><p class="section-description">Stop inventory loss, speed up cashier lines, and track your profits accurately every single day.</p></div>
<div class="features-grid">
<!-- Feature 1 -->
<div class="feature-card reveal tilt-card">
<div class="feature-icon-wrapper">
<svg fill="none" height="26" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="26"><rect height="14" rx="2" width="20" x="2" y="3"></rect><line x1="8" x2="16" y1="21" y2="21"></line><line x1="12" x2="12" y1="17" y2="21"></line><path d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M6 12h12"></path></svg>
</div>
<h3 class="feature-title">Fast Cashier POS</h3>
<p class="feature-text">Ring up items in seconds with barcode auto-lookup, quick change computing, and instant receipt printing.</p>
<span class="feature-tag-badge">FAST CHECKOUT</span>
</div>
<!-- Feature 2 -->
<div class="feature-card reveal tilt-card">
<div class="feature-icon-wrapper">
<svg fill="none" height="26" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="26"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" x2="12" y1="22.08" y2="12"></line></svg>
</div>
<h3 class="feature-title">Real-Time Inventory</h3>
<p class="feature-text">Track bulk cartons, packs, and loose piece sales. Stock automatically deducts the second an item is sold.</p>
<span class="feature-tag-badge">ACCURATE STOCK</span>
</div>
<!-- Feature 3 -->
<div class="feature-card reveal tilt-card">
<div class="feature-icon-wrapper">
<svg fill="none" height="26" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="26"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
</div>
<h3 class="feature-title">Online Storefront</h3>
<p class="feature-text">Share your branded store catalog link on Facebook or Viber. Customers send orders straight to your POS screen.</p>
<span class="feature-tag-badge">ONLINE ORDERS</span>
</div>
<!-- Feature 4 -->
<div class="feature-card reveal tilt-card">
<div class="feature-icon-wrapper">
<svg fill="none" height="26" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="26"><line x1="18" x2="18" y1="20" y2="10"></line><line x1="12" x2="12" y1="20" y2="4"></line><line x1="6" x2="6" y1="20" y2="14"></line></svg>
</div>
<h3 class="feature-title">Daily Profit Reports</h3>
<p class="feature-text">View net profits, best-selling snacks and groceries, and peak sales hours without manual notebook math.</p>
<span class="feature-tag-badge">CLEAR PROFITS</span>
</div>
<!-- Feature 5 -->
<div class="feature-card reveal tilt-card">
<div class="feature-icon-wrapper">
<svg fill="none" height="26" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="26"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
</div>
<h3 class="feature-title">Staff &amp; Cashier Accounts</h3>
<p class="feature-text">Give cashiers their own login for checkout while keeping your cost prices and admin settings strictly private.</p>
<span class="feature-tag-badge">STAFF SECURITY</span>
</div>
<!-- Feature 6 -->
<div class="feature-card reveal tilt-card">
<div class="feature-icon-wrapper">
<svg fill="none" height="26" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewbox="0 0 24 24" width="26"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
</div>
<h3 class="feature-title">Low-Stock Alerts</h3>
<p class="feature-text">Get automatic alerts before staple items run out of stock so your shelves never sit empty.</p>
<span class="feature-tag-badge">RESTOCK ALERTS</span>
</div>
</div>
</div>
</section>
<!-- ==========================================================================
     SECTION 4: MONOLITHIC STATS BAR WITH EASING COUNTERS
     ========================================================================== -->
<section class="stats-section">
<div class="container">
<div class="stats-grid"><div class="stat-card reveal"><div class="stat-number-wrap"><span class="counter" data-target="500">0</span><span class="accent-emerald">+</span></div><div class="stat-label">ACTIVE RETAIL STORES</div></div><div class="stat-card reveal" style="transition-delay: 0.1s;"><div class="stat-number-wrap"><span class="accent-gold">₱</span><span class="counter" data-target="250">0</span><span class="accent-gold">M+</span></div><div class="stat-label">TOTAL SALES PROCESSED</div></div><div class="stat-card reveal" style="transition-delay: 0.2s;"><div class="stat-number-wrap"><span class="counter-decimal" data-target="99.999">0.000</span><span class="accent-emerald">%</span></div><div class="stat-label">SYSTEM UPTIME</div></div><div class="stat-card reveal" style="transition-delay: 0.3s;"><div class="stat-number-wrap"><span class="counter" data-target="0">0</span><span class="accent-gold">₱</span></div><div class="stat-label">SETUP FEE</div></div></div>
</div>
</section>
<!-- ==========================================================================
     SECTION 5: 3D EXECUTIVE TERMINAL PREVIEW WITH DYNAMIC PERSPECTIVE & RADAR BEACON
     ========================================================================== -->
<section class="dashboard-preview-section" id="dashboard-preview">
<div class="container">
<div class="section-header reveal"><span class="section-eyebrow">SIMPLE &amp; CLEAN INTERFACE</span><h2 class="section-title">A clean register screen built for busy rush hours</h2><p class="section-description">A clear, distraction-free screen that helps cashiers serve customers fast without confusing menus.</p></div>
<div class="dashboard-stage reveal">
<div class="dashboard-mockup" id="mockup3d">
<!-- Terminal Window Bar -->
<div class="dash-topbar"><div class="dash-controls"><span class="dash-dot red"></span><span class="dash-dot yellow"></span><span class="dash-dot green"></span></div><div class="dash-title-tab" style="font-family: 'Space Grotesk', monospace; letter-spacing: 0.04em;">tindahub.local/pos — Store Branch 01</div><div style="font-size: 0.74rem; color: var(--gold-hallmark); font-weight: 800; display: flex; align-items: center; gap: 8px; letter-spacing: 0.08em;"><span class="beacon-dot"></span>ONLINE &amp; CONNECTED</div></div>
<!-- Terminal Content Body -->
<div class="dash-body">
<div class="dash-stats-row"><div class="dash-mini-card"><div class="dash-mini-label" style="color: var(--gold-hallmark);">TODAY'S TOTAL SALES</div><div class="dash-mini-val"><span class="currency">₱</span>148,920.00</div><div class="dash-trend"><svg fill="none" height="12" stroke="currentColor" stroke-width="3" viewbox="0 0 24 24" width="12"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg><span>+38.4% vs last week</span></div></div><div class="dash-mini-card"><div class="dash-mini-label">COMPLETED TRANSACTIONS</div><div class="dash-mini-val">1,148</div><div class="dash-trend"><span style="color: var(--text-muted);">Average basket: ₱342.80</span></div></div><div class="dash-mini-card"><div class="dash-mini-label" style="color: var(--gold-hallmark);">LOW STOCK WARNING</div><div class="dash-mini-val" style="color: var(--gold-hallmark);">2 <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Items to Restock</span></div><div class="dash-trend" style="color: var(--gold-hallmark);"><span>Instant Noodles, 3-in-1 Coffee</span></div></div></div>
<!-- Volume Chart with Dynamic Sequential Heights -->
<div class="dash-chart-section">
<div class="chart-header">
<div>
<h4 class="chart-title">Hourly Sales &amp; Customer Rush (Live)</h4>
<div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Track your store's busiest peak hours throughout the day</div>
</div>
<span class="chart-pill">Peak Hours: 5:30 PM - 8:00 PM</span>
</div>
<div class="bars-container" id="barChartContainer">
<div class="bar-col">
<div class="bar-fill" data-height="38%"></div>
<span class="bar-label">07:00</span>
</div>
<div class="bar-col">
<div class="bar-fill" data-height="56%"></div>
<span class="bar-label">09:00</span>
</div>
<div class="bar-col">
<div class="bar-fill" data-height="74%"></div>
<span class="bar-label">12:00</span>
</div>
<div class="bar-col">
<div class="bar-fill" data-height="48%"></div>
<span class="bar-label">15:00</span>
</div>
<div class="bar-col">
<div class="bar-fill" data-height="98%"></div>
<span class="bar-label">18:00</span>
</div>
<div class="bar-col">
<div class="bar-fill" data-height="86%"></div>
<span class="bar-label">20:00</span>
</div>
<div class="bar-col">
<div class="bar-fill" data-height="44%"></div>
<span class="bar-label">22:00</span>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- ==========================================================================
     SECTION 6: HOW IT WORKS ARCHITECTURE
     ========================================================================== -->
<section id="how-it-works">
<div class="container">
<div class="section-header reveal">
<span class="section-eyebrow">EASY 3-STEP SETUP</span>
<h2 class="section-title">Up and running in less than 5 minutes</h2>
<p class="section-description">No complicated hardware needed. Works smoothly on any phone, tablet, laptop, or desktop computer.</p>
</div>
<div class="steps-container">
<div class="step-card reveal tilt-card">
<div class="step-number">1</div>
<h3 class="step-title">Create Store Account</h3>
<p class="step-desc">Register your store on register.php with your city location and create your owner PIN.</p>
</div>
<div class="step-card reveal tilt-card" style="transition-delay: 0.15s;">
<div class="step-number">2</div>
<h3 class="step-title">Add Your Products</h3>
<p class="step-desc">Select from 1,000+ popular Philippine grocery items or add your own products with custom prices.</p>
</div>
<div class="step-card reveal tilt-card" style="transition-delay: 0.3s;">
<div class="step-number">3</div>
<h3 class="step-title">Start Selling</h3>
<p class="step-desc">Scan barcodes, ring up sales, print receipts, and check your real-time daily profit from anywhere.</p>
</div>
</div>
</div>
</section>
<!-- ==========================================================================
     SECTION 7: LUXURY CTA BANNER
     ========================================================================== -->
<section class="cta-section">
<div class="container">
<div class="cta-card reveal">
<div class="cta-mesh-ambient"></div>
<div class="cta-inner"><div style="display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(197, 160, 89, 0.4); background: rgba(197, 160, 89, 0.1); padding: 5px 16px; border-radius: 9999px; margin-bottom: 22px; font-size: 0.76rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.18em; color: var(--gold-hallmark);"><span>START TODAY</span></div><h2 class="cta-title">Ready to grow your retail store?</h2><p class="cta-desc">Join hundreds of Filipino store owners saving hours every week on inventory, accounting, and cashier checkouts.</p><div class="cta-actions"><a class="btn btn-primary btn-lg" href="register.php"><span>Get Started Free →</span><svg fill="none" height="18" stroke="currentColor" stroke-width="2.5" viewbox="0 0 24 24" width="18"><line x1="5" x2="19" y1="12" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a><a class="btn btn-luxury-white btn-lg" href="stores.php"><span>Explore Demo Store</span></a></div></div>
</div>
</div>
</section>
<!-- ==========================================================================
     SECTION 8: LUXURY FOOTER
     ========================================================================== -->
<footer class="site-footer">
<div class="container">
<div class="footer-top">
<div class="footer-brand">
<a class="brand-logo" href="landing.php">
<div class="logo-badge">T</div>
<span>TindaHub</span>
</a>
<p class="footer-tagline">TindaHub — Simple, fast POS and inventory system for Philippine retail stores and minimarts.</p>
</div>
<ul class="footer-nav">
<li><a class="footer-link" href="register.php">Register Store</a></li>
<li><a class="footer-link" href="admin-login.php">Owner / Admin Sign In</a></li>
<li><a class="footer-link" href="stores.php">Browse Stores</a></li>
<li><a class="footer-link" href="#features">Features</a></li>
<li><a class="footer-link" href="#how-it-works">How It Works</a></li>
</ul>
</div>
<div class="footer-bottom"><div>© 2025 TindaHub Philippines. Simple, fast POS &amp; inventory system for everyday retail stores.</div><div style="display: flex; gap: 16px; align-items: center;"><span style="color: var(--gold-hallmark); font-weight: 700; letter-spacing: 0.06em;">Compatible with XAMPP / Apache</span><span>•</span><span style="color: var(--emerald-jewel); font-weight: 600;">Fast, Lightweight &amp; Reliable</span></div></div>
</div>
</footer>
<!-- ==========================================================================
     INTERACTIVITY, THREE.JS 3D LUXURY SCENE & SMOOTH PHYSICS ENGINE
     ========================================================================== -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1. Navbar Glass Blur Scroll Effect
    const navbar = document.getElementById('main-navbar');
    const onScroll = () => {
      if (window.scrollY > 20) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // 2. Scroll Reveal System with Staggering
    const revealElements = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('active');
            obs.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12 });
      revealElements.forEach(el => observer.observe(el));
    } else {
      revealElements.forEach(el => el.classList.add('active'));
    }

    // 3. Number Counter Animation with Cubic Easing
    const counters = document.querySelectorAll('.counter');
    const decimalCounters = document.querySelectorAll('.counter-decimal');
    let countersAnimated = false;
    
    const runCounters = () => {
      if (countersAnimated) return;
      countersAnimated = true;
      
      counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        if (target === 0) {
          counter.innerText = '0';
          return;
        }
        const duration = 1800;
        const start = performance.now();
        const update = (now) => {
          const progress = Math.min((now - start) / duration, 1);
          const ease = 1 - Math.pow(1 - progress, 3);
          counter.innerText = Math.floor(ease * target);
          if (progress < 1) {
            requestAnimationFrame(update);
          } else {
            counter.innerText = target;
          }
        };
        requestAnimationFrame(update);
      });

      decimalCounters.forEach(counter => {
        const target = parseFloat(counter.getAttribute('data-target')) || 99.999;
        const duration = 2000;
        const start = performance.now();
        const update = (now) => {
          const progress = Math.min((now - start) / duration, 1);
          const ease = 1 - Math.pow(1 - progress, 3);
          const current = (ease * target).toFixed(3);
          counter.innerText = current;
          if (progress < 1) {
            requestAnimationFrame(update);
          } else {
            counter.innerText = target.toFixed(3);
          }
        };
        requestAnimationFrame(update);
      });
    };

    const statsSec = document.querySelector('.stats-section');
    if (statsSec && 'IntersectionObserver' in window) {
      new IntersectionObserver((entries, obs) => {
        entries.forEach(e => {
          if (e.isIntersecting) {
            runCounters();
            obs.unobserve(e.target);
          }
        });
      }, { threshold: 0.25 }).observe(statsSec);
    } else {
      runCounters();
    }

    // 4. Bar Chart Trigger with Continuous Subtle Pulsing
    const chartBars = document.querySelectorAll('.bar-fill');
    let chartAnimated = false;
    const triggerChart = () => {
      if (chartAnimated) return;
      chartAnimated = true;
      chartBars.forEach((b, i) => {
        setTimeout(() => {
          const h = b.getAttribute('data-height');
          if (h) b.style.height = h;
        }, i * 110);
      });
    };

    const chartCont = document.getElementById('barChartContainer');
    if (chartCont && 'IntersectionObserver' in window) {
      new IntersectionObserver((entries, obs) => {
        entries.forEach(e => {
          if (e.isIntersecting) {
            triggerChart();
            obs.unobserve(e.target);
          }
        });
      }, { threshold: 0.2 }).observe(chartCont);
    } else {
      triggerChart();
    }

    // 5. Interactive 3D Perspective Card Tilt on Mouse Move
    const tiltCards = document.querySelectorAll('.tilt-card, .feature-card');
    tiltCards.forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        card.style.setProperty('--mouse-x', `${x}px`);
        card.style.setProperty('--mouse-y', `${y}px`);

        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = ((y - centerY) / centerY) * -7;
        const rotateY = ((x - centerX) / centerX) * 7;

        card.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translate3d(0, -6px, 10px)`;
      });

      card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translate3d(0, 0, 0)';
      });
    });

    // 6. Interactive Executive Cockpit Perspective Tilt
    const cockpit = document.getElementById('mockup3d');
    if (cockpit) {
      cockpit.addEventListener('mousemove', (e) => {
        const rect = cockpit.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = ((y - centerY) / centerY) * -6 + 4;
        const rotateY = ((x - centerX) / centerX) * 6 - 2;

        cockpit.style.transform = `perspective(1600px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) scale(1.015)`;
      });

      cockpit.addEventListener('mouseleave', () => {
        cockpit.style.transform = 'rotateX(8deg) rotateY(-3deg) rotateZ(1deg)';
      });
    }

    // 7. Init Three.js Luxury 3D Scene in Hero Section
    initLuxury3DScene();
  });

  /**
   * Three.js Interactive Luxury Hardware, Coins & Specular Gold Particles
   */
  function initLuxury3DScene() {
    const container = document.getElementById('canvas-container');
    const canvas = document.getElementById('three-hero-canvas');
    if (!container || !canvas || typeof THREE === 'undefined') return;

    try {
      const scene = new THREE.Scene();
      
      let width = container.clientWidth || (window.innerWidth > 1024 ? window.innerWidth * 0.54 : window.innerWidth);
      let height = container.clientHeight || 580;

      const camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 100);
      camera.position.set(0, 1.0, 9.2);

      const renderer = new THREE.WebGLRenderer({
        canvas: canvas,
        alpha: true,
        antialias: true,
        powerPreference: 'high-performance'
      });
      renderer.setSize(width, height);
      renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

      // Luminous Lighting Array
      const ambientLight = new THREE.AmbientLight(0xFFFDF9, 2.2);
      scene.add(ambientLight);

      // Key light with warm champagne specular
      const dirLight = new THREE.DirectionalLight(0xFFFFFF, 2.6);
      dirLight.position.set(7, 14, 9);
      scene.add(dirLight);

      // Emerald Jewel Accent Rim Light
      const emeraldRim = new THREE.PointLight(0x059669, 3.2, 18);
      emeraldRim.position.set(-4, 3, 4);
      scene.add(emeraldRim);

      // Warm Champagne Gold Rim Light
      const goldRim = new THREE.PointLight(0xD4AF37, 3.6, 16);
      goldRim.position.set(4.5, -2, 4.5);
      scene.add(goldRim);

      // Materials: Pure Ceramic White, Satin Champagne Gold, Jewel Emerald
      const ceramicMat = new THREE.MeshStandardMaterial({
        color: 0xFFFFFF,
        roughness: 0.14,
        metalness: 0.18
      });

      const ivoryMetalMat = new THREE.MeshStandardMaterial({
        color: 0xF4EFEB,
        roughness: 0.22,
        metalness: 0.65
      });

      const jewelEmeraldMat = new THREE.MeshStandardMaterial({
        color: 0x059669,
        roughness: 0.12,
        metalness: 0.75,
        emissive: 0x047857,
        emissiveIntensity: 0.35
      });

      const screenGlassMat = new THREE.MeshStandardMaterial({
        color: 0xFFFFFF,
        roughness: 0.08,
        metalness: 0.3,
        emissive: 0xF9F6F0,
        emissiveIntensity: 0.25
      });

      const polishedGoldMat = new THREE.MeshStandardMaterial({
        color: 0xD4AF37,
        roughness: 0.16,
        metalness: 0.96
      });

      // Master Root Group with physics damping
      const masterGroup = new THREE.Group();
      scene.add(masterGroup);

      const floaters = [];
      function registerFloater(mesh, speed, amplitude, rx, ry, rz) {
        mesh.userData = {
          baseY: mesh.position.y,
          speed, amplitude, rx, ry, rz,
          offset: Math.random() * Math.PI * 2
        };
        floaters.push(mesh);
        masterGroup.add(mesh);
      }

      // ==========================================
      // MODEL 1: LUXURY SMART TERMINAL & REGISTER (CERAMIC & GOLD)
      // ==========================================
      const terminalGroup = new THREE.Group();

      // Precision Ivory Stand Base
      const standBase = new THREE.Mesh(new THREE.CylinderGeometry(1.25, 1.45, 0.14, 36), ivoryMetalMat);
      standBase.position.y = -0.55;
      terminalGroup.add(standBase);

      // Stand Base Gold Accent Ring
      const standGoldRing = new THREE.Mesh(new THREE.TorusGeometry(1.35, 0.03, 16, 48), polishedGoldMat);
      standGoldRing.position.y = -0.5;
      standGoldRing.rotation.x = Math.PI / 2;
      terminalGroup.add(standGoldRing);

      // Cantilever Arm (Polished Gold Accent)
      const arm = new THREE.Mesh(new THREE.BoxGeometry(0.26, 1.35, 0.38), polishedGoldMat);
      arm.position.set(0, 0.16, -0.2);
      arm.rotation.x = -0.2;
      terminalGroup.add(arm);

      // Main Ceramic White Display Frame
      const displayFrame = new THREE.Mesh(new THREE.BoxGeometry(2.7, 1.75, 0.13), ceramicMat);
      displayFrame.position.set(0, 0.95, 0.1);
      displayFrame.rotation.x = -0.18;
      terminalGroup.add(displayFrame);

      // Pristine Display Glass
      const displayGlass = new THREE.Mesh(new THREE.BoxGeometry(2.52, 1.58, 0.04), screenGlassMat);
      displayGlass.position.set(0, 0.95, 0.17);
      displayGlass.rotation.x = -0.18;
      terminalGroup.add(displayGlass);

      // Bezel Rim (Gold Trim)
      const bezelRim = new THREE.Mesh(new THREE.BoxGeometry(2.72, 0.04, 0.15), polishedGoldMat);
      bezelRim.position.set(0, 0.07, 0.28);
      terminalGroup.add(bezelRim);

      // Contactless Card Reader Puck
      const nfcPuck = new THREE.Mesh(new THREE.CylinderGeometry(0.4, 0.4, 0.1, 28), ceramicMat);
      nfcPuck.position.set(1.45, 0.3, 0.6);
      nfcPuck.rotation.x = 0.2;
      terminalGroup.add(nfcPuck);

      const nfcRing = new THREE.Mesh(new THREE.TorusGeometry(0.29, 0.026, 14, 36), jewelEmeraldMat);
      nfcRing.position.set(1.45, 0.35, 0.6);
      nfcRing.rotation.x = Math.PI / 2.3;
      terminalGroup.add(nfcRing);

      terminalGroup.position.set(0.4, 0.05, 0.2);
      terminalGroup.rotation.set(0.1, -0.36, 0.04);
      registerFloater(terminalGroup, 1.2, 0.18, 0.0006, 0.0018, 0.0005);

      // ==========================================
      // MODEL 2: CHAMPAGNE GOLD PESO COINS
      // ==========================================
      const coinGeom = new THREE.CylinderGeometry(0.48, 0.48, 0.09, 36);

      const coin1 = new THREE.Mesh(coinGeom, polishedGoldMat);
      coin1.position.set(-1.85, 1.45, 1.5);
      coin1.rotation.set(Math.PI / 3, 0.4, 0.7);
      registerFloater(coin1, 1.9, 0.26, 0.012, 0.016, 0.008);

      const coin2 = new THREE.Mesh(coinGeom, polishedGoldMat);
      coin2.position.set(2.7, -0.6, 1.15);
      coin2.rotation.set(Math.PI / 4, -0.6, 0.3);
      coin2.scale.set(0.85, 0.85, 0.85);
      registerFloater(coin2, 1.6, 0.22, -0.01, 0.014, 0.007);

      const coin3 = new THREE.Mesh(coinGeom, polishedGoldMat);
      coin3.position.set(1.7, 2.3, 0.55);
      coin3.rotation.set(Math.PI / 2.6, 0.5, -0.4);
      coin3.scale.set(0.74, 0.74, 0.74);
      registerFloater(coin3, 2.2, 0.24, 0.014, -0.012, 0.009);

      // ==========================================
      // MODEL 3: FLOATING CARDS & JEWEL PRISMS
      // ==========================================
      const chipCardGeom = new THREE.BoxGeometry(1.65, 1.05, 0.04);
      const chipCard = new THREE.Mesh(chipCardGeom, ceramicMat);
      chipCard.position.set(-2.45, -0.85, 0.65);
      chipCard.rotation.set(-0.2, 0.6, -0.15);
      
      const chip = new THREE.Mesh(new THREE.BoxGeometry(0.32, 0.25, 0.06), polishedGoldMat);
      chip.position.set(-0.35, 0, 0.02);
      chipCard.add(chip);
      registerFloater(chipCard, 1.5, 0.2, 0.003, 0.006, -0.003);

      const prismGeom = new THREE.CylinderGeometry(0.44, 0.44, 0.88, 6);
      const prism = new THREE.Mesh(prismGeom, jewelEmeraldMat);
      prism.position.set(-2.25, 1.85, -0.45);
      prism.rotation.set(0.4, 0.5, 0.3);
      registerFloater(prism, 1.4, 0.22, 0.006, 0.009, 0.005);

      const cubeGeom = new THREE.BoxGeometry(0.92, 0.92, 0.92);
      const cube = new THREE.Mesh(cubeGeom, ivoryMetalMat);
      cube.position.set(2.85, 1.35, -0.75);
      cube.rotation.set(0.4, -0.4, 0.25);
      registerFloater(cube, 1.3, 0.2, 0.004, -0.006, 0.003);

      // ==========================================
      // PARTICLES: CHAMPAGNE & EMERALD SPECULAR MOTES
      // ==========================================
      const particleCount = 55;
      const partGeom = new THREE.SphereGeometry(0.048, 8, 8);
      const partMat = new THREE.MeshBasicMaterial({
        color: 0xC5A059,
        transparent: true,
        opacity: 0.68
      });

      const particles = [];
      for (let i = 0; i < particleCount; i++) {
        const p = new THREE.Mesh(partGeom, partMat);
        p.position.set(
          (Math.random() - 0.5) * 13,
          (Math.random() - 0.5) * 9.5,
          (Math.random() - 0.5) * 6.5
        );
        p.userData = {
          speedY: 0.003 + Math.random() * 0.006,
          sway: Math.random() * Math.PI * 2,
          swaySpeed: 0.01 + Math.random() * 0.02
        };
        masterGroup.add(p);
        particles.push(p);
      }

      // Parallax Mouse Interaction
      const targetRot = { x: 0, y: 0 };
      const currentRot = { x: 0, y: 0 };

      window.addEventListener('mousemove', (e) => {
        const mx = (e.clientX / window.innerWidth) * 2 - 1;
        const my = -(e.clientY / window.innerHeight) * 2 + 1;
        targetRot.y = mx * 0.38;
        targetRot.x = -my * 0.26;
      }, { passive: true });

      // Resize Listener
      const onResize = () => {
        const nw = container.clientWidth || (window.innerWidth > 1024 ? window.innerWidth * 0.54 : window.innerWidth);
        const nh = container.clientHeight || 580;
        camera.aspect = nw / nh;
        camera.updateProjectionMatrix();
        renderer.setSize(nw, nh);
      };
      window.addEventListener('resize', onResize);

      // Animation Loop with Smooth Levitation & Particle Drift
      const clock = new THREE.Clock();
      function renderLoop() {
        requestAnimationFrame(renderLoop);
        const t = clock.getElapsedTime();

        // Parallax damping
        currentRot.x += (targetRot.x - currentRot.x) * 0.055;
        currentRot.y += (targetRot.y - currentRot.y) * 0.055;
        masterGroup.rotation.x = currentRot.x;
        masterGroup.rotation.y = currentRot.y;

        // Floating meshes
        floaters.forEach(obj => {
          const u = obj.userData;
          obj.position.y = u.baseY + Math.sin(t * u.speed + u.offset) * u.amplitude;
          obj.rotation.x += u.rx;
          obj.rotation.y += u.ry;
          obj.rotation.z += u.rz;
        });

        // Dust particle rise & sway
        particles.forEach(p => {
          p.position.y += p.userData.speedY;
          p.position.x += Math.sin(t * p.userData.swaySpeed + p.userData.sway) * 0.002;
          if (p.position.y > 4.8) p.position.y = -4.8;
        });

        renderer.render(scene, camera);
      }
      renderLoop();
    } catch (err) {
      console.warn('Three.js scene error:', err);
    }
    
  }
</script>
</body></html>