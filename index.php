<?php
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

$totalFruits    = $conn->query("SELECT COUNT(*) AS c FROM fruit")->fetch_assoc()['c'];
$totalEmployees = $conn->query("SELECT COUNT(*) AS c FROM employee")->fetch_assoc()['c'];
$totalSales     = $conn->query("SELECT COUNT(*) AS c FROM sale")->fetch_assoc()['c'];
$totalSuppliers = $conn->query("SELECT COUNT(*) AS c FROM supplier")->fetch_assoc()['c'];

$lowStock = $conn->query("SELECT f.Fruit_Name, f.Stock_Quantity, f.Minimum_Threshold FROM fruit f WHERE f.Stock_Quantity <= f.Minimum_Threshold ORDER BY f.Stock_Quantity ASC LIMIT 10");

$recentSales = $conn->query("
    SELECT s.Sale_ID, s.Sale_Date_Time, s.Payment_Status, e.Employee_Name,
           COALESCE(SUM(si.Sub_Total),0) AS Total
    FROM sale s
    JOIN employee e ON s.Employee_ID = e.Employee_ID
    LEFT JOIN sale_item si ON s.Sale_ID = si.Sale_ID
    GROUP BY s.Sale_ID
    ORDER BY s.Sale_Date_Time DESC LIMIT 10
");
?>

<div class="stats">
    <div class="stat-card">
        <div class="label">Total Fruits</div>
        <div class="value"><?= $totalFruits ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Employees</div>
        <div class="value"><?= $totalEmployees ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Total Sales</div>
        <div class="value"><?= $totalSales ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Suppliers</div>
        <div class="value"><?= $totalSuppliers ?></div>
    </div>
</div>

<h2 style="margin-bottom:.75rem;font-size:1.1rem;">Low Stock Alerts</h2>
<div class="table-wrap">
    <table>
        <thead><tr><th>Fruit</th><th>Stock</th><th>Threshold</th><th>Status</th></tr></thead>
        <tbody>
        <?php if ($lowStock && $lowStock->num_rows > 0): ?>
            <?php while ($r = $lowStock->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['Fruit_Name']) ?></td>
                <td><?= $r['Stock_Quantity'] ?></td>
                <td><?= $r['Minimum_Threshold'] ?></td>
                <td><span class="badge badge-danger">Low</span></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" style="text-align:center;color:#999;">No low stock items</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<h2 style="margin-bottom:.75rem;font-size:1.1rem;">Recent Sales</h2>
<div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Date</th><th>Employee</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php if ($recentSales && $recentSales->num_rows > 0): ?>
            <?php while ($r = $recentSales->fetch_assoc()): ?>
            <tr>
                <td>#<?= $r['Sale_ID'] ?></td>
                <td><?= $r['Sale_Date_Time'] ?></td>
                <td><?= htmlspecialchars($r['Employee_Name']) ?></td>
                <td>$<?= number_format($r['Total'], 2) ?></td>
                <td><span class="badge <?= $r['Payment_Status'] === 'Paid' ? 'badge-success' : 'badge-warning' ?>"><?= $r['Payment_Status'] ?></span></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;color:#999;">No sales yet</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
