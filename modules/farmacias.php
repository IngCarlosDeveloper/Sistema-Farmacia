<?php
require_once '../config/database.php';
requireLogin();

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $domicilio = $_POST['domicilio'] ?? '';
    $id_ciudad = $_POST['id_ciudad'] ?? 0;
    $id_propietario = $_POST['id_propietario'] ?? null;

    if ($id_propietario === '') {
    $id_propietario = null;
}
    
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO farmacia (nombre, domicilio, id_ciudad, id_propietario) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $domicilio, $id_ciudad, $id_propietario]);
        $message = "Pharmacy added successfully";
    } elseif ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE farmacia SET nombre = ?, domicilio = ?, 
                               id_ciudad = ?, id_propietario = ? WHERE id_farmacia = ?");
        $stmt->execute([$nombre, $domicilio, $id_ciudad, $id_propietario, $id]);
        $message = "Pharmacy updated successfully";
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM farmacia WHERE id_farmacia = ?");
    $stmt->execute([$id]);
    $message = "Pharmacy deleted successfully";
}

// Get pharmacies for listing
$stmt = $pdo->query("SELECT f.*, c.nombre as ciudad_nombre, p.nombre as propietario_nombre 
                     FROM farmacia f 
                     LEFT JOIN ciudad c ON f.id_ciudad = c.id_ciudad 
                     LEFT JOIN propietario p ON f.id_propietario = p.id_propietario 
                     ORDER BY f.nombre");
$farmacias = $stmt->fetchAll();

// Get cities for dropdown
$stmt = $pdo->query("SELECT * FROM ciudad ORDER BY nombre");
$ciudades = $stmt->fetchAll();

// Get owners for dropdown
$stmt = $pdo->query("SELECT * FROM propietario ORDER BY nombre");
$propietarios = $stmt->fetchAll();

// Get pharmacy data for editing
$farmaciaEdit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM farmacia WHERE id_farmacia = ?");
    $stmt->execute([$id]);
    $farmaciaEdit = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pharmacies - PharmacyDB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="module-container">
            <div class="module-header">
                <h2><i class="fas fa-clinic-medical"></i> Manage Pharmacies</h2>
                <a href="?action=add" class="btn-action">
                    <i class="fas fa-plus"></i> Add New Pharmacy
                </a>
            </div>
            
            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Add/Edit Form -->
            <?php if ($action === 'add' || $action === 'edit'): ?>
                <div class="form-container">
                    <h3>
                        <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?>"></i>
                        <?php echo $action === 'add' ? 'Add New Pharmacy' : 'Edit Pharmacy'; ?>
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">Pharmacy Name *</label>
                                <input type="text" id="nombre" name="nombre" required 
                                       value="<?php echo $farmaciaEdit['nombre'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="domicilio">Address *</label>
                                <input type="text" id="domicilio" name="domicilio" required 
                                       value="<?php echo $farmaciaEdit['domicilio'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="id_ciudad">City *</label>
                                <select id="id_ciudad" name="id_ciudad" required>
                                    <option value="">Select a city</option>
                                    <?php foreach ($ciudades as $ciudad): ?>
                                    <option value="<?php echo $ciudad['id_ciudad']; ?>"
                                        <?php echo ($farmaciaEdit['id_ciudad'] ?? '') == $ciudad['id_ciudad'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ciudad['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_propietario">Owner</label>
                                <select id="id_propietario" name="id_propietario">
                                    <option value="">Select an owner (optional)</option>
                                    <?php foreach ($propietarios as $propietario): ?>
                                    <option value="<?php echo $propietario['id_propietario']; ?>"
                                        <?php echo ($farmaciaEdit['id_propietario'] ?? '') == $propietario['id_propietario'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($propietario['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <a href="farmacias.php" class="btn btn-cancel">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Pharmacies List -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>Owner</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($farmacias as $farmacia): ?>
                    <tr>
                        <td><?php echo $farmacia['id_farmacia']; ?></td>
                        <td><?php echo htmlspecialchars($farmacia['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($farmacia['domicilio']); ?></td>
                        <td><?php echo htmlspecialchars($farmacia['ciudad_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($farmacia['propietario_nombre'] ?? 'N/A'); ?></td>
                        <td class="action-buttons">
                            <a href="?action=edit&id=<?php echo $farmacia['id_farmacia']; ?>" 
                               class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?action=delete&id=<?php echo $farmacia['id_farmacia']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Delete this pharmacy?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>