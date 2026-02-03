<?php
require_once '../config/database.php';
requireLogin();

$pdo = getDBConnection();
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? 0;

// Manejar envíos de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_farmacia = $_POST['id_farmacia'] ?? 0;
    $id_medicamento = $_POST['id_medicamento'] ?? 0;
    $precio = $_POST['precio'] ?? 0;
    
    if ($accion === 'agregar') {
        try {
            $stmt = $pdo->prepare("INSERT INTO distribucion (id_farmacia, id_medicamento, precio) VALUES (?, ?, ?)");
            $stmt->execute([$id_farmacia, $id_medicamento, $precio]);
            $mensaje = "Distribución agregada exitosamente";
        } catch (PDOException $e) {
            $error = "Error: Esta combinación farmacia-medicamento ya existe";
        }
    } elseif ($accion === 'editar' && $id) {
        $stmt = $pdo->prepare("UPDATE distribucion SET id_farmacia = ?, id_medicamento = ?, precio = ? WHERE id_distribucion = ?");
        $stmt->execute([$id_farmacia, $id_medicamento, $precio, $id]);
        $mensaje = "Distribución actualizada exitosamente";
    }
}

// Manejar eliminación
if ($accion === 'eliminar' && $id) {
    $stmt = $pdo->prepare("DELETE FROM distribucion WHERE id_distribucion = ?");
    $stmt->execute([$id]);
    $mensaje = "Distribución eliminada exitosamente";
}

// Obtener distribuciones para listar
$stmt = $pdo->query("SELECT d.*, 
                     f.nombre as farmacia_nombre, 
                     m.nombre_comercial as medicamento_nombre,
                     c.nombre as ciudad_nombre
                     FROM distribucion d 
                     JOIN farmacia f ON d.id_farmacia = f.id_farmacia 
                     JOIN medicamento m ON d.id_medicamento = m.id_medicamento
                     JOIN ciudad c ON f.id_ciudad = c.id_ciudad
                     ORDER BY f.nombre, m.nombre_comercial");
$distribuciones = $stmt->fetchAll();

// Obtener farmacias para dropdown
$stmt = $pdo->query("SELECT f.*, c.nombre as ciudad_nombre 
                     FROM farmacia f 
                     JOIN ciudad c ON f.id_ciudad = c.id_ciudad 
                     ORDER BY f.nombre");
$farmacias = $stmt->fetchAll();

// Obtener medicamentos para dropdown
$stmt = $pdo->query("SELECT * FROM medicamento ORDER BY nombre_comercial");
$medicamentos = $stmt->fetchAll();

// Obtener datos de distribución para editar
$distribucionEditar = null;
if ($accion === 'editar' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM distribucion WHERE id_distribucion = ?");
    $stmt->execute([$id]);
    $distribucionEditar = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Distribución - PharmacyDB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="module-container">
            <div class="module-header">
                <h2><i class="fas fa-truck"></i> Gestionar Distribución</h2>
                <a href="?accion=agregar" class="btn-action">
                    <i class="fas fa-plus"></i> Agregar Distribución
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
                        <?php echo $accion === 'agregar' ? 'Agregar Distribución' : 'Editar Distribución'; ?>
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="id_farmacia">Farmacia *</label>
                                <select id="id_farmacia" name="id_farmacia" required>
                                    <option value="">Seleccionar farmacia</option>
                                    <?php foreach ($farmacias as $farmacia): ?>
                                    <option value="<?php echo $farmacia['id_farmacia']; ?>"
                                        <?php echo ($distribucionEditar['id_farmacia'] ?? '') == $farmacia['id_farmacia'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($farmacia['nombre']); ?> - <?php echo htmlspecialchars($farmacia['ciudad_nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="id_medicamento">Medicamento *</label>
                                <select id="id_medicamento" name="id_medicamento" required>
                                    <option value="">Seleccionar medicamento</option>
                                    <?php foreach ($medicamentos as $medicamento): ?>
                                    <option value="<?php echo $medicamento['id_medicamento']; ?>"
                                        <?php echo ($distribucionEditar['id_medicamento'] ?? '') == $medicamento['id_medicamento'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($medicamento['nombre_comercial']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="precio">Precio ($) *</label>
                            <input type="number" id="precio" name="precio" required 
                                   step="0.01" min="0"
                                   value="<?php echo $distribucionEditar['precio'] ?? ''; ?>"
                                   placeholder="Ej: 850.50">
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="distribuciones.php" class="btn btn-cancel">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Lista de Distribuciones -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Farmacia</th>
                        <th>Ciudad</th>
                        <th>Medicamento</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($distribuciones as $distribucion): ?>
                    <tr>
                        <td><?php echo $distribucion['id_distribucion']; ?></td>
                        <td><?php echo htmlspecialchars($distribucion['farmacia_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($distribucion['ciudad_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($distribucion['medicamento_nombre']); ?></td>
                        <td>$<?php echo number_format($distribucion['precio'], 2); ?></td>
                        <td class="action-buttons">
                            <a href="?accion=editar&id=<?php echo $distribucion['id_distribucion']; ?>" 
                               class="btn-edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="?accion=eliminar&id=<?php echo $distribucion['id_distribucion']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('¿Eliminar esta distribución?')">
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