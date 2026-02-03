<?php
require_once '../config/database.php';
requireLogin();

$pdo = getDBConnection();
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? 0;

// Manejar envíos de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'] ?? '';
    
    if ($accion === 'agregar') {
        $stmt = $pdo->prepare("INSERT INTO drogas (descripcion) VALUES (?)");
        $stmt->execute([$descripcion]);
        $mensaje = "Droga agregada exitosamente";
    } elseif ($accion === 'editar' && $id) {
        $stmt = $pdo->prepare("UPDATE drogas SET descripcion = ? WHERE id_drogas = ?");
        $stmt->execute([$descripcion, $id]);
        $mensaje = "Droga actualizada exitosamente";
    }
}

// Manejar eliminación
if ($accion === 'eliminar' && $id) {
    $stmt = $pdo->prepare("DELETE FROM drogas WHERE id_drogas = ?");
    $stmt->execute([$id]);
    $mensaje = "Droga eliminada exitosamente";
}

// Obtener drogas para listar
$stmt = $pdo->query("SELECT d.*, 
                     (SELECT COUNT(*) FROM composicion c WHERE c.id_drogas = d.id_drogas) as uso_count 
                     FROM drogas d 
                     ORDER BY d.descripcion");
$drogas = $stmt->fetchAll();

// Obtener datos de droga para editar
$drogaEditar = null;
if ($accion === 'editar' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM drogas WHERE id_drogas = ?");
    $stmt->execute([$id]);
    $drogaEditar = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Drogas - PharmacyDB</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="module-container">
            <div class="module-header">
                <h2><i class="fas fa-flask"></i> Gestionar Drogas</h2>
                <a href="?accion=agregar" class="btn-action">
                    <i class="fas fa-plus"></i> Agregar Droga
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
                        <?php echo $accion === 'agregar' ? 'Agregar Droga' : 'Editar Droga'; ?>
                    </h3>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="descripcion">Descripción *</label>
                            <textarea id="descripcion" name="descripcion" required 
                                      rows="4" placeholder="Descripción detallada de la droga"><?php echo $drogaEditar['descripcion'] ?? ''; ?></textarea>
                            <small class="form-text">Ej: Paracetamol 500mg, Ibuprofeno 400mg, etc.</small>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="drogas.php" class="btn btn-cancel">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <!-- Lista de Drogas -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Usos en Medicamentos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($drogas as $droga): ?>
                    <tr>
                        <td><?php echo $droga['id_drogas']; ?></td>
                        <td><?php echo htmlspecialchars($droga['descripcion']); ?></td>
                        <td>
                            <span class="badge <?php echo $droga['uso_count'] > 0 ? 'badge-success' : 'badge-secondary'; ?>">
                                <?php echo $droga['uso_count']; ?> medicamento(s)
                            </span>
                        </td>
                        <td class="action-buttons">
                            <a href="?accion=editar&id=<?php echo $droga['id_drogas']; ?>" 
                               class="btn-edit">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="?accion=eliminar&id=<?php echo $droga['id_drogas']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('¿Eliminar esta droga?')">
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
    
    <style>
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .badge-success {
        background: #c6f6d5;
        color: #22543d;
    }
    
    .badge-secondary {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    .text-muted {
        color: #718096;
    }
    
    textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
        resize: vertical;
        font-family: inherit;
    }
    
    textarea:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .form-text {
        display: block;
        margin-top: 5px;
        color: #718096;
        font-size: 0.85rem;
    }
    </style>
</body>
</html>