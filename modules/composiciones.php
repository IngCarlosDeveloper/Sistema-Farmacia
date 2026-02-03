<?php
require_once '../config/database.php';
requireLogin();

$pdo = getDBConnection();
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? 0;

// Manejar envíos de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_medicamento = $_POST['id_medicamento'] ?? 0;
    $id_drogas = $_POST['id_drogas'] ?? 0;
    
    if ($accion === 'agregar') {
        try {
            $stmt = $pdo->prepare("INSERT INTO composicion (id_medicamento, id_drogas) VALUES (?, ?)");
            $stmt->execute([$id_medicamento, $id_drogas]);
            $mensaje = "Composición agregada exitosamente";
        } catch (PDOException $e) {
            $error = "Error: Esta combinación medicamento-droga ya existe";
        }
    } elseif ($accion === 'editar' && $id) {
        $stmt = $pdo->prepare("UPDATE composicion SET id_medicamento = ?, id_drogas = ? WHERE id_composicion = ?");
        $stmt->execute([$id_medicamento, $id_drogas, $id]);
        $mensaje = "Composición actualizada exitosamente";
    }
}

// Manejar eliminación
if ($accion === 'eliminar' && $id) {
    $stmt = $pdo->prepare("DELETE FROM composicion WHERE id_composicion = ?");
    $stmt->execute([$id]);
    $mensaje = "Composición eliminada exitosamente";
}

// Obtener composiciones para listar
$stmt = $pdo->query("SELECT c.*, m.nombre_comercial as medicamento_nombre, d.descripcion as droga_descripcion 
                     FROM composicion c 
                     JOIN medicamento m ON c.id_medicamento = m.id_medicamento 
                     JOIN drogas d ON c.id_drogas = d.id_drogas 
                     ORDER BY m.nombre_comercial");
$composiciones = $stmt->fetchAll();

// Obtener medicamentos para dropdown
$stmt = $pdo->query("SELECT * FROM medicamento ORDER BY nombre_comercial");
$medicamentos = $stmt->fetchAll();

// Obtener drogas para dropdown
$stmt = $pdo->query("SELECT * FROM drogas ORDER BY descripcion");
$drogas = $stmt->fetchAll();

// Obtener datos de composición para editar
$composicionEditar = null;
if ($accion === 'editar' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM composicion WHERE id_composicion = ?");
    $stmt->execute([$id]);
    $composicionEditar = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Composición de Medicamentos - PharmacyDB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="module-container">
            <div class="module-header">
                <h2><i class="fas fa-atom"></i> Gestionar Composición</h2>
                <a href="?accion=agregar" class="btn-action">
                    <i class="fas fa-plus"></i> Agregar Composición
                </a>
            </div>
            
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario Agregar/Editar -->
            <?php if ($accion === 'agregar' || $accion === 'editar'): ?>
                <div class="form-container">
                    <h3>
                        <i class="fas fa-<?php echo $accion === 'agregar' ? 'plus' : 'edit'; ?>"></i>
                        <?php echo $accion === 'agregar' ? 'Agregar Composición' : 'Editar Composición'; ?>
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="id_medicamento">Medicamento *</label>
                                <select id="id_medicamento" name="id_medicamento" required>
                                    <option value="">Seleccionar medicamento</option>
                                    <?php foreach ($medicamentos as $medicamento): ?>
                                    <option value="<?php echo $medicamento['id_medicamento']; ?>"
                                        <?php echo ($composicionEditar['id_medicamento'] ?? '') == $medicamento['id_medicamento'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($medicamento['nombre_comercial']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_drogas">Droga *</label>
                                <select id="id_drogas" name="id_drogas" required>
                                    <option value="">Seleccionar droga</option>
                                    <?php foreach ($drogas as $droga): ?>
                                    <option value="<?php echo $droga['id_drogas']; ?>"
                                        <?php echo ($composicionEditar['id_drogas'] ?? '') == $droga['id_drogas'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($droga['descripcion']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="composiciones.php" class="btn btn-cancel">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Lista de Composición -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Medicamento</th>
                        <th>Droga</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($composiciones as $composicion): ?>
                    <tr>
                        <td><?php echo $composicion['id_composicion']; ?></td>
                        <td><?php echo htmlspecialchars($composicion['medicamento_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($composicion['droga_descripcion']); ?></td>
                        <td class="action-buttons">
                            <a href="?accion=editar&id=<?php echo $composicion['id_composicion']; ?>" 
                               class="btn-edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="?accion=eliminar&id=<?php echo $composicion['id_composicion']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('¿Eliminar esta composición?')">
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