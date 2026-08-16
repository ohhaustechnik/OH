<?php
/* Dauerhafte Weiterleitung: Die Seite hiess frueher "E-Check & DGUV V3".
   DGUV-V3-Pruefungen werden nicht angeboten, der Inhalt liegt jetzt unter
   e-check-nuernberg.php.

   Warum als PHP-Datei und nicht nur per .htaccess: Die RewriteRule greift auf
   diesem Server nur, solange die Datei existiert - bei geloeschter Datei
   liefert der Hoster einen 404, bevor mod_rewrite zum Zug kommt. Diese Datei
   stellt die Weiterleitung unabhaengig davon sicher. */
header('HTTP/1.1 301 Moved Permanently');
header('Location: https://oh-haustechnik.de/e-check-nuernberg.php', true, 301);
exit;
