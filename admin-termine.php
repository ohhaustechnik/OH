<?php
$secretKey = "OH2026";
$file = __DIR__ . "/termine.json";

if (!isset($_GET["key"]) || $_GET["key"] !== $secretKey) {
    die("Kein Zugriff");
}

if (!file_exists($file)) {
    file_put_contents($file, "[]");
}

$termine = json_decode(file_get_contents($file), true);
if (!is_array($termine)) {
    $termine = [];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["add"])) {
        $termine[] = [
            "datum" => $_POST["datum"],
            "uhrzeit" => $_POST["uhrzeit"],
            "status" => "frei"
        ];
    }

    if (isset($_POST["delete"])) {
        unset($termine[$_POST["index"]]);
        $termine = array_values($termine);
    }

    if (isset($_POST["toggle"])) {
        $i = $_POST["index"];
        $termine[$i]["status"] = $termine[$i]["status"] === "frei" ? "gebucht" : "frei";
    }

    file_put_contents($file, json_encode($termine, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: admin-termine.php?key=" . $secretKey);
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OH Termine verwalten</title>
<style>
body{
    font-family: Arial, sans-serif;
    background:#eef5ff;
    padding:20px;
    color:#061324;
}
.box{
    max-width:700px;
    margin:0 auto;
    background:white;
    padding:24px;
    border-radius:20px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
}
h1{margin-bottom:20px;}
form{margin:0;}
.add-form{
    display:grid;
    gap:10px;
    margin-bottom:25px;
}
input,button{
    padding:14px;
    border-radius:12px;
    border:1px solid #d7e0ee;
    font-size:16px;
}
button{
    background:#2563eb;
    color:white;
    border:none;
    font-weight:700;
}
table{
    width:100%;
    border-collapse:collapse;
}
td,th{
    padding:12px;
    border-bottom:1px solid #e5eaf2;
    text-align:left;
}
.badge{
    padding:6px 10px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
}
.frei{background:#dcfce7;color:#166534;}
.gebucht{background:#fee2e2;color:#991b1b;}
.actions{
    display:flex;
    gap:6px;
    flex-wrap:wrap;
}
.actions button{
    padding:8px 10px;
    font-size:13px;
}
.delete{
    background:#dc2626;
}
.toggle{
    background:#0f172a;
}
.small{
    color:#64748b;
    font-size:14px;
    margin-bottom:20px;
}
</style>
</head>
<body>

<div class="box">
    <h1>Termine verwalten</h1>
    <p class="small">Nur Termine mit Status „frei“ und in der Zukunft werden später im Kalkulator angezeigt.</p>

    <form method="post" class="add-form">
        <input type="date" name="datum" required>
        <input type="time" name="uhrzeit" required>
        <button type="submit" name="add">+ Termin hinzufügen</button>
    </form>

    <table>
        <tr>
            <th>Datum</th>
            <th>Uhrzeit</th>
            <th>Status</th>
            <th>Aktion</th>
        </tr>

        <?php foreach ($termine as $index => $termin): ?>
        <tr>
            <td><?= htmlspecialchars($termin["datum"]) ?></td>
            <td><?= htmlspecialchars($termin["uhrzeit"]) ?></td>
            <td>
                <span class="badge <?= htmlspecialchars($termin["status"]) ?>">
                    <?= htmlspecialchars($termin["status"]) ?>
                </span>
            </td>
            <td>
                <div class="actions">
                    <form method="post">
                        <input type="hidden" name="index" value="<?= $index ?>">
                        <button class="toggle" type="submit" name="toggle">
                            Status ändern
                        </button>
                    </form>

                    <form method="post">
                        <input type="hidden" name="index" value="<?= $index ?>">
                        <button class="delete" type="submit" name="delete">
                            Löschen
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>