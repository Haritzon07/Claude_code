<header class="jugador-header">
    <div class="header-container">
        <div class="logo">
            <h2>🎰 BINGO VIRTUAL</h2>
        </div>

        <nav class="jugador-nav">
            <a href="index.php">Inicio</a>
            <a href="mis-cartones.php">Mis Cartones</a>
            <a href="historial.php">Historial</a>
        </nav>

        <div class="user-menu">
            <span class="user-name">👤 <?php echo $_SESSION['nombre']; ?></span>
            <a href="../logout.php" class="btn btn-logout">Salir</a>
        </div>
    </div>
</header>
