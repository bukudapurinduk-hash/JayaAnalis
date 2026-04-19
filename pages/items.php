<?php
require_once __DIR__ . '/../config/database.php';
require_login();
$user = get_current_user();

// Get all items with supplier info
$items = get_results($conn, "
    SELECT i.*, s.name as supplier_name
    FROM items i
    LEFT JOIN suppliers s ON i.supplier_id = s.supplier_id
    ORDER BY i.created_at DESC
");

// Get suppliers for dropdown
$suppliers = get_results($conn, "SELECT * FROM suppliers ORDER BY name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Barang - JayaAnalis</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }
        .navbar {
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-brand {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
        }
        .navbar-menu {
            display: flex;
            gap: 20px;
            list-style: none;
        }
        .navbar-menu a {
            color: #1f2937;
            text-decoration: none;
            font-weight: 500;
        }
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .navbar-logout {
            background-color: #dc2626;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        .content {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background-color: #1e40af;
        }
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            margin-right: 5px;
        }
        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }
        .btn-danger {
            background-color: #dc2626;
            color: white;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .data-table th {
            background-color: #f9fafb;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .data-table tr:hover {
            background-color: #f9fafb;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">🏢 JayaAnalis</div>
            <ul class="navbar-menu">
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="items.php">📦 Barang</a></li>
                <li><a href="suppliers.php">🏭 Supplier</a></li>
                <li><a href="transactions.php">💳 Transaksi</a></li>
                <li><a href="pricing.php">💰 Harga</a></li>
                <li><a href="audit_logs.php">📋 Audit</a></li>
            </ul>
            <div class="navbar-user">
                <span>Hello, <?php echo htmlspecialchars($user['username']); ?></span>
                <a href="../auth/logout.php" class="navbar-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="content">
            <h1>📦 Manajemen Barang</h1>
            
            <button class="btn btn-primary" onclick="openModal('addItemModal')">+ Tambah Barang</button>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Deskripsi</th>
                        <th>Supplier</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['item_id']; ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 50)); ?></td>
                        <td><?php echo htmlspecialchars($item['supplier_name'] ?? '-'); ?></td>
                        <td>Rp <?php echo number_format($item['pricing'], 0, ',', '.'); ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editItem(<?php echo $item['item_id']; ?>)">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteItem(<?php echo $item['item_id']; ?>)">Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="addItemModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addItemModal')">&times;</span>
            <h2>Tambah/Edit Barang</h2>
            
            <form id="itemForm" method="POST" action="../api/items_api.php">
                <input type="hidden" id="itemId" name="item_id">
                <input type="hidden" name="action" id="action" value="add">
                
                <div class="form-group">
                    <label for="itemName">Nama Barang:</label>
                    <input type="text" id="itemName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="itemDescription">Deskripsi:</label>
                    <textarea id="itemDescription" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="itemSupplier">Supplier:</label>
                    <select id="itemSupplier" name="supplier_id">
                        <option value="">-- Pilih Supplier --</option>
                        <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['supplier_id']; ?>">
                            <?php echo htmlspecialchars($supplier['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="itemPrice">Harga:</label>
                    <input type="number" id="itemPrice" name="pricing" step="0.01" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        function editItem(id) {
            fetch('../api/items_api.php?action=get&id=' + id)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('itemId').value = data.item_id;
                    document.getElementById('itemName').value = data.name;
                    document.getElementById('itemDescription').value = data.description;
                    document.getElementById('itemSupplier').value = data.supplier_id;
                    document.getElementById('itemPrice').value = data.pricing;
                    document.getElementById('action').value = 'edit';
                    openModal('addItemModal');
                });
        }
        
        function deleteItem(id) {
            if (confirm('Yakin ingin menghapus barang ini?')) {
                fetch('../api/items_api.php?action=delete&id=' + id, {method: 'POST'})
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            }
        }
    </script>
</body>
</html>