<?php
require_once '../config/database.php';
requireLogin();

$pdo = getDBConnection();
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? 0;

// Manejar envíos de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_comercial = $_POST['nombre_comercial'] ?? '';
    $id_medicamento_complemento = $_POST['id_medicamento_complemento'] ?? null;
    
    if ($accion === 'agregar') {
        $stmt = $pdo->prepare("INSERT INTO medicamento (nombre_comercial, id_medicamento_complemento) 
                               VALUES (?, ?)");
        $stmt->execute([$nombre_comercial, $id_medicamento_complemento]);
        $mensaje = "Medicamento agregado exitosamente";
    } elseif ($accion === 'editar' && $id) {
        $stmt = $pdo->prepare("UPDATE medicamento SET nombre_comercial = ?, 
                               id_medicamento_complemento = ? WHERE id_medicamento = ?");
        $stmt->execute([$nombre_comercial, $id_medicamento_complemento, $id]);
        $mensaje = "Medicamento actualizado exitosamente";
    }
}

// Manejar eliminación
if ($accion === 'eliminar' && $id) {
    $stmt = $pdo->prepare("DELETE FROM medicamento WHERE id_medicamento = ?");
    $stmt->execute([$id]);
    $mensaje = "Medicamento eliminado exitosamente";
}

// Obtener medicamentos para listar
$stmt = $pdo->query("SELECT m.*, mc.nombre_comercial as complemento_nombre 
                     FROM medicamento m 
                     LEFT JOIN medicamento mc ON m.id_medicamento_complemento = mc.id_medicamento 
                     ORDER BY m.nombre_comercial");
$medicamentos = $stmt->fetchAll();

// Obtener medicamentos para dropdown (excluyendo el actual en edición)
$stmt = $pdo->query("SELECT * FROM medicamento ORDER BY nombre_comercial");
$todosMedicamentos = $stmt->fetchAll();

// Obtener datos de medicamento para editar
$medicamentoEditar = null;
if ($accion === 'editar' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM medicamento WHERE id_medicamento = ?");
    $stmt->execute([$id]);
    $medicamentoEditar = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Medicamentos - PharmacyDB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="module-container">
            <div class="module-header">
                <h2><i class="fas fa-pills"></i> Gestionar Medicamentos</h2>
                <a href="?accion=agregar" class="btn-action">
                    <i class="fas fa-plus"></i> Agregar Medicamento
                </a>
            </div>
            
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario Agregar/Editar -->
            <?php if ($accion === 'agregar' || $accion === 'editar'): ?>
                <div class="form-container">
                    <h3>
                        <i class="fas fa-<?php echo $accion === 'agregar' ? 'plus' : 'edit'; ?>"></i>
                        <?php echo $accion === 'agregar' ? 'Agregar Medicamento' : 'Editar Medicamento'; ?>
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nombre_comercial">Nombre Comercial *</label>
                            <input type="text" id="nombre_comercial" name="nombre_comercial" required 
                                   value="<?php echo $medicamentoEditar['nombre_comercial'] ?? ''; ?>"
                                   placeholder="Ej: Termalgin">
                        </div>
                        
                        <div class="form-group">
                            <label for="id_medicamento_complemento">Medicamento Complementario</label>
                            <select id="id_medicamento_complemento" name="id_medicamento_complemento">
                                <option value="">Sin complemento (opcional)</option>
                                <?php foreach ($todosMedicamentos as $med): 
                                    // Excluir el medicamento actual en modo edición
                                    if ($accion === 'editar' && $med['id_medicamento'] == $id) continue;
                                ?>
                                <option value="<?php echo $med['id_medicamento']; ?>"
                                    <?php echo ($medicamentoEditar['id_medicamento_complemento'] ?? '') == $med['id_medicamento'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($med['nombre_comercial']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text">Medicamento que complementa a este (opcional)</small>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="medicamentos.php" class="btn btn-cancel">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Lista de Medicamentos -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Comercial</th>
                        <th>Complemento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicamentos as $medicamento): ?>
                    <tr>
                        <td><?php echo $medicamento['id_medicamento']; ?></td>
                        <td><?php echo htmlspecialchars($medicamento['nombre_comercial']); ?></td>
                        <td>
                            <?php if ($medicamento['complemento_nombre']): ?>
                                <span class="badge"><?php echo htmlspecialchars($medicamento['complemento_nombre']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">Ninguno</span>
                            <?php endif; ?>
                        </td>
                        <td class="action-buttons">
                            <a href="?accion=editar&id=<?php echo $medicamento['id_medicamento']; ?>" 
                               class="btn-edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="?accion=eliminar&id=<?php echo $medicamento['id_medicamento']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('¿Eliminar este medicamento?')">
                                <i class="fas fa-trash"></i> Eliminar
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