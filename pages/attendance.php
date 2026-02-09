<?php
$pageTitle = 'Attendance';
$activePage = 'attendance';
require_once __DIR__ . '/../includes/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT INTO attendance (Employee_ID, Shift_ID, Att_Date, Clock_In, Clock_Out) VALUES (?,?,?,?,?)");
        $clockOut = !empty($_POST['clock_out']) ? $_POST['clock_out'] : null;
        $stmt->bind_param("iisss", $_POST['employee'], $_POST['shift'], $_POST['date'], $_POST['clock_in'], $clockOut);
        $msg = $stmt->execute() ? 'Attendance recorded.' : 'Error: ' . $stmt->error;
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM attendance WHERE Attendance_ID=?");
        $stmt->bind_param("i", $_POST['id']);
        $msg = $stmt->execute() ? 'Record deleted.' : 'Error: ' . $stmt->error;
    }
}

$employees = $conn->query("SELECT * FROM employee ORDER BY Employee_Name");
$shifts = $conn->query("SELECT * FROM shift ORDER BY Start_Time");

$records = $conn->query("
    SELECT a.*, e.Employee_Name, s.Shift_Name, s.Start_Time, s.End_Time
    FROM attendance a
    JOIN employee e ON a.Employee_ID = e.Employee_ID
    JOIN shift s ON a.Shift_ID = s.Shift_ID
    ORDER BY a.Att_Date DESC, a.Clock_In DESC
    LIMIT 100
");

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<form method="post" style="background:var(--card);padding:1.25rem;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;">
    <h3 style="margin-bottom:.75rem;">Record Attendance</h3>
    <input type="hidden" name="action" value="add">
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
            <label>Shift</label>
            <select name="shift" required>
                <option value="">Select</option>
                <?php while ($s = $shifts->fetch_assoc()): ?>
                    <option value="<?= $s['Shift_ID'] ?>"><?= htmlspecialchars($s['Shift_Name']) ?> (<?= $s['Start_Time'] ?> - <?= $s['End_Time'] ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group"><label>Date</label><input type="date" name="date" required value="<?= date('Y-m-d') ?>"></div>
        <div class="form-group"><label>Clock In</label><input type="time" name="clock_in" required></div>
        <div class="form-group"><label>Clock Out</label><input type="time" name="clock_out"></div>
    </div>
    <button class="btn btn-primary" type="submit">Record</button>
</form>

<div class="table-wrap">
    <table>
        <thead><tr><th>ID</th><th>Employee</th><th>Shift</th><th>Date</th><th>Clock In</th><th>Clock Out</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($r = $records->fetch_assoc()): ?>
        <tr>
            <td><?= $r['Attendance_ID'] ?></td>
            <td><?= htmlspecialchars($r['Employee_Name']) ?></td>
            <td><?= htmlspecialchars($r['Shift_Name']) ?></td>
            <td><?= $r['Att_Date'] ?></td>
            <td><?= $r['Clock_In'] ?></td>
            <td><?= $r['Clock_Out'] ?? '-' ?></td>
            <td><span class="badge <?= $r['Status'] === 'On Time' ? 'badge-success' : 'badge-danger' ?>"><?= $r['Status'] ?></span></td>
            <td>
                <form method="post" style="display:inline;" onsubmit="return confirm('Delete?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $r['Attendance_ID'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
