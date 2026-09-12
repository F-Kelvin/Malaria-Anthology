<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {

        $error = 'Please enter your username and password.';

    } else {

        $stmt = $pdo->prepare(
            "SELECT id, username, password_hash, role, status
             FROM users
             WHERE username = ?
             LIMIT 1"
        );

        $stmt->execute([$username]);

        $user = $stmt->fetch();

        if (
            $user &&
            (int) $user['status'] === 1 &&
            in_array($user['role'], ['admin', 'editor'], true) &&
            password_verify($password, $user['password_hash'])
        ) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: dashboard.php');
            exit;

        } else {

            $error = 'Invalid username or password.';

        }
    }
}

$page_title = 'Admin Login';

require_once __DIR__ . '/../includes/header.php';

?>

<main>

    <section class="login-page">

        <div class="site-container">

            <div class="login-box">

                <h1>Admin Login</h1>

                <?php if ($error !== ''): ?>

                    <div class="login-error">
                        <?= e($error) ?>
                    </div>

                <?php endif; ?>

                <form method="POST" action="">

                    <div class="form-group">

                        <label for="username">
                            Username
                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            autocomplete="username"
                        >

                    </div>

                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                        >

                    </div>

                    <button type="submit">
                        Login
                    </button>

                </form>

            </div>

        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>