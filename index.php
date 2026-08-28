<?php
/* Bewertungen zentral aus daten/config.json laden. */
require_once __DIR__ . '/includes/buero-lib.php';
$ohRev = function_exists('oh_google_reviews') ? oh_google_reviews() : ['rating'=>5.0,'count'=>31];
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Elektriker in N&uuml;rnberg f&uuml;r Elektroinstallation, Altbausanierung, Smart Home, PV &amp; Wallbox. 5,0 Sterne bei Google. Jetzt Anfrage stellen.">
<meta name="robots" content="index, follow">
<meta name="theme-color" content="#0A0A0A">
<link rel="canonical" href="https://oh-haustechnik.de/">
<meta property="og:type" content="website">
<meta property="og:url" content="https://oh-haustechnik.de/">
<meta property="og:title" content="Elektriker N&uuml;rnberg &ndash; Elektroinstallation &amp; Altbausanierung">
<meta property="og:description" content="Elektroinstallation, Altbausanierung, Smart Home, PV &amp; Wallbox im Raum N&uuml;rnberg.">
<meta property="og:image" content="https://oh-haustechnik.de/assets/img/lp/poster.jpg">
<meta property="og:locale" content="de_DE">
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" type="image/png" sizes="48x48" href="/favicon-48.png">
<link rel="icon" type="image/png" sizes="192x192" href="/favicon-192.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="preload" as="image" href="assets/img/lp/poster.jpg" fetchpriority="high">
<script type="application/ld+json">{"@context":"https://schema.org","@type":"Electrician","name":"OH Haustechnik","image":"https://oh-haustechnik.de/assets/img/lp/poster.jpg","logo":{"@type":"ImageObject","url":"https://oh-haustechnik.de/assets/img/logo-google.png","width":512,"height":512},"@id":"https://oh-haustechnik.de/#business","url":"https://oh-haustechnik.de/","telephone":"+491757481006","email":"oh.Haustechnik@gmail.com","priceRange":"\u20ac\u20ac","founder":{"@type":"Person","name":"Onur-Can Hezer"},"address":{"@type":"PostalAddress","streetAddress":"Dianastra\u00dfe 62","postalCode":"90441","addressLocality":"N\u00fcrnberg","addressRegion":"Bayern","addressCountry":"DE"},"geo":{"@type":"GeoCoordinates","latitude":49.4521,"longitude":11.0767},"openingHoursSpecification":[{"@type":"OpeningHoursSpecification","dayOfWeek":["Monday","Tuesday","Wednesday","Thursday","Friday"],"opens":"07:00","closes":"19:00"}],"areaServed":[{"@type":"City","name":"N\u00fcrnberg"},{"@type":"City","name":"F\u00fcrth"},{"@type":"City","name":"Erlangen"},{"@type":"City","name":"Schwabach"},{"@type":"City","name":"Wendelstein"},{"@type":"City","name":"Zirndorf"},{"@type":"City","name":"Stein"},{"@type":"City","name":"Oberasbach"},{"@type":"City","name":"Feucht"},{"@type":"City","name":"Roth"},{"@type":"City","name":"Lauf an der Pegnitz"},{"@type":"City","name":"Altdorf bei N\u00fcrnberg"},{"@type":"City","name":"Schwaig bei N\u00fcrnberg"},{"@type":"City","name":"R\u00f6thenbach an der Pegnitz"},{"@type":"City","name":"Hersbruck"},{"@type":"City","name":"Herzogenaurach"},{"@type":"City","name":"Cadolzburg"},{"@type":"City","name":"Heroldsberg"},{"@type":"City","name":"Burgthann"},{"@type":"City","name":"Schwarzenbruck"},{"@type":"City","name":"Rednitzhembach"},{"@type":"City","name":"Allersberg"}],"knowsAbout":["Elektroinstallation","Altbausanierung","Smart Home","KNX","Loxone","Photovoltaik","Wallbox","E-Check","Wallbox-Installation","Wallbox-Anmeldung beim Netzbetreiber","PV-Überschussladen","SMA Sunny Home Manager","Zählerschrank erneuern","Unterverteilung","Ladeinfrastruktur","Elektroprüfung nach DIN VDE 0100-600","Steuerbare Verbrauchseinrichtungen nach § 14a EnWG"],"hasOfferCatalog":{"@type":"OfferCatalog","name":"Elektro-Leistungen","itemListElement":[{"@type":"Offer","itemOffered":{"@type":"Service","name":"Elektroinstallation","url":"https://oh-haustechnik.de/leistungen/elektroinstallation.php"}},{"@type":"Offer","itemOffered":{"@type":"Service","name":"Elektro-Altbausanierung","url":"https://oh-haustechnik.de/altbausanierung-nuernberg.php"}},{"@type":"Offer","itemOffered":{"@type":"Service","name":"Smart Home KNX & Loxone","url":"https://oh-haustechnik.de/smart-home-knx-loxone-nuernberg.php"}},{"@type":"Offer","itemOffered":{"@type":"Service","name":"Photovoltaik-Installation","url":"https://oh-haustechnik.de/photovoltaik-nuernberg.php"}},{"@type":"Offer","itemOffered":{"@type":"Service","name":"E-Check Elektropr\u00fcfung","url":"https://oh-haustechnik.de/e-check-nuernberg.php"}},{"@type":"Offer","itemOffered":{"@type":"Service","name":"Kundendienst und Fehlersuche","url":"https://oh-haustechnik.de/kundendienst-fehlersuche-nuernberg.php"}},{"@type":"Offer","itemOffered":{"@type":"Service","name":"Sicherheit und Schutztechnik","url":"https://oh-haustechnik.de/leistungen/schutztechnik.php"}},{"@type":"Offer","itemOffered":{"@type":"Service","name":"Netzwerkverkabelung","url":"https://oh-haustechnik.de/leistungen/netzwerkverkabelung.php"}}]},"aggregateRating":{"@type":"AggregateRating","ratingValue":"<?= number_format($ohRev['rating'],1,'.','') ?>","reviewCount":"<?= (int)$ohRev['count'] ?>"}}</script>
<title>Elektriker Nürnberg – Elektroinstallation | OH Haustechnik</title>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-17801418796"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'AW-17801418796');
  gtag('config', 'G-004VQKCXXC');
</script>
<script>window.OH_ADS_CONV = {"lead_form_submit":"sMAOCPTShb8cEKywsKhC","phone_click":"WVjGCJyxmeMcEKywsKhC"};</script>
<script defer src="assets/js/oh-track.js"></script>
<link rel="stylesheet" href="assets/css/funnel.css">
<link rel="stylesheet" href="assets/css/funnel-dark.css">
<style>
/* ---------- Fonts (lokal eingebettet, DSGVO-konform) ---------- */
@font-face{font-family:'Anton';font-style:normal;font-weight:400;font-display:swap;src:url(assets/fonts/anton.woff2) format('woff2');}
@font-face{font-family:'Inter';font-style:normal;font-weight:400;font-display:swap;src:url(assets/fonts/inter400.woff2) format('woff2');}
@font-face{font-family:'Inter';font-style:normal;font-weight:600;font-display:swap;src:url(assets/fonts/inter600.woff2) format('woff2');}
@font-face{font-family:'Inter';font-style:normal;font-weight:700;font-display:swap;src:url(assets/fonts/inter700.woff2) format('woff2');}

/* ---------- Tokens ---------- */
:root{
  --bg:#0A0A0A; --bg2:#111111; --panel:#1A1A1A;
  --yellow:#6E9BE0; --blue:#325AA0; --ink:#F5F5F5; --grey:#9CA3AF;
  --line:rgba(255,255,255,.08); --line2:rgba(255,255,255,.15);
  --head:'Anton',Impact,'Arial Narrow',system-ui,sans-serif;
  --body:'Inter',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
  --wrap:1200px;
}
*{box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{margin:0;background:var(--bg);color:var(--ink);font-family:var(--body);font-size:17px;line-height:1.6;-webkit-font-smoothing:antialiased;}
img{max-width:100%;display:block;}
a{color:inherit;text-decoration:none;}
h1,h2,h3{font-family:var(--head);text-transform:uppercase;line-height:.98;letter-spacing:0;font-weight:400;margin:0;}
.wrap{max-width:var(--wrap);margin:0 auto;padding:0 22px;}
.yellow{color:var(--yellow);}
.eyebrow{font-family:var(--body);font-weight:700;font-size:12.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--yellow);}
.muted{color:var(--grey);}
section{padding:96px 0;}
@media(max-width:720px){section{padding:56px 0;}body{font-size:16px;}}

/* ---------- Buttons ---------- */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-height:52px;padding:14px 26px;border-radius:10px;
  font-family:var(--body);font-weight:700;font-size:15px;letter-spacing:.03em;text-transform:uppercase;cursor:pointer;border:1px solid transparent;transition:all .2s ease;}
.btn-y{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.45);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);}
.btn-y:hover{background:rgba(255,255,255,.22);border-color:#fff;transform:translateY(-2px);box-shadow:0 12px 34px rgba(0,0,0,.4);}
.btn-ghost{background:rgba(255,255,255,.05);color:var(--ink);border-color:rgba(255,255,255,.22);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);}
.btn-ghost:hover{background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.6);color:#fff;transform:translateY(-2px);}

/* ---------- Header ---------- */
header{position:fixed;top:0;left:0;right:0;z-index:50;transition:background .3s ease,border-color .3s ease;border-bottom:1px solid transparent;}
header.scrolled{background:rgba(10,10,10,.94);backdrop-filter:blur(10px);border-bottom-color:var(--line);}
.nav{display:flex;align-items:center;gap:26px;height:104px;}
.nav .logo{height:80px;width:auto;filter:drop-shadow(0 2px 12px rgba(0,0,0,.7));}
.nav .links{display:flex;gap:26px;margin-left:auto;}
.nav .links a{font-size:13.5px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--grey);transition:color .2s;}
.nav .links a:hover{color:var(--ink);}
.nav .call{min-height:44px;padding:11px 20px;font-size:13.5px;}
.hamburger{display:none;}
@media(max-width:900px){
  .nav .links{display:none;}
  .nav{height:78px;gap:14px;}
  .nav .logo{height:60px;}
}

/* ---------- Hero ---------- */
.hero{position:relative;min-height:100svh;display:flex;align-items:center;padding-top:104px;overflow:hidden;}
.hero-bg{position:absolute;inset:0;z-index:0;}
.hero-bg img,.hero-bg video{width:100%;height:100%;object-fit:cover;}
.hero-bg::after{content:"";position:absolute;inset:0;
  background:linear-gradient(90deg,rgba(10,10,10,.94) 0%,rgba(10,10,10,.82) 45%,rgba(10,10,10,.55) 100%),linear-gradient(0deg,var(--bg),transparent 55%);}
.hero-in{position:relative;z-index:1;max-width:760px;padding:40px 0;}
.hero h1{font-size:clamp(44px,8.5vw,104px);margin:16px 0 0;text-wrap:balance;}
.hero .sub{margin:22px 0 30px;font-size:clamp(16px,2.4vw,20px);color:#d7d9de;max-width:560px;}
.hero .ctas{display:flex;gap:14px;flex-wrap:wrap;}
.trust{display:flex;align-items:center;gap:11px;margin-top:26px;font-size:14.5px;color:var(--grey);}
.trust .stars{color:#FFC107;letter-spacing:2px;}
.trust b{color:var(--ink);font-weight:600;}
.gg{width:20px;height:20px;flex:none;}

/* ---------- Section head ---------- */
.shead{max-width:720px;margin-bottom:44px;}
.shead h2{font-size:clamp(30px,5vw,52px);margin-top:10px;text-wrap:balance;}
.shead p{color:var(--grey);margin:14px 0 0;}

/* ---------- Leistungen ---------- */
.alt-bg{background:var(--bg2);}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
@media(max-width:900px){.grid{grid-template-columns:1fr 1fr;}}
@media(max-width:600px){.grid{grid-template-columns:1fr;}}
.card{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:26px 24px 28px;transition:all .2s ease;}
.card:hover{border-color:var(--yellow);transform:translateY(-3px);}
.card .n{font-family:var(--head);font-size:30px;color:var(--yellow);line-height:1;}
.card h3{font-size:19px;margin:14px 0 8px;letter-spacing:.01em;}
.card p{margin:0;font-size:15px;color:var(--grey);line-height:1.55;}
.card a.more{display:inline-block;margin-top:14px;font-size:13.5px;font-weight:700;color:var(--yellow);text-transform:uppercase;letter-spacing:.04em;}
.card a.more:hover{color:var(--ink);}

/* ---------- Altbau ---------- */
.altbau{display:grid;grid-template-columns:1.05fr .95fr;gap:54px;align-items:center;}
@media(max-width:900px){.altbau{grid-template-columns:1fr;gap:32px;}}
.altbau h2{font-size:clamp(30px,5vw,52px);}
.altbau .lead{color:#d7d9de;margin:18px 0 22px;}
.checks{list-style:none;padding:0;margin:0 0 28px;display:grid;gap:12px;}
.checks li{display:flex;gap:12px;font-size:15.5px;color:#cfd2d8;}
.checks .mk{color:var(--yellow);font-weight:700;flex:none;}
.altbau-photo{border:1px solid var(--line);border-radius:16px;overflow:hidden;aspect-ratio:4/5;}
.altbau-photo img{width:100%;height:100%;object-fit:cover;}

/* ---------- Referenzen ---------- */
.refs{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
@media(max-width:900px){.refs{grid-template-columns:1fr;}}
.ref{position:relative;border-radius:14px;overflow:hidden;aspect-ratio:4/3;border:1px solid var(--line);}
.ref img{width:100%;height:100%;object-fit:cover;transition:transform .4s ease;}
.ref:hover img{transform:scale(1.05);}
.ref .cap{position:absolute;inset:auto 0 0 0;padding:18px 18px 16px;
  background:linear-gradient(0deg,rgba(10,10,10,.9),transparent);}
.ref .cap span{font-family:var(--head);text-transform:uppercase;font-size:18px;letter-spacing:.01em;}
.ph-note{margin-top:16px;font-size:13px;color:var(--grey);}

/* ---------- Ablauf ---------- */
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;}
@media(max-width:900px){.steps{grid-template-columns:1fr 1fr;}}
@media(max-width:520px){.steps{grid-template-columns:1fr;}}
.step{border-top:2px solid var(--line2);padding-top:18px;}
.step .n{font-family:var(--head);font-size:40px;color:var(--yellow);line-height:1;}
.step h3{font-size:17px;margin:12px 0 6px;}
.step p{margin:0;font-size:14.5px;color:var(--grey);}

/* ---------- Über ---------- */
.about{display:grid;grid-template-columns:.85fr 1.15fr;gap:50px;align-items:center;}
@media(max-width:900px){.about{grid-template-columns:1fr;gap:28px;}}
.about-photo{border:1px solid var(--line);border-radius:16px;overflow:hidden;aspect-ratio:1/1;background:var(--panel);}
.about-photo img{width:100%;height:100%;object-fit:cover;}
.about h2{font-size:clamp(28px,4.5vw,46px);}
.about p{color:#cfd2d8;margin:16px 0 0;}
.badges{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px;}
.badge{border:1px solid var(--line2);border-radius:999px;padding:9px 16px;font-size:13.5px;color:#e4e6ea;font-weight:600;}

/* ---------- Formular ---------- */
.formwrap{max-width:760px;margin:0 auto;background:var(--panel);border:1px solid var(--line);border-radius:18px;padding:34px 30px 30px;}
@media(max-width:600px){.formwrap{padding:24px 18px;}}
.progress{display:flex;gap:8px;margin-bottom:26px;}
.progress i{height:5px;border-radius:3px;flex:1;background:rgba(255,255,255,.12);transition:background .3s;}
.progress i.on{background:var(--yellow);}
.step-panel{display:none;}
.step-panel.on{display:block;animation:fade .35s ease;}
@keyframes fade{from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:none;}}
.qlabel{font-family:var(--head);text-transform:uppercase;font-size:23px;margin:0 0 4px;}
.qhint{color:var(--grey);font-size:14px;margin:0 0 20px;}
.chips{display:flex;flex-wrap:wrap;gap:10px;}
.chip{border-radius:9999px;padding:11px 18px;background:transparent;border:1px solid rgba(255,255,255,.15);color:#F5F5F5;cursor:pointer;transition:all .2s ease;font-weight:600;font-size:14.5px;font-family:var(--body);}
.chip:hover{border-color:var(--yellow);}
.chip.active{background:var(--blue);color:#fff;border-color:var(--blue);}
.field{margin-top:16px;}
.field label{display:block;font-size:13px;font-weight:600;color:var(--grey);margin-bottom:7px;letter-spacing:.02em;}
.field input,.field textarea{width:100%;background:#0E0E0E;border:1px solid var(--line2);border-radius:10px;color:var(--ink);
  font-family:var(--body);font-size:16px;padding:14px 15px;transition:border-color .2s;}
.field input:focus,.field textarea:focus{outline:none;border-color:var(--yellow);}
.field textarea{min-height:96px;resize:vertical;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
@media(max-width:520px){.row2{grid-template-columns:1fr;}}
.hp{position:absolute;left:-9999px;opacity:0;height:0;overflow:hidden;}
.check{display:flex;gap:11px;align-items:flex-start;margin-top:18px;font-size:14px;color:#cfd2d8;}
.check input{width:19px;height:19px;margin-top:2px;accent-color:var(--blue);flex:none;}
.formnav{display:flex;align-items:center;gap:12px;margin-top:26px;}
.formnav .back{background:transparent;border:1px solid var(--line2);color:var(--grey);}
.formnav .spacer{flex:1;}
.promise{background:rgba(110,155,224,.10);border:1px solid rgba(110,155,224,.30);border-radius:11px;padding:14px 16px;font-size:14px;color:#d7e4f6;margin-top:22px;}
.err{color:#ff8a8a;font-size:13.5px;margin-top:10px;display:none;}
.thanks{display:none;text-align:center;padding:24px 10px;}
.thanks .big{font-family:var(--head);text-transform:uppercase;font-size:30px;color:var(--yellow);margin-bottom:10px;}

/* ---------- Kontakt ---------- */
.contact{background:var(--bg2);}
.cgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:10px;}
@media(max-width:760px){.cgrid{grid-template-columns:1fr;}}
.cbox{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:22px 22px 24px;}
.cbox .k{font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:var(--yellow);font-weight:700;}
.cbox .v{font-size:19px;font-weight:600;margin-top:8px;}
.cbox .v.small{font-size:16px;line-height:1.5;font-weight:500;color:#d7d9de;}

/* ---------- Footer ---------- */
footer{border-top:1px solid var(--line);padding:44px 0 40px;}
.fgrid{display:flex;flex-wrap:wrap;gap:26px;align-items:center;}
.fgrid .logo{height:50px;}
.fnav{display:flex;gap:20px;flex-wrap:wrap;margin-left:auto;}
.fnav a{font-size:14px;color:var(--grey);}
.fnav a:hover{color:var(--ink);}
.fgebiet{margin-top:22px;padding-top:20px;border-top:1px solid var(--line);font-size:13.5px;color:var(--grey);display:flex;gap:16px;flex-wrap:wrap;align-items:center;}
.fgebiet a{color:#b8bcc4;}
.fgebiet a:hover{color:var(--ink);}
.legal{margin-top:24px;font-size:13px;color:#6b7280;display:flex;gap:18px;flex-wrap:wrap;}
.legal a{color:#9CA3AF;text-decoration:underline;text-underline-offset:2px;}

/* ---------- Mobile Call Bar ---------- */
.callbar{position:fixed;left:0;right:0;bottom:0;z-index:60;display:none;
  background:rgba(16,16,16,.97);-webkit-backdrop-filter:blur(10px);backdrop-filter:blur(10px);
  border-top:1px solid var(--line2);color:#fff;text-align:center;font-weight:600;
  letter-spacing:.01em;padding:16px;font-size:15.5px;
  padding-bottom:calc(16px + env(safe-area-inset-bottom));}
@media(max-width:900px){.callbar{display:block;} body{padding-bottom:calc(56px + env(safe-area-inset-bottom));}}

/* ---------- Reveal ---------- */
.rv{opacity:0;transform:translateY(22px);transition:opacity .6s ease,transform .6s ease;}
.rv.in{opacity:1;transform:none;}
@media(prefers-reduced-motion:reduce){.rv{opacity:1;transform:none;transition:none;}html{scroll-behavior:auto;}}

/* preview ribbon */
.ribbon{position:fixed;top:104px;right:0;z-index:70;background:#1A1A1A;border:1px solid var(--line);border-right:0;border-radius:8px 0 0 8px;
  padding:7px 13px;font-size:11.5px;color:var(--grey);letter-spacing:.05em;}
@media(max-width:900px){.ribbon{top:64px;}}

/* Vorher-Nachher */
.refgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
@media(max-width:900px){.refgrid{grid-template-columns:1fr;}}
figure{margin:0;}
figure figcaption{margin-top:12px;font-family:var(--head);text-transform:uppercase;font-size:16px;letter-spacing:.01em;color:var(--ink);}
.ba{position:relative;aspect-ratio:4/3;overflow:hidden;border-radius:14px;border:1px solid var(--line);cursor:ew-resize;user-select:none;touch-action:none;background:#111;}
.ba img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;pointer-events:none;}
.ba .before{clip-path:inset(0 50% 0 0);}
.ba-lbl{position:absolute;top:10px;z-index:4;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;padding:4px 9px;border-radius:999px;background:rgba(10,10,10,.72);color:#fff;-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px);}
.ba-lbl.l{left:10px;} .ba-lbl.r{right:10px;}
.ba-handle{position:absolute;top:0;bottom:0;left:50%;width:2px;background:#fff;z-index:5;transform:translateX(-1px);box-shadow:0 0 12px rgba(0,0,0,.6);}
.ba-handle span{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:40px;height:40px;border-radius:50%;background:#fff;color:#0A0A0A;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 18px rgba(0,0,0,.45);}
.ba-handle span::before{content:"\21C4";font-size:17px;font-weight:700;}
.rtile{aspect-ratio:4/3;border-radius:14px;overflow:hidden;border:1px solid var(--line);}
.rtile img{width:100%;height:100%;object-fit:cover;transition:transform .4s ease;}
.rtile:hover img{transform:scale(1.05);}
</style>
</head>
<body>


<!-- ============ HEADER ============ -->
<header id="hd">
  <div class="wrap nav">
    <img class="logo" src="assets/img/logohaustechnikneu.png" alt="OH Haustechnik">
    <nav class="links" aria-label="Hauptnavigation">
      <a href="#leistungen">Leistungen</a>
      <a href="#altbau">Altbau</a>
      <a href="#referenzen">Referenzen</a>
      <a href="#ablauf">Ablauf</a>
      <a href="#kontakt">Kontakt</a>
    </nav>
    <a class="btn btn-y call" href="tel:+491757481006">Anrufen</a>
  </div>
</header>

<!-- ============ HERO ============ -->
<section class="hero" id="top">
  <div class="hero-bg"><video autoplay muted loop playsinline preload="metadata" poster="assets/img/lp/poster.jpg"><source src="assets/videos/Hero-video.mp4.mp4" type="video/mp4"></video></div>
  <div class="wrap hero-in">
    <div class="eyebrow rv">Elektrobetrieb · Nürnberg · Fürth · Schwabach · Wendelstein</div>
    <h1 class="rv">Elektro.<br><span class="yellow">Vom Altbau bis Smart Home.</span></h1>
    <p class="sub rv">Junger Elektrobetrieb aus Nürnberg — für Wohnung, Haus und Gewerbe. Elektroinstallation, Altbausanierung und Smart Home. Klare Absprachen, saubere Ausführung, schnelle Rückmeldung.</p>
    <div class="ctas rv">
      <button class="btn btn-y" type="button" data-open-funnel>Anfrage in 2 Minuten</button>
      <a class="btn btn-ghost" href="tel:+491757481006">Anrufen</a>
    </div>
    <div class="trust rv">
      <svg class="gg" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.7-6.1 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.5 6.1 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.3-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.9 1.2 8 3.1l5.7-5.7C34.5 6.1 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.4 0 10.3-2.1 14-5.5l-6.5-5.5C29.6 34.6 26.9 36 24 36c-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.2 4.2-4.1 5.6l6.5 5.5C41.4 36.9 44 31 44 24c0-1.3-.1-2.3-.4-3.5z"/></svg>
      <span><b><?= number_format($ohRev['rating'],1,',','') ?> Sterne</b> bei <?= (int)$ohRev['count'] ?> Google-Bewertungen</span>
    </div>
  </div>
</section>

<!-- ============ LEISTUNGEN ============ -->
<section id="leistungen">
  <div class="wrap">
    <div class="shead rv">
      <div class="eyebrow">Leistungen</div>
      <h2>Elektro-Leistungen in Nürnberg &amp; Umgebung.</h2>
      <p>Elektroinstallation, Altbausanierung, Smart Home, PV und Wallbox — im Raum Nürnberg, Fürth, Schwabach und Wendelstein. Sauber ausgeführt und nachvollziehbar dokumentiert.</p>
    </div>
    <div class="grid">
      <div class="card rv"><div class="n">01</div><h3>Elektro­installation</h3><p>Neuinstallation für Wohnung, Haus und Gewerbe — von der Planung bis zur Abnahme.</p><a class="more" href="leistungen/elektroinstallation.php">Mehr erfahren →</a></div>
      <div class="card rv"><div class="n">02</div><h3>Altbau­sanierung</h3><p>Alte Leitungen raus, moderner FI-Schutz rein. Elektrik auf heutigen Stand, ohne die Bausubstanz zu zerlegen.</p><a class="more" href="altbausanierung-nuernberg.php">Mehr erfahren →</a></div>
      <div class="card rv"><div class="n">03</div><h3>Fehlersuche &amp; Kundendienst</h3><p>Strom weg, FI fliegt, etwas funktioniert nicht? Wir finden die Ursache und beheben sie.</p><a class="more" href="kundendienst-fehlersuche-nuernberg.php">Mehr erfahren →</a></div>
      <div class="card rv"><div class="n">04</div><h3>Smart Home</h3><p>Licht, Beschattung, Heizung und Sicherheit vernetzt — mit Loxone oder KNX.</p><a class="more" href="smart-home-knx-loxone-nuernberg.php">Mehr erfahren →</a></div>
      <div class="card rv"><div class="n">05</div><h3>PV-Anlagen</h3><p>Photovoltaik von der Modulmontage bis zum Netzanschluss — alles aus einer Hand.</p><a class="more" href="photovoltaik-nuernberg.php">Mehr erfahren →</a></div>
      <div class="card rv"><div class="n">06</div><h3>Wallbox</h3><p>Ladepunkt fürs E-Auto — fachgerecht abgesichert und angemeldet.</p><a class="more" href="zaehlerschrank-wallbox-nuernberg.php">Mehr erfahren →</a></div>
    </div>
    <p class="rv" style="margin-top:28px"><a class="btn btn-ghost" href="leistungen.php">Alle Leistungen im Detail →</a></p>
  </div>
</section>

<!-- ============ ALTBAU ============ -->
<section id="altbau" class="alt-bg">
  <div class="wrap altbau">
    <div class="rv">
      <div class="eyebrow">Kernkompetenz</div>
      <h2>Altbausanierung ist unsere Stärke.</h2>
      <p class="lead">Alte Häuser haben ihre Tücken: zweiadrige oder brüchige Leitungen, fehlender FI-Schutz, Bausubstanz, die man nicht unnötig aufreißen will. Genau dafür sind wir da — Elektrosanierung im Altbau rund um Nürnberg.</p>
      <ul class="checks">
        <li><span class="mk">→</span> Alte Leitungen und marode Verteilungen erneuern</li>
        <li><span class="mk">→</span> FI-/Fehlerstromschutz nachrüsten — Sicherheit auf heutigem Stand</li>
        <li><span class="mk">→</span> Schlitze und Dosen fachgerecht gesetzt — verputzfertig für Ihren Maler</li>
        <li><span class="mk">→</span> Termintreu von der Planung bis zur Abnahme — ein Ansprechpartner</li>
      </ul>
      <a class="btn btn-y" href="#anfrage">Altbau-Anfrage stellen</a>
    </div>
    <div class="altbau-photo rv"><img src="assets/img/lp/altbau.jpg" alt="Neue Elektroleitungen im Altbau, sauber verlegt"></div>
  </div>
</section>

<!-- ============ REFERENZEN ============ -->
<section id="referenzen">
  <div class="wrap">
    <div class="shead rv"><div class="eyebrow">Referenzen</div><h2>Vorher. Nachher. Sauber.</h2>
      <p>Echte Projekte aus dem Raum Nürnberg — zieh den Regler und sieh den Unterschied.</p></div>
    <div class="refgrid">
      <figure class="rv">
        <div class="ba"><img class="after" src="assets/img/lp/ba1n.jpg" alt="Dachgeschoss nachher"><img class="before" src="assets/img/lp/ba1v.jpg" alt="Dachgeschoss vorher"><span class="ba-lbl l">Vorher</span><span class="ba-lbl r">Nachher</span><div class="ba-handle"><span></span></div></div>
        <figcaption>Dachgeschoss-Wohnung · Altbausanierung</figcaption>
      </figure>
      <figure class="rv">
        <div class="ba"><img class="after" src="assets/img/lp/ba2n.jpg" alt="Kueche nachher"><img class="before" src="assets/img/lp/ba2v.jpg" alt="Kueche vorher"><span class="ba-lbl l">Vorher</span><span class="ba-lbl r">Nachher</span><div class="ba-handle"><span></span></div></div>
        <figcaption>Küche · Komplettsanierung</figcaption>
      </figure>
      <figure class="rv">
        <div class="ba"><img class="after" src="assets/img/lp/ba3n.jpg" alt="Behandlungsraum nachher"><img class="before" src="assets/img/lp/ba3v.jpg" alt="Behandlungsraum vorher"><span class="ba-lbl l">Vorher</span><span class="ba-lbl r">Nachher</span><div class="ba-handle"><span></span></div></div>
        <figcaption>Kosmetik-/Behandlungsraum · Gewerbe</figcaption>
      </figure>
    </div>
    <div class="refgrid" style="margin-top:16px">
      <figure class="rv"><div class="rtile"><img src="assets/img/lp/uv.jpg" alt="Neue Unterverteilung mit FI-Schutz"></div><figcaption>Unterverteilung erneuert</figcaption></figure>
      <figure class="rv"><div class="rtile"><img src="assets/img/lp/bad.jpg" alt="Bad-Sanierung mit Fliesen"></div><figcaption>Bad-Sanierung</figcaption></figure>
      <figure class="rv"><div class="rtile"><img src="assets/img/lp/sh.jpg" alt="Zaehlerschrank Smart Home"></div><figcaption>Smart Home · Zählerschrank</figcaption></figure>
    </div>
  </div>
</section>

<!-- ============ ABLAUF ============ -->
<section id="ablauf" class="alt-bg">
  <div class="wrap">
    <div class="shead rv"><div class="eyebrow">Ablauf</div><h2>So läuft es ab.</h2></div>
    <div class="steps">
      <div class="step rv"><div class="n">01</div><h3>Anfrage</h3><p>Kurz schildern, worum es geht — über das Formular oder telefonisch.</p></div>
      <div class="step rv"><div class="n">02</div><h3>Vor-Ort-Termin</h3><p>Wir schauen uns die Lage vor Ort an und besprechen mit Ihnen die Möglichkeiten.</p></div>
      <div class="step rv"><div class="n">03</div><h3>Klares Angebot</h3><p>Verständlich aufgeschlüsselt — Sie wissen, was gemacht wird und was es kostet.</p></div>
      <div class="step rv"><div class="n">04</div><h3>Saubere Umsetzung</h3><p>Termintreu ausgeführt, aufgeräumt übergeben.</p></div>
    </div>
  </div>
</section>

<!-- ============ ÜBER ============ -->
<section id="ueber">
  <div class="wrap about">
    <div class="about-photo rv"><img src="assets/img/lp/about.jpg" alt="Fertige Elektroinstallation, Dachgeschoss Nürnberg"></div>
    <div class="rv">
      <div class="eyebrow">Der Betrieb</div>
      <h2>Ihr Elektrobetrieb aus Nürnberg.</h2>
      <p>OH Haustechnik ist Ihr Elektrobetrieb aus Nürnberg — Inhaber Onur-Can Hezer. Wir stehen für Elektroinstallation und Altbausanierung mit klaren Absprachen und sauberer Ausführung, von der ersten Anfrage bis zur Abnahme.</p>
      <p>Vom Altbau bis zum Smart Home: Wir arbeiten auf aktuellem Stand der Technik und Vorschriften — und erklären Ihnen jeden Schritt verständlich.</p>
      <div class="badges">
        <span class="badge">Elektroniker für Energie- &amp; Gebäudetechnik</span>
        <span class="badge">Persönlich &amp; direkt</span>
        <span class="badge"><?= number_format($ohRev['rating'],1,',','') ?>★ · <?= (int)$ohRev['count'] ?> Bewertungen</span>
      </div>
    </div>
  </div>
</section>

<!-- ============ FORMULAR ============ -->
<!-- ============ ANFRAGE ============ -->
<section id="anfrage" class="alt-bg">
  <div class="wrap" style="text-align:center">
    <div class="shead rv" style="margin:0 auto 30px">
      <div class="eyebrow" style="text-align:center">Anfrage</div>
      <h2>Angebot in 2 Minuten.</h2>
      <p style="margin-left:auto;margin-right:auto">Ein paar kurze Fragen zu Ihrem Objekt —
      danach melden wir uns mit einer Einschätzung. Kein Anruf nötig, keine Verpflichtung.</p>
    </div>
    <div class="rv" style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
      <button class="btn btn-y" type="button" data-open-funnel>Anfrage starten</button>
      <a class="btn btn-ghost" href="tel:+491757481006">Lieber anrufen</a>
    </div>
    <p class="rv" style="margin-top:22px;font-size:13.5px;color:var(--grey)">
      Ihre Angaben bleiben vertraulich und werden nur zur Bearbeitung Ihrer Anfrage genutzt.
    </p>
  </div>
</section>

<!-- ============ KONTAKT ============ -->
<section id="kontakt" class="contact">
  <div class="wrap">
    <div class="shead rv"><div class="eyebrow">Kontakt</div><h2>Elektriker Nürnberg — direkt erreichbar.</h2></div>
    <div class="cgrid">
      <a class="cbox rv" href="tel:+491757481006"><div class="k">Telefon</div><div class="v">+49 175 7481006</div></a>
      <a class="cbox rv" href="mailto:oh.Haustechnik@gmail.com"><div class="k">E-Mail</div><div class="v small">oh.Haustechnik@gmail.com</div></a>
      <div class="cbox rv"><div class="k">Einsatzgebiet</div><div class="v small">Nürnberg · Fürth · Schwabach · Wendelstein</div></div>
    </div>
  </div>
</section>

<!-- ============ FOOTER ============ -->
<footer>
  <div class="wrap">
    <div class="fgrid">
      <img class="logo" src="assets/img/logohaustechnikneu.png" alt="OH Haustechnik">
      <nav class="fnav" aria-label="Fußnavigation">
        <a href="#leistungen">Leistungen</a>
        <a href="ueber-uns.php">Über uns</a>
        <a href="kontakt.php">Kontakt</a>
        <a href="tel:+491757481006">+49 175 7481006</a>
      </nav>
    </div>
    <nav class="fgebiet" aria-label="Einsatzgebiet">
      <span>Einsatzgebiet:</span>
      <a href="elektroinstallation-nuernberg.php">Nürnberg</a>
      <a href="elektro-sanierung-fuerth.php">Fürth</a>
      <a href="elektro-sanierung-erlangen.php">Erlangen</a>
      <a href="elektriker-schwabach.php">Schwabach</a>
      <a href="elektriker-wendelstein.php">Wendelstein</a>
    </nav>
    <div class="legal">
      <span>OH Haustechnik · Onur-Can Hezer · Dianastraße 62, 90441 Nürnberg</span>
      <a href="impressum.php">Impressum</a>
      <a href="datenschutz.php">Datenschutz</a>
    </div>
  </div>
</footer>

<a class="callbar" href="tel:+491757481006">Anrufen · 0175 7481006</a>

<script>
// Sticky header
const hd=document.getElementById('hd');
addEventListener('scroll',()=>hd.classList.toggle('scrolled',scrollY>20),{passive:true});

// Scroll reveal
const io=new IntersectionObserver((es)=>{es.forEach((e,i)=>{if(e.isIntersecting){setTimeout(()=>e.target.classList.add('in'),i*60);io.unobserve(e.target);}})},{threshold:.14});
document.querySelectorAll('.rv').forEach(el=>io.observe(el));

document.querySelectorAll('.ba').forEach(ba=>{
  const before=ba.querySelector('.before'), handle=ba.querySelector('.ba-handle');
  function set(x){x=Math.max(0,Math.min(100,x));before.style.clipPath='inset(0 '+(100-x)+'% 0 0)';handle.style.left=x+'%';}
  let drag=false;
  const pos=e=>{const r=ba.getBoundingClientRect();const cx=(e.touches?e.touches[0].clientX:e.clientX);set((cx-r.left)/r.width*100);};
  ba.addEventListener('pointerdown',e=>{drag=true;try{ba.setPointerCapture(e.pointerId);}catch(_){}pos(e);});
  ba.addEventListener('pointermove',e=>{if(drag)pos(e);});
  ba.addEventListener('pointerup',()=>drag=false);
  ba.addEventListener('pointercancel',()=>drag=false);
  set(50);
});
</script>
<?php include __DIR__ . '/includes/funnel-modal.php'; ?>
<script src="assets/js/funnel.js"></script>
</body>
</html>
