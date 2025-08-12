<header class="header">
    <div class="container">
        <nav class="navbar">
            <div class="nav-brand">
                <a href="<?= getBaseUrl() ?>index.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span>EventHub</span>
                </a>
            </div>
            
            <div class="nav-menu" id="nav-menu">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>index.php" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>events.php" class="nav-link">Events</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>about.php" class="nav-link">About</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= getBaseUrl() ?>contact.php" class="nav-link">Contact</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= getBaseUrl() ?>user/dashboard.php">Dashboard</a></li>
                                <li><a href="<?= getBaseUrl() ?>user/profile.php">Profile</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><a href="<?= getBaseUrl() ?>admin/dashboard.php">Admin Panel</a></li>
                                <?php endif; ?>
                                <li><a href="<?= getBaseUrl() ?>logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="<?= getBaseUrl() ?>login.php" class="nav-link">Login</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= getBaseUrl() ?>register.php" class="btn btn-primary">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="nav-toggle" id="nav-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </nav>
    </div>
</header> 