<?php
require_once 'config/database.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - PharmacyDB</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="dashboard">
            <h1><i class="fas fa-tachometer-alt"></i> Panel de Control</h1>
            <p class="welcome">¡Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            
            <div class="modules-grid">
                <!-- Gestión de Ciudades -->
                <a href="modules/ciudades.php" class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-city"></i>
                    </div>
                    <h3>Ciudades</h3>
                    <p>Gestionar ciudades y provincias</p>
                </a>
                
                <!-- Gestión de Farmacias -->
                <a href="modules/farmacias.php" class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-clinic-medical"></i>
                    </div>
                    <h3>Farmacias</h3>
                    <p>Gestionar farmacias</p>
                </a>
                
                <!-- Gestión de Propietarios -->
                <a href="modules/propietarios.php" class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>Propietarios</h3>
                    <p>Gestionar dueños de farmacias</p>
                </a>
                
                <!-- Gestión de Medicamentos -->
                <a href="modules/medicamentos.php" class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-pills"></i>
                    </div>
                    <h3>Medicamentos</h3>
                    <p>Gestionar medicamentos</p>
                </a>
                
                <!-- Gestión de Drogas -->
                <a href="modules/drogas.php" class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-flask"></i>
                    </div>
                    <h3>Drogas</h3>
                    <p>Gestionar sustancias activas</p>
                </a>
                
                <!-- Gestión de Composición -->
                <a href="modules/composiciones.php" class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-atom"></i>
                    </div>
                    <h3>Composiciones</h3>
                    <p>Gestionar composición de medicamentos</p>
                </a>
                
                <!-- Gestión de Distribución -->
                <a href="modules/distribuciones.php" class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Distribuciones</h3>
                    <p>Gestionar distribución de medicamentos</p>
                </a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>