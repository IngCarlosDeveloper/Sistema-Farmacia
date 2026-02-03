<?php
require_once '../config/database.php';
requireLogin();

$pdo = getDBConnection();
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? 0;

// Manejar envíos de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $calle = $_POST['calle'] ?? '';
    $numero = $_POST['numero'] ?? 0;
    $codigo_postal = $_POST['codigo_postal'] ?? '';
    $id_ciudad = $_POST['id_ciudad'] ?? 0;
    
    if ($accion === 'agregar') {
        $stmt = $pdo->prepare("INSERT INTO propietario (dni, nombre, calle, numero, codigo_postal, id_ciudad) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$dni, $nombre, $calle, $numero, $codigo_postal, $id_ciudad]);
        $mensaje = "Propietario agregado exitosamente";
    } elseif ($accion === 'editar' && $id) {
        $stmt = $pdo->prepare("UPDATE propietario SET dni = ?, nombre = ?, calle = ?, 
                               numero = ?, codigo_postal = ?, id_ciudad = ? WHERE id_propietario = ?");
        $stmt->execute([$dni, $nombre, $calle, $numero, $codigo_postal, $id_ciudad, $id]);
        $mensaje = "Propietario actualizado exitosamente";
    }
}

// Manejar eliminación
if ($accion === 'eliminar' && $id) {
    $stmt = $pdo->prepare("DELETE FROM propietario WHERE id_propietario = ?");
    $stmt->execute([$id]);
    $mensaje = "Propietario eliminado exitosamente";
}

// Obtener propietarios para listar
$stmt = $pdo->query("SELECT p.*, c.nombre as ciudad_nombre 
                     FROM propietario p 
                     LEFT JOIN ciudad c ON p.id_ciudad = c.id_ciudad 
                     ORDER BY p.nombre");
$propietarios = $stmt->fetchAll();

// Obtener ciudades para dropdown
$stmt = $pdo->query("SELECT * FROM ciudad ORDER BY nombre");
$ciudades = $stmt->fetchAll();

// Obtener datos de propietario para editar
$propietarioEditar = null;
if ($accion === 'editar' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM propietario WHERE id_propietario = ?");
    $stmt->execute([$id]);
    $propietarioEditar = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Propietarios - PharmacyDB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="module-container">
            <div class="module-header">
                <h2><i class="fas fa-user-tie"></i> Gestionar Propietarios</h2>
                <a href="?accion=agregar" class="btn-action">
                    <i class="fas fa-plus"></i> Agregar Propietario
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
                        <?php echo $accion === 'agregar' ? 'Agregar Propietario' : 'Editar Propietario'; ?>
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="dni">DNI *</label>
                                <input type="text" id="dni" name="dni" required 
                                       value="<?php echo $propietarioEditar['dni'] ?? ''; ?>"
                                       placeholder="Ej: 30123456">
                            </div>
                            
                            <div class="form-group">
                                <label for="nombre">Nombre Completo *</label>
                                <input type="text" id="nombre" name="nombre" required 
                                       value="<?php echo $propietarioEditar['nombre'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="calle">Calle *</label>
                                <input type="text" id="calle" name="calle" required 
                                       value="<?php echo $propietarioEditar['calle'] ?? ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="numero">Número *</label>
                                <input type="number" id="numero" name="numero" required 
                                       value="<?php echo $propietarioEditar['numero'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="codigo_postal">Código Postal *</label>
                                <input type="text" id="codigo_postal" name="codigo_postal" required 
                                       value="<?php echo $propietarioEditar['codigo_postal'] ?? ''; ?>"
                                       placeholder="Ej: 1001">
                            </div>
                            
                            <div class="form-group">
                                <label for="id_ciudad">Ciudad *</label>
                                <select id="id_ciudad" name="id_ciudad" required>
                                    <option value="">Seleccionar ciudad</option>
                                    <?php foreach ($ciudades as $ciudad): ?>
                                    <option value="<?php echo $ciudad['id_ciudad']; ?>"
                                        <?php echo ($propietarioEditar['id_ciudad'] ?? '') == $ciudad['id_ciudad'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ciudad['nombre']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="propietarios.php" class="btn btn-cancel">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Lista de Propietarios -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>DNI</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Código Postal</th>
                        <th>Ciudad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($propietarios as $propietario): ?>
                    <tr>
                        <td><?php echo $propietario['id_propietario']; ?></td>
                        <td><?php echo htmlspecialchars($propietario['dni']); ?></td>
                        <td><?php echo htmlspecialchars($propietario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($propietario['calle']) . ' ' . $propietario['numero']; ?></td>
                        <td><?php echo htmlspecialchars($propietario['codigo_postal']); ?></td>
                        <td><?php echo htmlspecialchars($propietario['ciudad_nombre']); ?></td>
                        <td class="action-buttons">
                            <a href="?accion=editar&id=<?php echo $propietario['id_propietario']; ?>" 
                               class="btn-edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="?accion=eliminar&id=<?php echo $propietario['id_propietario']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('¿Eliminar este propietario?')">
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