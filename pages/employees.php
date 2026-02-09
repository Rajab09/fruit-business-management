<?php
$pageTitle = 'Employees';
$activePage = 'employees';
require_once __DIR__ . '/../includes/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO employee (Employee_Name, Employee_Role, Employee_Phone, Employee_Email, Employee_Hourly_Rate) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssssd", $_POST['name'], $_POST['role'], $_POST['phone'], $_POST['email'], $_POST['rate']);
        $msg = $stmt->execute() ? 'Employee added.' : 'Error: ' . $stmt->error;
    } elseif ($action === 'edit') {
        $stmt = $conn->prepare("UPDATE employee SET Employee_Name=?, Employee_Role=?, Employee_Phone=?, Employee_Email=?, Employee_Hourly_Rate=? WHERE Employee_ID=?");
        $stmt->bind_param("ssssdi", $_POST['name'], $_POST['role'], $_POST['phone'], $_POST['email'], $_POST['rate'], $_POST['id']);
        $msg = $stmt->execute() ? 'Employee updated.' : 'Error: ' . $stmt->error;
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM employee WHERE Employee_ID=?");
        $stmt->bind_param("i", $_POST['id']);
        $msg = $stmt->execute() ? 'Employee deleted.' : 'Error: ' . $stmt->error;
    }
}

$employees = $conn->query("SELECT * FROM employee ORDER BY Employee_Name");

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM employee WHERE Employee_ID=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<form method="post" style="background:var(--card);padding:1.25rem;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;">
    <h3 style="margin-bottom:.75rem;"><?= $edit ? 'Edit Employee' : 'Add Employee' ?></h3>
    <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['Employee_ID'] ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-group"><label>Name</label><input name="name" required value="<?= htmlspecialchars($edit['Employee_Name'] ?? '') ?>"></div>
        <div class="form-group"><label>Role</label><input name="role" required value="<?= htmlspecialchars($edit['Employee_Role'] ?? '') ?>"></div>
        <div class="form-group"><label>Phone</label><input name="phone" required value="<?= htmlspecialchars($edit['Employee_Phone'] ?? '') ?>"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required value="<?= htmlspecialchars($edit['Employee_Email'] ?? '') ?>"></div>
        <div class="form-group"><label>Hourly Rate ($)</label><input type="number" step="0.01" name="rate" required value="<?= $edit['Employee_Hourly_Rate'] ?? '' ?>"></div>
    </div>
    <button class="btn btn-primary" type="submit"><?= $edit ? 'Update' : 'Add Employee' ?></button>
    <?php if ($edit): ?><a href="employees.php" class="btn" style="margin-left:.5rem;">Cancel</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Role</th><th>Phone</th><th>Email</th><th>Rate</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($r = $employees->fetch_assoc()): ?>
        <tr>
            <td><?= $r['Employee_ID'] ?></td>
            <td><?= htmlspecialchars($r['Employee_Name']) ?></td>
            <td><?= htmlspecialchars($r['Employee_Role']) ?></td>
            <td><?= htmlspecialchars($r['Employee_Phone']) ?></td>
            <td><?= htmlspecialchars($r['Employee_Email']) ?></td>
            <td>$<?= number_format($r['Employee_Hourly_Rate'], 2) ?></td>
            <td>
                <a href="?edit=<?= $r['Employee_ID'] ?>" class="btn btn-primary btn-sm">Edit</a>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $r['Employee_ID'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
