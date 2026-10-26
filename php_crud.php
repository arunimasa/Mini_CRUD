<?php
// ====== 1. DATABASE CONNECTION (edit these) ======
$host   = "localhost";
$dbname = "patients";   // <-- change to your database name
$user   = "root";                 // default XAMPP/phpMyAdmin user
$pass   = "";                     // default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<p style='color:red;font-family:sans-serif'>Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// ====== 2. QUERIES (change table names if yours differ) ======
$queries = [
    "INNER JOIN" => [
        "desc" => "Only rows where patient_id exists in BOTH tables.",
        "sql"  => "SELECT s.site_id, s.site_name, s.location, p.patient_id, p.name, p.gender, p.medical_history
                   FROM sites s
                   INNER JOIN patients p ON s.patient_id = p.patient_id
                   ORDER BY s.site_id",
    ],
    "LEFT OUTER JOIN" => [
        "desc" => "ALL sites, plus matching patient data (NULL if the site has no matching patient).",
        "sql"  => "SELECT s.site_id, s.site_name, s.location, p.patient_id, p.name, p.gender, p.medical_history
                   FROM sites s
                   LEFT JOIN patients p ON s.patient_id = p.patient_id
                   ORDER BY s.site_id",
    ],
    "RIGHT OUTER JOIN" => [
        "desc" => "ALL patients, plus matching site data (NULL if the patient has no site).",
        "sql"  => "SELECT s.site_id, s.site_name, s.location, p.patient_id, p.name, p.gender, p.medical_history
                   FROM sites s
                   RIGHT JOIN patients p ON s.patient_id = p.patient_id
                   ORDER BY p.patient_id",
    ],
];

// Run each query and store results
$results = [];
foreach ($queries as $title => $q) {
    try {
        $results[$title] = $pdo->query($q["sql"])->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $results[$title] = "Query error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SQL JOIN Demo</title>
    <style>
        body { font-family: Segoe UI, Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 30px; color: #222; }
        h1 { text-align: center; margin-bottom: 30px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,.1); padding: 20px; margin: 0 auto 30px; max-width: 1000px; }
        .card h2 { margin: 0 0 5px; color: #2b6cb0; }
        .desc { color: #555; margin: 0 0 10px; }
        pre { background: #1e1e1e; color: #9cdcfe; padding: 12px; border-radius: 6px; overflow-x: auto; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #2b6cb0; color: #fff; text-align: left; padding: 8px; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f7fafc; }
        .null { color: #c53030; font-style: italic; background: #fff5f5 !important; }
        .count { font-size: 13px; color: #777; margin-top: 8px; }
        .error { color: #c53030; }
    </style>
</head>
<body>

<h1>SQL JOIN Demo: Sites &amp; Patients</h1>

<?php foreach ($queries as $title => $q): ?>
    <div class="card">
        <h2><?= htmlspecialchars($title) ?></h2>
        <p class="desc"><?= htmlspecialchars($q["desc"]) ?></p>
        <pre><?= htmlspecialchars(preg_replace('/\s+/', ' ', trim($q["sql"]))) ?></pre>

        <?php $rows = $results[$title]; ?>
        <?php if (is_string($rows)): ?>
            <p class="error"><?= htmlspecialchars($rows) ?></p>
        <?php elseif (empty($rows)): ?>
            <p>No rows returned.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <?php foreach (array_keys($rows[0]) as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($row as $val): ?>
                                <?php if ($val === null): ?>
                                    <td class="null">NULL</td>
                                <?php else: ?>
                                    <td><?= htmlspecialchars($val) ?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="count"><?= count($rows) ?> row(s) returned</p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

</body>
</html>