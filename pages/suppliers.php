<?php
$pageTitle = 'Suppliers';
$activePage = 'suppliers';
require_once __DIR__ . '/../includes/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO supplier (Supplier_Name, Supplier_Phone, Supplier_Email, Supplier_Address) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $_POST['name'], $_POST['phone'], $_POST['email'], $_POST['address']);
        $msg = $stmt->execute() ? 'Supplier added.' : 'Error: ' . $stmt->error;
    } elseif ($action === 'edit') {
        $stmt = $conn->prepare("UPDATE supplier SET Supplier_Name=?, Supplier_Phone=?, Supplier_Email=?, Supplier_Address=? WHERE Supplier_ID=?");
        $stmt->bind_param("ssssi", $_POST['name'], $_POST['phone'], $_POST['email'], $_POST['address'], $_POST['id']);
        $msg = $stmt->execute() ? 'Supplier updated.' : 'Error: ' . $stmt->error;
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM supplier WHERE Supplier_ID=?");
        $stmt->bind_param("i", $_POST['id']);
        $msg = $stmt->execute() ? 'Supplier deleted.' : 'Error: ' . $stmt->error;
    }
}

$suppliers = $conn->query("SELECT * FROM supplier ORDER BY Supplier_Name");

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM supplier WHERE Supplier_ID=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<form method="post" style="background:var(--card);padding:1.25rem;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;">
    <h3 style="margin-bottom:.75rem;"><?= $edit ? 'Edit Supplier' : 'Add Supplier' ?></h3>
    <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['Supplier_ID'] ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-group"><label>Name</label><input name="name" required value="<?= htmlspecialchars($edit['Supplier_Name'] ?? '') ?>"></div>
        <div class="form-group"><label>Phone</label><input name="phone" required value="<?= htmlspecialchars($edit['Supplier_Phone'] ?? '') ?>"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required value="<?= htmlspecialchars($edit['Supplier_Email'] ?? '') ?>"></div>
        <div class="form-group"><label>Address</label><input name="address" value="<?= htmlspecialchars($edit['Supplier_Address'] ?? '') ?>"></div>
    </div>
    <button class="btn btn-primary" type="submit"><?= $edit ? 'Update' : 'Add Supplier' ?></button>
    <?php if ($edit): ?><a href="suppliers.php" class="btn" style="margin-left:.5rem;">Cancel</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Address</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($r = $suppliers->fetch_assoc()): ?>
        <tr>
            <td><?= $r['Supplier_ID'] ?></td>
            <td><?= htmlspecialchars($r['Supplier_Name']) ?></td>
            <td><?= htmlspecialchars($r['Supplier_Phone']) ?></td>
            <td><?= htmlspecialchars($r['Supplier_Email']) ?></td>
            <td><?= htmlspecialchars($r['Supplier_Address']) ?></td>
            <td>
                <a href="?edit=<?= $r['Supplier_ID'] ?>" class="btn btn-primary btn-sm">Edit</a>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $r['Supplier_ID'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
