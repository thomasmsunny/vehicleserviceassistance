<?php
require_once 'includes/db_config.php';

function getServices() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM services WHERE status = 'active' OR status = '1' ORDER BY servicename");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function getTableStructure() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DESCRIBE services");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Display table structure
echo "<h2>Services Table Structure</h2>";
$tableStructure = getTableStructure();

if (!empty($tableStructure)) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($tableStructure as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Could not retrieve table structure.</p>";
}

$services = getServices();

echo "<h2>Services from Database</h2>";

if (!empty($services)) {
    echo "<p>Found " . count($services) . " active services:</p>";
    echo "<ul>";
    foreach ($services as $service) {
        echo "<li><strong>" . htmlspecialchars($service['servicename']) . "</strong> (ID: " . $service['sid'] . ", Status: " . htmlspecialchars($service['status']) . ")</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No active services found in the database.</p>";
    echo "<p>You can add services through the admin panel:</p>";
    echo "<p><a href='../admin/servicesoffered1.php'>Add New Service</a></p>";
}
?>