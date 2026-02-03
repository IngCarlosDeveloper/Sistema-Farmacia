<?php
require_once '../config/database.php';
requireLogin();

$pdo = getDBConnection();
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $provincia = $_POST['provincia'] ?? '';
    $habitantes = $_POST['habitantes'] ?? 0;
    $superficie = $_POST['superficie'] ?? 0;
    
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO ciudad (nombre, provincia, cantidad_habitantes, superficie) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $provincia, $habitantes, $superficie]);
        $message = "City added successfully";
    } elseif ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE ciudad SET nombre = ?, provincia = ?, 
                               cantidad_habitantes = ?, superficie = ? WHERE id_ciudad = ?");
        $stmt->execute([$nombre, $provincia, $habitantes, $superficie, $id]);
        $message = "City updated successfully";
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM ciudad WHERE id_ciudad = ?");
    $stmt->execute([$id]);
    $message = "City deleted successfully";
}

// Get cities for listing
$stmt = $pdo->query("SELECT * FROM ciudad ORDER BY nombre");
$ciudades = $stmt->fetchAll();

// Get city data for editing
$ciudadEdit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM ciudad WHERE id_ciudad = ?");
    $stmt->execute([$id]);
    $ciudadEdit = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cities - PharmacyDB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="module-container">
            <div class="module-header">
                <h2><i class="fas fa-city"></i> Manage Cities</h2>
                <a href="?action=add" class="btn-action">
                    <i class="fas fa-plus"></i> Add New City
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
                        <?php echo $action === 'add' ? 'Add New City' : 'Edit City'; ?>
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">City Name *</label>
                                <input type="text" id="nombre" name="nombre" required 
                                       value="<?php echo $ciudadEdit['nombre'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="provincia">Province *</label>
                                <input type="text" id="provincia" name="provincia" required 
                                       value="<?php echo $ciudadEdit['provincia'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="habitantes">Population</label>
                                <input type="number" id="habitantes" name="habitantes" 
                                       value="<?php echo $ciudadEdit['cantidad_habitantes'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="superficie">Area (km²)</label>
                                <input type="number" step="0.01" id="superficie" name="superficie" 
                                       value="<?php echo $ciudadEdit['superficie'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <a href="ciudades.php" class="btn btn-cancel">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Cities List -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>City Name</th>
                        <th>Province</th>
                        <th>Population</th>
                        <th>Area (km²)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ciudades as $ciudad): ?>
                    <tr>
                        <td><?php echo $ciudad['id_ciudad']; ?></td>
                        <td><?php echo htmlspecialchars($ciudad['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($ciudad['provincia']); ?></td>
                        <td><?php echo number_format($ciudad['cantidad_habitantes']); ?></td>
                        <td><?php echo number_format($ciudad['superficie'], 2); ?></td>
                        <td class="action-buttons">
                            <a href="?action=edit&id=<?php echo $ciudad['id_ciudad']; ?>" 
                               class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?action=delete&id=<?php echo $ciudad['id_ciudad']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Delete this city?')">
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