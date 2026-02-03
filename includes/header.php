<?php
// Verificar si el usuario está logueado para el encabezado del dashboard
if (basename($_SERVER['PHP_SELF']) !== 'dashboard.php' && 
    !strpos($_SERVER['PHP_SELF'], 'modules/')) {
    return;
}
?>
<header class="header">
    <div class="header-container">
        <div class="logo">
            <h1><i class="fas fa-prescription-bottle-alt"></i> PharmacyDB</h1>
        </div>
        <nav class="nav">
            <a href="../dashboard.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Panel
            </a>
            <span class="user-info">
                <i class="fas fa-user-circle"></i> 
                <?php echo htmlspecialchars($_SESSION['username']); ?>
            </span>
            <a href="login.php?logout=true" class="nav-link logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </nav>
    </div>
</header>

<?php
// Manejar cierre de sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>