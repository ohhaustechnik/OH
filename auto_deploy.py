# -*- coding: utf-8 -*-
"""
AUTO-DEPLOY OH-Haustechnik:  GitHub -> Live-FTP (nur diese Richtung!)

Aufruf (Windows-Aufgabenplaner ruft das alle paar Minuten):
    python auto_deploy.py

Was es macht:
  1. git fetch -> prueft, ob auf origin (Branch) NEUE Commits liegen.
  2. Fuer jede geaenderte Datei zwischen "zuletzt deployt" und "neu":
       - Konflikt-Check: wurde die LIVE-Datei unabhaengig geaendert?
         (Live == letzter-deployter-Stand?)  -> wenn NEIN: NICHT ueberschreiben,
         ueberspringen + Chef benachrichtigen (Regel #1: Live ist fuehrend).
       - sonst: Backup (lokal + Server .bak), BOM-Check, bei buero-lib.php PHP-Lint,
         dann hochladen.
  3. Nach Deploy: test_e003.py. Bei klarem Fehler -> Rollback aller Dateien.
  4. Nur GitHub -> FTP. Niemals FTP -> GitHub.

Eiserne Regeln befolgt: #1 Live fuehrend (Konflikt-Stopp), #2 Backup vor Aenderung,
#3 kein BOM (Git-Blob ist LF/ohne BOM, zusaetzlich geprueft), #5 Lint vor buero-lib.php,
#6 Test nach Deploy.
"""
import sys, os, io, json, time, ftplib, subprocess, urllib.request
from datetime import datetime
from pathlib import Path

# Windows-Konsole (cp1252) vertraegt keine Emojis -> auf UTF-8 umstellen, sonst Crash
try:
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")
except Exception:
    pass

# ---------------------------------------------------------------- Konfiguration
BASIS    = Path(__file__).parent                       # ...\OH T\system
REPO     = BASIS.parent / "OH"                         # ...\OH T\OH  (Git-Repo)
BRANCH   = "claude/gallant-brahmagupta-61u7q5"
WURZEL   = "oh-haustechnik.de"                         # FTP-Webroot
BASIS_URL= "https://oh-haustechnik.de"
ENV      = BASIS / "schleife" / ".env"
STATE    = BASIS / "auto_deploy_state.json"
LOGDATEI = BASIS / "auto_deploy.log"
LOCK     = BASIS / "auto_deploy.lock"
BACKUPDIR= BASIS / "auto_backups"
TESTSKRIPT = BASIS / "test_e003.py"

# Dateien, die NIE deployt werden (Secrets/Kundendaten/Docs/lokales Zeug)
NIE_DEPLOYEN_PRAEFIX = ("daten/", "app-react/src/", "app-react/node_modules/")
NIE_DEPLOYEN_ENDET   = (".bak", ".zip", ".md", ".log", ".gitignore")
NIE_DEPLOYEN_DATEI   = {"termine.json", "umbau.json", "chat.json", "auto_deploy.py"}

TEXT_ENDUNGEN = {".php", ".js", ".css", ".html", ".htm", ".json", ".xml",
                 ".txt", ".svg", ".csv", ".webmanifest"}

conf = {}
for z in ENV.read_text().splitlines():
    if "=" in z:
        k, v = z.split("=", 1); conf[k.strip()] = v.strip()

# ---------------------------------------------------------------- kleine Helfer
def log(msg):
    z = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    zeile = f"[{z}] {msg}"
    print(zeile)
    with open(LOGDATEI, "a", encoding="utf-8") as f:
        f.write(zeile + "\n")

def git(*args):
    # -c core.quotepath=false: Umlaut-/Sonderzeichen-Pfade roh (UTF-8) statt "N\303\274rnberg"
    # liefern, sonst werden solche Dateien nicht gefunden und uebersprungen.
    # surrogateescape: exakte Bytes erhalten, nichts geht beim Dekodieren verloren.
    r = subprocess.run(["git", "-c", "core.quotepath=false", "-C", str(REPO), *args],
                       capture_output=True, text=True, encoding="utf-8", errors="surrogateescape")
    return r.returncode, (r.stdout or "").strip(), (r.stderr or "").strip()

def git_blob(rev, pfad):
    """Roh-Bytes einer Datei aus einem Commit (Repo speichert LF, kein BOM)."""
    # -c core.quotepath=false auch hier, damit der Pfad mit Umlauten als Pathspec passt.
    r = subprocess.run(["git", "-c", "core.quotepath=false", "-C", str(REPO), "show", f"{rev}:{pfad}"],
                       capture_output=True)
    return r.stdout if r.returncode == 0 else None

def ist_text(pfad):
    name = pfad.rsplit("/", 1)[-1]
    if name == ".htaccess":
        return True
    return any(name.endswith(e) for e in TEXT_ENDUNGEN)

def norm(b, text):
    """Vergleichs-Normalisierung: BOM weg, CRLF->LF (nur Text)."""
    if b is None:
        return None
    if text:
        if b.startswith(b"\xef\xbb\xbf"):
            b = b[3:]
        b = b.replace(b"\r\n", b"\n").replace(b"\r", b"\n")
    return b

def deploy_bytes(b, text):
    """Bytes, die wirklich hochgeladen werden: Text -> LF, ohne BOM."""
    if text:
        if b.startswith(b"\xef\xbb\xbf"):
            b = b[3:]
        b = b.replace(b"\r\n", b"\n").replace(b"\r", b"\n")
    return b

def deploybar(pfad):
    if pfad in NIE_DEPLOYEN_DATEI:
        return False
    if any(pfad.startswith(p) for p in NIE_DEPLOYEN_PRAEFIX):
        return False
    if any(pfad.endswith(e) for e in NIE_DEPLOYEN_ENDET):
        return False
    return True

# ---------------------------------------------------------------- FTP
def ftp_verbinden():
    f = ftplib.FTP(conf["FTP_HOST"], timeout=60)
    f.login(conf["FTP_USER"], conf["FTP_PASS"])
    f.encoding = "utf-8"
    return f

def ftp_get(f, pfad):
    """Live-Datei holen -> Bytes oder None wenn nicht vorhanden."""
    buf = io.BytesIO()
    try:
        f.retrbinary(f"RETR /{WURZEL}/{pfad}", buf.write)
        return buf.getvalue()
    except ftplib.error_perm:
        return None

def ftp_put(f, pfad, daten):
    teile = pfad.split("/")[:-1]
    p = f"/{WURZEL}"
    for t in teile:
        p += f"/{t}"
        try: f.mkd(p)
        except ftplib.error_perm: pass
    f.storbinary(f"STOR /{WURZEL}/{pfad}", io.BytesIO(daten))

def ftp_del(f, pfad):
    try: f.delete(f"/{WURZEL}/{pfad}")
    except ftplib.error_perm: pass

def chef_melden(text):
    """Kurze Nachricht in den Handy-Befehls-Kanal (daten/chat.json) schreiben."""
    try:
        f = ftp_verbinden()
        try:
            roh = ftp_get(f, "daten/chat.json")
            chat = json.loads(roh.decode("utf-8")) if roh else []
            if not isinstance(chat, list): chat = []
            chat.append({"id": "c" + str(int(time.time())), "rolle": "claude",
                         "text": text, "ts": int(time.time()), "erledigt": True})
            ftp_put(f, "daten/chat.json",
                    json.dumps(chat, ensure_ascii=False, indent=1).encode("utf-8"))
        finally:
            f.quit()
    except Exception as e:
        log(f"  (Konnte Chef-Kanal nicht erreichen: {e})")

# ---------------------------------------------------------------- PHP-Lint
def php_lint(f, daten):
    """buero-lib.php-Bytes auf Fatal-/Parse-Error pruefen, bevor sie live gehen.
       Temp-Kopie + _lint.php hochladen, ueber HTTP 'LINT-OK' pruefen, Temp loeschen."""
    lint_lib = "includes/_lint_lib.php"
    lint_run = "_lint_oh.php"
    wrapper = (b"<?php error_reporting(E_ALL); ini_set('display_errors','1');\n"
               b"require __DIR__.'/includes/_lint_lib.php';\n"
               b"echo 'LINT-OK';\n")
    try:
        ftp_put(f, lint_lib, daten)
        ftp_put(f, lint_run, wrapper)
        time.sleep(1)
        try:
            with urllib.request.urlopen(f"{BASIS_URL}/{lint_run}", timeout=30) as r:
                out = r.read().decode("utf-8", "replace")
        except Exception as e:
            out = f"(HTTP-Fehler: {e})"
        ok = out.strip().endswith("LINT-OK") and "error" not in out.lower().replace("lint-ok","")
        return ok, out[-400:]
    finally:
        ftp_del(f, lint_lib)
        ftp_del(f, lint_run)

# ---------------------------------------------------------------- Test
def test_laufen():
    """Gibt zurueck: 'OK', 'FAIL' (klarer Fehler) oder 'UNKLAR' (nicht auswertbar)."""
    if not TESTSKRIPT.exists():
        return "UNKLAR", "test_e003.py fehlt"
    r = subprocess.run([sys.executable, str(TESTSKRIPT)],
                       capture_output=True, text=True, encoding="utf-8",
                       errors="replace", cwd=str(BASIS), timeout=120)
    out = (r.stdout or "") + (r.stderr or "")
    if "PHPSESSID: True" in out and "Dashboard liefert JSON: True" in out:
        if "JS-Fehler: []" in out:
            return "OK", out.strip()
        return "FAIL", out.strip()          # JS-Fehler vorhanden
    if "PHPSESSID: False" in out or "Dashboard liefert JSON: False" in out:
        return "FAIL", out.strip()
    return "UNKLAR", out.strip()            # Playwright-Fehler o.ae.

# ---------------------------------------------------------------- State / Lock
def state_lesen(default_sha):
    if STATE.exists():
        try:
            return json.loads(STATE.read_text(encoding="utf-8")).get("last_deployed_sha", default_sha)
        except Exception:
            pass
    return default_sha

def state_schreiben(sha):
    STATE.write_text(json.dumps({"last_deployed_sha": sha,
                                 "ts": datetime.now().isoformat()}, indent=1),
                     encoding="utf-8")

def lock_holen():
    if LOCK.exists():
        try:
            alter = time.time() - LOCK.stat().st_mtime
            if alter < 600:                 # <10 min: laeuft vermutlich noch
                return False
        except Exception:
            pass
    LOCK.write_text(str(int(time.time())))
    return True

def lock_frei():
    try: LOCK.unlink()
    except Exception: pass

# ---------------------------------------------------------------- Hauptlauf
def main():
    if not lock_holen():
        log("Lock aktiv – vorheriger Lauf noch nicht fertig, ueberspringe.")
        return
    try:
        # aktuellen lokalen HEAD als Start-Baseline (falls noch kein State)
        rc, head, _ = git("rev-parse", "HEAD")
        if rc != 0:
            log(f"FEHLER: kein Git-Repo unter {REPO}"); return
        last = state_lesen(head)

        rc, _, err = git("fetch", "origin", BRANCH)
        if rc != 0:
            log(f"git fetch fehlgeschlagen: {err}"); return

        rc, neu, _ = git("rev-parse", f"origin/{BRANCH}")
        if rc != 0:
            log(f"origin/{BRANCH} nicht gefunden"); return

        if neu == last:
            log("Nichts Neues auf GitHub.")
            return

        log(f"NEUE Commits: {last[:8]} -> {neu[:8]}")
        rc, namestatus, _ = git("diff", "--name-status", last, neu)
        eintraege = []
        for zeile in namestatus.splitlines():
            teile = zeile.split("\t")
            status = teile[0][0]            # A/M/D/R
            pfad = teile[-1]                # bei R: neuer Pfad
            eintraege.append((status, pfad))

        f = ftp_verbinden()
        deployt = []          # (pfad, backup_local_path_or_None)
        konflikte = []        # pfade
        geloescht_info = []   # geloeschte im Git (nur Hinweis)
        lint_fail = []
        try:
            for status, pfad in eintraege:
                if status == "D":
                    geloescht_info.append(pfad)
                    continue
                if not deploybar(pfad):
                    log(f"  uebersprungen (nicht deploybar): {pfad}")
                    continue

                text   = ist_text(pfad)
                neu_b  = git_blob(neu, pfad)
                if neu_b is None:
                    log(f"  WARN: {pfad} nicht im neuen Commit lesbar – uebersprungen")
                    continue
                alt_b  = git_blob(last, pfad)         # None wenn neue Datei
                live_b = ftp_get(f, pfad)

                # ---- Konflikt-Check (Regel #1) ----
                if live_b is not None:
                    basis = alt_b if alt_b is not None else neu_b
                    if norm(live_b, text) != norm(basis, text):
                        # Live wurde unabhaengig geaendert -> NICHT ueberschreiben
                        if norm(live_b, text) == norm(neu_b, text):
                            log(f"  OK: {pfad} – Live ist bereits auf neuem Stand")
                            continue
                        konflikte.append(pfad)
                        log(f"  KONFLIKT: {pfad} – Live weicht ab, NICHT ueberschrieben")
                        continue

                hoch = deploy_bytes(neu_b, text)
                if text and hoch.startswith(b"\xef\xbb\xbf"):
                    log(f"  FEHLER: {pfad} haette BOM – uebersprungen"); continue

                # ---- Backup (lokal + Server .bak) ----
                bkp_local = None
                if live_b is not None:
                    ts = datetime.now().strftime("%Y-%m-%d_%H%M%S")
                    bdir = BACKUPDIR / ts
                    bkp_local = bdir / pfad
                    bkp_local.parent.mkdir(parents=True, exist_ok=True)
                    bkp_local.write_bytes(live_b)
                    ftp_put(f, f"{pfad}.{ts}.bak", live_b)

                # ---- PHP-Lint nur fuer die Money-Lib ----
                if pfad == "includes/buero-lib.php":
                    ok, ausgabe = php_lint(f, hoch)
                    if not ok:
                        lint_fail.append(pfad)
                        log(f"  LINT-FEHLER buero-lib.php – NICHT deployt. {ausgabe[-200:]}")
                        continue
                    log("  Lint OK: buero-lib.php")

                # ---- Hochladen ----
                ftp_put(f, pfad, hoch)
                deployt.append((pfad, bkp_local, live_b))
                log(f"  deployt: {pfad} ({len(hoch)} B)")

            # ---- Test nach Deploy ----
            test_status = "—"
            if deployt:
                test_status, testout = test_laufen()
                log(f"  Test: {test_status}")
                if test_status == "FAIL":
                    log("  TEST FEHLGESCHLAGEN -> ROLLBACK")
                    for pfad, bkp_local, live_b in deployt:
                        if live_b is not None:
                            ftp_put(f, pfad, live_b)
                            log(f"    zurueckgerollt: {pfad}")
                    chef_melden("⚠️ Auto-Deploy GESTOPPT: Test nach Upload fehlgeschlagen, "
                                f"alles zurueckgerollt. Commit {neu[:8]} NICHT live. Bitte pruefen.")
                    return     # State NICHT vorruecken -> naechster Lauf versucht erneut
        finally:
            f.quit()

        # ---- State vorruecken + lokalen Branch nachziehen ----
        state_schreiben(neu)
        git("merge", "--ff-only", f"origin/{BRANCH}")

        # ---- Chef benachrichtigen (nur bei echtem Ereignis) ----
        teile = []
        if deployt:   teile.append(f"✅ {len(deployt)} Datei(en) live: " +
                                   ", ".join(p for p,_,_ in deployt))
        if konflikte: teile.append("⛔ KONFLIKT (Live behalten, NICHT ueberschrieben): " +
                                   ", ".join(konflikte))
        if lint_fail: teile.append("⚠️ Lint-Fehler (nicht deployt): " + ", ".join(lint_fail))
        if geloescht_info: teile.append("🗑️ Auf GitHub geloescht (Live unberuehrt gelassen): " +
                                        ", ".join(geloescht_info))
        if deployt and test_status not in ("OK", "—"):
            teile.append(f"Test-Status: {test_status} – bitte kurz draufschauen.")
        if teile:
            chef_melden("Auto-Deploy ({}): ".format(neu[:8]) + " | ".join(teile))
        log("Lauf fertig. " + (" | ".join(teile) if teile else "keine Aenderung deployt"))

    except Exception as e:
        log(f"AUSNAHME: {e!r}")
    finally:
        lock_frei()

if __name__ == "__main__":
    main()
