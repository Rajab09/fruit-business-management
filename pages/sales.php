<?php
$pageTitle = 'Sales';
$activePage = 'sales';
require_once __DIR__ . '/../includes/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'new_sale') {
        $stmt = $conn->prepare("INSERT INTO sale (Employee_ID, Payment_Status) VALUES (?, ?)");
        $stmt->bind_param("is", $_POST['employee'], $_POST['payment_status']);
        if ($stmt->execute()) {
            $saleId = $conn->insert_id;
            $msg = "Sale #$saleId created. Add items below.";
            header("Location: sales.php?items=$saleId&msg=" . urlencode($msg));
            exit;
        } else {
            $msg = 'Error: ' . $stmt->error;
        }
    } elseif ($action === 'add_item') {
        $fruit = $conn->query("SELECT Price FROM fruit WHERE Fruit_ID=" . (int)$_POST['fruit'])->fetch_assoc();
        $stmt = $conn->prepare("INSERT INTO sale_item (Sale_ID, Fruit_ID, Quantity, Unit_Price) VALUES (?,?,?,?)");
        $stmt->bind_param("iiid", $_POST['sale_id'], $_POST['fruit'], $_POST['qty'], $fruit['Price']);
        $msg = $stmt->execute() ? 'Item added.' : 'Error: ' . $stmt->error;
        header("Location: sales.php?items=" . $_POST['sale_id'] . "&msg=" . urlencode($msg));
        exit;
    } elseif ($action === 'update_status') {
        $stmt = $conn->prepare("UPDATE sale SET Payment_Status=? WHERE Sale_ID=?");
        $stmt->bind_param("si", $_POST['payment_status'], $_POST['id']);
        $msg = $stmt->execute() ? 'Status updated.' : 'Error: ' . $stmt->error;
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM sale_item WHERE Sale_ID=" . (int)$_POST['id']);
        $stmt = $conn->prepare("DELETE FROM sale WHERE Sale_ID=?");
        $stmt->bind_param("i", $_POST['id']);
        $msg = $stmt->execute() ? 'Sale deleted.' : 'Error: ' . $stmt->error;
    }
}

if (isset($_GET['msg'])) $msg = $_GET['msg'];

$employees = $conn->query("SELECT * FROM employee ORDER BY Employee_Name");
$fruits = $conn->query("SELECT * FROM fruit WHERE Status='Available' ORDER BY Fruit_Name");

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<?php if (isset($_GET['items'])): ?>
    <?php
    $saleId = (int)$_GET['items'];
    $sale = $conn->query("SELECT s.*, e.Employee_Name FROM sale s JOIN employee e ON s.Employee_ID=e.Employee_ID WHERE s.Sale_ID=$saleId")->fetch_assoc();
    $items = $conn->query("SELECT si.*, f.Fruit_Name FROM sale_item si JOIN fruit f ON si.Fruit_ID=f.Fruit_ID WHERE si.Sale_ID=$saleId");
    ?>
    <div style="background:var(--card);padding:1.25rem;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;">
        <h3>Sale #<?= $saleId ?> - <?= htmlspecialchars($sale['Employee_Name']) ?> (<?= $sale['Payment_Status'] ?>)</h3>
        <a href="sales.php" class="btn btn-sm" style="margin-top:.5rem;">Back to Sales</a>
    </div>
    <form method="post" style="background:var(--card);padding:1.25rem;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;">
        <h3 style="margin-bottom:.75rem;">Add Item</h3>
        <input type="hidden" name="action" value="add_item">
        <input type="hidden" name="sale_id" value="<?= $saleId ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Fruit</label>
                <select name="fruit" required>
                    <option value="">Select</option>
                    <?php $fruits->data_seek(0); while ($f = $fruits->fetch_assoc()): ?>
                        <option value="<?= $f['Fruit_ID'] ?>"><?= htmlspecialchars($f['Fruit_Name']) ?> ($<?= $f['Price'] ?>, stock: <?= $f['Stock_Quantity'] ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group"><label>Quantity</label><input type="number" name="qty" min="1" required></div>
        </div>
        <button class="btn btn-primary" type="submit">Add Item</button>
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Fruit</th><th>Qty</th><th>Unit Price</th><th>Sub Total</th></tr></thead>
            <tbody>
            <?php $total = 0; while ($i = $items->fetch_assoc()): $total += $i['Sub_Total']; ?>
            <tr>
                <td><?= htmlspecialchars($i['Fruit_Name']) ?></td>
                <td><?= $i['Quantity'] ?></td>
                <td>$<?= number_format($i['Unit_Price'], 2) ?></td>
                <td>$<?= number_format($i['Sub_Total'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
            <tr><td colspan="3" style="text-align:right;font-weight:700;">Total</td><td style="font-weight:700;">$<?= number_format($total, 2) ?></td></tr>
            </tbody>
        </table>
    </div>
<?php else: ?>

<form method="post" style="background:var(--card);padding:1.25rem;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;">
    <h3 style="margin-bottom:.75rem;">New Sale</h3>
    <input type="hidden" name="action" value="new_sale">
    <div class="form-grid">
        <div class="form-group">
            <label>Employee</label>
            <select name="employee" required>
                <option value="">Select</option>
                <?php while ($e = $employees->fetch_assoc()): ?>
                    <option value="<?= $e['Employee_ID'] ?>"><?= htmlspecialchars($e['Employee_Name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Payment Status</label>
            <select name="payment_status">
                <option value="Pending">Pending</option>
                <option value="Paid">Paid</option>
            </select>
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Create Sale</button>
</form>

<?php
$sales = $conn->query("
    SELECT s.Sale_ID, s.Sale_Date_Time, s.Payment_Status, e.Employee_Name,
           COALESCE(SUM(si.Sub_Total),0) AS Total
    FROM sale s
    JOIN employee e ON s.Employee_ID = e.Employee_ID
    LEFT JOIN sale_item si ON s.Sale_ID = si.Sale_ID
    GROUP BY s.Sale_ID
    ORDER BY s.Sale_Date_Time DESC
");
?>
<div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Date</th><th>Employee</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($r = $sales->fetch_assoc()): ?>
        <tr>
            <td>#<?= $r['Sale_ID'] ?></td>
            <td><?= $r['Sale_Date_Time'] ?></td>
            <td><?= htmlspecialchars($r['Employee_Name']) ?></td>
            <td>$<?= number_format($r['Total'], 2) ?></td>
            <td><span class="badge <?= $r['Payment_Status'] === 'Paid' ? 'badge-success' : 'badge-warning' ?>"><?= $r['Payment_Status'] ?></span></td>
            <td>
                <a href="?items=<?= $r['Sale_ID'] ?>" class="btn btn-primary btn-sm">Items</a>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete sale and all items?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $r['Sale_ID'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
