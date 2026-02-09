<?php
$pageTitle = 'Fruits';
$activePage = 'fruits';
require_once __DIR__ . '/../includes/db.php';

// Handle Add/Edit/Delete
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        try {
            $expiry_raw = $_POST['expiry'] ?? 'NOT SET';
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry_raw)) {
                $msg = 'Invalid date format received: "' . $expiry_raw . '". Please use the date picker to select a full date.';
                throw new Exception($msg);
            }
            $name = $_POST['name'];
            $unit = $_POST['unit'];
            $price = (float)$_POST['price'];
            $stock = (int)$_POST['stock'];
            $threshold = (int)$_POST['threshold'];
            $expiry = $_POST['expiry'];
            $status = $_POST['status'];
            $category = (int)$_POST['category'];
            $stmt = $conn->prepare("INSERT INTO fruit (Fruit_Name, Unit, Price, Stock_Quantity, Minimum_Threshold, Expiration_Date, Status, Category_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdiissi", $name, $unit, $price, $stock, $threshold, $expiry, $status, $category);
            $stmt->execute();
            $msg = 'Fruit added successfully.';
        } catch (Exception $e) {
            $msg = 'Error: ' . $e->getMessage();
        }
    } elseif ($action === 'edit') {
        try {
            $name = $_POST['name'];
            $unit = $_POST['unit'];
            $price = (float)$_POST['price'];
            $stock = (int)$_POST['stock'];
            $threshold = (int)$_POST['threshold'];
            $expiry = $_POST['expiry'];
            $status = $_POST['status'];
            $category = (int)$_POST['category'];
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE fruit SET Fruit_Name=?, Unit=?, Price=?, Stock_Quantity=?, Minimum_Threshold=?, Expiration_Date=?, Status=?, Category_ID=? WHERE Fruit_ID=?");
            $stmt->bind_param("ssdiissii", $name, $unit, $price, $stock, $threshold, $expiry, $status, $category, $id);
            $stmt->execute();
            $msg = 'Fruit updated successfully.';
        } catch (Exception $e) {
            $msg = 'Error: ' . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM fruit WHERE Fruit_ID=?");
        $stmt->bind_param("i", $_POST['id']);
        $msg = $stmt->execute() ? 'Fruit deleted.' : 'Error: ' . $stmt->error;
    }
}

$categories = $conn->query("SELECT * FROM fruit_category ORDER BY Category_Name");
$fruits = $conn->query("SELECT f.*, fc.Category_Name FROM fruit f JOIN fruit_category fc ON f.Category_ID = fc.Category_ID ORDER BY f.Fruit_Name");

$editFruit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM fruit WHERE Fruit_ID=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editFruit = $stmt->get_result()->fetch_assoc();
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<form method="post" style="background:var(--card);padding:1.25rem;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;">
    <h3 style="margin-bottom:.75rem;"><?= $editFruit ? 'Edit Fruit' : 'Add Fruit' ?></h3>
    <input type="hidden" name="action" value="<?= $editFruit ? 'edit' : 'add' ?>">
    <?php if ($editFruit): ?><input type="hidden" name="id" value="<?= $editFruit['Fruit_ID'] ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-group"><label>Name</label><input name="name" required value="<?= htmlspecialchars($editFruit['Fruit_Name'] ?? '') ?>"></div>
        <div class="form-group"><label>Unit</label><input name="unit" required value="<?= htmlspecialchars($editFruit['Unit'] ?? '') ?>" placeholder="kg, piece, dozen"></div>
        <div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" required value="<?= $editFruit['Price'] ?? '' ?>"></div>
        <div class="form-group"><label>Stock Qty</label><input type="number" name="stock" required value="<?= $editFruit['Stock_Quantity'] ?? '' ?>"></div>
        <div class="form-group"><label>Min Threshold</label><input type="number" name="threshold" required value="<?= $editFruit['Minimum_Threshold'] ?? '' ?>"></div>
        <div class="form-group"><label>Expiry Date</label><input type="date" name="expiry" required value="<?= $editFruit['Expiration_Date'] ?? '' ?>"></div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="Available" <?= ($editFruit['Status'] ?? '') === 'Available' ? 'selected' : '' ?>>Available</option>
                <option value="Unavailable" <?= ($editFruit['Status'] ?? '') === 'Unavailable' ? 'selected' : '' ?>>Unavailable</option>
            </select>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category" required>
                <option value="">Select</option>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?= $c['Category_ID'] ?>" <?= ($editFruit['Category_ID'] ?? '') == $c['Category_ID'] ? 'selected' : '' ?>><?= htmlspecialchars($c['Category_Name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>
    <button class="btn btn-primary" type="submit"><?= $editFruit ? 'Update' : 'Add Fruit' ?></button>
    <?php if ($editFruit): ?><a href="fruits.php" class="btn" style="margin-left:.5rem;">Cancel</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Unit</th><th>Price</th><th>Stock</th><th>Threshold</th><th>Expiry</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if ($fruits && $fruits->num_rows > 0): ?>
            <?php while ($r = $fruits->fetch_assoc()): ?>
            <tr>
                <td><?= $r['Fruit_ID'] ?></td>
                <td><?= htmlspecialchars($r['Fruit_Name']) ?></td>
                <td><?= htmlspecialchars($r['Category_Name']) ?></td>
                <td><?= htmlspecialchars($r['Unit']) ?></td>
                <td>$<?= number_format($r['Price'], 2) ?></td>
                <td><?= $r['Stock_Quantity'] ?></td>
                <td><?= $r['Minimum_Threshold'] ?></td>
                <td><?= $r['Expiration_Date'] ?></td>
                <td><span class="badge <?= $r['Status'] === 'Available' ? 'badge-success' : 'badge-danger' ?>"><?= $r['Status'] ?></span></td>
                <td>
                    <a href="?edit=<?= $r['Fruit_ID'] ?>" class="btn btn-primary btn-sm">Edit</a>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this fruit?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $r['Fruit_ID'] ?>">
                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="10" style="text-align:center;color:#999;">No fruits found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
