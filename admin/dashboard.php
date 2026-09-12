<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Admin Dashboard';

require_once __DIR__ . '/../includes/header.php';
?>

<main>

    <section class="admin-dashboard">

        <div class="site-container">

            <h1>Admin Dashboard</h1>

            <p>
                Welcome,
                <strong><?= e($_SESSION['username']) ?></strong>.
            </p>

            <div class="admin-grid">

                <a href="articles.php" class="admin-card">
                    <h2>Articles</h2>
                    <p>Create, edit and manage anthology articles.</p>
                </a>

                <a href="categories.php" class="admin-card">
                    <h2>Categories</h2>
                    <p>Manage the anthology categories.</p>
                </a>

                <?php if ($_SESSION['role'] === 'admin'): ?>

                    <a href="users.php" class="admin-card">
                        <h2>Users</h2>
                        <p>Manage administrator and editor accounts.</p>
                    </a>

                <?php endif; ?>

                <a href="logout.php" class="admin-card">
                    <h2>Logout</h2>
                    <p>Sign out of the administration area.</p>
                </a>

            </div>

        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>