<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fruit Master - <?= $pageTitle ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="/fruit_master/assets/css/style.css">
</head>
<body>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div class="brand">&#127819; Fruit Master</div>
    <nav>
        <a href="/fruit_master/index.php" class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">&#128202; Dashboard</a>
        <a href="/fruit_master/pages/fruits.php" class="<?= ($activePage ?? '') === 'fruits' ? 'active' : '' ?>">&#127815; Fruits</a>
        <a href="/fruit_master/pages/categories.php" class="<?= ($activePage ?? '') === 'categories' ? 'active' : '' ?>">&#128193; Categories</a>
        <a href="/fruit_master/pages/employees.php" class="<?= ($activePage ?? '') === 'employees' ? 'active' : '' ?>">&#128100; Employees</a>
        <a href="/fruit_master/pages/sales.php" class="<?= ($activePage ?? '') === 'sales' ? 'active' : '' ?>">&#128176; Sales</a>
        <a href="/fruit_master/pages/suppliers.php" class="<?= ($activePage ?? '') === 'suppliers' ? 'active' : '' ?>">&#128666; Suppliers</a>
        <a href="/fruit_master/pages/attendance.php" class="<?= ($activePage ?? '') === 'attendance' ? 'active' : '' ?>">&#128197; Attendance</a>
        <a href="/fruit_master/pages/inventory.php" class="<?= ($activePage ?? '') === 'inventory' ? 'active' : '' ?>">&#128230; Inventory Log</a>
    </nav>
</aside>

<div class="main">
    <div class="topbar">
        <button class="hamburger" onclick="toggleSidebar()">&#9776;</button>
        <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
        <div></div>
    </div>
    <div class="content">
