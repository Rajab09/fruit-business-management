<?php
$pageTitle = 'Categories';
$activePage = 'categories';
require_once __DIR__ . '/../includes/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO fruit_category (Category_Name) VALUES (?)");
        $stmt->bind_param("s", $_POST['name']);
        $msg = $stmt->execute() ? 'Category added.' : 'Error: ' . $stmt->error;
    } elseif ($action === 'edit') {
        $stmt = $conn->prepare("UPDATE fruit_category SET Category_Name=? WHERE Category_ID=?");
        $stmt->bind_param("si", $_POST['name'], $_POST['id']);
        $msg = $stmt->execute() ? 'Category updated.' : 'Error: ' . $stmt->error;
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM fruit_category WHERE Category_ID=?");
        $stmt->bind_param("i", $_POST['id']);
        $msg = $stmt->execute() ? 'Category deleted.' : 'Error: ' . $stmt->error;
    }
}

$categories = $conn->query("SELECT fc.*, COUNT(f.Fruit_ID) AS fruit_count FROM fruit_category fc LEFT JOIN fruit f ON fc.Category_ID = f.Category_ID GROUP BY fc.Category_ID ORDER BY fc.Category_Name");

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM fruit_category WHERE Category_ID=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<form method="post" style="background:var(--card);padding:1.25rem;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;">
    <h3 style="margin-bottom:.75rem;"><?= $edit ? 'Edit Category' : 'Add Category' ?></h3>
    <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['Category_ID'] ?>"><?php endif; ?>
    <div class="form-grid">
        <div class="form-group"><label>Category Name</label><input name="name" required value="<?= htmlspecialchars($edit['Category_Name'] ?? '') ?>"></div>
    </div>
    <button class="btn btn-primary" type="submit"><?= $edit ? 'Update' : 'Add Category' ?></button>
    <?php if ($edit): ?><a href="categories.php" class="btn" style="margin-left:.5rem;">Cancel</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Fruits</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($r = $categories->fetch_assoc()): ?>
        <tr>
            <td><?= $r['Category_ID'] ?></td>
            <td><?= htmlspecialchars($r['Category_Name']) ?></td>
            <td><?= $r['fruit_count'] ?></td>
            <td>
                <a href="?edit=<?= $r['Category_ID'] ?>" class="btn btn-primary btn-sm">Edit</a>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $r['Category_ID'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
