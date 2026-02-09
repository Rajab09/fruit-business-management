<?php
$pageTitle = 'Inventory Log';
$activePage = 'inventory';
require_once __DIR__ . '/../includes/db.php';

$logs = $conn->query("
    SELECT il.*, f.Fruit_Name, e.Employee_Name
    FROM inventory_log il
    JOIN fruit f ON il.Fruit_ID = f.Fruit_ID
    JOIN employee e ON il.Employee_ID = e.Employee_ID
    ORDER BY il.Transaction_Date DESC
    LIMIT 100
");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="table-wrap">
    <table>
        <thead><tr><th>Log ID</th><th>Fruit</th><th>Employee</th><th>Type</th><th>Quantity</th><th>Date</th></tr></thead>
        <tbody>
        <?php if ($logs && $logs->num_rows > 0): ?>
            <?php while ($r = $logs->fetch_assoc()): ?>
            <tr>
                <td><?= $r['Log_ID'] ?></td>
                <td><?= htmlspecialchars($r['Fruit_Name']) ?></td>
                <td><?= htmlspecialchars($r['Employee_Name']) ?></td>
                <td><span class="badge <?= $r['Transaction_Type'] === 'ADD' ? 'badge-success' : 'badge-danger' ?>"><?= $r['Transaction_Type'] ?></span></td>
                <td><?= $r['Quantity'] ?></td>
                <td><?= $r['Transaction_Date'] ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;color:#999;">No inventory transactions yet</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
