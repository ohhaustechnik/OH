<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<!-- ============================================================= -->
<!-- GEHEIMER BEREICH – NICHT INDEXIEREN, NICHT VERLINKEN          -->
<!-- Dieser Pfad ist absichtlich schwer zu erraten.               -->
<!-- ============================================================= -->
<meta name="robots" content="noindex, nofollow">
<meta name="googlebot" content="noindex, nofollow">
<meta name="theme-color" content="#0b0e17">
<title>GP2 Trainer</title>
<style>
:root{
  --bg:#0b0e17; --bg2:#121726; --card:#171d2e; --card2:#1d2438;
  --line:#262e44; --txt:#e8ecf6; --mut:#8b95ad; --mut2:#5c6680;
  --gold:#f5a623; --blue:#4f8ef7; --green:#3ecf8e; --red:#e8445a; --purple:#7c5cbf; --teal:#2ec4a9;
  --glow:0 0 40px rgba(79,142,247,.25);
  --r:18px;
}
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html,body{margin:0;padding:0}
body{
  font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
  background:var(--bg); color:var(--txt);
  background-image:radial-gradient(900px 500px at 80% -10%,rgba(124,92,191,.18),transparent),
                   radial-gradient(800px 500px at -10% 10%,rgba(79,142,247,.16),transparent),
                   radial-gradient(700px 600px at 50% 120%,rgba(62,207,142,.10),transparent);
  min-height:100vh; min-height:100dvh; overflow-x:hidden;
  -webkit-font-smoothing:antialiased;
}
::selection{background:rgba(245,166,35,.35)}
button{font-family:inherit}
input{font-family:inherit}

/* ===== Animations ===== */
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideIn{from{opacity:0;transform:translateX(-24px)}to{opacity:1;transform:none}}
@keyframes pop{0%{transform:scale(.9);opacity:0}60%{transform:scale(1.03)}100%{transform:scale(1);opacity:1}}
@keyframes shake{10%,90%{transform:translateX(-2px)}20%,80%{transform:translateX(4px)}30%,50%,70%{transform:translateX(-7px)}40%,60%{transform:translateX(7px)}}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.06)}}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes ring{0%{stroke-dashoffset:var(--circ)}}
.fade{animation:fadeUp .5s cubic-bezier(.2,.7,.3,1) both}
.fade2{animation:fadeUp .5s .08s cubic-bezier(.2,.7,.3,1) both}
.fade3{animation:fadeUp .5s .16s cubic-bezier(.2,.7,.3,1) both}
.stagger>*{animation:fadeUp .5s both}
.stagger>*:nth-child(1){animation-delay:.04s}
.stagger>*:nth-child(2){animation-delay:.10s}
.stagger>*:nth-child(3){animation-delay:.16s}
.stagger>*:nth-child(4){animation-delay:.22s}
.stagger>*:nth-child(5){animation-delay:.28s}
.stagger>*:nth-child(6){animation-delay:.34s}
.stagger>*:nth-child(7){animation-delay:.40s}
.stagger>*:nth-child(8){animation-delay:.46s}

/* ===== LOGIN ===== */
#login{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;padding:24px;z-index:50}
.lwrap{width:100%;max-width:400px;animation:pop .6s cubic-bezier(.2,.8,.3,1) both}
.lcard{background:linear-gradient(160deg,rgba(29,36,56,.92),rgba(18,23,38,.92));backdrop-filter:blur(20px);
  border:1px solid var(--line);border-radius:26px;padding:34px 28px;box-shadow:0 30px 80px rgba(0,0,0,.5)}
.lbadge{display:inline-flex;align-items:center;gap:7px;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;
  color:var(--gold);background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.25);padding:6px 12px;border-radius:30px;font-weight:700}
.lcard h1{font-size:27px;margin:18px 0 4px;letter-spacing:-.5px;line-height:1.15}
.lcard h1 span{background:linear-gradient(90deg,var(--gold),#ffd479);-webkit-background-clip:text;background-clip:text;color:transparent}
.lsub{color:var(--mut);font-size:14px;margin:0 0 26px;line-height:1.5}
.field{margin-bottom:14px}
.field label{display:block;font-size:12px;color:var(--mut);margin-bottom:7px;font-weight:600;letter-spacing:.3px}
.field input{width:100%;background:#0e1320;border:1.5px solid var(--line);color:var(--txt);
  padding:14px 16px;border-radius:14px;font-size:16px;transition:.2s;outline:none}
.field input:focus{border-color:var(--blue);box-shadow:0 0 0 4px rgba(79,142,247,.15)}
.lbtn{width:100%;margin-top:8px;padding:15px;border:none;border-radius:14px;cursor:pointer;
  background:linear-gradient(90deg,var(--gold),#ff9d3c);color:#1a1206;font-weight:800;font-size:16px;letter-spacing:.3px;
  transition:transform .15s,box-shadow .2s;box-shadow:0 10px 30px rgba(245,166,35,.3)}
.lbtn:active{transform:scale(.97)}
.lerr{color:var(--red);font-size:13.5px;margin-top:14px;text-align:center;min-height:18px;font-weight:600}
.lerr.show{animation:shake .5s}
.lhint{margin-top:22px;font-size:11px;color:var(--mut2);text-align:center;line-height:1.6;opacity:.7}

/* ===== APP SHELL ===== */
#app{display:none;min-height:100dvh}
.appheader{position:sticky;top:0;z-index:40;background:rgba(11,14,23,.92);backdrop-filter:blur(16px);
  border-bottom:1px solid var(--line);box-shadow:0 6px 24px rgba(0,0,0,.25)}
.topbar{display:flex;align-items:center;gap:12px;
  padding:12px 18px;padding-top:max(12px,env(safe-area-inset-top))}
.logo{font-weight:800;font-size:16px;letter-spacing:-.3px;display:flex;align-items:center;gap:8px}
.logo .dot{width:9px;height:9px;border-radius:50%;background:var(--green);box-shadow:0 0 12px var(--green);animation:pulse 2s infinite}
.tb-spacer{flex:1}
.tb-score{display:flex;align-items:center;gap:6px;background:rgba(245,166,35,.12);border:1px solid rgba(245,166,35,.25);
  color:var(--gold);font-weight:800;font-size:13px;padding:7px 12px;border-radius:30px}
.tb-streak{display:flex;align-items:center;gap:5px;background:rgba(232,68,90,.12);border:1px solid rgba(232,68,90,.25);
  color:#ff8194;font-weight:800;font-size:13px;padding:7px 11px;border-radius:30px}
.iconbtn{width:38px;height:38px;border-radius:11px;border:1px solid var(--line);background:var(--card);color:var(--mut);
  font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.2s}
.iconbtn:active{transform:scale(.92)}

.wrap{max-width:760px;margin:0 auto;padding:22px 18px}

/* ===== TOP NAV (Hauptmenü oben) ===== */
.topnav{display:flex;justify-content:space-around;gap:4px;padding:4px 8px 8px;
  border-top:1px solid var(--line)}
.nav-i{flex:1;background:none;border:none;color:var(--mut);font-size:10.5px;font-weight:600;cursor:pointer;
  display:flex;flex-direction:column;align-items:center;gap:3px;padding:7px 2px;border-radius:12px;transition:.2s}
.nav-i .ic{font-size:19px;transition:transform .2s}
.nav-i.active{color:var(--gold);background:rgba(245,166,35,.10)}
.nav-i.active .ic{transform:translateY(-1px) scale(1.10)}
.nav-i:active .ic{transform:scale(.85)}

/* ===== GENERIC ===== */
.page{display:none}
.page.on{display:block;animation:fadeIn .35s both}
h2.sec{font-size:13px;text-transform:uppercase;letter-spacing:1.4px;color:var(--mut);margin:30px 0 14px;font-weight:700}
.card{background:linear-gradient(160deg,var(--card2),var(--card));border:1px solid var(--line);border-radius:var(--r);padding:18px}
.btn{border:none;border-radius:13px;padding:13px 18px;font-weight:700;font-size:15px;cursor:pointer;transition:transform .15s,filter .2s}
.btn:active{transform:scale(.96)}
.btn.primary{background:linear-gradient(90deg,var(--gold),#ff9d3c);color:#1a1206;box-shadow:0 8px 24px rgba(245,166,35,.28)}
.btn.blue{background:linear-gradient(90deg,var(--blue),#6aa3ff);color:#06101f;box-shadow:0 8px 24px rgba(79,142,247,.28)}
.btn.ghost{background:var(--card);border:1px solid var(--line);color:var(--txt)}
.btn.green{background:linear-gradient(90deg,var(--green),#5fe0a6);color:#04130c}
.btn.red{background:linear-gradient(90deg,var(--red),#ff6b7e);color:#fff}

/* ===== DASHBOARD ===== */
.hero{background:linear-gradient(150deg,rgba(124,92,191,.25),rgba(79,142,247,.12) 60%,transparent);
  border:1px solid var(--line);border-radius:24px;padding:24px;position:relative;overflow:hidden}
.hero::after{content:'';position:absolute;top:-40px;right:-40px;width:160px;height:160px;border-radius:50%;
  background:radial-gradient(circle,rgba(245,166,35,.25),transparent 70%);animation:float 6s ease-in-out infinite}
.hero .hi{font-size:14px;color:var(--mut);font-weight:600}
.hero h1{font-size:28px;margin:4px 0 2px;letter-spacing:-.6px}
.hero h1 b{background:linear-gradient(90deg,var(--gold),#ffd479);-webkit-background-clip:text;background-clip:text;color:transparent}
.hero .date{font-size:13px;color:var(--mut);margin-top:6px}
.ring-row{display:flex;align-items:center;gap:20px;margin-top:18px}
.ring{position:relative;width:108px;height:108px;flex-shrink:0}
.ring svg{transform:rotate(-90deg)}
.ring .pct{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.ring .pct b{font-size:24px;font-weight:800}
.ring .pct small{font-size:10px;color:var(--mut);letter-spacing:.5px}
.ring-info{flex:1}
.ring-info .big{font-size:15px;font-weight:700;line-height:1.4}
.ring-info .sub{font-size:13px;color:var(--mut);margin-top:4px;line-height:1.5}

.statgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:11px;margin-top:14px}
.stat{background:var(--card);border:1px solid var(--line);border-radius:15px;padding:14px 12px;text-align:center}
.stat b{display:block;font-size:22px;font-weight:800}
.stat span{font-size:11px;color:var(--mut);font-weight:600}
.stat.gold b{color:var(--gold)} .stat.green b{color:var(--green)} .stat.red b{color:#ff8194}

/* Daily tasks */
.task{display:flex;align-items:center;gap:14px;background:var(--card);border:1px solid var(--line);
  border-radius:16px;padding:15px;margin-bottom:11px;transition:.25s;cursor:pointer;position:relative;overflow:hidden}
.task:active{transform:scale(.99)}
.task .tk-ic{width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.task .tk-main{flex:1;min-width:0}
.task .tk-main .t1{font-weight:700;font-size:15px}
.task .tk-main .t2{font-size:12.5px;color:var(--mut);margin-top:3px}
.task .tk-bar{height:6px;border-radius:6px;background:#0e1320;margin-top:8px;overflow:hidden}
.task .tk-bar i{display:block;height:100%;border-radius:6px;transition:width .6s cubic-bezier(.2,.8,.3,1)}
.task .tk-check{width:30px;height:30px;border-radius:50%;border:2px solid var(--line);flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:15px;color:transparent;transition:.25s}
.task.done .tk-check{background:var(--green);border-color:var(--green);color:#04130c}
.task.done{opacity:.72}
.task .tk-pct{font-size:12px;font-weight:800;color:var(--mut);min-width:34px;text-align:right}

.bigbtn-row{display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-top:14px}
.bigbtn{border:1px solid var(--line);border-radius:16px;padding:18px 14px;cursor:pointer;text-align:left;
  background:linear-gradient(150deg,var(--card2),var(--card));transition:transform .15s,border-color .2s}
.bigbtn:active{transform:scale(.97)}
.bigbtn .bb-ic{font-size:24px}
.bigbtn .bb-t{font-weight:700;font-size:15px;margin-top:8px}
.bigbtn .bb-s{font-size:12px;color:var(--mut);margin-top:2px}

/* Topic chips */
.chips{display:flex;gap:8px;overflow-x:auto;padding:4px 0 10px;-webkit-overflow-scrolling:touch;scrollbar-width:none}
.chips::-webkit-scrollbar{display:none}
.chip{white-space:nowrap;background:var(--card);border:1px solid var(--line);color:var(--mut);
  padding:9px 14px;border-radius:30px;font-size:13px;font-weight:600;cursor:pointer;transition:.2s;flex-shrink:0}
.chip.active{background:rgba(79,142,247,.16);border-color:var(--blue);color:#9cc2ff}

.topic-grid{display:grid;grid-template-columns:1fr 1fr;gap:11px}
.topic-c{background:linear-gradient(150deg,var(--card2),var(--card));border:1px solid var(--line);
  border-radius:16px;padding:15px;cursor:pointer;transition:transform .15s,border-color .2s}
.topic-c:active{transform:scale(.97)}
.topic-c .tc-ic{font-size:24px}
.topic-c .tc-n{font-weight:700;font-size:14px;margin-top:8px;line-height:1.2}
.topic-c .tc-c{font-size:12px;color:var(--mut);margin-top:3px}
.topic-c .tc-bar{height:5px;border-radius:5px;background:#0e1320;margin-top:9px;overflow:hidden}
.topic-c .tc-bar i{display:block;height:100%;background:var(--green);border-radius:5px;transition:width .5s}

/* ===== CARDS ===== */
.flashwrap{perspective:1600px;margin-top:8px}
.flash{position:relative;width:100%;min-height:360px;transform-style:preserve-3d;transition:transform .6s cubic-bezier(.2,.8,.3,1);cursor:pointer}
.flash.flip{transform:rotateY(180deg)}
.face{position:absolute;inset:0;backface-visibility:hidden;-webkit-backface-visibility:hidden;
  border-radius:22px;padding:24px;display:flex;flex-direction:column;border:1px solid var(--line);overflow-y:auto}
.face.front{background:linear-gradient(155deg,var(--card2),var(--card))}
.face.back{background:linear-gradient(155deg,#172033,#13182a);transform:rotateY(180deg)}
.face .badge2{align-self:flex-start;font-size:11px;font-weight:700;padding:5px 11px;border-radius:20px;margin-bottom:14px}
.face .qtext{font-size:19px;font-weight:700;line-height:1.4}
.face .atext{font-size:15px;line-height:1.65;color:#d4dbec;white-space:pre-wrap}
.face .hint{margin-top:auto;font-size:12px;color:var(--mut);padding-top:14px;text-align:center}
.card-ctl{display:grid;grid-template-columns:1fr 1fr;gap:11px;margin-top:14px}
.card-nav{display:flex;align-items:center;justify-content:space-between;margin-top:12px;gap:12px}
.card-nav .cn-btn{width:50px;height:50px;border-radius:15px;border:1px solid var(--line);background:var(--card);
  color:var(--txt);font-size:20px;cursor:pointer;transition:.15s}
.card-nav .cn-btn:active{transform:scale(.9)}
.card-nav .prog{flex:1;text-align:center;font-size:13px;color:var(--mut);font-weight:600}

/* ===== QUIZ ===== */
.quiz-q{font-size:19px;font-weight:700;line-height:1.4;margin:14px 0 18px}
.opt{display:block;width:100%;text-align:left;background:var(--card);border:1.5px solid var(--line);color:var(--txt);
  padding:15px 16px;border-radius:14px;margin-bottom:10px;font-size:14.5px;line-height:1.45;cursor:pointer;transition:.2s}
.opt:active{transform:scale(.99)}
.opt.correct{border-color:var(--green);background:rgba(62,207,142,.14);color:#aef3d3}
.opt.wrong{border-color:var(--red);background:rgba(232,68,90,.14);color:#ffb3bf}
.opt.dim{opacity:.45}
.qbar{height:7px;border-radius:7px;background:#0e1320;overflow:hidden;margin-bottom:6px}
.qbar i{display:block;height:100%;background:linear-gradient(90deg,var(--blue),var(--green));transition:width .4s}
.qmeta{display:flex;justify-content:space-between;font-size:12px;color:var(--mut);font-weight:600;margin-bottom:14px}
.quiz-result{text-align:center;padding:20px 0}
.quiz-result .big{font-size:54px;font-weight:800;margin:8px 0}
.quiz-result .msg{font-size:16px;color:var(--mut);margin-bottom:20px;line-height:1.5}

/* ===== EXAM LIST ===== */
.exam-i{background:var(--card);border:1px solid var(--line);border-radius:14px;margin-bottom:10px;overflow:hidden;transition:.2s}
.exam-i .eq{padding:15px 16px;cursor:pointer;display:flex;gap:12px;align-items:flex-start}
.exam-i .eq .ei-ic{font-size:18px;margin-top:1px}
.exam-i .eq .ei-q{flex:1;font-weight:600;font-size:14.5px;line-height:1.4}
.exam-i .eq .ei-ar{color:var(--mut);transition:transform .25s;font-size:13px;margin-top:3px}
.exam-i.open .ei-ar{transform:rotate(90deg)}
.exam-i .ea{max-height:0;overflow:hidden;transition:max-height .35s ease;background:#0e1320}
.exam-i.open .ea{max-height:1400px}
.exam-i .ea-in{padding:0 16px 16px;font-size:14px;line-height:1.65;color:#cdd5e8;white-space:pre-wrap;border-top:1px solid var(--line);padding-top:14px}

/* ===== FORMELN / RECHNER ===== */
.fs{margin-bottom:22px}
.fs h3.fh{font-size:14px;color:var(--gold);font-weight:800;margin:0 0 10px;letter-spacing:.3px}
.frow{background:var(--card);border:1px solid var(--line);border-radius:13px;padding:13px 15px;margin-bottom:9px}
.frow .fn{font-weight:700;font-size:14px}
.frow .ff{font-family:'SF Mono',ui-monospace,monospace;font-size:15px;color:var(--green);margin-top:5px;background:#0e1320;padding:8px 11px;border-radius:9px;display:inline-block}
.frow .fnote{font-size:12px;color:var(--mut);margin-top:7px}
.ccard{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:16px;margin-bottom:13px}
.ccard h3{margin:0 0 12px;font-size:15px}
.crow{display:flex;align-items:center;gap:10px;margin-bottom:9px}
.crow label{flex:1;font-size:13px;color:var(--mut)}
.crow input{width:120px;background:#0e1320;border:1.5px solid var(--line);color:var(--txt);padding:9px 11px;border-radius:10px;font-size:14px;outline:none}
.crow input:focus{border-color:var(--blue)}
.cres{margin-top:10px;background:rgba(62,207,142,.1);border:1px solid rgba(62,207,142,.3);color:#aef3d3;
  padding:11px 13px;border-radius:11px;font-size:14px;font-weight:600;min-height:20px}

/* ===== MODAL / TOAST ===== */
.modal-bg{position:fixed;inset:0;background:rgba(5,7,12,.7);backdrop-filter:blur(6px);z-index:60;
  display:none;align-items:center;justify-content:center;padding:24px;animation:fadeIn .25s}
.modal-bg.on{display:flex}
.modal{background:linear-gradient(160deg,var(--card2),var(--card));border:1px solid var(--line);border-radius:22px;
  padding:26px 24px;max-width:380px;width:100%;text-align:center;animation:pop .4s both}
.modal .m-ic{font-size:46px}
.modal h3{font-size:20px;margin:12px 0 6px}
.modal p{color:var(--mut);font-size:14.5px;line-height:1.55;margin:0 0 22px}
.modal .m-btns{display:flex;gap:11px}
.modal .m-btns .btn{flex:1}
.toast{position:fixed;left:50%;bottom:32px;transform:translateX(-50%) translateY(20px);z-index:70;
  background:var(--card2);border:1px solid var(--line);color:var(--txt);padding:13px 20px;border-radius:30px;
  font-size:14px;font-weight:600;box-shadow:0 14px 40px rgba(0,0,0,.5);opacity:0;transition:.35s;pointer-events:none;max-width:90%}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}

.empty{text-align:center;color:var(--mut);padding:40px 20px;font-size:14px}
.speak-pill{display:inline-flex;align-items:center;gap:7px;font-size:12px;color:var(--mut);
  background:var(--card);border:1px solid var(--line);padding:7px 13px;border-radius:30px;cursor:pointer;margin-top:14px}
.speak-pill:active{transform:scale(.95)}
.reset-row{display:flex;align-items:center;gap:14px}
.reset-info{flex:1;min-width:0}
.reset-row .rr-t{font-weight:700;font-size:14.5px}
.reset-row .rr-s{font-size:12px;color:var(--mut);margin-top:3px;line-height:1.45}
.reset-row .btn{flex-shrink:0;padding:10px 16px;font-size:14px}
/* Karteikarten-Statistik */
.cardstats{display:grid;grid-template-columns:repeat(3,1fr);gap:9px;margin:4px 0 12px}
.cardstats .cs{background:var(--card);border:1px solid var(--line);border-radius:13px;padding:11px 8px;text-align:center}
.cardstats .cs b{display:block;font-size:19px;font-weight:800}
.cardstats .cs span{font-size:10.5px;color:var(--mut);font-weight:600}
.cardstats .cs.green b{color:var(--green)} .cardstats .cs.gold b{color:var(--gold)}
.cardstats-bar{height:7px;border-radius:7px;background:#0e1320;overflow:hidden;margin-bottom:14px}
.cardstats-bar i{display:block;height:100%;background:linear-gradient(90deg,var(--green),#5fe0a6);transition:width .6s}
</style>
</head>
<body>

<!-- ====================== INTRO-SOUND ====================== -->
<!-- Verdrahtet mit der vorhandenen Website-Audiodatei.              -->
<!-- Pfad relativ zu diesem Ordner: ../assets/audio/...             -->
<!-- ERSETZEN: einfach eine andere Datei in /assets/audio/ ablegen  -->
<!-- und den 'src' unten anpassen.                                  -->
<!-- Hinweis: iOS/Safari erlauben Autoplay mit Ton erst NACH einer   -->
<!-- Nutzer-Interaktion (Tipp/Klick) – der Sound startet daher       -->
<!-- spätestens beim ersten Antippen (Login-Button) zuverlässig.     -->
<audio id="introSound" preload="auto">
  <source src="../assets/audio/oh-intro.mp3.mp3" type="audio/mpeg">
</audio>

<!-- ====================== LOGIN ====================== -->
<div id="login">
  <div class="lwrap">
    <div class="lcard">
      <span class="lbadge">🔒 Privater Lernbereich</span>
      <h1>GP Teil 2 · <span>Prüfungs&shy;trainer</span></h1>
      <p class="lsub">Elektroniker für Energie- und Gebäudetechnik · Bayern. Melde dich an und werde prüfungsbereit.</p>
      <div class="field">
        <label for="liName">Name</label>
        <input id="liName" type="text" autocomplete="username" autocapitalize="words" placeholder="Dein Name" inputmode="text">
      </div>
      <div class="field">
        <label for="liPass">Passwort</label>
        <input id="liPass" type="password" autocomplete="current-password" placeholder="••••">
      </div>
      <button class="lbtn" id="loginBtn">Anmelden &nbsp;→</button>
      <div class="lerr" id="loginErr"></div>
      <p class="lhint">Hinweis: Der Zugang ist clientseitig geschützt.<br>Das ist bequem, aber <b>nicht 100&nbsp;% sicher</b> – für echten Schutz später ein Server-Login ergänzen.</p>
    </div>
  </div>
</div>

<!-- ====================== APP ====================== -->
<div id="app">
  <div class="appheader">
    <div class="topbar">
      <div class="logo"><span class="dot"></span> GP2 Trainer</div>
      <div class="tb-spacer"></div>
      <div class="tb-streak" id="tbStreak" title="Streak">🔥 <span id="streakN">0</span></div>
      <div class="tb-score" id="tbScore" title="Punkte">⭐ <span id="scoreN">0</span></div>
      <button class="iconbtn" id="speakBtn" title="Vorlesen an/aus">🔊</button>
      <button class="iconbtn" id="logoutBtn" title="Abmelden">⏻</button>
    </div>
    <!-- Hauptnavigation – jetzt OBEN, auf allen Seiten sichtbar -->
    <div class="topnav">
      <button class="nav-i active" data-nav="home"><span class="ic">🏠</span>Start</button>
      <button class="nav-i" data-nav="cards"><span class="ic">🗂️</span>Karten</button>
      <button class="nav-i" data-nav="quiz"><span class="ic">🎯</span>Quiz</button>
      <button class="nav-i" data-nav="exam"><span class="ic">📋</span>Fragen</button>
      <button class="nav-i" data-nav="tools"><span class="ic">📐</span>Formel</button>
    </div>
  </div>

  <div class="wrap">
    <!-- PAGE: HOME / DASHBOARD -->
    <div class="page on" id="p-home"></div>
    <!-- PAGE: CARDS -->
    <div class="page" id="p-cards"></div>
    <!-- PAGE: QUIZ -->
    <div class="page" id="p-quiz"></div>
    <!-- PAGE: EXAM -->
    <div class="page" id="p-exam"></div>
    <!-- PAGE: TOOLS (Formeln + Rechner) -->
    <div class="page" id="p-tools"></div>
  </div>
</div>

<!-- MODAL -->
<div class="modal-bg" id="modalBg">
  <div class="modal" id="modalBox"></div>
</div>
<div class="toast" id="toast"></div>

<script>
/* ====================================================================
   GP2 PRÜFUNGSTRAINER – Single-File App
   Wissensbasis (ALL_QA / TM / CALCS / FORMELN) wurde 1:1 aus der
   bestehenden Datei "GP2 Lern App.html" übernommen und NICHT verändert.
   ==================================================================== */

/* ===== DATEN 1:1 ÜBERNOMMEN AUS 'GP2 Lern App.html' (unverändert) ===== */
const ALL_QA = [
  {tid:"blitz",topic:"Blitzschutz",ref:"Blitzschutz · äußerer vs. innerer",q:`Was unterscheidet äußeren und inneren Blitzschutz?`,a:`Äußerer Blitzschutz fängt den Blitz ab und leitet ihn sicher zur Erde: Fangeinrichtung (Fangstangen, Maschen auf dem Dach) Ableitungen (Leitungen an der Fassade nach unten) Erdungsanlage (Fundament-/Ringerder) Innerer Blitzschutz verhindert Schäden durch Überspannung im Gebäude — Kern ist der Blitzschutz-Potentialausgleich und der koordinierte Überspannungsschutz (SPD Typ 1/2/3).`},
  {tid:"blitz",topic:"Blitzschutz",ref:"Blitzschutz · Klassen / Typen",q:`In welche Typen gliedert sich der Überspannungsschutz (innerer Blitzschutz)? Nennen Sie 3 und ihre Aufgabe.`,a:`Typ 1 (Class I, Blitzstromableiter) — leitet den direkten Blitzteilstrom (Prüfwelle 10/350 µs) ab. Einbau am Gebäudeeintritt / Zählerschrank. Typ 2 (Class II, Überspannungsableiter) — schützt vor Schalt- und Ferneinkopplungen (8/20 µs). In der Unterverteilung. Typ 3 (Class III, Geräteschutz) — Feinschutz direkt vor empfindlichen Endgeräten (Steckdosennähe).`},
  {tid:"blitz",topic:"Blitzschutz",ref:"Blitzschutz · Anschluss Class 1",q:`Beschreiben Sie den Anschluss einer Überspannungsschutzeinrichtung (Class 1) in einem TN-S-System im Zählerschrank.`,a:`Im TN-S werden die Ableiter zwischen jedem Außenleiter (L1, L2, L3) und PE sowie zwischen N und PE angeschlossen (3+1-Schaltung). Der Anschluss erfolgt am Gebäudeeintritt / Zählerschrank , kurze Leitungswege (V-förmige Verdrahtung, „Anschluss in V“), Anschluss an die Haupterdungsschiene/PE.`},
  {tid:"blitz",topic:"Blitzschutz",ref:"Blitzschutz · grünes Feld",q:`Im Überspannungsableiter Class 2 ist ein grünes Anzeigefeld. Welche Bedeutung hat dieses?`,a:`Das grüne Feld zeigt Funktionsbereitschaft — die Schutzfunktion ist intakt. Schlägt die Anzeige auf rot um, ist das Ableitermodul überlastet/defekt und muss getauscht werden.`},
  {tid:"blitz",topic:"Blitzschutz",ref:"Blitzschutz · Anschluss-Querschnitt SPD",q:`Mit welchem Querschnitt wird der Überspannungsschutz (Class 2) im Unterverteiler angeschlossen?`,a:`Mindestens 6 mm² Cu auf der Schutzleiter-/Erdungsseite (Class 2). Bei Class 1 / Blitzstromableiter sind mindestens 16 mm² Cu gefordert.`},
  {tid:"blitz",topic:"Blitzschutz",ref:"Blitzschutz · äußerer PA Querschnitt",q:`Welchen Querschnitt muss der Anschluss der Blitzschutzanlage an die Haupterdungsschiene mindestens haben?`,a:`Mindestens 16 mm² Kupfer (bzw. 25 mm² Aluminium / 50 mm² Stahl) für blitzstromtragfähige Verbindungen.`},
  {tid:"blitz",topic:"Blitzschutz",ref:"Blitzschutz · PV ab 10 kWp",q:`Ab welcher PV-Leistung wird laut VdS 2010 eine Blitzschutzanlage gefordert und welche Klasse?`,a:`Ab 10 kWp wird für PV-Anlagen eine Blitzschutzanlage der Klasse III (LPS III) einschließlich koordiniertem Überspannungsschutz und Potentialausgleich empfohlen/gefordert (VdS 2010).`},
  {tid:"blitz",topic:"Blitzschutz",ref:"Blitzschutz · Trennungsabstand",q:`Was ist der Trennungsabstand und warum ist er einzuhalten?`,a:`Der Trennungsabstand s ist der Mindestabstand zwischen blitzstromführenden Teilen (Fangeinrichtung/Ableitung) und metallenen Installationen/Unterkonstruktionen, damit es nicht zu gefährlichen Überschlägen kommt. Wird er unterschritten, muss elektrisch verbunden (in den PA einbezogen) werden. Die Berechnung des Trennungsabstands ist vom Kunden/Planer zu übergeben.`},
  {tid:"antenne",topic:"Antennentechnik & Erdung",ref:"Antenne · warum erden",q:`Warum werden Antennen- und Fernmeldeanlagen (Telekommunikationsanlagen) geerdet?`,a:`Schutz vor atmosphärischen Überspannungen (Blitz, statische Aufladung) Ableitung von Fehler-/Berührungsspannungen Schutz von Personen und Geräten Funktion (Schirmung gegen Störeinkopplung, EMV)`},
  {tid:"antenne",topic:"Antennentechnik & Erdung",ref:"Antenne · Schirm erden Querschnitt",q:`Welchen Querschnitt benötigen Sie mindestens, um den Schirm von Antennenleitungen zu erden?`,a:`Mindestens 16 mm² Cu (bzw. 25 mm² Al / 50 mm² Stahl) für den Erdungsleiter der Antennenanlage (blitzstromtragfähig).`},
  {tid:"antenne",topic:"Antennentechnik & Erdung",ref:"Antenne · Widerstand berechnen",q:`Zur Antenne wird eine Erdungsleitung (16 mm² Kupfer, Länge 12 m) verlegt. Berechnen Sie den ohmschen Widerstand!`,a:`Formel: R = ρ · l / A ρ(Cu) = 0,0178 Ω·mm²/m l = 12 m A = 16 mm² R = 0,0178 · 12 / 16 R = 0,2136 / 16 R ≈ 0,0134 Ω (≈ 13,4 mΩ)`},
  {tid:"antenne",topic:"Antennentechnik & Erdung",ref:"Antenne · Anschluss an PA",q:`Wo wird die Antennenerdung angeschlossen?`,a:`An die Haupterdungsschiene (HES) bzw. den Potentialausgleich — möglichst kurz und auf direktem Weg, keine Induktionsschleifen.`},
  {tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Auslegung",q:`Wie legt man die Leistung einer PV-Anlage grob aus (Beispiel 4-Personen-Haushalt)?`,a:`Grundlage ist der Jahresverbrauch. Ein 4-Personen-Haushalt verbraucht ca. 5.000 kWh/Jahr . Ertrag ca. 800 kWh/kWp. Faustformel: P ≈ (Verbrauch / 1000) · 2 P ≈ (5000 / 1000) · 2 P ≈ 10 kWp`},
  {tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Potentialausgleich",q:`Welche Teile einer PV-Anlage sind an den Schutz-Potentialausgleich anzuschließen?`,a:`Das Gestell / die metallische Unterkonstruktion der Module Metallene Gehäuse (Wechselrichter, falls gefordert) Anschluss über Schutzpotentialausgleichs-/Blitzschutz-PA-Leitung von der HES Die Verbindungsleitung vom Gestell zum Erder ist nahe an den PV-Leitungen zu führen, um die Induktionsschleife klein zu halten.`},
  {tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Zählerschrank Dauerbetrieb",q:`Eine PV-Anlage / Ladestation soll im Zählerschrank angeschlossen werden. Was gilt für den Zählerplatz (Dauerbetrieb)?`,a:`Für Dauerstrom (Einspeisung/Ladestation) muss der Zählerplatz dauerstromfest sein. Die interne Verdrahtung und die Sammelschiene müssen für den Dauerstrom ausgelegt sein; ein eigener Anschlussraum / Zusatzfeld ist vorzusehen. Maßgeblich ist die VDE-AR-N 4101 und die TAB des Netzbetreibers.`},
  {tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Speicher/Batterieraum HAK",q:`Darf der Hausanschlusskasten (HAK) in einem Batterieraum / Speichersystem-Raum montiert werden?`,a:`Nein. Der HAK darf nicht in Räumen mit Batterie-/Speichersystemen montiert werden — wegen möglicher explosionsfähiger Gase und der besonderen Brandlast. Hausanschluss und Speicher sind zu trennen.`},
  {tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · DC-Freischaltung",q:`Warum und wie muss eine PV-Anlage freischaltbar sein?`,a:`PV-Generatoren und Wechselrichter müssen gewartet werden können — daher muss die Spannungsversorgung freigeschaltet und gegen Wiedereinschalten gesichert werden. Es sind absperrbare Lasttrennschalter einzusetzen — sowohl auf der DC-Seite (Gleichspannung) als auch auf der AC-Seite .`},
  {tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · MC4-Stecker",q:`Was ist beim Stecken/Trennen von PV-Steckverbindern (z. B. MC4) zu beachten?`,a:`Während des Steckens oder Trennens darf kein Strom fließen. Steckverbinder bis 1000–1500 V DC, IP68-dicht, aber nicht dauerhaft im Wasser. Leitungen normgerecht befestigen, nicht lose aufs Dach legen.`},
  {tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Isolationsmessung ANTI-PID",q:`Worauf ist bei einer Isolationsmessung an einer PV-Anlage mit ANTI-PID-Box zu achten?`,a:`Die ANTI-PID-Box muss vor der Messung abgeklemmt werden , sonst kann ihre Elektronik zerstört werden. Sie wird parallel zum Wechselrichter angeschlossen und meldet Isolationsfehler unter ca. 200 kΩ.`},
  {tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Lastmanagement",q:`Welche zwei Arten von Lastmanagement gibt es bei Ladeinfrastruktur?`,a:`Statisches Lastmanagement: feste Leistungsreserve des Hausanschlusses wird der Ladeinfrastruktur dauerhaft zugewiesen — ungenutzte Reserve bleibt liegen. Dynamisches Lastmanagement: der aktuelle Gebäudeverbrauch wird gemessen, die Restleistung der Ladeinfrastruktur zugeteilt — Hausanschluss wird optimal ausgenutzt.`},
  {tid:"wp",topic:"Wärmepumpe & Wärmetechnik",ref:"Wärmepumpe · Kreisprozess",q:`Nennen Sie die vier Stationen des Kreisprozesses einer Wärmepumpe und wo Energie aufgenommen bzw. abgegeben wird.`,a:`Verdampfer — Kältemittel nimmt Umweltwärme auf, verdampft (Wärme aufgenommen ). Verdichter (Kompressor) — verdichtet das Gas, Druck & Temperatur steigen (elektrische Energie zugeführt). Verflüssiger (Kondensator) — gibt die Wärme an den Heizkreis ab (Wärme abgegeben ). Expansionsventil (Drossel) — Druck wird abgebaut, Kältemittel kühlt ab, Kreis beginnt von vorn.`},
  {tid:"wp",topic:"Wärmepumpe & Wärmetechnik",ref:"Wärmepumpe · Wärmeträger",q:`Nennen Sie neben Erdkollektor und Erdwärmesonde 2 weitere Wärmeträger / Energiequellen einer Wärmepumpe.`,a:`Außenluft (Luft/Wasser-Wärmepumpe) Grundwasser (Wasser/Wasser-Wärmepumpe) (ggf. Abwärme/Abluft)`},
  {tid:"wp",topic:"Wärmepumpe & Wärmetechnik",ref:"Wärmepumpe · Leistungszahl ε",q:`Geben Sie die Formel der Leistungszahl ε an und berechnen Sie ε, wenn P_el = 1250 W und die abgegebene Wärmeleistung 4 kW beträgt.`,a:`Formel: ε = Q_ab / P_el (abgegebene Wärme / el. Leistung) Q_ab = 4 kW = 4000 W P_el = 1250 W ε = 4000 / 1250 ε = 3,2 Aus 1 Teil Strom werden 3,2 Teile Wärme — der Rest kommt aus der Umwelt.`},
  {tid:"wp",topic:"Wärmepumpe & Wärmetechnik",ref:"Wärmetechnik · Durchlauferhitzer-Leistung",q:`Berechnen Sie die Leistung eines Durchlauferhitzers: 14 Liter/Minute werden von 10 °C auf 60 °C erhitzt (Wärmeverluste vernachlässigt).`,a:`Formel: P = (m · c · Δϑ) / t m = 14 kg (14 l Wasser pro Minute) c = 4187 J/(kg·K) Δϑ = 60 − 10 = 50 K t = 60 s P = (14 · 4187 · 50) / 60 P = 2.930.900 / 60 P ≈ 48.848 W ≈ 48,8 kW`},
  {tid:"wp",topic:"Wärmepumpe & Wärmetechnik",ref:"Wärmetechnik · Kochend vs. Durchlauf",q:`Nennen Sie 3 Unterschiede zwischen Kochendwassergerät und Durchlauferhitzer.`,a:`Kochendwassergerät: kleiner Speicher, hält Wasser auf ~95–100 °C bereit; geringe Anschlussleistung (~2 kW, 230 V). Durchlauferhitzer: erwärmt im Durchfluss, kein Speicher; hohe Leistung (18–27 kW, 400 V Drehstrom). Kochendgerät: punktuell kleine Mengen (Teeküche); Durchlauferhitzer: große Mengen (Dusche/Bad) auf Anforderung.`},
  {tid:"wp",topic:"Wärmepumpe & Wärmetechnik",ref:"Wärmetechnik · kein Neutralleiter",q:`Wieso benötigt ein Durchlauferhitzer beim Anschluss keinen Neutralleiter? Begründen Sie.`,a:`Die drei Heizwendeln sind als symmetrische Drehstromlast (Sternschaltung) ausgeführt. Bei gleichmäßiger Belastung der drei Außenleiter ist der Summenstrom im Sternpunkt = 0 — es fließt kein Neutralleiterstrom, daher wird kein N benötigt.`},
  {tid:"wp",topic:"Wärmepumpe & Wärmetechnik",ref:"Wärmetechnik · Durchlauferhitzer im Bad",q:`Darf ein Durchlauferhitzer im Schutzbereich 1 im Badezimmer angeschlossen werden? Aus welcher Norm geht das hervor?`,a:`Im Schutzbereich 1 sind nur fest angeschlossene, dafür geeignete Betriebsmittel zulässig (z. B. Durchlauferhitzer, wenn vom Hersteller freigegeben). Geregelt in DIN VDE 0100-701 (Räume mit Badewanne/Dusche).`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht · Begriffe",q:`Erklären Sie die Begriffe Beleuchtungsstärke, Lichtstrom und Lichtausbeute.`,a:`Lichtstrom Φ (Lumen, lm): gesamte von einer Lichtquelle abgestrahlte Lichtleistung. Beleuchtungsstärke E (Lux, lx): auf eine Fläche treffender Lichtstrom — E = Φ / A (1 lx = 1 lm/m²). Lichtausbeute η (lm/W): Wirkungsgrad der Lampe — Lichtstrom je aufgenommener elektrischer Leistung.`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht · Lichtausbeute berechnen",q:`Berechnen Sie die Lichtausbeute η. Gesamtlichtstrom 50000 lm, Gesamtleistung 600 W.`,a:`Formel: η = Φ / P Φ = 50000 lm P = 600 W η = 50000 / 600 η ≈ 83,3 lm/W`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht · Beleuchtungsstärke prüfen",q:`Montageraum 9,8 m × 4,75 m, 10 Leuchtstofflampen à 3800 lm, Beleuchtungswirkungsgrad 0,56. Wird 500 lx eingehalten? (Wartungsfaktor schon berücksichtigt)`,a:`Formel: E = (n · Φ · η_B) / A A = 9,8 · 4,75 = 46,55 m² n = 10 Φ = 3800 lm η_B = 0,56 E = (10 · 3800 · 0,56) / 46,55 E = 21280 / 46,55 E ≈ 457 lx Nein — 457 lx &lt; 500 lx. Die geforderte Beleuchtungsstärke wird nicht eingehalten; mehr/stärkere Leuchten nötig.`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht · Leuchtmittelarten",q:`Nennen Sie neben LEDs 2 weitere Leuchtmittelarten.`,a:`Leuchtstofflampe (Niederdruck-Entladung) Halogen-Glühlampe (Hochdruck-Entladungslampe / Natriumdampflampe)`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht · Vorteile LED",q:`Nennen Sie 2 Vorteile von LEDs.`,a:`Hohe Lichtausbeute / geringer Energieverbrauch Sehr lange Lebensdauer, geringe Wartung (Sofort hell, dimmbar, schaltfest, kein Quecksilber)`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht · Qualitätsmerkmale",q:`Nennen Sie 3 Qualitätsmerkmale einer guten Beleuchtung.`,a:`Ausreichende Beleuchtungsstärke (lx nach Arbeitsstätte) Gute Farbwiedergabe (CRI/Ra) und passende Farbtemperatur Begrenzte Blendung und Gleichmäßigkeit, geringes Flimmern`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht · Kostenvergleich",q:`50 Leuchtstoffröhren à 36 W sollen durch 50 LEDs à 8 W ersetzt werden. Kostenersparnis pro Jahr bei 8 h/Tag, 350 Tagen, 0,29 €/kWh?`,a:`Leistungsdifferenz je Lampe: 36 − 8 = 28 W Gesamt-Einsparleistung: 50 · 28 = 1400 W = 1,4 kW Betriebsstunden/Jahr: 8 · 350 = 2800 h Energieeinsparung: 1,4 kW · 2800 h = 3920 kWh Kostenersparnis: 3920 · 0,29 € ≈ 1136,80 € / Jahr`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht/Trafo · Sekundärlänge",q:`Der Trafo-Hersteller gibt für den NV-Halogen-Sekundärkreis max. 2 m Länge an. Begründen Sie.`,a:`Im Niedervolt-Sekundärkreis (z. B. 12 V) fließen bei gleicher Leistung sehr hohe Ströme . Lange Leitungen verursachen großen Spannungsfall (Lampen werden dunkler) und Erwärmung. Darum kurze Leitung mit ausreichendem Querschnitt.`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht/Trafo · LED-Ersatz Probleme",q:`Halogenlampen sollen durch LED-Lampen ersetzt werden. Welche Probleme können auftreten?`,a:`Mindestlast des konventionellen/elektronischen Trafos wird nicht erreicht → Flackern oder kein Start. Dimmer nicht LED-tauglich → Flackern, Brummen. EVG nicht für LED ausgelegt → Brummen, frühzeitiger Ausfall.`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht/Trafo · Symbole",q:`Erklären Sie die Trafo-Symbole „Haus mit Pfeil“ und „Doppelquadrat“.`,a:`Haus mit Pfeil: kurzschlussfester / spannungsweicher Trafo (Sicherheitstransformator, fehlersicher). Doppelquadrat (▢ im ▢): Schutzklasse II — schutzisoliert, kein Schutzleiteranschluss.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Klemmbrett 230/400 V",q:`Auf dem Leistungsschild steht 230/400 V. Welche Schaltung wird am 400-V-Netz gewählt und wie werden die Brücken gesetzt?`,a:`Am 400-V-Netz wird die Sternschaltung (Y) gewählt, weil jede Wicklung für 230 V ausgelegt ist und im Stern an jeder Wicklung 230 V (= 400 V / √3) anliegen. Brücken: U2–V2–W2 werden zusammengebrückt (Sternpunkt), Zuleitung an U1, V1, W1.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Rechtslauf/Linkslauf",q:`Wie ändert man die Drehrichtung eines Drehstrommotors?`,a:`Durch Vertauschen von zwei Außenleitern (z. B. L1 und L2). Damit kehrt sich das Drehfeld um, der Motor läuft rückwärts.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Motor · Steinmetzschaltung",q:`Warum ist für den Anschluss kleiner Drehstrommotoren am Einphasen-Wechselstromnetz ein Kondensator erforderlich?`,a:`Am Einphasennetz fehlt das Drehfeld. Der Kondensator (Betriebskondensator) erzeugt eine phasenverschobene „Hilfsphase“ für die dritte Wicklung — so entsteht ein Drehfeld , und der Motor läuft an (Steinmetzschaltung). Dabei sinkt die Leistung auf ca. 70 % der Nennleistung .`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Kompensation · warum",q:`Warum wird Blindstrom kompensiert?`,a:`Blindstrom (bei Spulen, Trafos, Motoren) erzeugt das Magnetfeld, kann aber nicht in Wirkleistung umgesetzt werden. Kompensation, um: Blindstromkosten zu senken Leitungen, Trafos und Schaltgeräte zu entlasten den Spannungsfall der Leitung zu reduzieren`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Kompensation · Reihe vs. Parallel",q:`Erklären Sie den Unterschied zwischen Reihen- und Parallelkompensation.`,a:`Parallelkompensation: Kondensator parallel zum Verbraucher — Standard für Motoren/Anlagen, verbessert cos φ des Netzes. Reihenkompensation: Kondensator in Reihe (z. B. bei Leuchtstofflampen-Vorschaltgeräten / „duo-Schaltung“) — wirkt im einzelnen Betriebsmittel.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Kompensation · warum nicht cos φ = 1",q:`Warum wird nicht auf cos φ = 1 kompensiert?`,a:`Bei exakter Kompensation auf 1 besteht die Gefahr der Überkompensation (kapazitiv) bei Lastschwankungen und von Resonanzen / Schwingungen mit dem Netz. Üblich ist daher cos φ ≈ 0,9 … 0,95 .`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Kompensation · Kondensator berechnen",q:`Ein Fleischwolf (230 V, 14 A, cos φ₁ = 0,7) soll auf cos φ₂ = 0,95 kompensiert werden. Berechnen Sie die Kapazität.`,a:`Wirkleistung: P = U · I · cosφ₁ = 230 · 14 · 0,7 = 2254 W φ₁ = arccos(0,7) = 45,57° → tanφ₁ = 1,0202 φ₂ = arccos(0,95) = 18,19° → tanφ₂ = 0,3287 Blindleistung des Kondensators: Q_C = P · (tanφ₁ − tanφ₂) Q_C = 2254 · (1,0202 − 0,3287) Q_C = 2254 · 0,6915 ≈ 1558 var Kapazität: C = Q_C / (U² · 2π·f) C = 1558 / (230² · 314,16) C = 1558 / (52900 · 314,16) C ≈ 93,8 µF`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Trafo · Übersetzung",q:`230 V Netz, 5 Magnetventile mit 24 V. Berechnen Sie das Übersetzungsverhältnis des Trafos.`,a:`Formel: ü = U₁ / U₂ = N₁ / N₂ ü = 230 / 24 ü ≈ 9,58`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Trafo · Leistung",q:`Für welche Leistung muss der Trafo ausgelegt werden, wenn alle 5 Ventile (je 24 V, 120 mA) gleichzeitig laufen?`,a:`Leistung je Ventil: P = U · I = 24 V · 0,12 A = 2,88 VA Gesamt (5 Ventile): P_ges = 5 · 2,88 VA P_ges = 14,4 VA`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Trafo · Primärstrom",q:`Wie hoch ist der Strom in der Primärspule? (P = 14,4 VA, U₁ = 230 V)`,a:`Formel: I₁ = P / U₁ I₁ = 14,4 / 230 I₁ ≈ 0,063 A ≈ 63 mA`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Trafo · Luftspalt",q:`Welche Funktion hat der Luftspalt im Eisenkern?`,a:`Der Luftspalt verhindert die magnetische Sättigung des Eisenkerns und linearisiert die Kennlinie — wichtig bei Gleichstromanteil/Drosseln, hält die Induktivität stabiler.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Trafo · spannungsweicher Trafo",q:`Nennen Sie ein Anwendungsbeispiel für einen spannungsweichen Trafo.`,a:`Klingeltrafo / Spielzeugtrafo / Schweißtrafo — die Ausgangsspannung bricht bei Belastung gewollt ein, dadurch kurzschlussfest .`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Leistungsschild lesen",q:`Auf einem Motor-Leistungsschild stehen verschiedene Spannungsangaben. Wann Stern, wann Dreieck am 400-V-Netz?`,a:`230/400 V: nur Stern am 400-V-Netz (jede Wicklung 230 V). 400/690 V: Stern oder Dreieck möglich — im Dreieck 400 V je Wicklung (Δ 400 V), im Stern für 690-V-Netz. Die kleinere Spannungsangabe ist die Wicklungsspannung. Liegt sie auf der Netz-Außenleiterspannung → Dreieck; liegt die Netzspannung darüber → Stern.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Garagentormotor (Prüfaufgabe)",q:`Ein Wechselstrom-Garagentormotor: P = 700 W, η = 91 %, cos φ = 0,89. a) Bemessungsstrom für den Motorschutzschalter? b) Gleiche Daten als Drehstrommotor — welcher Strom?`,a:`a) Wechselstrom (1~), U = 230 V Formel: I = P / (U · η · cosφ) I = 700 / (230 · 0,91 · 0,89) I = 700 / 186,3 I ≈ 3,76 A b) Drehstrom (3~), U = 400 V Formel: I = P / (√3 · U · η · cosφ) I = 700 / (1,732 · 400 · 0,91 · 0,89) I = 700 / 561,1 I ≈ 1,25 A Der Drehstrommotor zieht bei gleicher Leistung deutlich weniger Strom je Leiter → Motorschutzschalter niedriger einstellen.`},
  {tid:"pa",topic:"Potentialausgleich",ref:"PA · Aufgabe",q:`Wozu dient der Schutzpotentialausgleich?`,a:`Er verbindet alle leitfähigen Teile, sodass im Fehlerfall keine gefährlichen Berührungsspannungen / Potentialunterschiede entstehen. Alle Teile liegen möglichst auf gleichem Potential → Personenschutz.`},
  {tid:"pa",topic:"Potentialausgleich",ref:"PA · Bauteil erkennen (HES)",q:`Welches Bauteil ist die Sammelschiene mit Anschlüssen für PE/PEN, Antenne, Gas-, Heizungs-, Wasserrohre, Blitzschutz und Fundamenterder?`,a:`Die Haupterdungsschiene (HES) — auch Potentialausgleichsschiene (PAS). Zentraler Sternpunkt des gesamten Potentialausgleichs.`},
  {tid:"pa",topic:"Potentialausgleich",ref:"PA · anzuschließende Teile",q:`Welche Teile sind an den Schutz-Potentialausgleich anzuschließen? Nennen Sie mehrere.`,a:`Schutzleiter (PE/PEN) Fundamenterder / Erdungsleiter Metallene Wasser-, Gas- und Heizungsrohre Metallene Lüftungs-/Klimakanäle Blitzschutzanlage Antennenerdung, leitfähige Gebäudeteile`},
  {tid:"pa",topic:"Potentialausgleich",ref:"PA · Querschnitte",q:`Mit welchem Querschnitt muss der Potentialausgleich ausgeführt werden?`,a:`Haupt-Schutzpotentialausgleich: mind. 6 mm² Cu , in der Regel 16 mm² , mind. die Hälfte des größten Schutzleiters. Zusätzlicher (örtlicher) PA: mind. 2,5 mm² (geschützt verlegt) bzw. 4 mm² (ungeschützt). Blitzschutz-PA: mind. 16 mm² Cu .`},
  {tid:"pa",topic:"Potentialausgleich",ref:"PA · Bad",q:`Ist im Badezimmer immer ein zusätzlicher Potentialausgleich notwendig? Begründen Sie.`,a:`Nicht zwingend. Auf den zusätzlichen PA kann verzichtet werden, wenn alle Stromkreise des Bades über einen RCD ≤ 30 mA geschützt sind, der Haupt-PA vorhanden ist und keine fremden leitfähigen Teile ein Potential einschleppen (DIN VDE 0100-701).`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · Aufgaben Fundamenterder",q:`Nennen Sie 2 Aufgaben, die ein Fundamenterder erfüllen muss.`,a:`Sicherer Erdungswiderstand für Schutzmaßnahmen (Schutz-/Funktionserdung) Bezugspotential für den Potentialausgleich und (Anschluss-)Erder für den Blitzschutz/Überspannungsschutz`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · Erderarten",q:`Nennen Sie 3 verschiedene Erderarten.`,a:`Fundamenterder (im Beton-Fundament) Ringerder (um das Gebäude im Erdreich) Tiefen-/Staberder (senkrecht eingetrieben) (Banderder / Oberflächenerder)`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · Mindestmaße",q:`Welche Mindestabmessungen gelten für einen Fundamenterder (feuerverzinkter Rund-/Bandstahl)?`,a:`Bandstahl: mind. 30 × 3,5 mm Rundstahl: mind. Ø 10 mm Verzinkter Stahl, in den Beton eingebettet, mind. 5 cm Betonüberdeckung.`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · Maschenweite",q:`Welche maximale Maschenweite ist bei Fundament-/Ringerdern einzuhalten?`,a:`Maximal 20 m × 20 m Maschenweite (nach DIN 18014). Bei großen Fundamenten wird der Erder zu einem Maschennetz ergänzt.`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · Norm",q:`In welcher DIN-Norm ist die Ausführung von Erdungsanlagen (Fundamenterder) geregelt?`,a:`DIN 18014 — „Fundamenterder, Planungs-, Ausführungs- und Dokumentationsgrundlagen“. Ergänzend DIN VDE 0100-540.`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · wer darf",q:`Wer darf Erder einbringen?`,a:`Das Einbringen (Verlegen im Fundament/Erdreich) darf durch Bau-/Tiefbau erfolgen, aber die elektrotechnische Planung, Anschluss und Dokumentation muss durch eine Elektrofachkraft erfolgen bzw. überwacht werden.`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · weiße/schwarze Wanne",q:`Erklären Sie die Begriffe weiße und schwarze Wanne.`,a:`Schwarze Wanne: Abdichtung des Bauwerks gegen Wasser durch außen aufgebrachte Bitumen-/Dichtungsbahnen (schwarz). Weiße Wanne: Bauwerk aus wasserundurchlässigem Beton (WU-Beton) — der Beton selbst dichtet. Wichtig für den Erder: bei abgedichtetem/isoliertem Fundament wirkt der Beton-Erder nicht nach außen → es ist ein zusätzlicher Ringerder außerhalb der Abdichtung nötig.`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · max. Widerstand RCD",q:`In der UV ist ein RCD Typ A / 40 A / 30 mA eingebaut. Berechnen Sie den max. zulässigen Erdungswiderstand.`,a:`Formel: R_A ≤ U_L / I_Δn (U_L = zulässige Berührungsspannung 50 V) R_A ≤ 50 V / 0,03 A R_A ≤ 1666 Ω Der Erdungswiderstand muss also unter ~1667 Ω liegen — bei 30 mA RCD praktisch immer leicht erfüllt.`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · drei Netzsysteme",q:`Nennen Sie die Netzsysteme.`,a:`TN-System (TN-C, TN-S, TN-C-S) TT-System IT-System`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · Buchstaben",q:`Erklären Sie die Buchstaben T, N, C, S.`,a:`1. Buchstabe (Erdung der Quelle): T = direkt geerdet (Terra), I = isoliert/über Impedanz. 2. Buchstabe (Körper der Anlage): T = eigener Erder, N = mit Betriebserder verbunden (Neutral). Zusatz: C = N und PE kombiniert (PEN, Combined), S = getrennt (Separated).`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · TN-C Vorteile",q:`Welche Vorteile bietet das TN-C-System und welche Schutzeinrichtungen dürfen verwendet werden?`,a:`Vorteil: nur ein kombinierter PEN-Leiter (Material-/Kostenersparnis). Schutz durch Überstromschutzeinrichtungen (LS/Sicherung) . Ein RCD ist nicht möglich , da N und PE nicht getrennt sind. PEN nur ab 10 mm² Cu / 16 mm² Al zulässig.`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · Schutz neben LS im TN",q:`Welche Schutzmaßnahmen werden neben dem LS-Schalter im TN-Netz angewandt? Nennen Sie 2.`,a:`Fehlerstromschutzeinrichtung (RCD) Schutzpotentialausgleich / Schutzerdung (Schutztrennung, Schutzkleinspannung in Sonderfällen)`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · TT→TN, NYM-O",q:`Ein TT-Netz wird auf TN umgestellt. Darf die NYM-O-Leitung weiterverwendet werden?`,a:`Nein — die NYM- O -Leitung hat keinen grün-gelben Schutzleiter (O = ohne PE). Im TN-System wird ein PE benötigt → es muss auf NYM-J (mit Schutzleiter) umgestellt werden.`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · PEN→HES Funktion",q:`Am HAK verbindet eine Leitung den PEN mit der Haupterdungsschiene. Welche Funktion erfüllt sie?`,a:`Sie bindet den PEN/PE in den Schutzpotentialausgleich ein und stellt die Erdung des Systems am Gebäudeeintritt her — Bezugspotential für die gesamte Anlage.`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · RCD je Stromkreis",q:`Für jeden Stromkreis soll ein eigener RCD verwendet werden — ist das möglich?`,a:`Ja, das ist möglich und erhöht die Verfügbarkeit (bei Fehler fällt nur ein Stromkreis aus, bessere Selektivität). Üblicher ist aus Kostengründen ein RCD je Gruppe, aber zulässig ist ein RCD je Stromkreis.`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Fehlerarten · zuordnen",q:`Nennen Sie die typischen Fehlerarten in Verteilnetzen.`,a:`Fehler 1 – Leerlauf / Unterbrechung (Leitungsbruch) Fehler 2 – Kurzschluss (L gegen N/L) Fehler 3 – Erdschluss (aktiver Leiter gegen Erde) Fehler 4 – Körperschluss (aktiver Leiter gegen leitfähiges Gehäuse)`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Fehlerarten · Leuchte schaltet nicht",q:`In einem Stromkreis lässt sich die Leuchte nicht mehr schalten. Welche Fehlerart liegt vor?`,a:`Unterbrechung / Leerlauf (Fehler 1) — z. B. Leitungsbruch oder lose Klemme, der Stromkreis ist offen.`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Fehlerarten · Körperschluss",q:`Welches Schutzorgan schaltet einen Körperschluss (Fehler 4) ab?`,a:`Die Fehlerstromschutzeinrichtung (RCD) — sie erkennt den über PE/Erde abfließenden Fehlerstrom. Bei genügend hohem Fehlerstrom kann auch der LS/die Sicherung auslösen (Abschaltung durch Überstrom im TN).`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · 30 vs 300 mA",q:`Nennen Sie die Aufgabe eines 30-mA-RCDs und eines 300-mA-RCDs.`,a:`30 mA: Personenschutz (zusätzlicher Schutz, z. B. Steckdosen ≤ 32 A, Bäder, außen). 300 mA: Brandschutz (vorbeugender Brandschutz bei Fehlerströmen, kein Personenschutz).`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · Grundprinzip",q:`Wie ist das Grundprinzip der Fehlerstrom-Schutzeinrichtung?`,a:`Über einen Summenstromwandler wird die Summe der hin- und rückfließenden Ströme gemessen. Im fehlerfreien Fall ist sie null . Fließt ein Teil über Erde ab (Fehlerstrom), entsteht eine Differenz → der RCD löst aus .`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · Nenndaten",q:`Was sind die wichtigsten Nenndaten einer Fehlerstrom-Schutzeinrichtung?`,a:`Bemessungsstrom I_n (z. B. 40 A) Bemessungsfehlerstrom I_Δn (z. B. 30 mA) Typ (AC, A, F, B …) Bemessungsspannung / Polzahl`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · Test-Taste",q:`Weshalb muss man die Test-Taste des RCD betätigen und welcher Hinweis gilt für den Kunden?`,a:`Die Test-Taste prüft die mechanische Auslösefunktion des RCD. Hinweis an den Kunden: in regelmäßigen Abständen (Empfehlung ~ halbjährlich) selbst testen, damit der Mechanismus nicht „festsitzt“.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · Prüftaste prüft was nicht",q:`Welche Funktion prüft die Prüftaste eines RCD und welche nicht?`,a:`Sie prüft die Funktion der Auslöseeinheit (interner Fehlerstrom). Sie prüft nicht die korrekte Erdung / den Schutzleiteranschluss der Anlage und nicht den tatsächlichen Auslösefehlerstrom unter realen Bedingungen.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"LS · Auslösemechanismen",q:`Nennen Sie die 2 Auslösemechanismen eines LS-Schalters und die zugehörigen Fehler.`,a:`Thermisch (Bimetall): bei Überlast — verzögert. Magnetisch (Elektromagnet): bei Kurzschluss — sofort.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"LS · B vs C",q:`Wodurch unterscheiden sich Leitungsschutzschalter Typ B und C? Nennen Sie 3 Charakteristiken.`,a:`Unterschied ist der magnetische Auslösebereich (Vielfaches des Nennstroms): B: löst bei 3–5 · I_n aus → Haushalt, ohmsche Lasten. C: löst bei 5–10 · I_n aus → induktive Lasten mit Einschaltstrom (Motoren, Trafos). D: 10–20 · I_n → hohe Einschaltströme.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"LS · Unterschied Sicherung",q:`Welcher Unterschied besteht zwischen LS-Schalter und Schmelzsicherung? Welche Vorteile hat der LS?`,a:`Die Schmelzsicherung wird beim Auslösen zerstört und muss ersetzt werden; der LS-Schalter ist wiedereinschaltbar . Vorteile LS: wiederverwendbar, definierte Charakteristik, schaltet alle Pole, gut erkennbarer Zustand.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"LS · B16 bei 24 A",q:`Ein B16-Automat wird mit 24 A belastet. Wann muss er spätestens auslösen?`,a:`24 A = 1,5 · I_n . Das liegt im thermischen (Überlast-)Bereich . Nach der Auslösekennlinie muss er innerhalb der großen Prüfzeit (≤ 1 h) auslösen (beim 1,45-fachen sicheres Auslösen innerhalb 1 h bei I_n ≤ 63 A).`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"Sicherungen · 3 Arten",q:`Nennen Sie 3 Arten von Sicherungen für den Leitungsschutz.`,a:`Schmelzsicherung D (Diazed) Schmelzsicherung D0 (Neozed) NH-Sicherung (Niederspannungs-Hochleistung) (Leitungsschutzschalter)`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"LS · Aufgaben SLS",q:`Welche Aufgaben hat der SLS-Schalter? Nennen Sie 3.`,a:`Selektiver Hauptleitungsschutz (vor dem Zähler / Hauptstromversorgung) Selektivität zu nachgeschalteten LS-Schaltern Hauptschalter-/Trennfunktion für die Anlage Schutz der Hauptleitung vor Überlast und Kurzschluss`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · 40 A begründen",q:`Es wird ein RCD mit Bemessungsstrom 40 A gewählt. Begründen Sie.`,a:`Der RCD-Bemessungsstrom muss ≥ dem Summenstrom der dahinterliegenden Stromkreise und ≥ dem vorgeschalteten Überstromschutz sein. Bei z. B. 2 × B16 + Reserve wählt man die nächstgrößere Normgröße (40 A), damit der RCD thermisch nicht überlastet wird.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Inbetriebnahme Reihenfolge",q:`Wie sehen die notwendigen Schritte und deren Reihenfolge für die Inbetriebnahme einer Anlage aus?`,a:`Erst Besichtigen, dann Erproben, dann Messen (nach DIN VDE 0100-600): Besichtigen (Sichtprüfung, spannungsfrei) Erproben/Funktionsprüfung Messen: Durchgängigkeit Schutzleiter/PA → Isolationswiderstand → (Schutz durch automatische Abschaltung) → Schleifenimpedanz, RCD-Prüfung Dokumentation / Protokoll`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Wiederholungsprüfung Norm",q:`Nach welcher Norm wird die Wiederholungsprüfung ortsfester Anlagen durchgeführt?`,a:`DIN VDE 0105-100 (Betrieb von elektrischen Anlagen). Erstprüfung nach DIN VDE 0100-600.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Besichtigen",q:`Was sind notwendige Tätigkeiten beim Besichtigen (Sichtprüfung)?`,a:`Richtige Auswahl der Betriebsmittel, Schutzmaßnahmen Leiterkennzeichnung, Anschlüsse, Klemmstellen Beschädigungen, Schutz gegen direktes Berühren Vorhandensein von Schaltplänen, Beschriftung, Schutzeinrichtungen`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Wechsel/Kreuzschaltung",q:`Wie wird die Funktion einer Wechsel-/Kreuzschaltung vollständig geprüft?`,a:`Durch Durchschalten aller Schalterstellungen : jede Schalterkombination muss die Leuchte ein- und ausschalten können. Bei 2 Schaltern (Wechsel): 4 Kombinationen prüfen.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Zweck Isolationswiderstand",q:`Welchen Zweck hat die Prüfung des Isolationswiderstandes?`,a:`Nachweis, dass die Isolierung intakt ist — keine gefährlichen Ableit-/Fehlerströme zwischen aktiven Leitern und gegen PE/Erde. Schutz vor Fehlerströmen und Bränden.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Messspannung Iso",q:`Erläutern Sie die Wahl der Messspannung bei der Isolationsmessung.`,a:`Die Messspannung richtet sich nach der Nennspannung des Stromkreises. Für übliche 230/400-V-Anlagen wird mit 500 V DC gemessen; Mindest-Isolationswiderstand ≥ 1 MΩ . Bei SELV/PELV 250 V DC (≥ 0,5 MΩ).`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Iso 0,5 MΩ Herd",q:`Der Isolationswiderstand am Elektroherd beträgt 0,5 MΩ. Ist das zulässig?`,a:`Nein. Für Stromkreise bis 500 V wird R_iso ≥ 1 MΩ gefordert. 0,5 MΩ liegt darunter → Mangel, Ursache suchen (Feuchtigkeit, schadhafte Heizwendel).`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Schleifenimpedanz vs Netzinnenwiderstand",q:`Wie unterscheiden sich Schleifenimpedanz und Netzinnenwiderstand?`,a:`Schleifenimpedanz Z_S: Außenleiter → über PE/Schutzleiter zurück (Fehlerschleife L-PE). Maßgeblich für die Abschaltbedingung. Netzinnenwiderstand Z_I: Außenleiter → Neutralleiter (L-N). Maßgeblich für Kurzschlussstrom/Spannungsfall.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · IK B16",q:`Welcher Kurzschlussstrom I_K wird mindestens erwartet für einen LS B16?`,a:`Der I_K muss so groß sein, dass die magnetische Auslösung sicher anspricht: oberes Ende 5 · I_n = 5 · 16 A = 80 A . Mit Sicherheitsfaktor wird häufig der 1,5-fache Wert (~ ≥ 120 A) gemessen gefordert, damit die Abschaltbedingung sicher erfüllt ist.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Durchgang auf Riso",q:`Bei der Schutzleiter-Durchgangsmessung ist das Gerät auf R_iso eingestellt. Auswirkung?`,a:`Statt mit kleinem Prüfstrom (200 mA, niederohmig) wird mit hoher Prüfgleichspannung gemessen — der niederohmige Schutzleiter wird so nicht korrekt bewertet, mögliche Beschädigung empfindlicher Bauteile, falsches/unbrauchbares Ergebnis.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Berührungsstrom 0 mA",q:`Am metallenen Motorgehäuse messen Sie 0,0 mA Berührungsstrom. Warum?`,a:`Das Gehäuse ist korrekt mit dem Schutzleiter (PE) verbunden — kein Fehler vorhanden, daher kein Berührungsstrom. (Oder das Gerät ist schutzisoliert SK II.)`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Geräteprüfung · wer darf",q:`Wer darf die Prüfung beweglicher Geräte nach DIN VDE 0701/0702 durchführen?`,a:`Eine Elektrofachkraft oder eine elektrotechnisch unterwiesene Person (EUP) unter Leitung und Aufsicht einer Elektrofachkraft, mit geeigneten Messgeräten.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Geräteprüfung · Sichtprüfung",q:`Nennen Sie 5 Bestandteile der Sichtprüfung bei der Geräteprüfung.`,a:`Anschlussleitung / Stecker auf Schäden Gehäuse und Schutzabdeckungen Zugentlastung und Biegeschutz Aufschriften / Bemessungsdaten lesbar Verschmutzung, Korrosion, Überlastungsspuren`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Geräteprüfung · Schutzleiterwiderstand",q:`Bei einer Kabeltrommel (25 m, 1,5 mm²) messen Sie 0,88 Ω Schutzleiterwiderstand. In Ordnung?`,a:`Grundwert: bis 5 m Leitung R ≤ 0,3 Ω Je weitere 7,5 m: + 0,1 Ω (Grenzwert max. 1 Ω) 25 m → 5 m Basis + 20 m 20 m / 7,5 m ≈ 2,67 → 3 · 0,1 Ω = 0,3 Ω Grenzwert: 0,3 + 0,3 = 0,6 Ω (begrenzt auf max. 1 Ω) Messwert 0,88 Ω > 0,6 Ω Wert ist (knapp) zu hoch für die berechnete Länge — Übergangswiderstand prüfen. Absolut bleibt er unter dem Maximalgrenzwert 1 Ω.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Geräteprüfung · Ursache hoher Wert",q:`Welche Ursache kann ein zu hoher Schutzleiterwiderstand haben?`,a:`Lockere/korrodierte Klemmstelle, schlechter Kontakt Beschädigte / angebrochene Schutzleiteradern (Kabelbruch) Zu langer/zu dünner Schutzleiter`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Prüfstrom Niederohm",q:`Welcher Prüfstrom ist bei der Messung niederohmiger Verbindungen (Schutzleiter/PA) vorgeschrieben und warum kein Multimeter?`,a:`Es dürfen nur Messgeräte nach DIN VDE 0413-4 verwendet werden. Der Prüfstrom muss ± 200 mA (DC oder AC) betragen. Ein gewöhnliches Ohmmeter / Multimeter ist nicht geeignet , weil es diesen Prüfstrom nicht liefert. Bei Gleichstrommessung ist die Polarität zu wechseln — unterschiedliche Messwerte bei Polaritätsumkehr deuten auf einen Fehler hin (z. B. Korrosion, Übergangsstelle).`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · N und PE trennen",q:`Warum muss man im TN-System bei der Niederohm-/Isolationsmessung N und PE trennen?`,a:`Sind N und PE verbunden, würde man bei einem Vertauschen von N und PE den Fehler nicht erkennen — die Messung würde fälschlich „in Ordnung“ anzeigen. Durch das Trennen wird eine Fehlinterpretation vermieden und jede Verbindung eindeutig geprüft.`},
  {tid:"hausanschluss",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Hausanschluss · weitere Möglichkeiten",q:`Nennen Sie neben dem Hausanschlussraum 2 weitere Möglichkeiten, den Hausanschluss fachgerecht auszuführen.`,a:`Hausanschlusswand Hausanschlussnische (mit Außentür) (Hausanschlusssäule)`},
  {tid:"hausanschluss",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Hausanschluss · Wand vs Nische",q:`Erklären Sie den Unterschied zwischen Hausanschlusswand und Hausanschlussnische.`,a:`Hausanschlusswand: die Anschlusseinrichtungen sind an einer Wand im Gebäude/Hausanschlussraum montiert. Hausanschlussnische: eine von außen zugängliche Nische in der Außenwand mit eigener Tür — der EVU-Mitarbeiter kommt ohne Betreten des Gebäudes an den Anschluss.`},
  {tid:"hausanschluss",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Hausanschluss · Netzsystem Abbildung",q:`Um welches Netzsystem handelt es sich, wenn der PEN in N und PE aufgeteilt wird (mit Haupterdungsschiene)?`,a:`Ein TN-C-S-System — bis zur Aufteilung gemeinsam (TN-C, PEN), ab der HES getrennt in N und PE (TN-S).`},
  {tid:"hausanschluss",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Einspeisung · TN-C → TN-S",q:`Laut VDE-AR-N 4101 muss TN-C im HAK in TN-S aufgetrennt werden. Vorteil der Auftrennung am Einspeisepunkt?`,a:`Nach der Auftrennung ist der PE stromlos (nur im Fehlerfall stromführend). Das ermöglicht den Einsatz von RCDs für die ganze Anlage, verbessert EMV und Personenschutz. Bei Auftrennung erst im UV wären Teile davor noch TN-C (kein RCD-Schutz).`},
  {tid:"hausanschluss",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Einspeisung · Schleifenimpedanz HAK",q:`Am HAK (63 A) messen Sie eine Schleifenimpedanz von 2,5 Ω zwischen Außenleiter und PEN. Ist das ausreichend?`,a:`Kurzschlussstrom: I_K = U₀ / Z_S I_K = 230 V / 2,5 Ω = 92 A Erforderlich für Abschaltung (gG-Sicherung 63 A, Abschaltung in 5 s): grob ≈ 5 · I_n = 315 A Nicht ausreichend — der Kurzschlussstrom (92 A) ist zu klein, um die 63-A-Sicherung sicher in der geforderten Zeit auszulösen. Schleifenimpedanz zu hoch.`},
  {tid:"hausanschluss",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Zähler · Ladesäule Querschnitt",q:`Ein Zählerplatz hat 10 mm² interne Verdrahtung. Eine Ladesäule mit 32 A (Dauerstrom) soll angeschlossen werden. Reicht das?`,a:`Bei Dauerstrom ist der Belastungsgrad höher. 10 mm² Cu trägt je nach Verlegeart ~ 40–50 A — für 32 A Dauerstrom ist die Strombelastbarkeit zwar grenzwertig ausreichend, aber für Dauerbetrieb muss die Häufung/Erwärmung im Zählerschrank berücksichtigt werden. In der Regel ist 10 mm² hier zu knapp → größerer Querschnitt / dauerstromfeste Auslegung (Anlage 3 / TAB beachten).`},
  {tid:"hausanschluss",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Zähler · alten Platz weiterverwenden",q:`Dürfen Sie einen vorhandenen Zählerplatz grundsätzlich für eine neue Anlage verwenden?`,a:`Nur wenn er den aktuellen Normen/TAB entspricht (z. B. eHZ-tauglich, ausreichender Raum, dauerstromfest, VDE-AR-N 4101). Alte Zählerplätze entsprechen oft nicht mehr den heutigen Anforderungen → meist Erneuerung nötig.`},
  {tid:"koerper",topic:"Körperstrom & Gefahren des Stroms",ref:"Körperstrom · berechnen",q:`Welcher Strom fließt durch den Menschen, wenn R_K = 1 kΩ und R_x = 120 Ω (B16-A-Stromkreis)?`,a:`Formel: I = U / (R_K + R_x) U = 230 V R_K = 1000 Ω R_x = 120 Ω I = 230 / (1000 + 120) I = 230 / 1120 I ≈ 0,205 A ≈ 205 mA 205 mA sind lebensgefährlich (weit über der Loslassgrenze, Herzkammerflimmern möglich).`},
  {tid:"koerper",topic:"Körperstrom & Gefahren des Stroms",ref:"Körperstrom · Wirkung 100 ms",q:`Welche Auswirkungen hätte ein Strom bei 100 ms Einwirkdauer? (Strom-Zeit-Diagramm AC)`,a:`Bereich 1 (&lt; 0,5 mA): keine Wahrnehmung. Bereich 2: spürbar, keine schädlichen Wirkungen. Bereich 3: Muskelverkrampfung, Atembeschwerden — reversibel. Bereich 4 (&gt; ca. 50 mA bei 100 ms): Herzkammerflimmern, Lebensgefahr. Bei 100 ms und höheren Strömen steigt die Gefahr von Herzkammerflimmern stark an.`},
  {tid:"koerper",topic:"Körperstrom & Gefahren des Stroms",ref:"Körperstrom · Schutzkontakt nicht durchgeklemmt",q:`Bei schutzisolierten Leuchten (Anlage mit RCD) ist der Schutzkontakt nicht durchgeklemmt. 2 mögliche Auswirkungen?`,a:`Nachfolgende Geräte/Steckdosen haben keinen Schutzleiter mehr (PE unterbrochen) → kein Schutz bei Körperschluss. Im Fehlerfall keine automatische Abschaltung über PE / kein definierter Bezugspunkt → gefährliche Berührungsspannung möglich.`},
  {tid:"koerper",topic:"Körperstrom & Gefahren des Stroms",ref:"Körperstrom · RCD löst nicht aus",q:`Sie prüfen an einer Steckdose den RCD; er löst nicht aus. 3 mögliche Fehlerursachen?`,a:`Schutzleiter (PE) nicht/falsch angeschlossen → kein Rückweg für den Prüfstrom. RCD defekt oder Auslösemechanismus klemmt. Falsch verdrahtet (N und PE vertauscht/gebrückt), Messgerät falsch eingestellt.`},
  {tid:"koerper",topic:"Körperstrom & Gefahren des Stroms",ref:"Schutzmaßnahmen · Iso 0,26 MΩ überall",q:`Bei der Isolationsmessung messen Sie in ALLEN Stromkreisen denselben Wert 0,26 MΩ. Welcher Fehler?`,a:`Ein gemeinsamer Fehler — typischerweise sind alle N-Leiter auf einer durchgehenden N-Schiene verbunden (nicht je Stromkreis aufgetrennt). Es wird in Wahrheit immer dieselbe Schleife gemessen. Zum korrekten Messen müssen die Stromkreise/N-Leiter aufgetrennt werden.`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · strukturierte Verkabelung",q:`Was versteht man unter strukturierter Verkabelung und in welche Bereiche teilt man sie?`,a:`Einheitliche, dienstneutrale Verkabelung nach DIN EN 50173 / ISO IEC 11801 . Aufteilung in: Primär (Campus, zwischen Gebäuden) — meist LWL Sekundär (Backbone/Steigbereich) — meist LWL Tertiär (Etagenverkabelung) — meist Kupfer (Twisted Pair)`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · Klasse vs CAT",q:`Was ist der Unterschied zwischen „Klasse“ und „CAT“ (Kategorie)?`,a:`Klasse: Leistungsfähigkeit der gesamten Übertragungsstrecke (z. B. Klasse E). CAT (Kategorie): Leistungsfähigkeit der einzelnen Komponenten (Kabel, Dosen, Patchfelder, z. B. Cat 6). Cat-6-Komponenten allein garantieren noch keine Klasse-E-Strecke — die fertige Installation muss gemessen/zertifiziert werden.`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · Abnahme Verdrahtungsfehler",q:`Nach einer Datenverkabelung ist eine Abnahmemessung nötig. Welche Verdrahtungsfehler können auftreten? Nennen Sie 3.`,a:`Vertauschte Adernpaare (Crossed Pairs) Aufgesplittete Paare (Split Pairs) → Übersprechen Unterbrechung / Kurzschluss einer Ader, vertauschte Stifte (Reversed Pair)`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · Permanent Link vs Channel",q:`Was ist der Unterschied zwischen Permanent Link und Channel Link?`,a:`Permanent Link: fest installierte Strecke bis 90 m, je ein Steckverbinder an jedem Ende (ohne Patchkabel). Channel Link: komplette Verbindung inkl. Rangier-/Geräteanschlusskabel (bis zu 100 m gesamt).`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · 250 m Strecke",q:`Die Strecke vom Verteilerschrank bis zum Gebäudeverteiler beträgt 250 m. Welche Komponenten werden benötigt?`,a:`Kupfer (TP) ist auf 100 m begrenzt → für 250 m wird Lichtwellenleiter (LWL/Glasfaser) benötigt, mit Medienkonvertern / Switches mit SFP-Modulen an beiden Enden zur Umsetzung Kupfer↔LWL.`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · LWL-Sicherheit",q:`Welche Sicherheitsmaßnahmen sind beim Spleißen und Umgang mit LWL zu beachten? Nennen Sie 3.`,a:`Nicht in die Faser/Stecker blicken (Laserlicht, Augenschäden). Faserreste sicher entsorgen (Glassplitter, Schutzbrille). Sauberkeit/Reinigung der Stecker, keine Verschmutzung; nicht essen/trinken am Arbeitsplatz.`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · schlechtes WLAN",q:`Ein Kunde klagt über schlechte WLAN-Verbindung zur FritzBox. Nennen Sie 3 mögliche Gründe.`,a:`Große Entfernung / dicke Wände / Stahlbeton → Dämpfung. Störungen durch andere Funknetze / Geräte (überlappende Kanäle, Mikrowelle). Veralteter Standard / Router ungünstig platziert (Boden, Schrank) / zu viele Geräte.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Biegeradius NYM",q:`Welchen Biegeradius muss man bei der Mantelleitung NYM beachten?`,a:`Mindestbiegeradius etwa das 4-fache des Leitungsdurchmessers bei fester Verlegung (Herstellerangaben beachten). Zu enges Biegen beschädigt Adern/Isolierung.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Installationszonen",q:`In welchen Bereichen sind die Installationszonen für die Leitungsverlegung im Wohnbereich definiert?`,a:`Nach DIN 18015-3 : Waagerechte Zonen: 15–45 cm oben (unter der Decke) und 15–45 cm über dem Fußboden . Senkrechte Zonen: 10–30 cm neben Türen, Fenstern und Raumecken .`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Badbereiche",q:`Welche Bereiche sind in Räumen mit Badewanne/Dusche zu beachten?`,a:`Nach DIN VDE 0100-701 : Bereich 0: Inneres der Wanne/Dusche — nur SELV ≤ 12 V. Bereich 1: senkrecht über Wanne/Dusche bis 2,25 m — nur fest angeschlossene, geeignete Geräte. Bereich 2: 0,6 m seitlich um Bereich 1 — eingeschränkt zulässig (z. B. Leuchten Schutzart beachten).`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Querschnitt/Spannungsfall",q:`Wie berechnet man den nötigen Querschnitt aus dem zulässigen Spannungsfall?`,a:`Formel (Wechselstrom): A = (2 · l · I · cosφ) / (γ · Δu) A = Querschnitt in mm² l = einfache Leitungslänge (m) I = Strom (A) γ = Leitwert Cu = 56 m/(Ω·mm²) Δu = zulässiger Spannungsfall (V) (Faktor 2 = Hin- und Rückleiter; bei Drehstrom √3 statt 2) Zulässiger Spannungsfall im Wohnbereich i. d. R. ≤ 3 % von der Hauptverteilung bis zum Verbraucher.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Verbindungstechnik öffentliche Gebäude",q:`Wie nennt man die Klemmtechnik, die in öffentlichen Gebäuden vorgeschrieben ist, und ihr Vorteil?`,a:`Reihenklemmen (Klemmtechnik auf Tragschiene). In öffentlichen Gebäuden vorgeschrieben. Vorteil: bessere Übersicht und Dokumentation , saubere/eindeutige Verdrahtung, leicht prüf- und erweiterbar.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · NOT-AUS vs NOT-HALT",q:`Was ist der Unterschied zwischen NOT-AUS und NOT-HALT?`,a:`NOT-AUS: schaltet die elektrische Energie ab — Schutz vor elektrischer Gefährdung. NOT-HALT: bringt eine Maschinenbewegung zum Stillstand — Schutz vor mechanischer Gefährdung (Energie darf für sicheres Stillsetzen erhalten bleiben).`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · SPS vs VPS",q:`Was ist der Unterschied zwischen SPS und VPS?`,a:`VPS (verbindungsprogrammierte Steuerung): Funktion durch feste Verdrahtung festgelegt (Schütze, Relais). Änderung = Umverdrahten. SPS (speicherprogrammierte Steuerung): Funktion im Programm gespeichert. Änderung = Software, Hardware bleibt.`},
  {tid:"steuerung",topic:"Steuerungstechnik · Schütz & Verriegelung",ref:"Steuerung · Last vs Steuerstromkreis",q:`Was ist der Unterschied zwischen Laststromkreis und Steuerstromkreis?`,a:`Laststromkreis (Hauptstromkreis): führt den großen Motorstrom — Sicherung, Schützhauptkontakte (1/2, 3/4, 5/6), Motorschutz, Motor. Steuerstromkreis: kleiner Strom, steuert die Schützspulen — Taster, Hilfskontakte (13/14, 21/22), Schützspule (A1/A2).`},
  {tid:"steuerung",topic:"Steuerungstechnik · Schütz & Verriegelung",ref:"Steuerung · Selbsthaltung",q:`Wie funktioniert eine Selbsthaltung (Schützschaltung)?`,a:`Ein Schließer-Hilfskontakt (13/14) des Schützes wird parallel zum EIN-Taster geschaltet. Beim Drücken zieht das Schütz an, der Hilfskontakt überbrückt den Taster — das Schütz hält sich selbst , auch wenn der Taster losgelassen wird. Der AUS-Taster (Öffner) unterbricht die Selbsthaltung.`},
  {tid:"steuerung",topic:"Steuerungstechnik · Schütz & Verriegelung",ref:"Steuerung · Schütz-Verriegelung",q:`Was bedeutet Schütz-Verriegelung (gegenseitige Verriegelung) bei einer Wendeschaltung?`,a:`In den Stromkreis jedes Schützes wird ein Öffner-Hilfskontakt (21/22) des jeweils anderen Schützes eingebaut. Ist Q1 aktiv, öffnet dessen 21/22 im Stromkreis von Q2 → Q2 kann nicht gleichzeitig anziehen . So wird ein Kurzschluss durch zwei gegenläufige Drehfelder verhindert.`},
  {tid:"steuerung",topic:"Steuerungstechnik · Schütz & Verriegelung",ref:"Steuerung · Taster- vs Schützverriegelung",q:`Worin unterscheiden sich Taster-Verriegelung und Schütz-Verriegelung?`,a:`Taster-Verriegelung: über Doppeltaster (Schließer + Öffner im selben Taster) — beim Drücken wird das Gegenschütz schon im Taster abgeschaltet. Ermöglicht direkte Umschaltung ohne vorher AUS zu drücken. Schütz-Verriegelung: über Öffner-Hilfskontakte der Schütze (21/22) — wirkt sicher elektrisch, aber Umschaltung oft nur über AUS (indirekt). In der Praxis kombiniert man beide für sichere, direkte Umschaltung.`},
  {tid:"steuerung",topic:"Steuerungstechnik · Schütz & Verriegelung",ref:"Steuerung · Wendeschütz Drehrichtung",q:`Wie kehrt eine Wendeschützschaltung die Drehrichtung des Motors um?`,a:`Es gibt zwei Schütze: Q1 schaltet die Phasen L1-L2-L3 direkt, Q2 schaltet zwei Phasen vertauscht (z. B. L1 und L3 getauscht). Dadurch dreht das Drehfeld um → Motor läuft rückwärts. Die Schütze sind gegeneinander verriegelt.`},
  {tid:"steuerung",topic:"Steuerungstechnik · Schütz & Verriegelung",ref:"Steuerung · Tippbetrieb",q:`Was versteht man unter Tippbetrieb (z. B. bei einer Torsteuerung)?`,a:`Im Tippbetrieb läuft der Antrieb nur, solange der Taster gedrückt wird — es gibt keine Selbsthaltung. Lässt man los, stoppt die Bewegung. Bei einer Torsteuerung: Tor fährt nur bei gedrücktem Taster auf/zu (Totmannprinzip), Sicherheit bei Hindernissen.`},
  {tid:"steuerung",topic:"Steuerungstechnik · Schütz & Verriegelung",ref:"Steuerung · NOT-AUS rastend",q:`Warum ist der NOT-AUS-Taster (z. B. Notaus S0 einer Toranlage) rastend ausgeführt?`,a:`Der rastende NOT-AUS bleibt nach dem Betätigen mechanisch gedrückt (verriegelt) und muss bewusst entriegelt (gedreht/gezogen) werden. So kann die Anlage nicht versehentlich wieder anlaufen, bevor die Gefahr beseitigt und quittiert ist.`},
  {tid:"blitz",topic:"Blitzschutz",ref:"Blitzschutz · Klassen",q:`In welche Klassen gliedert sich der innere Blitzschutz / Überspannungsschutz? Nennen Sie 3 Typen und ihre Aufgabe.`,a:`Typ 1 (Class I) – Blitzstromableiter: grober Schutz, leitet direkte Blitzteilströme (10/350 µs) ab. Einbau im Hauptverteiler / Zählerschrank. Typ 2 (Class II) – Überspannungsableiter: mittlerer Schutz gegen Schalt- und ferne Blitzüberspannungen (8/20 µs). Einbau in der Unterverteilung. Typ 3 (Class III) – Geräteschutz: feiner Schutz direkt am Endgerät / in der Steckdose. Merkhilfe: Typ 1 = grob, Typ 2 = mittel, Typ 3 = fein. Sie bauen ein Energiekaskaden-Konzept auf (grob → fein).`},
  {tid:"blitz",topic:"Blitzschutz",ref:"Blitzschutz · äußerer PA",q:`Welchen Querschnitt muss der Anschluss der Blitzschutzanlage an der Haupterdungsschiene mindestens haben?`,a:`Mindestens 16 mm² Kupfer (bzw. 25 mm² Aluminium oder 50 mm² Stahl) als Verbindung der äußeren Blitzschutzanlage zur Potentialausgleichsschiene/Erdung.`},
  {tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Speicher/Batterieraum",q:`Darf der Hausanschluss (HAK) in einem Batterieraum / Speichersystem-Raum montiert werden?`,a:`Nur in Abstimmung mit dem Netzbetreiber und dem Hersteller des Speichersystems. In der Entscheidungstabelle steht „nein“ mit Fußnote — also grundsätzlich nicht ohne Freigabe.`},
  {tid:"wp",topic:"Wärmepumpe & Wärmetechnik",ref:"Wärmetechnik · Durchlauferhitzer im Bad",q:`Darf ein Durchlauferhitzer im Installationsbereich (Schutzbereich) 1 im Badezimmer elektrisch angeschlossen werden? Aus welcher Norm geht das hervor?`,a:`Ja, ein fest angeschlossener Durchlauferhitzer ist im Bereich 1 zulässig, sofern er für diesen Bereich geeignet ist und über RCD (30 mA) geschützt wird. Geregelt in DIN VDE 0100-701 (Räume mit Badewanne oder Dusche).`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht · Lichtausbeute berechnen",q:`Berechnen Sie die Lichtausbeute η. Gesamtlichtstrom 50000 lm, Gesamtleistung der Beleuchtung 600 W.`,a:`η = Φ / P η = 50000 lm / 600 W η ≈ 83,3 lm/W Einordnung: 83 lm/W ist ein typischer guter LED-Wert. Glühlampe lag bei ~12 lm/W.`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht · Beleuchtungsstärke prüfen",q:`Montageraum 9,8 m × 4,75 m, 10 Leuchtstofflampen mit je 3800 lm, Beleuchtungswirkungsgrad 0,56. Wird die geforderte Beleuchtungsstärke von 500 lx eingehalten? (Wartungsfaktor schon berücksichtigt)`,a:`A = 9,8 · 4,75 = 46,55 m² Φ_gesamt = 10 · 3800 lm = 38000 lm Φ_nutz = 38000 · 0,56 = 21280 lm E = Φ_nutz / A E = 21280 / 46,55 E ≈ 457 lx 457 lx nicht eingehalten . Es müssten mehr/stärkere Leuchten eingebaut werden.`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht/Trafo · Sekundärlänge",q:`Der Trafo-Hersteller gibt für den Sekundärstromkreis (NV-Halogen) eine maximale Länge von 2 m an. Begründen Sie das.`,a:`Im Niederspannungs-Sekundärkreis (z.&nbsp;B. 11,5 V) fließen bei gleicher Leistung sehr hohe Ströme . Lange Leitungen erzeugen einen großen Spannungsfall und Verluste → die Lampen würden dunkler. Außerdem Erwärmung der Leitung. Darum kurze Leitung (≤ 2 m) und ausreichender Querschnitt.`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht/Trafo · LED-Ersatz Probleme",q:`Die Halogenlampen sollen durch LED-Lampen ersetzt werden. Welche Probleme können auftreten?`,a:`LEDs haben eine viel kleinere Last als Halogen → der elektronische Trafo erreicht seine Mindestlast nicht und schaltet nicht sicher ein. Flackern oder Nachglimmen, besonders bei Dimmern (LED nicht dimmbar / Trafo nicht LED-geeignet).`},
  {tid:"licht",topic:"Lichttechnik / Beleuchtung",ref:"Licht/Trafo · Symbole",q:`Erklären Sie die Symbole auf dem Trafo: „Haus mit Pfeil“ und das „Doppelquadrat“.`,a:`Haus-Symbol: nur für den Innenbereich / trockene Räume geeignet. Doppelquadrat (□ im □): Schutzklasse II – schutzisoliert (kein Schutzleiteranschluss nötig).`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Klemmbrett 230/400 V",q:`Auf dem Leistungsschild steht 230/400 V. Welche Schaltung wird gewählt und wie werden die Brücken im Klemmbrett gesetzt?`,a:`Bei einem 400-V-Netz und der Angabe 230/400 V wird der Motor in Stern (Y) geschaltet, weil jede Wicklung für 230 V ausgelegt ist und im Stern die 400 V auf √3 (≈ 230 V) je Wicklung aufgeteilt werden. Sternschaltung: Brücke über W2 – U2 – V2 (die unteren Klemmen werden zum Sternpunkt verbunden). L1→U1, L2→V1, L3→W1. Merke: Wicklungsspannung = kleinere Zahl (230 V) → die zu dieser Spannung passende Außenleiterspannung (400 V) ⇒ Stern.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Rechtslauf",q:`Wie werden die Außenleiter für Rechtslauf an das Klemmbrett angeschlossen?`,a:`Für Rechtslauf: L1 → U1, L2 → V1, L3 → W1 (Drehfeld in normaler Phasenfolge). Vertauschen von zwei Außenleitern ergibt Linkslauf.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Stromaufnahme",q:`Berechnen Sie die Stromaufnahme des Motors (Beispiel: P = 15 kW, U = 400 V, cos φ = 0,90, η aus Datenblatt).`,a:`I = P / (√3 · U · cos φ · η) Mit P_N = 15 kW (abgegebene Wellenleistung), η ≈ 0,903, cos φ = 0,90: I = 15000 / (1,732 · 400 · 0,90 · 0,903) I ≈ 15000 / 563,0 I ≈ 26,6 A Achtung: Steht P als abgegebene Leistung (Welle), muss η rein. Ist die aufgenommene Leistung gegeben, entfällt η. Tabellenbuch-Formel beachten.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Bedeutung 15 kW",q:`Was bedeutet die Angabe „15 kW“ auf dem Leistungsschild genau?`,a:`Das ist die abgegebene mechanische Nennleistung an der Welle (Bemessungsleistung P_N) bei Nennbetrieb — nicht die elektrisch aufgenommene Leistung. Die aufgenommene Leistung ist wegen des Wirkungsgrads größer.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · brummt nur",q:`Ein Drehstrom-Asynchronmotor läuft nach dem Einschalten nicht an, er brummt nur. Nennen Sie 1 möglichen Fehler.`,a:`Eine Phase fehlt (z.&nbsp;B. Sicherung/Leiter unterbrochen → „Einphasenlauf“). Der Motor bekommt kein vollständiges Drehfeld, entwickelt kein Anlaufmoment und brummt nur.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Asynchron begründen",q:`Begründen Sie, warum es sich um einen Asynchronmotor handelt.`,a:`Die Drehzahl auf dem Schild (z.&nbsp;B. 2950 oder 1430 min⁻¹) liegt unter der synchronen Drehzahl (3000 bzw. 1500 min⁻¹ bei 50 Hz). Diese Differenz (Schlupf) ist nötig, damit im Läufer eine Spannung induziert wird → typisch für den Asynchronmotor.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Polpaarzahl",q:`Bestimmen Sie die Polpaarzahl und begründen Sie (Beispiel n ≈ 2950 min⁻¹ bei 50 Hz).`,a:`n_syn = f · 60 / p 2950 min⁻¹ liegt knapp unter 3000 min⁻¹ → n_syn = 3000 min⁻¹ p = f · 60 / n_syn = 50 · 60 / 3000 = 1 Polpaarzahl p = 1 (2 Pole). Begründung: die Betriebsdrehzahl liegt knapp unter der synchronen Drehzahl 3000 min⁻¹, daraus folgt p = 1.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Drehstrom · Motorschutzschalter",q:`In einem 400-V-Netz fällt der Motorschutzschalter nach kurzer Zeit. Auf welchen Stromwert muss er eingestellt werden? (siehe Leistungsschild 48/28 A)`,a:`Auf den Bemessungsstrom bei der gewählten Schaltung einstellen. Bei 400 V wird der Motor in Stern betrieben → der kleinere Wert 28 A ist einzustellen (48 A gilt für Dreieck/230 V).`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Kompensation · warum",q:`Warum sollte man kompensieren?`,a:`Um den Blindleistungsanteil zu verringern und den Leistungsfaktor cos φ zu verbessern. Das senkt den Gesamtstrom → kleinere Leitungsverluste, geringere Querschnitte, Entlastung von Trafo/Netz und Vermeidung von Blindstromkosten.`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Kompensation · Arten",q:`Nennen Sie 3 Arten der Kompensation.`,a:`Einzelkompensation (direkt am Verbraucher). Gruppenkompensation (für mehrere Verbraucher zusammen). Zentralkompensation (geregelte Kompensationsanlage in der Hauptverteilung).`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Kompensation · Kondensator berechnen",q:`Ein Fleischwolf mit 230 V, 14 A, cos φ = 0,7 soll auf cos φ = 0,95 kompensiert werden. Berechnen Sie die Kapazität des Kondensators.`,a:`P = U · I · cos φ = 230 · 14 · 0,7 = 2254 W φ1 = arccos 0,7 = 45,57° → tan φ1 = 1,020 φ2 = arccos 0,95 = 18,19° → tan φ2 = 0,329 Q_C = P · (tan φ1 − tan φ2) Q_C = 2254 · (1,020 − 0,329) Q_C ≈ 1558 var C = Q_C / (2π · f · U²) C = 1558 / (2π · 50 · 230²) C ≈ 1558 / 1,662·10⁶ C ≈ 93,7 µF`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Trafo · Übersetzung",q:`230 V Netz, 5 Magnetventile mit 24 V, je 120 mA. Berechnen Sie das Übersetzungsverhältnis des Trafos.`,a:`ü = U1 / U2 = 230 V / 24 V ü ≈ 9,58`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Trafo · Leistung",q:`Für welche Leistung muss der Trafo ausgelegt werden, wenn alle 5 Ventile gleichzeitig in Betrieb sind?`,a:`I2 = 5 · 120 mA = 600 mA S = U2 · I2 = 24 V · 0,6 A S = 14,4 VA`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Trafo · Primärstrom",q:`Wie hoch ist der Strom in der Primärspule?`,a:`I1 = S / U1 = 14,4 VA / 230 V I1 ≈ 0,0626 A ≈ 62,6 mA Kontrolle über ü: I1 = I2 / ü = 600 mA / 9,58 ≈ 62,6 mA ✓`},
  {tid:"strom",topic:"Wechsel- & Drehstrom · Motor · Trafo",ref:"Trafo · spannungsweicher Trafo",q:`Nennen Sie 1 Anwendungsbeispiel für einen spannungsweichen Trafo.`,a:`Halogenlampen-Trafo (Klingeltrafo, Spielzeugtrafo zählen ebenfalls dazu) — die Spannung bricht bei Überlast/Kurzschluss ein und schützt so.`},
  {tid:"pa",topic:"Potentialausgleich",ref:"PA · anzuschließende Teile",q:`Welche Teile der Anlage sind an den Schutz-Potentialausgleich anzuschließen? Nennen Sie mehrere.`,a:`Schutzleiter PE bzw. PEN Fundament-/Ringerder Wasserrohre (metallisch) Gasleitung Heizungsrohre Antennenanlage / SAT-Anlage Blitzschutzanlage PV-Anlage / Fernmeldeanlage`},
  {tid:"pa",topic:"Potentialausgleich",ref:"PA · Bad",q:`Ist im Badezimmer immer ein (zusätzlicher) Potentialausgleich notwendig? Begründen Sie.`,a:`Nein, nicht immer. Ein zusätzlicher Potentialausgleich ist nur nötig, wenn fremde leitfähige Teile (z.&nbsp;B. metallene Rohre) ins Bad eingeführt werden und der Schutz durch RCD/PA nicht schon anderweitig sichergestellt ist. Bei rein nichtleitenden (Kunststoff-)Rohren entfällt er.`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · Mindestmaße",q:`Welche Mindestabmessungen sind für einen Fundamenterder (feuerverzinkter Rundstahl bzw. Bandstahl) vorgeschrieben?`,a:`Rundmaterial: Durchmesser mindestens 10 mm . Bandmaterial: mindestens 30 mm × 3,5 mm . Aus Anlage 1 der Unterlagen. Verbindung zur Bewehrung in Abständen ≤ 2 m.`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · Norm",q:`In welcher DIN-Norm ist die Ausführung von Erdungsanlagen geregelt?`,a:`DIN 18014 (Fundamenterder / Erdungsanlagen für Gebäude).`},
  {tid:"erdung",topic:"Erdungsanlagen & Fundamenterder",ref:"Erdung · max. Widerstand RCD",q:`In der Unterverteilung ist ein RCD Typ A / 40 A / 30 mA eingebaut. Berechnen Sie den maximal zulässigen Widerstand der Erdungsanlage.`,a:`R_E ≤ U_L / I_Δn U_L = 50 V (zulässige Berührungsspannung) I_Δn = 30 mA = 0,03 A R_E ≤ 50 V / 0,03 A R_E ≤ 1666,7 Ω Merke: bei einem 30-mA-RCD ist der zulässige Erdungswiderstand sehr hoch (~1667 Ω) — der RCD löst schon bei kleinstem Fehlerstrom aus.`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · drei Netzsysteme",q:`Nennen Sie die drei Netzsysteme.`,a:`TN-System (mit Unterarten TN-C, TN-S, TN-C-S) TT-System IT-System`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · wer gibt vor",q:`Wer gibt das zu installierende Netzsystem vor?`,a:`Das EVU / der Netzbetreiber (VNB) – „Energieversorgungsunternehmen“.`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · TN-C Vorteile",q:`Welche Vorteile bietet das TN-C-Netzsystem und welche Schutzeinrichtungen dürfen verwendet werden?`,a:`Vorteil: nur eine kombinierte PEN-Leitung nötig → weniger Material/Aufwand. Nachteil/Einschränkung: ein RCD (FI) ist nicht verwendbar, da N und PE im PEN zusammengefasst sind. Verwendbar sind Überstromschutzeinrichtungen (LS-Schalter, Sicherungen).`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · Schutz neben LS im TN",q:`Welche Schutzmaßnahmen werden neben dem LS-Schalter in einem TN-Netz angewandt? Nennen Sie 2.`,a:`Fehlerstromschutzschalter (RCD/FI) Überspannungsschutz (ÜSS / SPD)`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · TT→TN, NYM-O",q:`Ein TT-Netz wird auf ein TN-Netz umgestellt. Darf die NYM-O-Leitung weiterhin verwendet werden? Erläutern Sie kurz.`,a:`Die NYM-O ist eine Mantelleitung ohne grün-gelben Schutzleiter (nur Außenleiter + N). Im TN-System wird zwingend ein Schutzleiter (PE) benötigt → es muss die NYM-J (mit PE) verwendet werden. Die NYM-O darf so nicht weiterverwendet werden.`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · PEN→HES Funktion",q:`Am HAK befindet sich eine Verbindungsleitung von PEN-Leiter zur Haupterdungsschiene. Welche Funktion erfüllt diese Verbindung?`,a:`Sie stellt die Auftrennung des PEN in PE und N (TN-C-S) bzw. die Erdung des Schutzleiters her und verbindet die Anlage mit dem Erdungs-/Potentialausgleichssystem. So liegt der Schutzleiter auf Erdpotential.`},
  {tid:"netz",topic:"Netzsysteme & Fehlerarten",ref:"Netz · RCD je Stromkreis",q:`Für jeden Stromkreis soll ein eigener RCD in der Verteilung verwendet werden – ist das möglich? Begründen Sie.`,a:`Ja, möglich — vorausgesetzt es liegt ein System mit getrenntem N und PE (TN-S) vor. Im TN-C (PEN) geht es nicht, weil der RCD einen separaten N braucht. Jeder Stromkreis mit eigenem RCD erhöht die Selektivität (ein Fehler legt nicht alles still).`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · 40 A begründen",q:`Es wird ein Fehlerstromschutzschalter mit einem Nennstrom von 40 A gewählt. Begründen Sie die Entscheidung.`,a:`Der Bemessungsstrom des RCD muss mindestens so groß sein wie die Summe der nachgeschalteten Betriebsströme / der vorgeschalteten Überstromschutzeinrichtungen . Mit 40 A ist der RCD nicht überlastet, auch wenn mehrere Stromkreise (z.&nbsp;B. 2× 16 A) dahinter hängen.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · Abschaltzeit",q:`In welcher Zeit muss der Fehlerstromschutzschalter im vorliegenden Netzsystem abschalten?`,a:`Im Endstromkreis (TN, ≤ 32 A) innerhalb von ≤ 0,4 s ; bei Verteilerstromkreisen ≤ 5 s. Bei Personenschutz mit 30 mA praktisch nahezu sofort.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · Auslösebereich",q:`In welchem Fehlerstrombereich darf bzw. muss der eingesetzte Fehlerstromschutzschalter auslösen?`,a:`Er darf frühestens bei 0,5 · I_Δn auslösen und muss spätestens bei I_Δn auslösen. Beispiel 30 mA: darf nicht unter 15 mA, muss bis 30 mA auslösen.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · Nennstrom nicht überschreiten",q:`Wie kann man sicherstellen, dass der Nennstrom eines Fehlerstromschutzschalters nicht überschritten wird?`,a:`Durch eine vorgeschaltete Überstromschutzeinrichtung (LS-Schalter/Sicherung), deren Bemessungsstrom ≤ Bemessungsstrom des RCD ist.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · Test-Taste",q:`Weshalb ist es notwendig, nach dem ersten Einschalten des RCD die „Test“-Taste zu betätigen? / Welcher Hinweis bzgl. Zeitabstände an den Kunden?`,a:`Die Prüftaste erzeugt künstlich einen Fehlerstrom und prüft die mechanische Auslösefunktion . Der Kunde soll den RCD regelmäßig (etwa halbjährlich) mit der Prüftaste testen, damit die Mechanik nicht „festsitzt“ und im Ernstfall sicher auslöst.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"RCD · Prüftaste prüft was nicht",q:`Welche Funktion wird mit der Prüftaste einer Fehlerstrom-Schutzeinrichtung überprüft und welche nicht?`,a:`Geprüft: die mechanische Auslösung / Funktion des Geräts selbst. Nicht geprüft: ob die Erdung/Schleifenimpedanz der Anlage in Ordnung ist und ob die Auslösezeit/der Auslösestrom die Normwerte einhält — das verlangt eine Messung mit dem Prüfgerät.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"LS · B vs C",q:`Wodurch unterscheiden sich Leitungsschutzschalter vom Typ B und C? / Nennen Sie 3 Auslösecharakteristiken.`,a:`B: magnetische Auslösung beim 3- bis 5-fachen Nennstrom (normale Haushalts-/Steckdosenstromkreise). C: 5- bis 10-fach (Geräte mit höherem Einschaltstrom, z.&nbsp;B. Motoren, Trafos). D: 10- bis 20-fach (sehr hohe Einschaltströme). Weitere: Typ Z (2–3-fach), K (8–12-fach).`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"LS · Unterschied Sicherung",q:`Welcher Unterschied besteht zwischen einem Leitungsschutzschalter und einer Schmelzsicherung? Welche Vorteile hat der LS-Schalter?`,a:`Eine Schmelzsicherung muss nach dem Auslösen ersetzt werden, der LS-Schalter ist wieder einschaltbar . Vorteile LS: wiederverwendbar, allpolige Trennung möglich, klar definierte Charakteristik, keine falsche Sicherung einsetzbar.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"LS · B16 bei 24 A",q:`Ein B16-Automat wird mit 24 A belastet. Bestimmen Sie die Zeit, wann er spätestens auslösen muss (Auslösekennlinie).`,a:`24 A / 16 A = 1,5-facher Bemessungsstrom Beim 1,5-fachen Nennstrom liegt man im thermischen (Überlast-)Bereich . Aus der Kennlinie: er muss innerhalb von ≤ 1 Stunde (großer Prüfstrom, 1,45·I_n) auslösen. Genauen Wert aus der abgebildeten Kennlinie ablesen.`},
  {tid:"schutz",topic:"Schutzgeräte: RCD, LS-Schalter, Sicherungen",ref:"Sicherungen · 3 Arten",q:`Nennen Sie 3 Arten von Sicherungen, die für den Leitungsschutz angewandt werden können.`,a:`Neozed (D0-Sicherung) Diazed (D-Sicherung) LS-Schalter (Leitungsschutzschalter) — auch NH-Sicherung möglich`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Inbetriebnahme",q:`Wie sehen die notwendigen Schritte und deren Reihenfolge für eine Inbetriebnahme der elektrischen Anlage aus?`,a:`Reihenfolge: Besichtigen → Erproben → Messen. Besichtigen (Sichtprüfung vor Inbetriebnahme) Erproben/Prüfen der Funktion Messen (Schutzleiter, Isolationswiderstand, Schleifenimpedanz, RCD, Spannungsfall) Dokumentation/Protokoll, Übergabe`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Wiederholungsprüfung Norm",q:`Nach welcher Norm wird eine Wiederholungsprüfung ortsfester Anlagen durchgeführt?`,a:`DIN VDE 0105-100 (Betrieb / Wiederholungsprüfung ortsfester elektrischer Anlagen). Die Errichtung/Erstprüfung ist DIN VDE 0100-600.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Besichtigen",q:`Was sind die notwendigen Tätigkeiten beim Besichtigen (Sichtprüfung)?`,a:`Auswahl/Eignung der Betriebsmittel prüfen (richtige Querschnitte, Schutzart) Schutzmaßnahmen gegen elektrischen Schlag vorhanden Korrekter Anschluss von Leitern, Beschriftung/Kennzeichnung Keine sichtbaren Beschädigungen, Schutz gegen äußere Einflüsse`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Wechsel/Kreuzschaltung",q:`Wie wird die Funktion einer Wechselschaltung / Kreuzschaltung vollständig geprüft?`,a:`Jeder Schalter wird einzeln in alle Stellungen geschaltet und überprüft, dass die Leuchte von jeder Schaltstelle aus ein- und ausgeschaltet werden kann (alle Schaltkombinationen durchgehen).`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Zweck Isolationswiderstand",q:`Welchen Zweck hat eine Prüfung des Isolationswiderstandes?`,a:`Nachweis, dass die Isolierung zwischen aktiven Leitern und gegen PE/Erde intakt ist (kein Schluss, keine Feuchtigkeit/Beschädigung) → Schutz vor Kurzschluss und elektrischem Schlag.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Messspannung Iso",q:`Die Einstellung der gewählten Messspannung für die Prüfung des Isolationswiderstandes ist zu erläutern.`,a:`Die Prüfgleichspannung richtet sich nach der Nennspannung des Stromkreises. Für übliche Anlagen (bis 500 V) wird mit 500 V DC gemessen; Mindest-Isolationswiderstand ≥ 1 MΩ (für SELV/PELV 250 V / ≥ 0,5 MΩ).`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Iso 0,5 MΩ Herd",q:`Der Isolationswiderstand am Elektroherd beträgt 0,5 MΩ. Ist dies zulässig? Bewerten Sie das Messergebnis.`,a:`Für Stromkreise bis 500 V ist ≥ 1 MΩ gefordert → 0,5 MΩ ist zu niedrig / nicht zulässig . Ursache kann Feuchtigkeit in der Heizwendel sein. Hinweis: bei Heizgeräten kann der Wert nach „Trockenheizen“ wieder steigen — trotzdem Mangel dokumentieren.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Schleifenimpedanz vs Netzinnenwiderstand",q:`Wie unterscheiden sich die Messungen von Schleifenimpedanz und Netzinnenwiderstand?`,a:`Schleifenimpedanz (Z_S): gemessen in der Fehlerschleife L – PE (Außenleiter über Erde/Schutzleiter zurück) → zur Beurteilung der Abschaltbedingung. Netzinnenwiderstand: gemessen zwischen L – N → zur Beurteilung der Spannungsqualität / des Kurzschlussstroms im Betriebsstromkreis.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · IK B16",q:`Welcher Messwert für I_K (Kurzschlussstrom) wird mindestens erwartet für den verbauten Leitungsschutzschalter B16 A?`,a:`B-Charakteristik löst beim 5-fachen Nennstrom magnetisch aus: I_K min = 5 · 16 A = 80 A Sicher: oft mit Faktor 1,45 gerechnet → ≈ 116 A. Der gemessene Kurzschlussstrom muss mindestens den magnetischen Auslösestrom (5·I_n = 80 A) erreichen, damit der LS im Fehlerfall sicher schnell abschaltet.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Durchgang auf Riso",q:`Bei der Durchgangsmessung des Schutzleiters ist Ihr Messgerät auf R_iso eingestellt. Welche Auswirkung hat das?`,a:`Bei R_iso misst das Gerät mit hoher Spannung, aber sehr kleinem Prüfstrom . Die Durchgangsmessung verlangt jedoch einen Prüfstrom von mind. 200 mA . Mit der falschen Einstellung werden schlechte/lose Verbindungen nicht erkannt → das Messergebnis ist unbrauchbar/zu gut.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Messen · Berührungsstrom 0 mA",q:`An einem metallenen Motorgehäuse messen Sie den Berührungsstrom; Anzeige 0,0 mA. Warum können Sie hier keinen Berührungsstrom messen?`,a:`Weil das Gehäuse ordnungsgemäß über den Schutzleiter geerdet ist (bzw. das Gerät schutzisoliert ist). Dann liegt kein gefährliches Potential an → es fließt kein Berührungsstrom. Erst bei fehlendem/unterbrochenem PE wäre ein Strom messbar.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Geräteprüfung · wer darf",q:`Bewegliche Geräte benötigen nach DIN VDE 0701/0702 eine regelmäßige Prüfung. Wer darf diese Prüfung durchführen?`,a:`Eine Elektrofachkraft oder eine elektrotechnisch unterwiesene Person (EuP) unter Leitung und Aufsicht einer Elektrofachkraft – mit geeignetem Prüfgerät.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Geräteprüfung · Schutzleiterwiderstand",q:`Bei der Messung des Schutzleiterwiderstandes messen Sie 0,88 Ω an einer Kabeltrommel (25 m, 1,5 mm²). Ist dieser Wert in Ordnung? Begründen Sie.`,a:`R = l / (κ · A) = 25 / (56 · 1,5) R ≈ 0,30 Ω (theoretisch für 25 m, 1,5 mm²) Grenzwert bei Leitungslänge > 5 m: bis ca. 1 Ω zulässig (+ 0,1 Ω je 7,5 m) 0,88 Ω liegt unter dem zulässigen Grenzwert (~1 Ω) → der Wert ist (knapp) in Ordnung , passt für die Länge der voll abgerollten Trommel.`},
  {tid:"messen",topic:"Messen & Prüfen · Geräteprüfung",ref:"Geräteprüfung · Ursache Wert",q:`Welche Ursache kann ein (zu hoher) Schutzleiterwiderstand haben?`,a:`Lange/aufgerollte Leitung (Kabeltrommel) → längerer Schutzleiter Korrodierte oder lose Klemmstellen/Steckkontakte Zu kleiner Leiterquerschnitt / teilweise gebrochene Adern`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"HAK · Aufgaben",q:`Nennen Sie 2 Aufgaben, die ein HAK (Hausanschlusskasten) erfüllen muss.`,a:`Übergabepunkt vom Versorgungsnetz zur Hausinstallation. Überstrom-/Kurzschlussschutz der Hauptleitung (NH-Sicherungen) und Trennmöglichkeit der Anlage vom Netz.`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"HAK · NH",q:`Was bedeutet die Abkürzung NH (Sicherung)?`,a:`Niederspannungs-Hochleistungs-Sicherung (NH-Sicherung). Sitzt im HAK und schützt die Hauptleitung.`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Zähler · Montageort",q:`Wo darf ein Zählerschrank montiert werden und wo nicht? Je 3 Beispiele.`,a:`Erlaubt: Technikraum, Keller, Flur/Treppenraum (nicht über Treppenstufen). Nicht erlaubt: Durchgangsräume, Heizräume, Brandgefährdete Räume (auch: Nassräume, Räume dauerhaft > 30 °C).`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Zähler · Hauptleitung 5 Schienen",q:`Wie wird die Hauptleitung an den 5 Stromschienen des Zählerschrankes angeschlossen?`,a:`An die 5 Sammelschienen L1, L2, L3, N und PE — jede Ader der Hauptleitung auf die zugehörige Schiene (TN-S, 5-Leiter).`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Zähler · Anzahl Zählerplätze",q:`Wonach richtet sich die Anzahl der notwendigen Zählerplätze? 4 Nennungen.`,a:`Anzahl der Wohn-/Nutzungseinheiten Anzahl der Verbrauchergruppen (Allgemeinstrom, Wärmepumpe, PV, Ladestation …) Art der Anlagen (Bezug / Dauerstrom / Erzeugung) Vorgaben des Netzbetreibers (TAB) und Reserveplätze`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Zähler · Bauteil SLS",q:`Das abgebildete Bauteil (E63, ~230/400 V, Ui=690 V, im Zählerschrank) – wie lautet die genaue Bezeichnung?`,a:`Selektiver Hauptleitungsschutzschalter (SLS-Schalter) mit E-Charakteristik, hier 63 A.`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Zähler · EHZ Vorteile",q:`Nennen Sie 3 Vorteile des EHZ (elektronischer Haushaltszähler).`,a:`Einfache Montage durch 3-Punkt-/1-Punkt-Befestigung (BKE) Einfaches Ablesen des Zählers (digital) Platzsparend / kompakte Bauform`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Zähler · oberer Anschlussraum",q:`Welche Bauteile sind im oberen Anschlussraum eines Zählerschranks erlaubt? Nennen Sie 3.`,a:`SLS-Schalter (selektiver Hauptleitungsschutz) Überspannungsschutz (SPD Typ 1/2) Hauptklemmen / Sammelschienen und ggf. Hauptschalter`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Hausanschlussraum · Maße",q:`Nennen Sie die Mindestmaße, die ein Hausanschlussraum aufweisen muss (Anlage 2).`,a:`Mindestens 2,0 m lang und 2,0 m hoch , an mindestens einer Gebäudeaußenwand. Breite bei Belegung einer Wand ≥ 1,50 m, bei gegenüberliegenden Wänden ≥ 1,80 m. Vorgeschrieben ab mehr als fünf Anschlussnutzern (DIN 18012).`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Hausanschluss · Wand vs Nische",q:`Erklären Sie den Unterschied zwischen einer Hausanschlusswand und einer Hausanschlussnische (Anlage 2).`,a:`Hausanschlusswand: eine Gebäudeaußenwand zur Befestigung der Anschluss-/Betriebseinrichtungen, für Gebäude bis fünf Anschlussnutzer. Hausanschlussnische: bauseits erstellte Nische zur Einführung der Anschlussleitungen — nur für nicht unterkellerte Einfamilienhäuser geeignet.`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Einspeisung · Schleifenimpedanz HAK",q:`Am HAK (63 A) stellen Sie eine Schleifenimpedanz von 2,5 Ω zwischen Außenleiter und PEN fest. Ist dies ausreichend? Begründen Sie anhand der Kennlinie.`,a:`I_K = U0 / Z_S = 230 V / 2,5 Ω = 92 A Damit die 63-A-NH-Sicherung rechtzeitig (≤ 5 s) abschaltet, muss der Kurzschlussstrom über dem Auslösewert der Kennlinie liegen. 92 A reicht für eine 63-A-Sicherung in der Regel nicht sicher (zu wenig Reserve) → Wert anhand der Kennlinie prüfen, ggf. Z_S zu hoch. Mit der Kennlinie aus der Aufgabe abgleichen.`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Einspeisung · TN-C → TN-S",q:`Laut VDE-AR-N 4101 muss das TN-C-Netz im HAK in ein TN-S-Netz aufgetrennt werden. Worin liegt der Vorteil der Aufteilung am Einspeisepunkt gegenüber einer Aufteilung im Unterverteiler?`,a:`Bei Auftrennung direkt am Einspeisepunkt sind N und PE in der gesamten Anlage durchgehend getrennt (TN-S) . Vorteil: RCDs können überall eingesetzt werden , keine Betriebsströme auf dem PE → bessere EMV, sicherer Schutz, weniger Störungen.`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Hausanschluss · Netzsystem Abbildung",q:`Um welches Netzsystem handelt es sich (Abbildung mit PEN, der in N und PE aufgeteilt wird, Haupterdungsschiene)?`,a:`Ein TN-C-S-System : vom Netz kommt der kombinierte PEN (TN-C-Teil), im Hausanschluss wird er in N und PE aufgeteilt (ab dort TN-S).`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Zähler · Ladesäule Querschnitt",q:`Ein Zählerplatz hat eine interne flexible Verdrahtung mit 10 mm². Eine Ladesäule mit 32 A (Dauerstrom) soll zusätzlich angeschlossen werden. Reicht dieser Querschnitt (Anlage 3)?`,a:`Ja. Laut Belastungstabelle (Anlage 3) ist eine Zählerverdrahtung mit 10 mm² für Dauerstromanlagen bis ≤ 32 A zulässig (Schutz mit SLS HTS335C, 35 A). Die 32-A-Ladesäule liegt genau an der Grenze → reicht aus, mehr ginge nur mit 16 mm².`},
  {tid:"haus",topic:"Hausanschluss · Zähler · Einspeisung",ref:"Zähler · alten Platz weiterverwenden",q:`Dürfen Sie einen vorhandenen, abgebildeten Zählerplatz grundsätzlich für eine neue Anlage verwenden? Begründen Sie.`,a:`Nur wenn er den aktuellen Anforderungen (VDE-AR-N 4101, 3-Punkt-Befestigung BKE, oberer Anschlussraum 300 mm, Verdrahtungsquerschnitt) entspricht. Alte Zählerplätze ohne BKE/zu kleinen Anschlussraum dürfen nicht weiterverwendet werden → dann Neuinstallation nötig.`},
  {tid:"koerper",topic:"Körperstrom & Gefahren des Stroms",ref:"Körperstrom · berechnen",q:`Welcher Strom fließt durch den Menschen, wenn der Widerstand R_K = 1 kΩ und R_x = 120 Ω beträgt (B16-A-Stromkreis)?`,a:`I = U / (R_K + R_x) I = 230 V / (1000 Ω + 120 Ω) I = 230 / 1120 I ≈ 0,205 A = 205 mA Bewertung: 205 mA ist lebensgefährlich (Herzkammerflimmern). Der B16 würde diesen Strom nicht als Überlast erkennen → genau dafür braucht es den RCD!`},
  {tid:"koerper",topic:"Körperstrom & Gefahren des Stroms",ref:"Körperstrom · Wirkung 100 ms",q:`Welche Auswirkungen hätte ein Strom auf einen Menschen bei einer Einwirkdauer von 100 ms? (Strom-Zeit-Diagramm AC)`,a:`Das hängt von der Stromstärke ab; im Diagramm wird der Punkt (Strom / 100 ms) einer Zone zugeordnet: AC-1: normalerweise keine Wirkung. AC-2: meist keine schädliche Wirkung. AC-3: Muskelverkrampfungen, Atemschwierigkeiten, meist kein organischer Schaden. AC-4: Herzkammerflimmern, Herz-/Atemstillstand, Verbrennungen. Beispiel: ein kleiner Strom bei 100 ms liegt in AC-1 (keine Wirkung). Den konkreten Wert im Diagramm einzeichnen.`},
  {tid:"koerper",topic:"Körperstrom & Gefahren des Stroms",ref:"Körperstrom · Schutzkontakt nicht durchgeklemmt",q:`In einer Installation (mit RCD) ist bei schutzisolierten Leuchten der Schutzkontakt nicht durchgeklemmt. Nennen Sie 2 mögliche Auswirkungen.`,a:`Bei nachfolgend angeschlossenen Betriebsmitteln der Schutzklasse I fehlt der PE → kein Schutz bei Körperschluss (Gefahr). Die Schutzleiterdurchgängigkeit/Abschaltbedingung ist unterbrochen → der RCD bekommt im Fehlerfall keinen Rückweg, Schutzfunktion ist nicht gewährleistet.`},
  {tid:"koerper",topic:"Körperstrom & Gefahren des Stroms",ref:"Körperstrom · RCD löst nicht aus",q:`Sie überprüfen an einer Steckdose den RCD; bei der Messung löst er nicht aus. Nennen Sie 3 mögliche Fehlerursachen.`,a:`RCD defekt / Mechanik festsitzend N und PE vertauscht oder verbunden (Fehlerstrom fließt nicht über den Wandler) Kein/falscher Schutzleiteranschluss an der Steckdose → Prüfgerät kann keinen Fehlerstrom erzeugen`},
  {tid:"koerper",topic:"Körperstrom & Gefahren des Stroms",ref:"Schutzmaßnahmen · Iso 0,26 MΩ überall",q:`Bei einer Isolationsmessung stellen Sie in allen Stromkreisen denselben Wert R_iso = 0,26 MΩ fest. Welcher Fehler könnte vorliegen?`,a:`Wenn alle Stromkreise exakt denselben (niedrigen) Wert zeigen, liegt der Fehler meist an einer gemeinsamen Stelle : z.&nbsp;B. eine Brücke/Verbindung im Verteiler (N-Schiene nicht abgeklemmt) oder Feuchtigkeit an der gemeinsamen Sammelschiene. Beim Messen müssen die Stromkreise getrennt (N abgeklemmt) sein.`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · IP-Infos",q:`Fritzbox hat IP 192.168.178.1. Ergänzen Sie Klasse, Subnetzmaske, Netzwerkadresse, Broadcast.`,a:`Klasse: C (privater Bereich 192.168.x.x) Subnetzmaske: 255.255.255.0 Netzwerkadresse: 192.168.178.0 Broadcast: 192.168.178.255`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · feste IP Laptop",q:`Vergeben Sie eine mögliche feste IP-Adresse für den Laptop an der Fritzbox.`,a:`Z.&nbsp;B. 192.168.178.50 (gleiches Netz, nicht die .1 des Routers, außerhalb des DHCP-Bereichs).`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · APIPA",q:`Ein PC zeigt die IP 169.254.12.13. Warum kann er sich nicht mit dem Internet verbinden?`,a:`169.254.x.x ist eine APIPA-Adresse — der PC hat keine IP vom DHCP-Server bekommen und sich selbst eine vergeben. Er ist nicht im richtigen Netz → keine Verbindung.`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · Fehler beheben",q:`Wie kann der Fehler (169.254er-Adresse) behoben werden?`,a:`Kabel/WLAN-Verbindung prüfen, DHCP am Router aktivieren IP neu beziehen: ipconfig /release dann ipconfig /renew (oder feste, passende IP vergeben)`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · Befehl Verbindung testen",q:`Mit welchem Konsolen-Befehl testen Sie die Verbindung vom PC zur Fritzbox?`,a:`ping – z.&nbsp;B. ping 192.168.178.1`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · TCP/IP-Konfig anzeigen",q:`Mit welchem Konsolen-Befehl können Sie die TCP/IP-Konfiguration Ihres PCs anzeigen?`,a:`ipconfig (Windows) bzw. ipconfig /all für Details. (Bei Linux/Mac: ifconfig / ip a .)`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · Datenverkabelung 10 GBit",q:`Kunde braucht Datenverkabelung für 16 PCs, 10 GBit/s, Verteilerschrank vorhanden. Welche weiteren Komponenten bieten Sie an?`,a:`Verlegekabel min. Cat 6A (für 10 GBit/s) Patchpanel, Netzwerk-Switch (10G-fähig), Patchkabel Netzwerkdosen (RJ45, Cat 6A) an den Arbeitsplätzen`},
  {tid:"it",topic:"IT-Systeme & Datennetz",ref:"IT · Verdrahtungsfehler",q:`Nach einer Datenverkabelung ist eine Abnahmemessung erforderlich. Welche Verdrahtungsfehler können auftreten? Nennen Sie 3.`,a:`Vertauschte Adernpaare (Miswire / Split Pair) Unterbrechung (Open) oder Kurzschluss (Short) Zu hohe Dämpfung / NEXT (Übersprechen), zu lange Strecke`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Biegeradius NYM",q:`Welchen Biegeradius muss man bei der Leitungsverlegung der Mantelleitung Typ NYM beachten?`,a:`Mindestbiegeradius etwa das 4-fache des Leitungsdurchmessers (bei festen Mantelleitungen). Genauen Faktor nach Herstellerangabe/Tabellenbuch.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Badbereiche",q:`Welche Bereiche für die elektrische Installation sind in Räumen mit Badewanne oder Dusche zu beachten?`,a:`Schutzbereiche nach DIN VDE 0100-701 : Bereich 0 (in der Wanne/Dusche), Bereich 1 (senkrecht darüber bis 2,25 m), Bereich 2 (0,6 m seitlich um Bereich 1). Je Bereich gelten Anforderungen an Schutzart (IPX) und zulässige Betriebsmittel.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Drehstromdose 5 Adern",q:`Die Leitung zur Drehstromsteckdose enthält 5 farbige Adern. Welche Bedeutung haben die Farben?`,a:`Braun, Schwarz, Grau = Außenleiter L1, L2, L3 Blau = Neutralleiter N Grün-Gelb = Schutzleiter PE`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · H07V-K 1,5 bl",q:`Erläutern Sie die Bezeichnung H07V-K 1,5 bl und wo diese eingesetzt wird.`,a:`H = harmonisierte Norm, 07 = 450/750 V, V = PVC-Isolierung, K = feindrahtig (flexibel), 1,5 = 1,5 mm², bl = blau (Neutralleiter). Einsatz: als Verdrahtungsleitung im Schaltschrank / in Rohren und Kanälen (Einzelader).`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Mantelleitung im Freien",q:`Welche Forderungen müssen eingehalten werden, wenn Mantelleitungen im Freien verlegt werden?`,a:`UV-/witterungsbeständig bzw. gegen Sonneneinstrahlung geschützt verlegen. Mechanischer Schutz, ausreichende Befestigung, Schutz gegen Feuchtigkeit (geeignete Leitung/Rohr).`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · M25",q:`Was sagt die Bezeichnung M25 aus und welche Bestimmungen gelten für die Verlegung von Installationsrohren?`,a:`M25 = metrisches Installationsrohr mit Außendurchmesser 25 mm . Bestimmungen: Rohre dehnungs-/knickfrei verlegen, Befestigungsabstände einhalten, Biegeradien beachten, max. Füllgrad der Leitungen, einziehbar bleiben.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · NOT-AUS",q:`In der Aufgabe kommt ein „NOT-AUS“ zum Einsatz. Welche Anforderungen stellt man an diesen?`,a:`Muss als Öffner (NC) mit Zwangsöffnung ausgeführt sein. Rot/gelb gekennzeichnet, gut erreichbar, verrastend (bleibt betätigt), Wiedereinschalten nur bewusst. Schaltet die gefährliche Bewegung/Energie sicher ab (Ruhestromprinzip).`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Querschnitt Strombelastbarkeit",q:`Wählen Sie den Querschnitt für die Zuleitung anhand der Strombelastbarkeit nach DIN VDE 0298-4 (Beispiel Durchlauferhitzer 21 kW, 400 V).`,a:`I = P / (√3 · U · cos φ) I = 21000 / (1,732 · 400 · 1) I ≈ 30,3 A Laut Tabelle (Verlegeart, Umgebungstemp.) → z. B. 6 mm² Cu Verlegeart und Häufung/Temperatur über Umrechnungsfaktoren berücksichtigen — dann in der Strombelastbarkeitstabelle den Querschnitt ablesen.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Spannungsfall",q:`Überprüfen Sie, ob der maximale Spannungsfall von 3 % eingehalten wird (Beispiel: I ≈ 30,3 A, Länge 20 m, 6 mm² Cu).`,a:`ΔU = (√3 · I · l) / (κ · A) ΔU = (1,732 · 30,3 · 20) / (56 · 6) ΔU ≈ 1049,5 / 336 ΔU ≈ 3,12 V ΔU% = ΔU / 400 V · 100 ≈ 0,78 % 0,78 % liegt deutlich unter 3 % → eingehalten .`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · LS-Schalter Bemessung",q:`Nennen Sie den Bemessungsstrom des notwendigen LS-Schalters (Beispiel I ≈ 30 A, 3-polig).`,a:`B25 A, 3-polig (nächster passender Bemessungsstrom über dem Betriebsstrom, aber ≤ Strombelastbarkeit der Leitung).`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Temperatur 40 °C",q:`Ist der Querschnitt noch zulässig, wenn die Umgebungstemperatur auf 40 °C steigt? Begründen Sie rechnerisch.`,a:`I_z = I_r · f (Umrechnungsfaktor f ≈ 0,82 bei 40 °C) I_z = 31 A · 0,82 = 25,4 A ... (je nach Tabellenwert ~26–27 A) Der reduzierte zulässige Strom muss noch über dem Betriebsstrom liegen → laut Tabelle gerade noch zulässig . Mit den genauen Faktoren aus DIN VDE 0298-4 rechnen.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Verlegearten",q:`Wieso wird zwischen unterschiedlichen Verlegearten unterschieden?`,a:`Weil sich je nach Verlegeart der Wärmewiderstand / die Wärmeabfuhr ändert. Eine in Wärmedämmung verlegte Leitung kann die Wärme schlechter abgeben → geringere zulässige Strombelastbarkeit als frei verlegt.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Stegleitung/Mantelleitung",q:`Wo können Stegleitungen und Mantelleitungen verlegt werden?`,a:`Stegleitungen (NYIF): nur unter Putz, auf nicht brennbarem Untergrund. Mantelleitungen (NYM): auf, in und unter Putz, in trockenen, feuchten Räumen, auch im Freien bei UV-Schutz.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · NYM-J Typenbezeichnung",q:`Entschlüsseln Sie NYM-J 3×1,5 mm² bzw. 5×1,5 mm². Woran erkennt man, dass Leitungen normgerecht sind?`,a:`N = Norm(leitung), Y = PVC-Isolierung, M = Mantelleitung, J = mit Schutzleiter (grün-gelb). 3×1,5 = 3 Adern à 1,5 mm². Normgerecht erkennbar am VDE-Zeichen / Aufdruck auf dem Mantel.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · VPS/SPS/Kleinsteuerung",q:`Wodurch unterscheiden sich VPS, SPS und Kleinsteuerung/Logikmodul?`,a:`VPS (Verbindungsprogrammierte Steuerung): Funktion durch feste Verdrahtung (Schütze/Relais) — Änderung = umverdrahten. SPS (Speicherprogrammierte Steuerung): Funktion durch Programm/Software — Änderung = umprogrammieren, viele Ein-/Ausgänge. Kleinsteuerung / Logikmodul (z.&nbsp;B. LOGO!, EASY): kleine SPS für einfache Aufgaben, wenige E/A, günstig.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Vor-/Nachteile VPS/SPS",q:`Nennen Sie Vorteile und Nachteile von VPS- und SPS-Steuerungen.`,a:`VPS Vorteil: robust, einfach, keine Software. Nachteil: bei Änderung neu verdrahten, viel Platz/Material. SPS Vorteil: flexibel (per Software änderbar), kompakt, viele Funktionen. Nachteil: Programmierkenntnisse nötig, abhängig von Elektronik/Software.`},
  {tid:"install",topic:"Installation, Leitungen & Verlegung",ref:"Install · Kleinsteuerung Zuordnung",q:`Garagentor mit Kleinsteuerung: ordnen Sie Eingänge (I) und Ausgänge (Q) zu (S0 NOT-AUS, S1 Stopp, S2 Tor hoch, S3 Tor runter, B1/B2 Endschalter, Schütze, Lampen).`,a:`I1: S0 NOT-AUS — Öffner (NC) I2: S2 Tor hoch — Schließer (NO) I3: S3 Tor runter — Schließer (NO) I4: S1 Stopp — Öffner (NC) I5: B1 Endschalter oben — Öffner (NC) I6: B2 Endschalter unten — Öffner (NC) Q1: Schütz „hoch“ · Q2: Schütz „runter“ Q3: Lampe grün (Tor offen) · Q4: Lampe rot (Tor in Bewegung) Merke: Sicherheitsbefehle (NOT-AUS, Stopp, Endschalter) als Öffner verdrahten (Ruhestromprinzip), Start-Taster als Schließer.`},
];// ═══ ALLE PoG-FRAGEN (ein Topic: "pog") ═══
const POG_EXTRA = [
// STAAT
{tid:"pog",topic:"PoG",ref:"Staat · Kennzeichen",q:`Welche 3 Kennzeichen hat ein moderner Staat?`,a:`1. Staatsgebiet – das geografische Territorium
2. Staatsvolk – die Bevölkerung/Staatsangehörigen
3. Staatsgewalt – alleiniges Recht, Macht auszuüben und Gesetze durchzusetzen`},
{tid:"pog",topic:"PoG",ref:"Staat · Gewaltmonopol",q:`Was versteht man unter dem Gewaltmonopol des Staates?`,a:`Nur der Staat darf Gewalt anwenden. Kein Bürger darf eigenmächtig Gewalt einsetzen. Ausgeübt durch Polizei (innere Sicherheit) und Armee (äußere Sicherheit). Ziel: Chaos und "Recht des Stärkeren" verhindern.`},
{tid:"pog",topic:"PoG",ref:"Staat · 8 Aufgaben",q:`Nennen Sie 8 Staatsaufgaben mit Beispielen.`,a:`1. Innere Sicherheit → Polizei
2. Äußere Sicherheit → Bundeswehr
3. Rechtssicherheit → Gesetze, Gerichte
4. Soziale Sicherheit → Wohngeld, Sozialhilfe
5. Wirtschaftsförderung → Subventionen
6. Funktionierende Verwaltung → Behörden, Ämter
7. Schutz der Lebensgrundlagen → Umweltschutz
8. Daseinsvorsorge → Müllabfuhr, ÖPNV`},
// GRUNDGESETZ
{tid:"pog",topic:"PoG",ref:"GG · Entstehung",q:`Wann wurde das Grundgesetz verabschiedet? Was geschah bei der Wiedervereinigung?`,a:`23. Mai 1949 vom Parlamentarischen Rat verabschiedet. Gilt als Geburtsstunde der BRD. Bei der Wiedervereinigung am 3.10.1990 blieb das GG erhalten – KEINE neue Verfassung.`},
{tid:"pog",topic:"PoG",ref:"GG · Änderung",q:`Wie kann das Grundgesetz geändert werden? Was ist die Ewigkeitsgarantie?`,a:`GG-Änderung: 2/3-Mehrheit im Bundestag UND 2/3-Mehrheit im Bundesrat. Ewigkeitsgarantie (Art. 79 Abs. 3): NICHT änderbar: Menschenwürde (Art. 1), demokratische Grundordnung (Art. 20), Bundesstaatlichkeit.`},
{tid:"pog",topic:"PoG",ref:"GG · Grundrechte Kategorien",q:`Was sind Grundrechte und wie werden sie eingeteilt?`,a:`Grundrechte = wichtigste Rechte (Art. 1–19 GG). Einklagbar, gelten unmittelbar.
MENSCHENRECHTE: für JEDEN Menschen. Artikel beginnt: "Jeder hat das Recht..."
Beispiele: Art. 2 (Persönlichkeit), Art. 4 (Glaube), Art. 5 (Meinung)
BÜRGERRECHTE: nur für deutsche Staatsbürger. Artikel beginnt: "Alle Deutschen..."
Beispiele: Art. 8 (Versammlung), Art. 11 (Freizügigkeit), Art. 12 (Berufswahl)`},
{tid:"pog",topic:"PoG",ref:"GG · Art. 1 Menschenwürde",q:`Was steht in Art. 1 GG und warum ist er unantastbar?`,a:`"Die Würde des Menschen ist unantastbar. Sie zu achten und zu schützen ist Verpflichtung aller staatlichen Gewalt." Absolut geschützt – keine Ausnahme möglich. Durch Ewigkeitsgarantie (Art. 79) nicht änderbar. Konsequenz: Folter immer verboten, keine Todesstrafe (Art. 102).`},
{tid:"pog",topic:"PoG",ref:"GG · Grundrechtsfunktionen",q:`Welche 3 Funktionen haben die Grundrechte?`,a:`1. ABWEHRRECHTE: schützen vor staatlichen Eingriffen (z.B. niemand kann zum Beruf gezwungen werden)
2. LEISTUNGSRECHTE: Staat muss Leistungen stellen (z.B. Sozialhilfe, Existenzminimum aus Art. 1)
3. TEILHABERECHTE: Bürger können an staatlichen Einrichtungen teilhaben (z.B. Gleichheitssatz Art. 3)`},
{tid:"pog",topic:"PoG",ref:"GG · Grundrechte einschränken",q:`Nennen Sie konkrete Beispiele, wo Grundrechte eingeschränkt werden dürfen.`,a:`Art. 6 (Elternrecht): Sorgerecht kann bei Kindesvernachlässigung entzogen werden.
Art. 13 (Wohnungsfreiheit): Hausdurchsuchung mit richterlichem Beschluss erlaubt.
Art. 14 (Eigentum): Enteignung bei öffentlichem Nutzen möglich.
Art. 8 (Versammlung): Demos können unter bestimmten Bedingungen verboten werden.
Grundsatz: Einschränkung nur durch Gesetz und verhältnismäßig.`},
{tid:"pog",topic:"PoG",ref:"GG · Art. 2-5 Menschenrechte",q:`Welche Grundrechte in Art. 2–5 GG sind Menschenrechte?`,a:`Art. 2: Freie Entfaltung der Persönlichkeit → Menschenrecht
Art. 3: Gleichheit vor dem Gesetz → Menschenrecht
Art. 4: Glaubens- und Gewissensfreiheit → Menschenrecht
Art. 5: Meinungs-, Presse-, Rundfunkfreiheit → Menschenrecht
Erkennbar: alle beginnen mit "Jeder hat das Recht..."`},
{tid:"pog",topic:"PoG",ref:"GG · Art. 8-13 Bürgerrechte",q:`Welche Grundrechte sind Bürgerrechte (nur für Deutsche)?`,a:`Art. 8: Versammlungsfreiheit – ohne Anmeldung, friedlich, ohne Waffen
Art. 9: Vereinigungsfreiheit
Art. 11: Freizügigkeit im Bundesgebiet
Art. 12: Berufswahlfreiheit
Art. 13: Unverletzlichkeit der Wohnung
Art. 16: Auslieferungsverbot
Erkennbar: beginnen mit "Alle Deutschen haben das Recht..."`},
{tid:"pog",topic:"PoG",ref:"GG · rechtmäßige Verfassung",q:`Warum ist das Grundgesetz eine rechtmäßige Verfassung?`,a:`1. Hat Vorrang vor anderen Gesetzen
2. Staatliche Organe müssen sich daran halten
3. Kann nur schwer geändert werden (2/3-Mehrheit)
4. Es gibt ein Verfassungsgericht (Bundesverfassungsgericht)
5. Verfassungsgebende Gewalt geht vom Volk aus → alle 5 Kriterien erfüllt!`},
// STRUKTURPRINZIPIEN
{tid:"pog",topic:"PoG",ref:"Strukturprinzipien · 5 Prinzipien",q:`Welche 5 Strukturprinzipien legt Art. 20 GG fest?`,a:`1. DEMOKRATIE: Staatsgewalt geht vom Volk aus. Freie, gleiche, geheime Wahlen.
2. REPUBLIK: Staatsoberhaupt wird gewählt, nicht durch Erbfolge bestimmt.
3. BUNDESSTAAT: 16 Länder bilden gemeinsam die BRD.
4. RECHTSSTAAT: Gesetze binden alle – auch den Staat.
5. SOZIALSTAAT: Staat sorgt für soziale Gerechtigkeit.
Alle 5 durch Ewigkeitsgarantie unveränderbar.`},
{tid:"pog",topic:"PoG",ref:"Strukturprinzipien · Demokratie",q:`Was ist der Unterschied zwischen direkter und indirekter Demokratie?`,a:`DIREKTE DEMOKRATIE: Bürger entscheiden selbst über Gesetze.
Beispiele: Volksentscheid, Bürgerentscheid, Volksbegehren, Petition
Vorteile: mehr Bürgermacht, höhere Akzeptanz
INDIREKTE DEMOKRATIE: Volk wählt Vertreter (Abgeordnete), die entscheiden.
Vorteile: effizient, Bürger müssen nicht über alles informiert sein
Deutschland: überwiegend indirekt. Direkte Elemente nur auf Landesebene.
Grund: schlechte Erfahrungen mit Volksentscheiden in der Weimarer Republik`},
{tid:"pog",topic:"PoG",ref:"Strukturprinzipien · Bund vs. Länder",q:`Welche Aufgaben hat der BUND, welche die LÄNDER?`,a:`BUND: Außenpolitik, Bundeswehr, Strafrecht, Luftverkehr, Waffenexport, Währung
LÄNDER: Bildung/Schulwesen, Kultur, Polizeirecht, Vorschulerziehung, kommunale Verwaltung
Bundesrat: Länder wirken an der Bundesgesetzgebung mit. Viele Bundesgesetze brauchen Bundesrat-Zustimmung.
Art. 31: "Bundesrecht bricht Landesrecht."`},
{tid:"pog",topic:"PoG",ref:"Strukturprinzipien · Föderalismus",q:`Welche Vor- und Nachteile hat der Föderalismus?`,a:`VORTEILE: Machteinschränkung, bessere Gewaltenteilung, höhere Bürgernähe, regionale Probleme besser lösbar, eigenständiges Handeln der Länder
NACHTEILE: komplizierte Strukturen, langsamere Entscheidungen, hohe Kosten, unterschiedliche Regelungen je Bundesland (z.B. Schulrecht)`},
{tid:"pog",topic:"PoG",ref:"Strukturprinzipien · Rechtsstaat",q:`Was sind die wichtigsten Merkmale eines Rechtsstaates?`,a:`1. Rechtssicherheit: Gesetze gelten für alle gleich
2. Rechtsgleichheit: Alle Bürger gleich vor dem Gesetz (Art. 3)
3. Richterunabhängigkeit: Richter nur dem Gesetz verpflichtet (Art. 97)
4. Keine rückwirkenden Gesetze
5. Verhältnismäßigkeitsprinzip
6. Bundesverfassungsgericht kann Gesetze für verfassungswidrig erklären`},
{tid:"pog",topic:"PoG",ref:"Strukturprinzipien · Sozialstaat",q:`Was bedeutet Sozialstaat und welche Leistungen gibt es?`,a:`Staat muss soziale Gerechtigkeit herstellen und Schwächere schützen.
5 Sozialversicherungen: Kranken-, Renten-, Pflege-, Arbeitslosen-, Unfallversicherung.
Weitere: Sozialhilfe, Wohngeld, Kindergeld, Elterngeld, Bürgergeld.
Aus Art. 1 (Menschenwürde) folgt Existenzminimum: Erwerbsfähige → Bürgergeld, Erwerbsunfähige → Sozialhilfe.`},
{tid:"pog",topic:"PoG",ref:"Strukturprinzipien · Republik",q:`Was ist der Unterschied zwischen Republik und Monarchie?`,a:`MONARCHIE: Staatsoberhaupt durch Erbfolge (König, Kaiser). Beispiele: Großbritannien, Schweden
REPUBLIK: Staatsoberhaupt wird gewählt. In DE = Bundespräsident, gewählt von der Bundesversammlung.
"Republik" = lateinisch "res publica" (öffentliche Angelegenheit)
Wichtig: Republik ≠ automatisch Demokratie. Die DDR nannte sich auch Republik, war aber eine Diktatur.`},
// GEWALTENTEILUNG
{tid:"pog",topic:"PoG",ref:"Gewaltenteilung · Grundprinzip",q:`Was ist Gewaltenteilung und wozu dient sie?`,a:`Staatsgewalt wird auf unabhängige Organe aufgeteilt, die sich gegenseitig kontrollieren.
Ziel: Machtmissbrauch verhindern, Freiheit und Gleichheit schützen.
Geht zurück auf Charles de Montesquieu (1689–1755).
HORIZONTAL: Legislative – Exekutive – Judikative
VERTIKAL: Bund – Länder – Kommunen (Föderalismus)
Weitere Kontrollen: Wahlen, Opposition, freie Medien`},
{tid:"pog",topic:"PoG",ref:"Gewaltenteilung · Legislative",q:`Was macht die Legislative? Nenne alle Ebenen.`,a:`Legislative = macht die Gesetze.
Bundesebene: Bundestag (Volksvertretung) + Bundesrat (Länderkammer)
Landesebene: Landesparlamente / Landtage (z.B. Bayerischer Landtag)
Kommunal: Gemeinderäte, Stadträte, Kreistage
Kontrolle: Bundesverfassungsgericht kann Gesetze für verfassungswidrig erklären`},
{tid:"pog",topic:"PoG",ref:"Gewaltenteilung · Exekutive",q:`Was macht die Exekutive? Nenne alle Ebenen.`,a:`Exekutive = setzt Gesetze um und verwaltet.
Bundesebene: Bundesregierung (Kanzler + Minister), Bundesverwaltung
Landesebene: Landesregierungen und Verwaltungen
Kommunal: Kreis-, Stadt- und Gemeindeverwaltungen
Dazu gehören: Polizei, Finanzamt, Staatsanwaltschaft
Kontrolle: Kanzler kann durch Misstrauensvotum abgewählt werden`},
{tid:"pog",topic:"PoG",ref:"Gewaltenteilung · Judikative",q:`Was macht die Judikative? Nenne alle Ebenen.`,a:`Judikative = wendet Gesetze an, fällt Urteile. Richter UNABHÄNGIG (Art. 97).
Bundesebene: Bundesverfassungsgericht, Bundesgerichtshof
Landesebene: Oberlandesgerichte, Landgerichte
Kommunal: Amtsgerichte (kleinere Fälle)
Stärkste Kontrolle: Bundesverfassungsgericht kann Gesetze des Bundestags für nichtig erklären`},
{tid:"pog",topic:"PoG",ref:"Gewaltenteilung · Beispiele",q:`Nennen Sie 3 Beispiele, wie die Gewaltenteilung in der Praxis funktioniert.`,a:`1. Misstrauensvotum: Bundestag (Legislative) kann Bundeskanzler (Exekutive) abwählen
2. Bundesverfassungsgericht: kann Gesetze des Bundestags (Legislative) für verfassungswidrig erklären
3. Verwaltungsklage: Bürger kann gegen staatliche Bescheide vor Gericht klagen (Judikative kontrolliert Exekutive)`},
// WEIMARER REPUBLIK
{tid:"pog",topic:"PoG",ref:"Weimarer Republik · Scheitern",q:`Warum scheiterte die Weimarer Republik (1918–1933)?`,a:`Äußere Belastungen: Ende 1. Weltkrieg, Versailler Vertrag (Reparationen), Hyperinflation 1923, Weltwirtschaftskrise 1929 (Massenarbeitslosigkeit)
Verfassungsschwächen: Reichspräsident hatte zu viel Macht:
- konnte Reichstag auflösen (Art. 25)
- konnte Grundrechte außer Kraft setzen (Art. 48 – Notstandsparagraph)
- ernannte und entließ Reichskanzler (Art. 53)
Grundrechte waren nicht einklagbar. Folge: Hitler nutzte Art. 48, Ermächtigungsgesetz 1933 schaltete alle anderen Gewalten aus.`},
{tid:"pog",topic:"PoG",ref:"Weimarer Republik vs. GG",q:`Wie unterscheiden sich Grundrechte in der Weimarer Verfassung vom Grundgesetz?`,a:`WEIMARER REPUBLIK:
- Grundrechte nicht einklagbar (nur Programmsätze)
- Konnten durch Art. 48 außer Kraft gesetzt werden
- Reichspräsident = "Ersatzkaiser" mit zu viel Macht
GRUNDGESETZ (1949):
- Grundrechte einklagbar (Art. 19 Abs. 4)
- Menschenwürde absolut unantastbar
- Ewigkeitsgarantie schützt Kernprinzipien
- Konstruktives Misstrauensvotum: Kanzler nur abwählbar wenn gleichzeitig Nachfolger gewählt
Merksatz: "Das Grundgesetz versteht man mit dem Blick zurück."`},
{tid:"pog",topic:"PoG",ref:"Weimarer Republik · Verfassung",q:`Was waren die demokratischen Elemente der Weimarer Verfassung?`,a:`- Wahl des Reichstags durch allgemeines, direktes Wahlrecht
- Wahl des Reichspräsidenten direkt durch das Volk
- Volksbegehren und Volksentscheide als Instrumente direkter Demokratie
Die Weimarer Verfassung galt als "demokratischste Demokratie der Welt" – scheiterte aber an ihrer eigenen Schwäche: zu viel Macht beim Reichspräsidenten.`},
// WIRTSCHAFT & KONJUNKTUR
{tid:"pog",topic:"PoG",ref:"Konjunktur · 4 Phasen",q:`Beschreiben Sie die 4 Phasen der Konjunkturwelle.`,a:`1. AUFSCHWUNG: BIP steigt, Investitionen nehmen zu, Arbeitslosigkeit sinkt, positive Stimmung
2. HOCHKONJUNKTUR (Boom): Vollbeschäftigung, hohe Inflation, Kapazitäten ausgelastet, Fachkräftemangel
3. ABSCHWUNG (Rezession): BIP sinkt, Entlassungen steigen, Investitionen rückläufig, Insolvenzen nehmen zu
4. TIEFSTAND (Depression): hohe Arbeitslosigkeit, Deflation möglich, kaum Investitionen
Dann beginnt neuer Zyklus. Dauer: ca. 4–12 Jahre.`},
{tid:"pog",topic:"PoG",ref:"Konjunktur · Arbeitslosigkeit",q:`Wie hängt Konjunktur mit Arbeitslosigkeit zusammen?`,a:`Hochkonjunktur → niedrige Arbeitslosigkeit (viel Produktion, viele Stellen)
Rezession/Depression → hohe Arbeitslosigkeit (Produktion sinkt, Entlassungen)
Konjunkturelle Arbeitslosigkeit: entsteht direkt durch wirtschaftlichen Abschwung.
Gegenmittel: staatliche Konjunkturprogramme (Investitionen, Steuersenkungen)`},
{tid:"pog",topic:"PoG",ref:"Soziale Marktwirtschaft",q:`Was ist die soziale Marktwirtschaft und was sind ihre Grundprinzipien?`,a:`Wirtschaftssystem der BRD = freie Marktwirtschaft + soziale Absicherung.
Grundprinzipien:
1. Wettbewerbsfreiheit
2. Vertragsfreiheit
3. Eigentumsrecht (privat)
4. Sozialer Ausgleich (Sozialversicherungen)
5. Stabiles Geld (Preisstabilität)
Gegensatz: Planwirtschaft (staatlich gesteuert) oder reiner Kapitalismus (ohne Sozialsystem)`},
{tid:"pog",topic:"PoG",ref:"Angebot & Nachfrage",q:`Wie funktioniert die Preisbildung am Markt?`,a:`NACHFRAGE: Bei niedrigem Preis viel Nachfrage, bei hohem wenig → fallende Kurve
ANGEBOT: Bei hohem Preis viel Angebot, bei niedrigem wenig → steigende Kurve
GLEICHGEWICHTSPREIS: Schnittpunkt = Marktpreis, bei dem Angebot = Nachfrage
Nachfrage steigt → Preis steigt. Angebot steigt → Preis sinkt.
Knappheit → Preis steigt. Überangebot → Preis sinkt.`},
{tid:"pog",topic:"PoG",ref:"Magisches Viereck",q:`Was ist das "Magische Viereck" der Wirtschaftspolitik?`,a:`4 gleichzeitig angestrebte Ziele (Stabilitätsgesetz 1967):
1. Stabiles Preisniveau (~2% Inflation)
2. Hoher Beschäftigungsstand (<3% Arbeitslosigkeit)
3. Stetiges Wirtschaftswachstum (~2-3% BIP/Jahr)
4. Außenwirtschaftliches Gleichgewicht (Exporte ≈ Importe)
"Magisch" = alle 4 gleichzeitig kaum erreichbar → Zielkonflikte unvermeidbar`},
// ARBEITSLOSIGKEIT
{tid:"pog",topic:"PoG",ref:"Arbeitslosigkeit · 6 Arten",q:`Nennen Sie alle 6 Arten der Arbeitslosigkeit mit Erklärung.`,a:`1. STRUKTURELL: Branchen schrumpfen dauerhaft (z.B. Kohle, Stahl). Ursache: technolog. Wandel.
2. KONJUNKTURELL: wirtschaftlicher Abschwung. Nachfrage sinkt → Entlassungen.
3. SAISONAL: saisonbedingte Schwankungen (z.B. Bau im Winter, Tourismus).
4. PERSONENBEDINGT: Qualifikationen passen nicht zum Stellenangebot (Mismatch).
5. FRIKTIONELL: kurzfristig beim Stellenwechsel.
6. REGIONAL: betrifft eine ganze Region (z.B. strukturschwache Gebiete in Ostdeutschland).`},
{tid:"pog",topic:"PoG",ref:"Arbeitslosigkeit · Strukturell",q:`Was ist strukturelle Arbeitslosigkeit und was sind ihre Ursachen?`,a:`Entsteht, wenn in bestimmten Branchen die Beschäftigungsmöglichkeiten dauerhaft sinken.
Ursachen: Rationalisierung (Automatisierung), mangelnde Nachfrage, mangelnde Wettbewerbsfähigkeit, technologischer Rückstand.
Beispiele: Steinkohlebergbau, Porzellanindustrie, Baugewerbe.
Besonderheit: Betroffene können sich oft nicht einfach umorientieren → Umschulungen nötig.`},
{tid:"pog",topic:"PoG",ref:"Arbeitslosigkeit · Saisonal",q:`Was ist saisonale Arbeitslosigkeit? Nennen Sie 3 Branchen.`,a:`Saisonale Arbeitslosigkeit entsteht durch jahreszeitliche Schwankungen im Personalbedarf.
Branchen: Baugewerbe (wenig Arbeit im Winter), Tourismus (Nebensaison), Landwirtschaft (außerhalb der Ernte).
Besonderheit: Vorhersehbar und regelmäßig wiederkehrend. Kein strukturelles Problem.`},
// SOZIALVERSICHERUNG
{tid:"pog",topic:"PoG",ref:"5 Sozialversicherungen",q:`Welche 5 gesetzlichen Sozialversicherungen gibt es?`,a:`1. KRANKENVERSICHERUNG (GKV): bei Krankheit. Beitrag: 14,6% (7,3% AN + 7,3% AG)
2. RENTENVERSICHERUNG: im Alter. 18,6% (je 9,3% AN+AG)
3. PFLEGEVERSICHERUNG: bei Pflegebedürftigkeit. 3,4%
4. ARBEITSLOSENVERSICHERUNG: ALG I bei Jobverlust. 2,6% (je 1,3% AN+AG)
5. UNFALLVERSICHERUNG: bei Arbeitsunfällen. NUR Arbeitgeber zahlt. Träger: Berufsgenossenschaften`},
{tid:"pog",topic:"PoG",ref:"GKV · Versicherte",q:`Wer ist in der gesetzlichen Krankenversicherung versichert?`,a:`PFLICHTVERSICHERT: Arbeitnehmer bis 66.600 € Jahresgehalt, Arbeitslose, Rentner, Studenten, Landwirte
FAMILIENVERSICHERT (kostenlos): Ehe-/Lebenspartner ohne eigenes Einkommen, Kinder bis 18 (bis 25 bei Ausbildung/Studium)
NICHT pflichtversichert: Beamte, Richter, Selbstständige → können private KV wählen
Beitragsbemessungsgrenze: ca. 5.000 €/Monat`},
{tid:"pog",topic:"PoG",ref:"GKV · Beiträge",q:`Wie hoch sind die GKV-Beiträge und wie werden sie aufgeteilt?`,a:`Beitragssatz: 14,6% des Bruttoeinkommens
Aufteilung: 7,3% AN + 7,3% AG + individuelle Zusatzbeiträge der Kasse (~1,6%)
Gesundheitsfonds: alle Beiträge werden gesammelt und nach Schlüssel (Alter, Geschlecht, Gesundheit) verteilt.
Zusätzlich: Bundeszuschüsse aus Steuergeldern.`},
{tid:"pog",topic:"PoG",ref:"GKV · Leistungen",q:`Welche Leistungen bietet die GKV?`,a:`1. PRÄVENTION: Kuren, Schutzimpfungen, Betriebliche Gesundheitsförderung
2. FRÜHERKENNUNG: Vorsorgeuntersuchungen (U1–U9 für Kinder, Krebsvorsorge)
3. KRANKENBEHANDLUNG: alle medizinisch notwendigen Behandlungen
4. KRANKENGELD: 70% des Bruttogehalts ab 7. Woche (Arbeitgeber zahlt 6 Wochen laut EntgFG). Max. 78 Wochen in 3 Jahren.
5. MUTTERSCHAFTSGELD: 13 €/Tag, 6 Wochen vor bis 8 Wochen nach Geburt`},
// ARBEITSRECHT
{tid:"pog",topic:"PoG",ref:"Kündigungsfristen",q:`Welche gesetzlichen Kündigungsfristen gelten?`,a:`Grundkündigung (§ 622 BGB): 4 Wochen zum 15. oder Monatsende.
Staffelung nach Betriebszugehörigkeit (Arbeitgeber):
2 J → 1 Mon | 5 J → 2 Mon | 8 J → 3 Mon | 10 J → 4 Mon | 12 J → 5 Mon | 15 J → 6 Mon | 20 J → 7 Mon
Probezeit: 2 Wochen jederzeit.
Azubi nach Probezeit: 4 Wochen.
Tarifvertrag kann längere Fristen festlegen.`},
{tid:"pog",topic:"PoG",ref:"Kündigungsarten",q:`Was ist der Unterschied zwischen ordentlicher, außerordentlicher und fristloser Kündigung?`,a:`ORDENTLICH: unter Einhaltung der gesetzlichen/tariflichen Fristen. Keine Begründung nötig.
AUSSERORDENTLICH: aus wichtigem Grund, innerhalb 2 Wochen. Meist sofortige Wirkung.
FRISTLOS: sofort wirksam. Nur bei schwerwiegenden Gründen (Diebstahl, Körperverletzung).
ABMAHNUNG: Vorwarnung vor Kündigung bei weniger schweren Vergehen.
Besonderen Schutz: Schwangere, Schwerbehinderte, Betriebsratsmitglieder.`},
{tid:"pog",topic:"PoG",ref:"Tarifvertrag",q:`Was ist ein Tarifvertrag, wer schließt ihn ab und was regelt er?`,a:`Vertrag zwischen GEWERKSCHAFT und ARBEITGEBERVERBAND über Arbeitsbedingungen.
Inhalt: Löhne/Gehälter, Arbeitszeiten, Urlaubsansprüche, Zuschläge, Kündigungsfristen.
Abschluss: z.B. IG Metall + ZVEI. Im Elektrohandwerk: ZVEH-Tarifvertrag.
Geltung: für Mitglieder beider Verbände. Allgemeinverbindlichkeit: gilt für alle wenn Bundesminister dies erklärt.
Günstigkeitsprinzip: Abweichungen im Arbeitsvertrag nur ZUGUNSTEN des AN.`},
{tid:"pog",topic:"PoG",ref:"Arbeitsvertrag",q:`Was muss ein Arbeitsvertrag mindestens enthalten?`,a:`Laut Nachweisgesetz (NachweisG):
Name/Anschrift beider Parteien, Arbeitsbeginn, Arbeitsort, Tätigkeitsbeschreibung, Arbeitszeit, Vergütung, Urlaubsanspruch (mind. 24 Werktage), Kündigungsfristen, Hinweis auf Tarifvertrag.
Probezeit: max. 6 Monate, muss vereinbart werden.
Form: schriftlich, vor Arbeitsbeginn.
Günstigkeitsprinzip: Abweichungen nur zugunsten des AN.`},
{tid:"pog",topic:"PoG",ref:"Urlaub & Mindestlohn",q:`Wie viele Urlaubstage stehen Arbeitnehmern zu und was gilt für den Mindestlohn?`,a:`URLAUB (BUrlG): mind. 24 Werktage (6-Tage-Woche) = 20 Arbeitstage (5-Tage-Woche).
Tarifvertrag: oft 28–30 Tage. Azubis unter 18: mind. 25 Werktage (JArbSchG).
MINDESTLOHN: seit 2015 gesetzlich. Gilt ab 18 Jahren.
Azubis: eigene Mindest-Ausbildungsvergütung nach BBiG (1. LJ: mind. 649 €/Monat).
Tarifvertrag kann höheren Mindestlohn festlegen.`},
// AUSBILDUNG
{tid:"pog",topic:"PoG",ref:"BBiG",q:`Was regelt das BBiG und welche Pflichten haben Betrieb und Azubi?`,a:`BBiG = Berufsbildungsgesetz, regelt duale Berufsausbildung.
BETRIEB muss: Ausbildungsmittel kostenlos stellen, Vergütung zahlen, nur ausbildungsrelevante Aufgaben geben, Berufsschulbesuch ermöglichen.
AZUBI muss: Berichtsheft führen, Berufsschule besuchen, sorgfältig arbeiten, Betriebsgeheimnisse wahren.
Probezeit: 1–4 Monate. In Probezeit jederzeit fristlos kündbar.
Prüfungen: Zwischenprüfung/Teil 1 + Abschlussprüfung/Teil 2 (= Gesellenprüfung GP).
Nichtbestehen: max. 2 Wiederholungen.`},
{tid:"pog",topic:"PoG",ref:"Duales System",q:`Was ist das duale Ausbildungssystem?`,a:`Ausbildung an 2 Lernorten gleichzeitig:
1. BETRIEB: praktische Ausbildung (3–4 Tage/Woche)
2. BERUFSSCHULE: theoretische Ausbildung (1–2 Tage/Woche)
Vorteile: Praxisnähe, Azubis verdienen, hohe Übernahmequoten, international als Vorbild.
Im Elektrohandwerk zusätzlich: überbetriebliche Kurse an der Innung (ETE, ET).`},
];

// Alle PoG-Fragen in ALL_QA einfügen

// ═══ WEITERE POG-FRAGEN ═══
const POG_EXTRA2 = [
// STAAT & DEMOKRATIE
{tid:"pog",topic:"PoG",ref:"Demokratie · Wahlen",q:`Welche 5 Wahlgrundsätze gelten bei Bundestagswahlen?`,
a:`1. Allgemein: alle Deutschen ab 18 dürfen wählen
2. Unmittelbar: Wähler bestimmt direkt die Abgeordneten (keine Wahlmänner)
3. Frei: kein Zwang, keine Beeinflussung
4. Gleich: jede Stimme zählt gleich viel
5. Geheim: Wahlgeheimnis, niemand darf wissen wen man wählt
Geregelt in Art. 38 GG`},

{tid:"pog",topic:"PoG",ref:"Demokratie · Bundesorgane",q:`Welche Bundesorgane gibt es in Deutschland?`,
a:`Bundestag: Volksvertretung, macht Gesetze, wählt Bundeskanzler
Bundesrat: Vertretung der 16 Bundesländer, wirkt bei Bundesgesetzen mit
Bundesregierung: Bundeskanzler + Minister, führt die Regierung
Bundespräsident: Staatsoberhaupt, repräsentativ, unterschreibt Gesetze
Bundesverfassungsgericht: höchstes Gericht, prüft Verfassungsmäßigkeit`},

{tid:"pog",topic:"PoG",ref:"Demokratie · Bundestag",q:`Was ist der Bundestag und welche Aufgaben hat er?`,
a:`Bundestag = direkt gewählte Volksvertretung (ca. 700 Abgeordnete, 4 Jahre Amtszeit).
Aufgaben:
1. Gesetzgebung: beschließt Bundesgesetze
2. Wahl des Bundeskanzlers (Mehrheitswahl)
3. Haushaltsbeschluss: genehmigt den Bundeshaushalt
4. Kontrolle der Regierung (Fragestunden, Untersuchungsausschüsse)
5. Misstrauensvotum: kann Kanzler abwählen (konstruktiv)`},

{tid:"pog",topic:"PoG",ref:"Demokratie · Bundesrat",q:`Was ist der Bundesrat und welche Aufgaben hat er?`,
a:`Bundesrat = Vertretung der 16 Bundesländer auf Bundesebene.
Zusammensetzung: je nach Einwohnerzahl 3–6 Stimmen pro Land, besetzt mit Landesministern.
Aufgaben:
1. Mitbestimmung bei Gesetzen (Zustimmungsgesetze brauchen Bundesrat-Mehrheit)
2. Einspruchsrecht gegen Bundesgesetze
3. Kann Vermittlungsausschuss anrufen
Wichtig: kein direkt gewähltes Organ – Mitglieder werden von Landesregierungen entsandt`},

{tid:"pog",topic:"PoG",ref:"Demokratie · Bundespräsident",q:`Was sind die Aufgaben des Bundespräsidenten?`,
a:`Bundespräsident = Staatsoberhaupt der BRD. Amtszeit 5 Jahre, max. 2 Amtszeiten.
Wahl: durch Bundesversammlung (Bundestag + gleich viele Landesvertreter).
Aufgaben: Repräsentation Deutschlands nach außen, Unterzeichnung von Bundesgesetzen, Ernennung/Entlassung des Bundeskanzlers, Auflösung des Bundestags (in bestimmten Situationen).
Wichtig: keine aktive Regierungsmacht – Bundespräsident ist vor allem repräsentativ tätig.`},

{tid:"pog",topic:"PoG",ref:"Parteien · Funktion",q:`Welche Funktion haben Parteien in der Demokratie?`,
a:`Parteien sind nach Art. 21 GG verfassungsrechtlich anerkannt.
Aufgaben: Politische Willensbildung des Volkes, Kandidatenaufstellung für Wahlen, Verbindung zwischen Bevölkerung und Staat, Programmatische Orientierung.
5%-Hürde: Partei muss mind. 5% der Zweitstimmen erhalten, um in den Bundestag einzuziehen (Splitterparteien verhindern).
Parteienverbot: nur durch Bundesverfassungsgericht möglich (Art. 21 GG)`},

{tid:"pog",topic:"PoG",ref:"Konjunktur · Instrumente",q:`Mit welchen Maßnahmen kann der Staat konjunkturelle Schwankungen ausgleichen?`,
a:`In der REZESSION (Wirtschaft schwächelt):
Expansive Fiskalpolitik: staatliche Investitionen erhöhen (z.B. Infrastruktur), Steuern senken, Transferleistungen erhöhen
Expansive Geldpolitik (EZB): Zinsen senken → Kredite günstiger → mehr Investitionen

In der HOCHKONJUNKTUR (Überhitzung):
Restriktive Fiskalpolitik: Ausgaben kürzen, Steuern erhöhen, Schulden tilgen
Restriktive Geldpolitik: Zinsen erhöhen → teurere Kredite → weniger Investitionen`},

{tid:"pog",topic:"PoG",ref:"Wirtschaft · BIP",q:`Was ist das Bruttoinlandsprodukt (BIP) und wofür wird es verwendet?`,
a:`BIP = Gesamtwert aller in einem Land produzierten Waren und Dienstleistungen in einem Jahr.
Verwendung: Messung der wirtschaftlichen Leistung, Vergleich zwischen Ländern, Basis für Konjunkturanalyse.
Nominales BIP: in laufenden Preisen. Reales BIP: inflationsbereinigt.
BIP pro Kopf: BIP geteilt durch Einwohnerzahl = Wohlstandsindikator.
Deutschland: ca. 4 Billionen Euro BIP/Jahr (eine der größten Volkswirtschaften weltweit).`},

{tid:"pog",topic:"PoG",ref:"Wirtschaft · Inflation",q:`Was ist Inflation und welche Arten gibt es?`,
a:`Inflation = allgemeiner Anstieg des Preisniveaus → Kaufkraft des Geldes sinkt.
Arten: Schleichende Inflation (<2%/Jahr): normal, von EZB angestrebt. Galoppierende Inflation (2–10%): problematisch. Hyperinflation (>50%/Monat): Währungszusammenbruch (z.B. DE 1923).
Ursachen: zu viel Geld im Umlauf, Nachfrageboom, Angebotsschocks (z.B. Energiepreise).
Deflation: Preise sinken → Konsumenten warten → Wirtschaft lähmt sich (gefährlicher als Inflation!)`},

{tid:"pog",topic:"PoG",ref:"Wirtschaft · Globalisierung",q:`Was versteht man unter Globalisierung und welche Vor- und Nachteile hat sie?`,
a:`Globalisierung = weltweite Verflechtung von Wirtschaft, Kultur, Politik und Kommunikation.
VORTEILE: günstigere Waren durch weltweiten Wettbewerb, mehr Auswahl, wirtschaftliches Wachstum, internationale Zusammenarbeit
NACHTEILE: Arbeitsplatzverlagerung in Billiglohnländer, Abhängigkeit von globalen Lieferketten, Umweltbelastung durch Transport, wachsende Ungleichheit zwischen arm und reich`},

// SOZIALVERSICHERUNG VERTIEFT
{tid:"pog",topic:"PoG",ref:"Rentenversicherung",q:`Wie funktioniert die gesetzliche Rentenversicherung?`,
a:`Umlageverfahren: Beiträge der aktiv Beschäftigten finanzieren direkt die aktuellen Renten.
Beitragssatz: 18,6% (je 9,3% AN + AG).
Rentenhöhe hängt ab von: Dauer der Einzahlung, Höhe des Einkommens, Renteneintrittsjahr.
Probleme: demografischer Wandel (weniger Beitragszahler, mehr Rentner) → Rentenkürzungen oder Beitragserhöhungen nötig.
Renteneintrittsalter: schrittweise auf 67 Jahre angehoben.`},

{tid:"pog",topic:"PoG",ref:"Pflegeversicherung",q:`Was leistet die gesetzliche Pflegeversicherung?`,
a:`Absicherung bei Pflegebedürftigkeit. Beitragssatz: 3,4% (Kinderlose: 4%).
Pflegegrade 1–5 (je nach Pflegebedarf).
Leistungen je nach Pflegegrad: ambulante Pflege (zu Hause), stationäre Pflege (Pflegeheim), Pflegegeld für Angehörige, Entlastungsleistungen.
Wichtig: Pflegeversicherung deckt nur TEIL der Kosten → Eigenanteil der Betroffenen oft hoch!
Träger: gesetzliche Pflegekassen (an GKV angegliedert).`},

{tid:"pog",topic:"PoG",ref:"Arbeitslosenversicherung",q:`Was leistet die Arbeitslosenversicherung (ALG I)?`,
a:`ALG I (Arbeitslosengeld I) = Leistung der Bundesagentur für Arbeit.
Voraussetzung: mind. 12 Monate in letzten 2 Jahren versicherungspflichtig beschäftigt.
Höhe: 60% des letzten Nettogehalts (67% mit Kindern).
Dauer: 6–24 Monate (je nach Beschäftigungsdauer und Alter).
Nach ALG I: Bürgergeld (früher ALG II/Hartz IV) vom Jobcenter.
Beitrag: 2,6% (je 1,3% AN+AG).`},

{tid:"pog",topic:"PoG",ref:"Unfallversicherung",q:`Was ist die gesetzliche Unfallversicherung und was deckt sie ab?`,
a:`Pflichtversicherung für alle Arbeitnehmer. NUR vom Arbeitgeber finanziert.
Träger: Berufsgenossenschaften (nach Branche aufgeteilt, z.B. BG BAU, BG ETEM für Elektro).
Deckt ab: Arbeitsunfälle (auf dem Arbeitsweg = Wegeunfall + am Arbeitsplatz), Berufskrankheiten.
Leistungen: Heilbehandlung, Rehabilitation, Verletztengeld, Berufsunfähigkeitsrente.
Wichtig: Präventionsauftrag – BGen unterstützen Betriebe bei Arbeitssicherheit (DGUV V3).`},

// ARBEITSRECHT VERTIEFT
{tid:"pog",topic:"PoG",ref:"Arbeitnehmerschutz · Gesetze",q:`Welche wichtigen Arbeitnehmerschutzgesetze gibt es?`,
a:`ArbZG (Arbeitszeitgesetz): max. 8h/Tag (bis 10h möglich wenn Ausgleich), Mindestruhezeit 11h, Sonn-/Feiertagsschutz.
JArbSchG (Jugendarbeitsschutzgesetz): Unter 18: max. 8h/Tag, keine Nachtarbeit, mind. 30 Werktage Urlaub.
MuSchG (Mutterschutzgesetz): Beschäftigungsverbot 6 Wochen vor und 8 Wochen nach Geburt.
KSchG (Kündigungsschutzgesetz): Schutz vor unberechtigter Kündigung ab 6 Monaten Betriebszugehörigkeit.
BEEG (Bundeselterngeldgesetz): Elterngeld + Elternzeit bis 3 Jahre pro Kind.`},

{tid:"pog",topic:"PoG",ref:"Betriebsrat",q:`Was ist ein Betriebsrat und welche Rechte hat er?`,
a:`Betriebsrat = demokratisch gewählte Interessenvertretung der Arbeitnehmer im Betrieb.
Ab 5 ständig Beschäftigten wählbar (BetrVG – Betriebsverfassungsgesetz).
Rechte: Mitbestimmungsrechte: Arbeitszeiten, Urlaubsplanung, Betriebsordnung.
Mitwirkungsrechte: Einstellungen, Versetzungen, Kündigungen (muss angehört werden).
Informationsrechte: wirtschaftliche Situation des Unternehmens.
Schutz: Betriebsratsmitglieder genießen besonderen Kündigungsschutz.`},

{tid:"pog",topic:"PoG",ref:"Arbeitskampf",q:`Was sind Streik und Aussperrung? Wann sind sie legal?`,
a:`STREIK: Arbeitnehmer verweigern kollektiv die Arbeit, um Tarifforderungen durchzusetzen.
Legal wenn: von Gewerkschaft ausgerufen, nach gescheiterter Tarifverhandlung, Urabstimmung (mind. 75% Zustimmung nötig).
Streikende erhalten Streikgeld von der Gewerkschaft (ca. 60-70% des Nettogehalts).
AUSSPERRUNG: Arbeitgeber sperren Arbeitnehmer kollektiv aus.
Umstrittenes Mittel, selten angewendet.
FRIEDENSPFLICHT: Während laufendem Tarifvertrag kein Streik erlaubt!`},

{tid:"pog",topic:"PoG",ref:"Mitbestimmung",q:`Was versteht man unter betrieblicher und unternehmerischer Mitbestimmung?`,
a:`BETRIEBLICHE MITBESTIMMUNG (BetrVG): Betriebsrat auf Betriebsebene. Mitbestimmung bei sozialen, personellen und wirtschaftlichen Angelegenheiten.
UNTERNEHMERISCHE MITBESTIMMUNG (MitbestG): In Unternehmen ab 500 Mitarbeitern: Arbeitnehmervertreter im Aufsichtsrat. Ab 2000 Mitarbeiter: paritätische Mitbestimmung (gleich viele AN- und AG-Vertreter im Aufsichtsrat).
Ziel: Arbeitnehmer haben Einfluss auf strategische Unternehmensentscheidungen.`},

// AUSBILDUNG VERTIEFT
{tid:"pog",topic:"PoG",ref:"Ausbildungsvertrag",q:`Was muss ein Ausbildungsvertrag enthalten?`,
a:`Pflichtinhalt (§ 11 BBiG): Art, sachliche und zeitliche Gliederung der Ausbildung, Beginn und Dauer, Ausbildungsort, Ausbildungsvergütung (je Lehrjahr), Urlaubsanspruch, Probezeit (1–4 Monate), Kündigungsvoraussetzungen.
Form: schriftlich vor Beginn der Ausbildung.
Eintragung: ins Ausbildungsregister der zuständigen Kammer (z.B. HWK Nürnberg).
Wichtig: Ausbildungsvertrag ≠ Arbeitsvertrag, aber viele Arbeitsrechtsgrundsätze gelten entsprechend.`},

{tid:"pog",topic:"PoG",ref:"Ausbildungsvergütung",q:`Wie ist die Ausbildungsvergütung geregelt?`,
a:`Gesetzliche Mindestvergütung (§ 17 BBiG, seit 2020):
1. Lehrjahr: mind. 649 €/Monat (2024)
2. Lehrjahr: mind. 766 €/Monat (+18%)
3. Lehrjahr: mind. 876 €/Monat (+35%)
4. Lehrjahr (falls vorhanden): mind. 909 €/Monat (+40%)
Tarifvertrag: kann höhere Vergütung festlegen → gilt dann vorrangig!
Im Elektrohandwerk (ZVEH-TV): meist höher als gesetzliches Minimum.
Sozialversicherung: Azubis sind voll sozialversicherungspflichtig.`},

{tid:"pog",topic:"PoG",ref:"Gesellenprüfung",q:`Wie ist die Gesellenprüfung (GP) im Elektrohandwerk aufgebaut?`,
a:`Zweiteilige Abschlussprüfung nach BBiG:
TEIL 1 (GP1): findet nach ca. 18 Monaten Ausbildung statt. Praktische + theoretische Aufgaben aus dem ersten Ausbildungsabschnitt. Gewichtung: 30% der Gesamtnote.
TEIL 2 (GP2): am Ende der Ausbildung. Praktische Arbeit + schriftliche Prüfung (EG = Elektrotechnische Grundlagen + PoG). Gewichtung: 70% der Gesamtnote.
Nichtbestehen: max. 2 Wiederholungen möglich.
Zuständig: Handwerkskammer (HWK) des jeweiligen Bezirks.`},

{tid:"pog",topic:"PoG",ref:"Berufsschule",q:`Welche Funktion hat die Berufsschule im dualen System?`,
a:`Berufsschule = staatlicher Bildungsort im dualen System.
Aufgaben: allgemeine Bildung vertiefen (z.B. Deutsch, Englisch, Mathematik), berufliche Fachtheorie vermitteln (Elektrotechnik, PoG), auf Gesellenprüfung vorbereiten.
Pflicht: Berufsschulpflicht gilt bis Ende der Ausbildung (Länderrecht).
Stundenplan: 1–2 Tage/Woche oder Blockunterricht (mehrere Wochen am Stück).
Abschluss: Berufsschulabschluss. Bei gutem Ergebnis: gleichzeitig mittlerer Bildungsabschluss (Realschule).`},

// GESELLSCHAFT
{tid:"pog",topic:"PoG",ref:"Soziale Ungleichheit",q:`Was versteht man unter sozialer Ungleichheit und wie misst man sie?`,
a:`Soziale Ungleichheit = ungleiche Verteilung von Ressourcen (Einkommen, Bildung, Gesundheit, Macht) in einer Gesellschaft.
Messung: Gini-Koeffizient (0=vollständige Gleichheit, 1=maximale Ungleichheit). Deutschland ca. 0,29.
Ursachen: unterschiedliche Bildungschancen, Erbschaften, Herkunft, Diskriminierung.
Soziale Schichten: Oberschicht, Mittelschicht (größte Gruppe), Unterschicht.
Soziale Mobilität: Möglichkeit, in eine andere Schicht auf- oder abzusteigen. In Deutschland möglich, aber bildungsabhängig.`},

{tid:"pog",topic:"PoG",ref:"Steuern",q:`Was sind Steuern und wie werden sie eingeteilt?`,
a:`Steuern = Pflichtabgaben an den Staat ohne direkten Gegenwert.
Einteilung: DIREKTE STEUERN: zahlt direkt der Steuerpflichtige. Einkommensteuer (bis 45%), Körperschaftsteuer (Unternehmen), Gewerbesteuer.
INDIREKTE STEUERN: im Preis von Waren enthalten. Umsatzsteuer (MwSt 19%/7%), Energiesteuer, Tabaksteuer.
PROGRESSIONSPRINZIP: wer mehr verdient, zahlt mehr Steuern (Einkommensteuer).
Steuereinnahmen: finanzieren Staatsaufgaben (Bildung, Infrastruktur, Soziales).`},

{tid:"pog",topic:"PoG",ref:"Europäische Union",q:`Was ist die EU und welche Bedeutung hat sie für Deutschland?`,
a:`EU = Europäische Union, Zusammenschluss von 27 europäischen Staaten (Stand 2024).
Wichtige Errungenschaften: Binnenmarkt (freier Waren-, Dienstleistungs-, Kapital- und Personenverkehr), Euro (Gemeinschaftswährung, 20 Länder), Schengen-Raum (offene Grenzen), EU-Bürgerrechte.
Organe: Europäisches Parlament (direkt gewählt), Europäische Kommission (Exekutive), Europäischer Rat (Staatschefs), Europäischer Gerichtshof.
Für Deutschland: wichtigster Exportmarkt, politische Stabilität, europäisches Recht hat Vorrang vor nationalem Recht.`},

{tid:"pog",topic:"PoG",ref:"Umweltpolitik",q:`Welche umweltpolitischen Ziele verfolgt Deutschland?`,
a:`Klimaschutzgesetz: Deutschland muss bis 2045 klimaneutral sein.
Ziele bis 2030: 65% weniger CO₂ als 1990, 80% Strom aus Erneuerbaren (EEG).
Instrumente: CO₂-Preis (Emissionshandel), Förderung Erneuerbarer (EEG-Umlage), Energieeffizienzgesetze, Gebäudeenergiegesetz (GEG).
Relevanz für Elektroinstallateure: PV-Anlagen, Wärmepumpen, E-Auto-Ladepunkte – wachsendes Arbeitsfeld.`},
];

for(const q of POG_EXTRA2){ ALL_QA.push(q); }

for(const q of POG_EXTRA){ ALL_QA.push(q); }

// PV Betriebsarten
const PV_BETR = [
{tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Betriebsarten",q:`Welche 4 Betriebsarten gibt es bei PV-Anlagen?`,a:`1. NETZGEKOPPELT (Grid-Tie): einspeisen ins öffentliche Netz. Häufigste Form in DE.
2. INSELBETRIEB (Off-Grid): vollständig vom Netz getrennt. Batteriespeicher zwingend.
3. HYBRIDBETRIEB: netzgekoppelt + Batteriespeicher. Bei Netzausfall → Inselbetrieb.
4. NOTSTROM/BACKUP: bestimmte Verbraucher laufen bei Netzausfall weiter.`},
{tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Inselbetrieb",q:`Was sind die Anforderungen beim Inselbetrieb?`,a:`Keine Verbindung zum öffentlichen Netz.
Pflicht: Batteriespeicher (Puffer für Nacht/Schlechtwetter)
Inselwechselrichter erzeugt selbst 230V/50Hz (kein Netz als Referenz)
Lastmanagement: Verbrauch darf Erzeugung nicht dauerhaft übersteigen
Speicher muss mehrere Tage abdecken (Autonomietage)
Anwendung: Berghütten, Boote, Gartenhäuser`},
{tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Hybridbetrieb",q:`Was unterscheidet einen Hybrid-Wechselrichter vom normalen?`,a:`NORMAL: nur netzgekoppelt, bei Netzausfall abschalten, kein Speicher möglich.
HYBRID: Batterieladeeinheit + Wechselrichter + Einspeiseeinheit in einem Gerät.
Kann: Batterie laden (aus PV + Netz), ins Netz einspeisen, bei Netzausfall Inselbetrieb.
Backup: definierte Steckdosen laufen weiter. Bidirektionaler Zähler zwingend.`},
{tid:"pv",topic:"PV-Anlage & Speicher",ref:"PV · Netzgekoppelt",q:`Wie funktioniert eine netzgekoppelte PV-Anlage?`,a:`PV-Module → Gleichstrom → Wechselrichter → 230V/50Hz → Einspeisung ins Netz.
Wichtig: netzgeführter Wechselrichter schaltet bei Netzausfall AUTOMATISCH ab (VDE V 0126-1-1). Kein Inselbetrieb möglich!
Anmeldung beim Netzbetreiber ab 800W Pflicht. Bidirektionaler Zähler für Einspeisung + Eigenverbrauch.`},
];
for(const q of PV_BETR){ ALL_QA.push(q); }


// ═══ TOPIC META ═══
const TM = {
  blitz:{icon:'⚡',name:'Blitzschutz',color:'#f5a623'},
  antenne:{icon:'📡',name:'Antennentechnik',color:'#3ecf8e'},
  pv:{icon:'☀️',name:'PV-Anlage',color:'#f5a623'},
  wp:{icon:'🌡️',name:'Wärmepumpe',color:'#e8445a'},
  licht:{icon:'💡',name:'Lichttechnik',color:'#2ec4a9'},
  strom:{icon:'🔄',name:'Drehstrom & Motor',color:'#4f8ef7'},
  pa:{icon:'🔗',name:'Potentialausgleich',color:'#7c5cbf'},
  erdung:{icon:'🌍',name:'Erdungsanlagen',color:'#3ecf8e'},
  netz:{icon:'🏗️',name:'Netzsysteme',color:'#f5a623'},
  schutz:{icon:'🛡️',name:'Schutzgeräte',color:'#e8445a'},
  messen:{icon:'📏',name:'Messen & Prüfen',color:'#4f8ef7'},
  messung600:{icon:'📊',name:'Anlagenmessung',color:'#4f8ef7'},
  hausanschluss:{icon:'🏠',name:'Hausanschluss',color:'#7c5cbf'},
  haus:{icon:'🏠',name:'HAK & Zähler',color:'#7c5cbf'},
  koerper:{icon:'⚠️',name:'Körperstrom',color:'#e8445a'},
  it:{icon:'🌐',name:'IT & Datennetz',color:'#2ec4a9'},
  install:{icon:'🔧',name:'Installation',color:'#3ecf8e'},
  steuerung:{icon:'⚙️',name:'Steuerungstechnik',color:'#4f8ef7'},
  knx:{icon:'🏡',name:'KNX',color:'#7c5cbf'},
  easy:{icon:'🔌',name:'Eaton Easy',color:'#3ecf8e'},
  zaehler:{icon:'🔢',name:'Zählertechnik',color:'#f5a623'},
  trafo:{icon:'🔁',name:'Transformatoren',color:'#4f8ef7'},
  formeln:{icon:'📐',name:'Formeln',color:'#e8445a'},
  vde:{icon:'📋',name:'VDE Normen',color:'#7c5cbf'},
  arbeit:{icon:'🦺',name:'Arbeitssicherheit',color:'#e8445a'},
  pog:{icon:'🏛️',name:'PoG – Politik & Gesellschaft',color:'#7c5cbf'},
};

/* ===== RECHNER (CALCS) – 1:1 übernommen ===== */
const CALCS=[
  {id:'spf1',title:'Spannungsfall 1-phasig',
   fields:[{id:'l',lbl:'Länge l [m]'},{id:'I',lbl:'Strom I [A]'},{id:'cp',lbl:'cosφ'},{id:'A',lbl:'Querschnitt [mm²]'},{id:'k',lbl:'κ (Cu=56, Al=34)'}],
   fn:(v)=>{const d=2*v.l*v.I*v.cp/(v.k*v.A);return `ΔU = ${d.toFixed(2)} V = ${(d/230*100).toFixed(2)}% (Grenzwert: 3% Licht, 5% sonst)`;}},
  {id:'spf3',title:'Spannungsfall Drehstrom',
   fields:[{id:'l',lbl:'Länge l [m]'},{id:'I',lbl:'Strom I [A]'},{id:'cp',lbl:'cosφ'},{id:'A',lbl:'Querschnitt [mm²]'},{id:'k',lbl:'κ (Cu=56, Al=34)'}],
   fn:(v)=>{const d=Math.sqrt(3)*v.l*v.I*v.cp/(v.k*v.A);return `ΔU = ${d.toFixed(2)} V = ${(d/400*100).toFixed(2)}%`;}},
  {id:'pvmod',title:'PV-Module berechnen',
   fields:[{id:'P',lbl:'Ziel-Leistung [Wp]'},{id:'Pm',lbl:'Leistung pro Modul [Wp]'}],
   fn:(v)=>{const n=Math.ceil(v.P/v.Pm);return `${n} Module (${n}×${v.Pm}Wp = ${n*v.Pm}Wp)`;}},
  {id:'pvsp',title:'PV-Speicher',
   fields:[{id:'P',lbl:'Leistung [kW]'},{id:'h',lbl:'Stunden [h]'},{id:'eta',lbl:'Wirkungsgrad (0.9)'},{id:'dod',lbl:'Entladetiefe DOD (0.85)'}],
   fn:(v)=>{const n=v.P*v.h,b=n/v.eta/v.dod;return `Netto: ${n.toFixed(1)} kWh → Brutto: ${b.toFixed(1)} kWh`;}},
  {id:'pvjahr',title:'PV-Jahresertrag',
   fields:[{id:'P',lbl:'Anlagenleistung [kWp]'},{id:'h',lbl:'Volllaststunden (850–1100)'}],
   fn:(v)=>{const e=v.P*v.h;return `${e.toFixed(0)} kWh/a · CO₂: ${(e*0.4).toFixed(0)} kg/a`;}},
  {id:'licht',title:'Anzahl Leuchten',
   fields:[{id:'E',lbl:'Beleuchtungsstärke [lx]'},{id:'A',lbl:'Raumfläche [m²]'},{id:'phi',lbl:'Lichtstrom/Leuchte [lm]'},{id:'eta',lbl:'Nutzlichtgrad η (0.6)'},{id:'mf',lbl:'Wartungsfaktor (0.8)'}],
   fn:(v)=>{const n=v.E*v.A/(v.phi*v.eta*v.mf);return `${n.toFixed(1)} → mind. ${Math.ceil(n)} Leuchten`;}},
  {id:'strom',title:'Strom aus Leistung',
   fields:[{id:'P',lbl:'Leistung P [W]'},{id:'U',lbl:'Spannung U [V]'},{id:'cp',lbl:'cosφ'},{id:'typ',lbl:'1=Einphasen, 3=Drehstrom'}],
   fn:(v)=>{const I=v.typ===3?v.P/(Math.sqrt(3)*v.U*v.cp):v.P/(v.U*v.cp);return `I = ${I.toFixed(2)} A`;}},
  {id:'trafo',title:'Trafo-Übersetzung',
   fields:[{id:'U1',lbl:'U1 [V]'},{id:'U2',lbl:'U2 [V]'},{id:'I1',lbl:'I1 [A] (0=nein)'}],
   fn:(v)=>{const u=v.U1/v.U2;let r=`ü = ${u.toFixed(3)}`;if(v.I1>0)r+=` | I2 = ${(v.I1*u).toFixed(2)} A`;return r;}},
];

/* ===== FORMELSAMMLUNG (FORMELN) – 1:1 übernommen ===== */
const FORMELN=[
  {s:'⚠️ Auswendig (kein Tabellenbuch!)',items:[
    {n:'Spannungsfall 1-phasig',u:'V',f:'ΔU = 2·l·I·cosφ / (κ·A)',note:'Faktor 2 = Hin+Rückleiter! κ_Cu=56, κ_Al=34'},
    {n:'Spannungsfall Drehstrom',u:'V',f:'ΔU = √3·l·I·cosφ / (κ·A)',note:'≤3% Licht, ≤5% Motoren'},
    {n:'Widerstand',u:'Ω',f:'R = l / (κ·A)',note:''},
    {n:'Ohmsches Gesetz',u:'Ω',f:'R = U / I',note:''},
    {n:'Leistung',u:'W',f:'P = U·I = I²·R',note:''},
  ]},
  {s:'Drehstrom & Motor',items:[
    {n:'Wirkleistung',u:'W',f:'P = √3·U·I·cosφ',note:''},
    {n:'Scheinleistung',u:'VA',f:'S = √3·U·I',note:'S² = P² + Q²'},
    {n:'Strom',u:'A',f:'I = P / (√3·U·cosφ)',note:''},
    {n:'Schlupf',u:'%',f:'s = (n₀–n)/n₀ · 100',note:''},
  ]},
  {s:'PV & Speicher',items:[
    {n:'Modulanzahl',u:'Stk',f:'n = P_gesamt / P_Modul',note:'aufrunden!'},
    {n:'Jahresertrag',u:'kWh',f:'E = P_peak · h_voll',note:'DE: 850–1100 h/a'},
    {n:'Speicher Brutto',u:'kWh',f:'C = P·t / (η·DOD)',note:'η≈0,9; DOD≈0,85'},
  ]},
  {s:'Lichttechnik',items:[
    {n:'Anzahl Leuchten',u:'Stk',f:'n = E·A / (Φ·η·MF)',note:'MF=Wartungsfaktor 0,8'},
    {n:'Beleuchtungsstärke',u:'lx',f:'E = Φ / A',note:''},
  ]},
  {s:'Schutzmaßnahmen',items:[
    {n:'RISO',u:'MΩ',f:'> 1 MΩ (empf. >100 MΩ)',note:''},
    {n:'Schutzleiter',u:'Ω',f:'< 1 Ω',note:''},
    {n:'RCD Auslösezeit',u:'ms',f:'< 200ms bei IΔN',note:''},
    {n:'Trafo Übersetzung',u:'–',f:'ü = U1/U2 = N1/N2',note:''},
  ]},
];

/* ====================================================================
   APP-LOGIK
   ==================================================================== */

// gm(): Topic-Meta inkl. Fallback (wie im Original)
function gm(tid){return TM[tid]||{icon:'📚',name:tid,color:'#4f8ef7'};}
const ALL_TOPICS=[...new Set(ALL_QA.map(q=>q.tid))];

/* ---- Zugangsdaten (clientseitig!) ----------------------------------
   ACHTUNG: Clientseitiger Passwortschutz ist KEIN echter Schutz – der
   Code ist im Browser einsehbar. Für echten Schutz später ein Backend
   (z.B. PHP/Node mit Session) vorschalten. Struktur ist dafür bereits
   getrennt: nur checkLogin() müsste gegen den Server tauschen.        */
const USERS = [
  { name:'alen', pass:'alen' },
  { name:'onur', pass:'onur' }
];
function checkLogin(name, pass){
  const n=(name||'').trim().toLowerCase(), p=(pass||'').trim().toLowerCase();
  return USERS.some(u=>u.name===n && u.pass===p);
}

/* ---- Prüfungsbereiche GP Teil 2 (Bayern) ---------------------------
   Offizielle Prüfungsbereiche für Elektroniker/in für Energie- und
   Gebäudetechnik, Teil 2. Gewichtung laut Aufgabenstellung.
   Jeder Bereich zieht seine Tagesfragen aus passenden Themen (tids).  */
const EXAM_AREAS = [
  { id:'kunde', name:'Kundenauftrag', weight:'25%', icon:'🛠️', color:'#f5a623',
    desc:'Arbeitsaufgabe + Fachgespräch · Installation & Inbetriebnahme',
    tids:['install','haus','hausanschluss','pv','wp','steuerung'] },
  { id:'entwurf', name:'Systementwurf', weight:'12,5%', icon:'📐', color:'#4f8ef7',
    desc:'Planen & Dimensionieren von Anlagen',
    tids:['netz','schutz','blitz','erdung','pa','licht','antenne'] },
  { id:'analyse', name:'Funktions- & Systemanalyse', weight:'12,5%', icon:'🔍', color:'#3ecf8e',
    desc:'Messen, Prüfen, Fehler analysieren',
    tids:['messen','strom','koerper','it'] },
  { id:'wiso', name:'Wirtschafts- & Sozialkunde', weight:'10%', icon:'🏛️', color:'#7c5cbf',
    desc:'Politik, Gesellschaft, Recht & Wirtschaft',
    tids:['pog'] }
];

/* ---- Tagesziel ----------------------------------------------------- */
const DAILY_TARGET_MIN = 200; // ~3,5 h ernsthaftes Lernen pro Tag
const QUESTIONS_PER_AREA = 8; // Pflichtfragen je Bereich und Tag

/* ====================================================================
   STATE & PERSISTENZ (pro Benutzer getrennt)
   ==================================================================== */
let USER = null;            // {name, key}
let SP = null;             // Speicher-Objekt des aktiven Users
let speakOn = true;

function todayKey(){ const d=new Date(); return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); }
function storeKey(name){ return 'gp2trainer_'+name.toLowerCase(); }

function defaultProgress(){
  return {
    score:0,
    streak:0,
    lastActive:null,
    known:[],          // gewusste Fragen (Karteikarten)
    wrong:[],          // falsch beantwortete Fragen (Wiederholung)
    daily:{},          // { 'YYYY-MM-DD': { kunde:[idx..], entwurf:[..], ... , done:false } }
    studyMin:{},       // { 'YYYY-MM-DD': Minuten }
    quizBest:0,
    seenIntro:false
  };
}
function loadProgress(name){
  try{ const raw=localStorage.getItem(storeKey(name)); if(raw){ return Object.assign(defaultProgress(), JSON.parse(raw)); } }catch(e){}
  return defaultProgress();
}
function save(){ if(USER){ try{ localStorage.setItem(storeKey(USER.name), JSON.stringify(SP)); }catch(e){} } }

function addScore(n){ SP.score+=n; document.getElementById('scoreN').textContent=SP.score; save(); }

/* Streak-Logik: aufeinanderfolgende aktive Tage */
function touchStreak(){
  const t=todayKey();
  if(SP.lastActive===t){ return; }
  const y=new Date(); y.setDate(y.getDate()-1);
  const yKey=y.getFullYear()+'-'+String(y.getMonth()+1).padStart(2,'0')+'-'+String(y.getDate()).padStart(2,'0');
  if(SP.lastActive===yKey){ SP.streak=(SP.streak||0)+1; }
  else if(SP.lastActive===null){ SP.streak=1; }
  else { SP.streak=1; } // Lücke -> Reset auf 1
  SP.lastActive=t; save();
}

/* ====================================================================
   SPRACHAUSGABE (Web Speech API)
   ==================================================================== */
function speak(text, opts={}){
  if(!speakOn) return;
  if(!('speechSynthesis' in window)) return;
  try{
    const u=new SpeechSynthesisUtterance(text);
    u.lang='de-DE'; u.rate=opts.rate||1.0; u.pitch=opts.pitch||1.0; u.volume=1;
    // bevorzugt eine deutsche Stimme wählen
    const vs=speechSynthesis.getVoices();
    const de=vs.find(v=>/de[-_]/i.test(v.lang));
    if(de) u.voice=de;
    if(opts.flush!==false) speechSynthesis.cancel();
    speechSynthesis.speak(u);
  }catch(e){}
}
function speakQueue(text){ speak(text,{flush:false}); }
// Stimmen werden teils asynchron geladen
if('speechSynthesis' in window){ speechSynthesis.onvoiceschanged=()=>{}; }

/* ====================================================================
   LOGIN-FLOW
   ==================================================================== */
function doLogin(){
  const nameEl=document.getElementById('liName');
  const passEl=document.getElementById('liPass');
  const errEl=document.getElementById('loginErr');
  const name=nameEl.value, pass=passEl.value;
  // Sound spätestens jetzt (nach User-Interaktion -> iOS-sicher)
  tryPlayIntro();
  if(!checkLogin(name,pass)){
    errEl.textContent='Hoppla – Name oder Passwort stimmt nicht. Versuch es nochmal. 💪';
    errEl.classList.remove('show'); void errEl.offsetWidth; errEl.classList.add('show');
    passEl.value=''; passEl.focus();
    return;
  }
  const cleanName=name.trim();
  const displayName=cleanName.charAt(0).toUpperCase()+cleanName.slice(1).toLowerCase();
  USER={ name:cleanName.toLowerCase(), display:displayName };
  SP=loadProgress(USER.name);
  enterApp();
}

function tryPlayIntro(){
  const a=document.getElementById('introSound');
  if(!a) return;
  // Nur abspielen, wenn eine Quelle hinterlegt wurde (Platzhalter sonst leer)
  if(a.querySelector('source')){
    // Leise starten (30%), damit die Sprachbegrüßung klar hörbar bleibt
    a.volume=0.30;
    a.currentTime=0;
    a.play().catch(()=>{ /* Autoplay evtl. blockiert – kein Problem */ });
    // Nach ~18 s sanft ausblenden und stoppen (Intro soll nicht 4 Min laufen)
    scheduleIntroFade(a);
  }
}
// Konfiguration: Dauer bis zum Ausblenden + Ausblend-Länge
const INTRO_PLAY_MS = 18000; // wie lange die Musik spielt, bevor sie ausblendet
const INTRO_FADE_MS = 2500;  // Dauer des sanften Ausblendens
function scheduleIntroFade(a){
  if(a._fadeT) clearTimeout(a._fadeT);
  if(a._fadeI) clearInterval(a._fadeI);
  a._fadeT=setTimeout(()=>{
    const start=a.volume, steps=25, dt=INTRO_FADE_MS/steps;
    let i=0;
    a._fadeI=setInterval(()=>{
      i++;
      a.volume=Math.max(0, start*(1-i/steps));
      if(i>=steps){ clearInterval(a._fadeI); a.pause(); a.currentTime=0; a.volume=start; }
    }, dt);
  }, INTRO_PLAY_MS);
}
function stopIntro(){
  const a=document.getElementById('introSound');
  if(!a) return;
  if(a._fadeT) clearTimeout(a._fadeT);
  if(a._fadeI) clearInterval(a._fadeI);
  try{ a.pause(); a.currentTime=0; }catch(e){}
}

/* ====================================================================
   MISCH-ENGINE ("KI"): sorgt dafür, dass nie alles gleich ist.
   Wählt bei jedem Login zufällig aus Text-Pools und mischt Reihenfolgen.
   Das Easter Egg für Alen bleibt bewusst FEST.
   ==================================================================== */
function pick(arr){ return arr[Math.floor(Math.random()*arr.length)]; }

// Verschiedene Begrüßungen (Login) – {n} wird durch den Namen ersetzt
const GREETINGS = [
  'Hallo {n}, fangen wir an.',
  'Willkommen zurück, {n}. Auf geht\'s!',
  'Schön dass du da bist, {n}. Zeit zu lernen.',
  'Servus {n}, lass uns prüfungsreif werden.',
  'Na {n}, bereit für deine Bestform? Los geht\'s.',
  'Guten Tag {n}. Jeder Tag bringt dich näher ans Ziel.',
  'Hey {n}, dein Training wartet. Volle Konzentration!',
  'Moin {n}, heute machen wir dich ein Stück stärker.'
];
// Motivationssprüche für die Tagesansage
const MOTIV_LINES = [
  'Das musst du schaffen, um dein Ziel zu erreichen. Los geht\'s!',
  'Disziplin schlägt Talent. Bleib dran!',
  'Jede Frage heute ist ein Schritt zur bestandenen Prüfung.',
  'Konsequenz ist dein Vorteil. Zieh es durch!',
  'Wer heute übt, lacht in der Prüfung. Auf geht\'s!',
  'Kein Tag ohne Fortschritt. Du packst das!',
  'Dranbleiben zahlt sich aus. Zeig dir selbst, was du kannst!'
];
// Sprüche, wenn das Tagesziel schon erreicht ist
const DONE_LINES = [
  '{n}, du hast heute schon alles geschafft. Stark! Wiederhole jetzt deine Fehler, um es zu festigen.',
  'Top, {n}! Tagesziel erreicht. Eine Runde Fehler-Wiederholung macht dich noch sicherer.',
  '{n}, alles erledigt für heute. Klasse Disziplin! Festige es mit deinen Fehlerfragen.',
  'Geschafft, {n}! Heute schon prüfungsreif trainiert. Wiederholung hält es frisch.'
];
function fillName(t){ return t.replace('{n}', USER.display); }

function enterApp(){
  document.getElementById('login').style.display='none';
  const app=document.getElementById('app');
  app.style.display='block';
  app.classList.add('fade');
  touchStreak();
  ensureDaily();
  renderTopbar();
  navTo('home');
  // Begrüßung per Sprachausgabe – jedes Mal zufällig anders (Misch-Engine)
  setTimeout(()=>{
    speak(fillName(pick(GREETINGS)));
    // EASTER EGG für Alen (bleibt IMMER gleich)
    if(USER.name==='alen'){
      setTimeout(()=>{
        speakQueue('Ein guter Rat von einem guten Kollegen namens Eren: Leg meine Eier.');
        showToast('🥚 Ein guter Rat von Kollege Eren: „Leg meine Eier.“', 6000);
      }, 2600);
    }
  }, 450);
}

function logout(){
  save();
  try{ speechSynthesis.cancel(); }catch(e){}
  stopIntro();
  USER=null; SP=null;
  document.getElementById('app').style.display='none';
  document.getElementById('login').style.display='flex';
  document.getElementById('liPass').value='';
}

function renderTopbar(){
  document.getElementById('scoreN').textContent=SP.score;
  document.getElementById('streakN').textContent=SP.streak||0;
}

/* ====================================================================
   TAGESAUFGABEN
   ==================================================================== */
// einfacher seedbarer Zufall, damit jeder Tag/Bereich ein stabiles Set hat
function seededPick(arr, n, seedStr){
  let h=2166136261;
  for(let i=0;i<seedStr.length;i++){ h^=seedStr.charCodeAt(i); h=Math.imul(h,16777619); }
  const idx=arr.map((_,i)=>i);
  for(let i=idx.length-1;i>0;i--){ h=(Math.imul(h,48271))>>>0; const j=h%(i+1); [idx[i],idx[j]]=[idx[j],idx[i]]; }
  return idx.slice(0,Math.min(n,idx.length));
}
function areaQuestions(area){ return ALL_QA.map((q,i)=>({q,i})).filter(o=>area.tids.includes(o.q.tid)); }

function ensureDaily(){
  const t=todayKey();
  if(!SP.daily[t]){
    const day={ done:false };
    EXAM_AREAS.forEach(area=>{
      const pool=areaQuestions(area);
      const idxs=pool.length?seededPick(pool, QUESTIONS_PER_AREA, t+area.id):[];
      day[area.id]={ total:idxs.length, done:[], qids:idxs.map(k=>pool[k].i) };
    });
    SP.daily[t]=day;
    save();
  }
}
function dailyAreaProgress(areaId){
  const d=SP.daily[todayKey()][areaId];
  if(!d||!d.total) return {done:0,total:0,pct:0};
  return {done:d.done.length, total:d.total, pct:Math.round(d.done.length/d.total*100)};
}
function dailyOverallPct(){
  const d=SP.daily[todayKey()];
  let done=0,total=0;
  EXAM_AREAS.forEach(a=>{ done+=d[a.id].done.length; total+=d[a.id].total; });
  return total?Math.round(done/total*100):0;
}
function markDailyDone(areaId, qid){
  const d=SP.daily[todayKey()][areaId];
  if(d && !d.done.includes(qid)){ d.done.push(qid); save(); }
}

/* Gesamt-Prüfungsbereitschaft: Mischung aus gewussten Fragen + heutigem Pensum */
function readinessPct(){
  const knownPct = ALL_QA.length? SP.known.length/ALL_QA.length : 0;
  const dailyPct = dailyOverallPct()/100;
  const streakBoost = Math.min((SP.streak||0)/30, 1); // 30-Tage-Streak = voll
  const pct = (knownPct*0.6 + dailyPct*0.25 + streakBoost*0.15)*100;
  return Math.min(100, Math.round(pct));
}

/* ====================================================================
   NAVIGATION
   ==================================================================== */
let CUR='home';
function navTo(id){
  CUR=id;
  document.querySelectorAll('.page').forEach(p=>p.classList.remove('on'));
  document.getElementById('p-'+id).classList.add('on');
  document.querySelectorAll('.nav-i').forEach(b=>b.classList.toggle('active', b.dataset.nav===id));
  if(id==='home') renderHome();
  if(id==='cards') renderCardsPage();
  if(id==='quiz') renderQuizPage();
  if(id==='exam') renderExamPage();
  if(id==='tools') renderToolsPage();
  window.scrollTo({top:0,behavior:'instant'});
}

/* ====================================================================
   DASHBOARD
   ==================================================================== */
function ringSVG(pct,color,size=108){
  const r=(size/2)-9, c=2*Math.PI*r, off=c*(1-pct/100);
  return `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
    <circle cx="${size/2}" cy="${size/2}" r="${r}" stroke="#0e1320" stroke-width="9" fill="none"/>
    <circle cx="${size/2}" cy="${size/2}" r="${r}" stroke="${color}" stroke-width="9" fill="none"
      stroke-linecap="round" stroke-dasharray="${c}" stroke-dashoffset="${off}"
      style="transition:stroke-dashoffset 1s cubic-bezier(.2,.8,.3,1)"/>
  </svg>`;
}
function greetingWord(){ const h=new Date().getHours(); return h<11?'Guten Morgen':h<18?'Servus':'Guten Abend'; }

function renderHome(){
  const ready=readinessPct();
  const dpct=dailyOverallPct();
  const dateStr=new Date().toLocaleDateString('de-DE',{weekday:'long',day:'numeric',month:'long'});
  const knownN=SP.known.length, wrongN=SP.wrong.length;

  let html=`
  <div class="hero fade">
    <div class="hi">${greetingWord()},</div>
    <h1><b>${USER.display}</b> 👋</h1>
    <div class="date">${dateStr}</div>
    <div class="ring-row">
      <div class="ring">${ringSVG(ready, ready>=80?'#3ecf8e':ready>=40?'#f5a623':'#4f8ef7')}
        <div class="pct"><b>${ready}%</b><small>PRÜFUNGSBEREIT</small></div>
      </div>
      <div class="ring-info">
        <div class="big">${ready>=80?'Stark! Du bist auf Kurs. 🚀':ready>=40?'Solide – jetzt dranbleiben! 💪':'Lass uns loslegen. Jeder Tag zählt.'}</div>
        <div class="sub">Ziel: <b>100&nbsp;% prüfungsbereit</b>. Dein tägliches Pflichtprogramm bringt dich dorthin.</div>
      </div>
    </div>
  </div>

  <div class="statgrid fade2 stagger">
    <div class="stat gold"><b>🔥 ${SP.streak||0}</b><span>Tage Streak</span></div>
    <div class="stat green"><b>${knownN}</b><span>gewusst</span></div>
    <div class="stat red"><b>${wrongN}</b><span>zu üben</span></div>
  </div>

  <h2 class="sec">📌 Heutiges Pflichtprogramm · ${dpct}%</h2>
  <div class="card fade2" style="margin-bottom:14px">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px">
      <div style="flex:1">
        <div style="font-weight:700;font-size:15px">Tagesziel: 4 Prüfungsbereiche</div>
        <div style="font-size:12.5px;color:var(--mut);margin-top:3px">≈ 3–4 Stunden ernsthaftes Training · jeden Tag neu</div>
      </div>
      <div class="ring" style="width:64px;height:64px">${ringSVG(dpct, dpct>=100?'#3ecf8e':'#f5a623',64)}
        <div class="pct"><b style="font-size:15px">${dpct}%</b></div>
      </div>
    </div>
  </div>
  <div class="stagger">`;

  EXAM_AREAS.forEach(area=>{
    const p=dailyAreaProgress(area.id);
    const done=p.total && p.done>=p.total;
    html+=`
    <div class="task ${done?'done':''}" onclick="startDailyArea('${area.id}')">
      <div class="tk-ic" style="background:${area.color}22;color:${area.color}">${area.icon}</div>
      <div class="tk-main">
        <div class="t1">${area.name} <span style="color:var(--mut);font-weight:600;font-size:12px">· ${area.weight}</span></div>
        <div class="t2">${area.desc}</div>
        <div class="tk-bar"><i style="width:${p.pct}%;background:${area.color}"></i></div>
      </div>
      <div class="tk-pct">${p.done}/${p.total}</div>
      <div class="tk-check">✓</div>
    </div>`;
  });
  html+=`</div>`;

  html+=`
  <div class="bigbtn-row fade2">
    <div class="bigbtn" onclick="startWrongReview()">
      <div class="bb-ic">🔁</div><div class="bb-t">Fehler wiederholen</div>
      <div class="bb-s">${wrongN} Frage${wrongN===1?'':'n'} im Stapel</div>
    </div>
    <div class="bigbtn" onclick="navTo('quiz')">
      <div class="bb-ic">🎯</div><div class="bb-t">Schnell-Quiz</div>
      <div class="bb-s">20 zufällige Fragen</div>
    </div>
  </div>

  <h2 class="sec">📚 Themen (${ALL_TOPICS.length})</h2>
  <div class="topic-grid stagger" id="homeTopics"></div>

  <div style="text-align:center">
    <div class="speak-pill" onclick="announceToday()">🔊 Heutige Aufgaben vorlesen lassen</div>
  </div>

  <h2 class="sec">⚙️ Einstellungen · Zurücksetzen</h2>
  <div class="card" style="margin-bottom:14px">
    <div class="reset-row">
      <div class="reset-info">
        <div class="rr-t">Lernfortschritt zurücksetzen</div>
        <div class="rr-s">Aktuell ${readinessPct()}% · setzt gewusste Karten, Fehler-Stapel, Tagesaufgaben & Streak auf 0</div>
      </div>
      <button class="btn red" onclick="confirmResetProgress()">Reset</button>
    </div>
    <div class="reset-row" style="border-top:1px solid var(--line);margin-top:12px;padding-top:14px">
      <div class="reset-info">
        <div class="rr-t">Punkte zurücksetzen</div>
        <div class="rr-s">Aktuell ${SP.score} Punkte · setzt nur den Punktestand auf 0</div>
      </div>
      <button class="btn red" onclick="confirmResetPoints()">Reset</button>
    </div>
  </div>
  <div style="height:10px"></div>`;

  document.getElementById('p-home').innerHTML=html;

  // Themen-Kacheln
  const tg=document.getElementById('homeTopics');
  tg.innerHTML=ALL_TOPICS.map(tid=>{
    const m=gm(tid); const qs=ALL_QA.filter(q=>q.tid===tid);
    const kn=qs.filter(q=>SP.known.includes(qid(q))).length;
    const pct=qs.length?Math.round(kn/qs.length*100):0;
    return `<div class="topic-c" onclick="openTopicCards('${tid}')">
      <div class="tc-ic">${m.icon}</div>
      <div class="tc-n">${m.name}</div>
      <div class="tc-c">${qs.length} Fragen · ${pct}%</div>
      <div class="tc-bar"><i style="width:${pct}%"></i></div>
    </div>`;
  }).join('');

  // Tageskündigung beim ersten Betreten pro Sitzung
  if(!homeAnnounced){ homeAnnounced=true; setTimeout(announceToday, USER.name==='alen'?6200:3400); }
}

let homeAnnounced=false;
function announceToday(){
  const dpct=dailyOverallPct();
  if(dpct>=100){
    speak(fillName(pick(DONE_LINES)));
    return;
  }
  // Reihenfolge der offenen Bereiche zufällig mischen (Misch-Engine)
  const open=shuffle(EXAM_AREAS.filter(a=>{const p=dailyAreaProgress(a.id);return p.done<p.total;}).map(a=>a.name));
  let txt=`${USER.display}, heute stehen ${open.length} Bereiche an: ${open.join(', ')}. `;
  txt+=`Insgesamt ${EXAM_AREAS.length*QUESTIONS_PER_AREA} Pflichtfragen. `;
  txt+=pick(MOTIV_LINES);
  speak(txt);
}

function qid(q){ return q.tid+':'+q.q.slice(0,40); }
function qIndexOf(q){ return ALL_QA.indexOf(q); }

/* ====================================================================
   TAGES-DRILL (startet Karteikarten gefiltert auf Bereichs-Fragen)
   ==================================================================== */
let drill=null; // {areaId, qids:[], pos, mode:'daily'|'wrong'}
function startDailyArea(areaId){
  const area=EXAM_AREAS.find(a=>a.id===areaId);
  const d=SP.daily[todayKey()][areaId];
  if(!d.qids.length){ showToast('Keine Fragen in diesem Bereich.'); return; }
  drill={ areaId, qids:shuffle(d.qids.slice()), pos:0, mode:'daily', title:area.name, color:area.color };
  speak(`Bereich ${area.name}. ${area.desc}. Konzentration!`);
  navTo('cards'); renderDrillCard();
}
function startWrongReview(){
  if(!SP.wrong.length){ showToast('🎉 Keine Fehler im Stapel – top!'); return; }
  const qids=SP.wrong.map(w=>ALL_QA.findIndex(q=>qid(q)===w)).filter(i=>i>=0);
  drill={ areaId:null, qids, pos:0, mode:'wrong', title:'Fehler-Wiederholung', color:'#e8445a' };
  speak('Wiederholung deiner Fehler. Hier wirst du besser. Bleib dran!');
  navTo('cards'); renderDrillCard();
}

/* ====================================================================
   KARTEIKARTEN (Flip)
   ==================================================================== */
let cardState={ topic:'all', deck:[], pos:0 };

function renderCardsPage(){
  if(drill){ renderDrillCard(); return; }
  buildDeck(cardState.topic);
  const chips=['all',...ALL_TOPICS].map(t=>{
    const lbl=t==='all'?`Alle (${ALL_QA.length})`:gm(t).icon+' '+gm(t).name;
    return `<button class="chip ${cardState.topic===t?'active':''}" onclick="setCardTopic('${t}')">${lbl}</button>`;
  }).join('');
  // Statistik für die aktuelle Auswahl (Thema oder alle)
  const scope=cardState.topic==='all'?ALL_QA:ALL_QA.filter(q=>q.tid===cardState.topic);
  const total=scope.length;
  const correct=scope.filter(q=>SP.known.includes(qid(q))).length;
  const todo=total-correct;
  const pct=total?Math.round(correct/total*100):0;
  document.getElementById('p-cards').innerHTML=`
    <h2 class="sec" style="margin-top:6px">🗂️ Karteikarten</h2>
    <div class="cardstats">
      <div class="cs"><b>${total}</b><span>Karten gesamt</span></div>
      <div class="cs green"><b>${correct}</b><span>richtig</span></div>
      <div class="cs gold"><b>${todo}</b><span>zu üben</span></div>
    </div>
    <div class="cardstats-bar"><i style="width:${pct}%"></i></div>
    <div style="text-align:center;font-size:12px;color:var(--mut);margin:-8px 0 12px">Fortschritt: <b style="color:var(--green)">${pct}%</b> · Lernstand wird automatisch gespeichert</div>
    <div class="chips">${chips}</div>
    <div id="cardArea"></div>`;
  cardState.pos=0;
  renderCardFace();
}
function setCardTopic(t){ cardState.topic=t; drill=null; renderCardsPage(); }
function buildDeck(topic){
  let src=topic==='all'?ALL_QA.slice():ALL_QA.filter(q=>q.tid===topic);
  // Mischen mit gleichmäßiger Themenverteilung (bei "alle"), sonst einfach
  // mischen. So ist die Reihenfolge bei jedem Start neu und kein Thema
  // dominiert.
  src=topic==='all'?balancedByTopic(src):shuffle(src);
  // Dann Spaced Repetition light: Fehler zuerst, dann Neue, dann Gewusste.
  // Array.sort ist stabil -> die gemischte Verteilung bleibt je Stufe erhalten.
  src.sort((a,b)=>{
    const aw=SP.wrong.includes(qid(a))?0:SP.known.includes(qid(a))?2:1;
    const bw=SP.wrong.includes(qid(b))?0:SP.known.includes(qid(b))?2:1;
    return aw-bw;
  });
  cardState.deck=src;
}
function renderDrillCard(){
  const area=document.getElementById('p-cards');
  cardState.deck=drill.qids.map(i=>ALL_QA[i]);
  cardState.pos=drill.pos;
  area.innerHTML=`
    <h2 class="sec" style="margin-top:6px;color:${drill.color}">
      ${drill.mode==='wrong'?'🔁':'📌'} ${drill.title}</h2>
    <div id="cardArea"></div>
    <div style="text-align:center;margin-top:8px">
      <button class="btn ghost" onclick="exitDrill()">← Zurück zum Dashboard</button>
    </div>`;
  renderCardFace();
}
function exitDrill(){ drill=null; navTo('home'); }

function renderCardFace(){
  const deck=cardState.deck;
  const host=document.getElementById('cardArea');
  if(!deck.length){ host.innerHTML=`<div class="empty">Keine Karten vorhanden.</div>`; return; }
  if(cardState.pos>=deck.length) cardState.pos=0;
  const q=deck[cardState.pos]; const m=gm(q.tid);
  const isKnown=SP.known.includes(qid(q));
  host.innerHTML=`
    <div class="flashwrap">
      <div class="flash" id="flashEl" onclick="flipFlash()">
        <div class="face front">
          <span class="badge2" style="background:${m.color}22;color:${m.color}">${m.icon} ${m.name}</span>
          <div class="qtext">${q.q}</div>
          <div class="hint">👆 Tippen zum Umdrehen</div>
        </div>
        <div class="face back">
          <span class="badge2" style="background:rgba(62,207,142,.18);color:#7ce9b8">💡 Antwort</span>
          <div class="atext">${q.a}</div>
          <div class="hint">👆 Tippen zum Zurückdrehen</div>
        </div>
      </div>
    </div>
    <div class="card-ctl">
      <button class="btn red" onclick="rateCard(false)">✗ Nochmal üben</button>
      <button class="btn green" onclick="rateCard(true)">✓ Gewusst${isKnown?' ✓':''}</button>
    </div>
    <div class="card-nav">
      <button class="cn-btn" onclick="prevCard()">‹</button>
      <div class="prog">${cardState.pos+1} / ${deck.length}</div>
      <button class="cn-btn" onclick="nextCard()">›</button>
    </div>`;
}
function flipFlash(){ document.getElementById('flashEl').classList.toggle('flip'); }
function nextCard(){ cardState.pos=(cardState.pos+1)%cardState.deck.length; if(drill)drill.pos=cardState.pos; renderCardFace(); }
function prevCard(){ cardState.pos=(cardState.pos-1+cardState.deck.length)%cardState.deck.length; if(drill)drill.pos=cardState.pos; renderCardFace(); }
function rateCard(ok){
  const q=cardState.deck[cardState.pos]; const id=qid(q);
  if(ok){
    if(!SP.known.includes(id)){ SP.known.push(id); addScore(5); }
    SP.wrong=SP.wrong.filter(w=>w!==id);
    // Tagesfortschritt zählen, falls Karte zu heutigem Bereich gehört
    if(drill && drill.mode==='daily'){ markDailyDone(drill.areaId, qIndexOf(q)); }
  } else {
    if(!SP.wrong.includes(id)) SP.wrong.push(id);
    SP.known=SP.known.filter(k=>k!==id);
  }
  save();
  // Tagesabschluss prüfen
  if(drill && drill.mode==='daily'){ checkAreaComplete(drill.areaId); }
  // weiter
  if(cardState.pos>=cardState.deck.length-1){
    if(drill){ /* bleibt, Schleife */ }
  }
  setTimeout(nextCard,180);
  if(!drill){ renderTopbar(); }
}
function checkAreaComplete(areaId){
  const p=dailyAreaProgress(areaId);
  if(p.total && p.done>=p.total){
    const area=EXAM_AREAS.find(a=>a.id===areaId);
    addScore(20);
    showToast(`✅ ${area.name} erledigt! +20 Punkte`);
    speak(`Bereich ${area.name} geschafft. Sehr gut, ${USER.display}!`);
    if(dailyOverallPct()>=100){
      setTimeout(()=>{ showDayDoneModal(); },600);
    }
  }
}
function openTopicCards(tid){ drill=null; cardState.topic=tid; navTo('cards'); }

/* ====================================================================
   QUIZ (Multiple Choice)
   ==================================================================== */
let quiz=null;
function renderQuizPage(){
  if(quiz && !quiz.done){ renderQuizQ(); return; }
  const chips=['all',...ALL_TOPICS].map(t=>{
    const lbl=t==='all'?'🎲 Alle Themen':gm(t).icon+' '+gm(t).name;
    return `<button class="chip ${quizTopic===t?'active':''}" onclick="setQuizTopic('${t}')">${lbl}</button>`;
  }).join('');
  document.getElementById('p-quiz').innerHTML=`
    <h2 class="sec" style="margin-top:6px">🎯 Quiz</h2>
    <div class="chips">${chips}</div>
    <div class="card fade" style="text-align:center;padding:26px 18px">
      <div style="font-size:40px">🎯</div>
      <div style="font-weight:700;font-size:18px;margin-top:8px">Multiple-Choice-Quiz</div>
      <div style="color:var(--mut);font-size:14px;margin:8px 0 20px;line-height:1.5">
        ${QUIZ_LEN} Fragen, gemischt aus <b>${quizTopic==='all'?'allen Themen':gm(quizTopic).name}</b>.<br>Bestleistung: <b style="color:var(--gold)">${SP.quizBest||0}/${QUIZ_LEN}</b></div>
      <button class="btn primary" style="width:100%" onclick="startQuiz()">Quiz starten →</button>
    </div>`;
}
const QUIZ_LEN=20; // Fragen pro Quiz-Durchgang
let quizTopic='all';
function setQuizTopic(t){ quizTopic=t; renderQuizPage(); }
function startQuiz(){
  let src;
  if(quizTopic==='all'){
    // gemischt aus verschiedenen Themen + gleichmäßige Themenverteilung
    src=balancedByTopic(ALL_QA.slice());
  }else{
    src=shuffle(ALL_QA.filter(q=>q.tid===quizTopic));
  }
  quiz={ list:src.slice(0,QUIZ_LEN), pos:0, score:0, answered:false, done:false };
  navTo('quiz'); renderQuizQ();
}
function shuffle(a){ for(let i=a.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[a[i],a[j]]=[a[j],a[i]];} return a; }
/* Gleichmäßige Themenverteilung: pro Thema mischen, dann reihum (Round-Robin)
   einsammeln. So kommt kein Thema zu oft, keines zu selten – und die
   Reihenfolge ist trotzdem bei jedem Aufruf neu (Zufall je Gruppe). */
function balancedByTopic(list){
  const groups={};
  for(const q of list){ (groups[q.tid]=groups[q.tid]||[]).push(q); }
  const tids=shuffle(Object.keys(groups));
  for(const t of tids) shuffle(groups[t]);
  const out=[]; let added=true;
  while(added){
    added=false;
    for(const t of tids){ if(groups[t].length){ out.push(groups[t].shift()); added=true; } }
  }
  return out;
}
function shortAnswer(a){ // erste sinnvolle Phrase als MC-Option
  let s=a.replace(/\s+/g,' ').trim();
  // bis zum ersten starken Trenner
  const cut=s.search(/[.!?·•]| – |  /);
  if(cut>20 && cut<120) s=s.slice(0,cut);
  return s.length>110?s.slice(0,107)+'…':s;
}
function buildOptions(q){
  const correct=shortAnswer(q.a);
  let pool=ALL_QA.filter(x=>x.tid===q.tid && x.q!==q.q);
  if(pool.length<3) pool=pool.concat(ALL_QA.filter(x=>x.q!==q.q));
  shuffle(pool);
  const opts=[{t:correct,ok:true}];
  const seen=new Set([correct]);
  for(const x of pool){ const t=shortAnswer(x.a); if(!seen.has(t)){ seen.add(t); opts.push({t,ok:false}); } if(opts.length>=4) break; }
  return shuffle(opts);
}
function renderQuizQ(){
  if(quiz.done){ renderQuizResult(); return; }
  const q=quiz.list[quiz.pos]; const m=gm(q.tid);
  quiz.opts=quiz.opts||{};
  if(!quiz.opts[quiz.pos]) quiz.opts[quiz.pos]=buildOptions(q);
  const opts=quiz.opts[quiz.pos];
  const pct=Math.round(quiz.pos/quiz.list.length*100);
  document.getElementById('p-quiz').innerHTML=`
    <h2 class="sec" style="margin-top:6px">🎯 Quiz</h2>
    <div class="qbar"><i style="width:${pct}%"></i></div>
    <div class="qmeta"><span>Frage ${quiz.pos+1} / ${quiz.list.length}</span><span>⭐ ${quiz.score} richtig</span></div>
    <span class="badge2" style="background:${m.color}22;color:${m.color};display:inline-block;font-size:11px;font-weight:700;padding:5px 11px;border-radius:20px">${m.icon} ${m.name}</span>
    <div class="quiz-q">${q.q}</div>
    <div id="quizOpts">${opts.map((o,i)=>`<button class="opt" onclick="answerQuiz(${i})">${o.t}</button>`).join('')}</div>`;
}
function answerQuiz(i){
  if(quiz.answered) return;
  quiz.answered=true;
  const opts=quiz.opts[quiz.pos];
  const btns=document.querySelectorAll('#quizOpts .opt');
  const q=quiz.list[quiz.pos];
  btns.forEach((b,k)=>{
    if(opts[k].ok) b.classList.add('correct');
    else if(k===i) b.classList.add('wrong');
    else b.classList.add('dim');
    b.onclick=null;
  });
  if(opts[i].ok){
    quiz.score++; addScore(3);
    if(!SP.known.includes(qid(q))){ SP.known.push(qid(q)); }
    SP.wrong=SP.wrong.filter(w=>w!==qid(q));
  } else {
    if(!SP.wrong.includes(qid(q))) SP.wrong.push(qid(q));
  }
  save(); renderTopbar();
  const next=document.createElement('button');
  next.className='btn blue'; next.style.width='100%'; next.style.marginTop='8px';
  next.textContent=quiz.pos>=quiz.list.length-1?'Ergebnis ansehen →':'Weiter →';
  next.onclick=nextQuiz;
  document.getElementById('quizOpts').appendChild(next);
}
function nextQuiz(){
  quiz.answered=false; quiz.pos++;
  if(quiz.pos>=quiz.list.length){ quiz.done=true; renderQuizResult(); }
  else renderQuizQ();
}
function renderQuizResult(){
  const s=quiz.score, tot=quiz.list.length;
  if(s>(SP.quizBest||0)){ SP.quizBest=s; save(); }
  const pct=Math.round(s/tot*100);
  let emoji=pct>=90?'🏆':pct>=70?'🔥':pct>=50?'💪':'📚';
  let msg=pct>=90?'Prüfungsreif! Weltklasse.':pct>=70?'Stark – fast geschafft!':pct>=50?'Guter Anfang, dranbleiben!':'Nicht aufgeben – Wiederholung macht den Meister.';
  speak(`${s} von ${tot} richtig. ${msg}`);
  document.getElementById('p-quiz').innerHTML=`
    <div class="quiz-result fade">
      <div style="font-size:60px">${emoji}</div>
      <div class="big" style="color:${pct>=70?'#3ecf8e':pct>=50?'#f5a623':'#ff8194'}">${s}/${tot}</div>
      <div class="msg">${msg}</div>
      <button class="btn primary" style="width:100%;margin-bottom:10px" onclick="startQuiz()">Nochmal 🎯</button>
      <button class="btn ghost" style="width:100%" onclick="quiz=null;navTo('home')">Zum Dashboard</button>
    </div>`;
}

/* ====================================================================
   PRÜFUNGSFRAGEN-LISTE
   ==================================================================== */
let examTopic='all';
function renderExamPage(){
  const chips=['all',...ALL_TOPICS].map(t=>{
    const lbl=t==='all'?`Alle (${ALL_QA.length})`:gm(t).icon+' '+gm(t).name;
    return `<button class="chip ${examTopic===t?'active':''}" onclick="setExamTopic('${t}')">${lbl}</button>`;
  }).join('');
  let qs=examTopic==='all'?ALL_QA:ALL_QA.filter(q=>q.tid===examTopic);
  const list=qs.map((q,i)=>{
    const m=gm(q.tid);
    return `<div class="exam-i" id="exi${i}">
      <div class="eq" onclick="toggleExam(${i})">
        <span class="ei-ic">${m.icon}</span>
        <span class="ei-q">${q.q}</span>
        <span class="ei-ar">▸</span>
      </div>
      <div class="ea"><div class="ea-in">${q.a}</div></div>
    </div>`;
  }).join('');
  document.getElementById('p-exam').innerHTML=`
    <h2 class="sec" style="margin-top:6px">📋 Prüfungsfragen</h2>
    <div class="chips">${chips}</div>
    <div style="font-size:12.5px;color:var(--mut);margin-bottom:12px">${qs.length} Fragen · zum Aufklappen tippen</div>
    ${list}`;
}
function setExamTopic(t){ examTopic=t; renderExamPage(); }
function toggleExam(i){ document.getElementById('exi'+i).classList.toggle('open'); }

/* ====================================================================
   FORMELN + RECHNER
   ==================================================================== */
function renderToolsPage(){
  const fhtml=FORMELN.map(s=>`
    <div class="fs">
      <h3 class="fh">${s.s}</h3>
      ${s.items.map(it=>`<div class="frow">
        <div class="fn">${it.n} <span style="color:var(--mut);font-weight:600">${it.u?'['+it.u+']':''}</span></div>
        <div class="ff">${it.f}</div>
        ${it.note?`<div class="fnote">${it.note}</div>`:''}
      </div>`).join('')}
    </div>`).join('');
  const chtml=CALCS.map(c=>`
    <div class="ccard">
      <h3>${c.title}</h3>
      ${c.fields.map(f=>`<div class="crow"><label>${f.lbl}</label><input id="cr_${c.id}_${f.id}" type="number" step="any" inputmode="decimal" placeholder="0"></div>`).join('')}
      <button class="btn blue" style="width:100%;margin-top:4px" onclick="doCalc('${c.id}')">Berechnen</button>
      <div class="cres" id="cr_res_${c.id}">— Ergebnis —</div>
    </div>`).join('');
  document.getElementById('p-tools').innerHTML=`
    <h2 class="sec" style="margin-top:6px">📐 Formelsammlung</h2>
    ${fhtml}
    <h2 class="sec">🧮 Rechner</h2>
    ${chtml}
    <div style="height:8px"></div>`;
}
function doCalc(id){
  const calc=CALCS.find(c=>c.id===id);
  const v={};
  for(const f of calc.fields) v[f.id]=parseFloat(document.getElementById('cr_'+id+'_'+f.id).value)||0;
  try{ document.getElementById('cr_res_'+id).textContent=calc.fn(v); }
  catch(e){ document.getElementById('cr_res_'+id).textContent='Fehler – bitte alle Werte eingeben.'; }
}

/* ====================================================================
   MODALS / TOAST
   ==================================================================== */
function showToast(txt, ms=2800){
  const t=document.getElementById('toast'); t.innerHTML=txt; t.classList.add('show');
  clearTimeout(t._h); t._h=setTimeout(()=>t.classList.remove('show'), ms);
}
function openModal(html){ document.getElementById('modalBox').innerHTML=html; document.getElementById('modalBg').classList.add('on'); }
function closeModal(){ document.getElementById('modalBg').classList.remove('on'); }
document.getElementById('modalBg').addEventListener('click',e=>{ if(e.target.id==='modalBg') closeModal(); });

function askDailyDone(){
  const dpct=dailyOverallPct();
  if(dpct>=100){ return; }
  openModal(`
    <div class="m-ic">📅</div>
    <h3>Hast du heute schon trainiert?</h3>
    <p>Dein Pflichtprogramm ist zu <b>${dpct}%</b> erledigt. Bleib dran, ${USER.display} – Konsequenz schlägt Talent.</p>
    <div class="m-btns">
      <button class="btn ghost" onclick="closeModal()">Später</button>
      <button class="btn primary" onclick="closeModal();startFirstOpenArea()">Jetzt loslegen 🚀</button>
    </div>`);
}
function startFirstOpenArea(){
  const a=EXAM_AREAS.find(a=>{const p=dailyAreaProgress(a.id);return p.done<p.total;});
  if(a) startDailyArea(a.id);
}
function showDayDoneModal(){
  openModal(`
    <div class="m-ic">🏆</div>
    <h3>Tagesziel erreicht!</h3>
    <p>Du hast heute alle 4 Prüfungsbereiche geschafft, ${USER.display}. Streak: <b>🔥 ${SP.streak}</b>. So wird man prüfungsbereit!</p>
    <div class="m-btns">
      <button class="btn green" style="flex:1" onclick="closeModal();navTo('home')">Weiter so! 💪</button>
    </div>`);
  speak(`Tagesziel erreicht, ${USER.display}! Hervorragend. So erreichst du dein Ziel.`);
}

/* ====================================================================
   RESET-FUNKTIONEN (getrennt: Lernfortschritt vs. Punkte)
   Es werden NUR die Daten des aktuell eingeloggten Benutzers verändert
   (SP = sein Speicher-Objekt). Andere Benutzer/Keys bleiben unberührt.
   ==================================================================== */
function confirmResetProgress(){
  openModal(`
    <div class="m-ic">♻️</div>
    <h3>Lernfortschritt zurücksetzen?</h3>
    <p>Möchtest du deinen Lernfortschritt wirklich zurücksetzen? Gewusste Karten, der Fehler-Stapel, die Tagesaufgaben und dein Streak werden auf 0 gesetzt. Deine Punkte bleiben erhalten.</p>
    <div class="m-btns">
      <button class="btn ghost" onclick="closeModal()">Abbrechen</button>
      <button class="btn red" onclick="doResetProgress()">Ja, zurücksetzen</button>
    </div>`);
}
function doResetProgress(){
  // nur Fortschritts-Daten leeren – Punkte (SP.score) NICHT anfassen
  SP.known=[]; SP.wrong=[]; SP.daily={}; SP.studyMin={}; SP.streak=0; SP.lastActive=null;
  save();
  ensureDaily();      // heutiges Pensum frisch anlegen (alles offen -> 0%)
  renderTopbar();     // Streak bleibt 0, damit Fortschritt exakt 0% zeigt
  closeModal();
  navTo('home');
  showToast('♻️ Lernfortschritt zurückgesetzt – 0%');
  speak('Dein Lernfortschritt wurde zurückgesetzt. Neuer Start, volle Energie!');
}
function confirmResetPoints(){
  openModal(`
    <div class="m-ic">⭐</div>
    <h3>Punkte zurücksetzen?</h3>
    <p>Möchtest du deine Punkte wirklich zurücksetzen? Dein Punktestand wird auf 0 gesetzt. Dein Lernfortschritt bleibt erhalten.</p>
    <div class="m-btns">
      <button class="btn ghost" onclick="closeModal()">Abbrechen</button>
      <button class="btn red" onclick="doResetPoints()">Ja, zurücksetzen</button>
    </div>`);
}
function doResetPoints(){
  // nur den Punktestand leeren – Lernfortschritt bleibt
  SP.score=0; SP.quizBest=0;
  save();
  renderTopbar();
  closeModal();
  navTo('home');
  showToast('⭐ Punkte zurückgesetzt – 0');
  speak('Deine Punkte stehen wieder bei null. Auf ein Neues!');
}

/* ====================================================================
   EVENTS
   ==================================================================== */
document.getElementById('loginBtn').addEventListener('click', doLogin);
document.getElementById('liPass').addEventListener('keydown', e=>{ if(e.key==='Enter') doLogin(); });
document.getElementById('liName').addEventListener('keydown', e=>{ if(e.key==='Enter') document.getElementById('liPass').focus(); });
document.getElementById('logoutBtn').addEventListener('click', logout);
document.getElementById('speakBtn').addEventListener('click', ()=>{
  speakOn=!speakOn;
  document.getElementById('speakBtn').textContent=speakOn?'🔊':'🔇';
  if(!speakOn){ try{speechSynthesis.cancel();}catch(e){} stopIntro(); } else { speak('Sprachausgabe an.'); }
  showToast(speakOn?'🔊 Sprachausgabe an':'🔇 Ton aus');
});
document.querySelectorAll('.nav-i').forEach(b=>b.addEventListener('click',()=>{ if(b.dataset.nav!=='cards') drill=null; navTo(b.dataset.nav); }));

// Frage "Hast du heute schon trainiert?" kurz nach Login
const _enterApp=enterApp;
enterApp=function(){ _enterApp(); setTimeout(askDailyDone, USER&&USER.name==='alen'?7500:4200); };

// Autofokus Login
window.addEventListener('load', ()=>{ document.getElementById('liName').focus(); });
</script>
</body>
</html>
