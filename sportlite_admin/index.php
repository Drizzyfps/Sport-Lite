<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Panel de Administración</h2>
        <div class="dashboard-grid">
            <a href="tournaments/eliminar.php" class="dashboard-item">
                <span class="icon-eliminar">🗑️</span>
                <span>Eliminar Torneos</span>
            </a>
            <a href="news/eliminar.php" class="dashboard-item">
                <span class="icon-eliminar">🗑️</span>
                <span>Eliminar Noticias</span>
            </a>
            <div class="dashboard-item">
                <span style="font-size: 2.5em;">📱</span>
                <span>Diseño Responsive</span>
            </div>
            <a href="tournaments/mis_torneos.php" class="dashboard-item">
                <span style="font-size: 2.5em;">🏆</span>
                <span>Mis Torneos</span>
            </a>
            <a href="tournaments/editar.php" class="dashboard-item">
                <span class="icon-editar">✏️</span>
                <span>Editar Torneos</span>
            </a>
            <a href="news/editar.php" class="dashboard-item">
                <span class="icon-editar">✏️</span>
                <span>Editar Noticias</span>
            </a>
            <div class="dashboard-item">
                <span class="icon-correo">📧</span>
                <span>Confirmación por Correo</span>
            </div>
            <a href="payments/" class="dashboard-item">
                <span class="icon-historial">🧾</span>
                <span>Historial de Pagos</span>
            </a>
            <a href="reservations/confirmar.php" class="dashboard-item">
                <span class="icon-confirmar">✅</span>
                <span>Confirmar Reservas</span>
            </a>
            <a href="reviews/" class="dashboard-item">
                <span class="icon-moderar">👁️</span>
                <span>Moderación de Reseñas</span>
            </a>
            <a href="tournaments/estadisticas.php" class="dashboard-item">
                <span class="icon-estadisticas">📊</span>
                <span>Estadísticas de Torneos</span>
            </a>
            <a href="users/roles.php" class="dashboard-item">
                <span class="icon-rol">🛠️</span>
                <span>Cambio de Rol</span>
            </a>
        </div>
    </div>
</body>
</html>