<?php
/* Geteilter Header im neuen Dark-Design (gleiche Optik wie index.php).
   Einbindung: include __DIR__.'/header-dark.php';
   Optional vorher setzen: $oh_active = 'leistungen'|'ueber-uns'|'kontakt'; */
$oh_active = $oh_active ?? '';
function oh_nav_on($key, $active) { return $key === $active ? ' class="on"' : ''; }
?>
<header id="hd">
  <div class="wrap nav">
    <a href="/index.php"><img class="logo" src="/assets/img/logohaustechnikneu.png" alt="OH Haustechnik"></a>
    <nav class="links" aria-label="Hauptnavigation">
      <a href="/leistungen.php"<?= oh_nav_on('leistungen', $oh_active) ?>>Leistungen</a>
      <a href="/index.php#altbau">Altbau</a>
      <a href="/index.php#referenzen">Referenzen</a>
      <a href="/ueber-uns.php"<?= oh_nav_on('ueber-uns', $oh_active) ?>>Über uns</a>
      <a href="/kontakt.php"<?= oh_nav_on('kontakt', $oh_active) ?>>Kontakt</a>
    </nav>
    <a class="btn btn-y call" href="tel:+491757481006">Anrufen</a>
  </div>
</header>
