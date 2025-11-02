<header class="admin-header">
    <div class="header-container">
        <div class="logo">
            <h2>🎰 BINGO VIRTUAL</h2>
            <span class="admin-badge">ADMIN</span>
        </div>

        <nav class="admin-nav">
            <a href="index.php">Dashboard</a>
            <a href="sorteos.php">Sorteos</a>
            <a href="cartones.php">Cartones</a>
            <a href="pagos-pendientes.php">Pagos</a>
            <a href="jugadores.php">Jugadores</a>
            <a href="ganadores.php">Ganadores</a>
        </nav>

        <div class="user-menu">
            <span class="user-name">👤 <?php echo $_SESSION['nombre']; ?></span>
            <a href="../logout.php" class="btn btn-logout">Cerrar Sesión</a>
        </div>
    </div>
</header>
