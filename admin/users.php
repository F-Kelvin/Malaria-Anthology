<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

/*
 * Only administrators can manage users.
 */
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

$page_title = 'Manage Users';

$message = '';
$error = '';

/*
 * Handle POST actions
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /*
     * Create user
     */
    if ($action === 'create') {

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = $_POST['role'] ?? 'editor';

        if ($username === '' || $email === '' || $password === '') {

            $error = 'Username, email and password are required.';

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = 'Please enter a valid email address.';

        } elseif (strlen($password) < 8) {

            $error = 'Password must be at least 8 characters long.';

        } elseif (!in_array($role, ['admin', 'editor', 'user'], true)) {

            $error = 'Invalid user role.';

        } else {

            try {

                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $pdo->prepare("
                    INSERT INTO users
                    (
                        username,
                        email,
                        password_hash,
                        role,
                        status
                    )
                    VALUES
                    (
                        :username,
                        :email,
                        :password_hash,
                        :role,
                        1
                    )
                ");

                $stmt->execute([
                    ':username'     => $username,
                    ':email'        => $email,
                    ':password_hash' => $passwordHash,
                    ':role'         => $role
                ]);

                $message = 'User created successfully.';

            } catch (PDOException $e) {

                if ($e->getCode() === '23000') {

                    $error =
                        'That username or email address already exists.';

                } else {

                    $error =
                        'Database error: ' . $e->getMessage();
                }
            }
        }
    }

    /*
     * Change user status
     */
    if ($action === 'status') {

        $id = filter_input(
            INPUT_POST,
            'id',
            FILTER_VALIDATE_INT
        );

        $status = filter_input(
            INPUT_POST,
            'status',
            FILTER_VALIDATE_INT
        );

        if (!$id || !in_array($status, [0, 1], true)) {

            $error = 'Invalid user information.';

        } elseif ($id === (int)($_SESSION['user_id'] ?? 0)) {

            $error = 'You cannot deactivate your own account.';

        } else {

            try {

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET status = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $status,
                    $id
                ]);

                $message = $status === 1
                    ? 'User activated successfully.'
                    : 'User deactivated successfully.';

            } catch (PDOException $e) {

                $error =
                    'Database error: ' . $e->getMessage();
            }
        }
    }

    /*
     * Change user role
     */
    if ($action === 'role') {

        $id = filter_input(
            INPUT_POST,
            'id',
            FILTER_VALIDATE_INT
        );

        $role = $_POST['role'] ?? '';

        if (!$id || !in_array(
            $role,
            ['admin', 'editor', 'user'],
            true
        )) {

            $error = 'Invalid user information.';

        } elseif ($id === (int)($_SESSION['user_id'] ?? 0)
            && $role !== 'admin') {

            $error =
                'You cannot remove administrator privileges from your own account.';

        } else {

            try {

                $stmt = $pdo->prepare("
                    UPDATE users
                    SET role = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $role,
                    $id
                ]);

                $message = 'User role updated successfully.';

            } catch (PDOException $e) {

                $error =
                    'Database error: ' . $e->getMessage();
            }
        }
    }
}

/*
 * Get users
 */
$stmt = $pdo->query("
    SELECT
        id,
        username,
        email,
        role,
        status,
        created_at,
        updated_at
    FROM users
    ORDER BY id ASC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Manage Users | Malaria Anthology
    </title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
        }

        h1 {
            margin-top: 0;
        }

        h2 {
            margin-top: 35px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
        }

        .message {
            background: #dff5e1;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .error {
            background: #ffdede;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .create-button {
            margin-top: 15px;
            background: #222;
            color: #fff;
        }

        .activate-button {
            background: #18794e;
            color: #fff;
        }

        .deactivate-button {
            background: #b42318;
            color: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #f0f2f4;
        }

        .inline-form {
            display: inline-block;
            margin: 0;
        }

        .role-select {
            width: auto;
            min-width: 100px;
            padding: 7px;
        }

    </style>

</head>

<body>

<div class="container">

    <a
        class="back"
        href="dashboard.php"
    >
        ← Back to Dashboard
    </a>

    <h1>Manage Users</h1>

    <p>
        Manage administrator, editor and user accounts.
    </p>

    <?php if ($message): ?>

        <div class="message">
            <?= e($message) ?>
        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="error">
            <?= e($error) ?>
        </div>

    <?php endif; ?>


    <h2>Add New User</h2>

    <form method="POST">

        <input
            type="hidden"
            name="action"
            value="create"
        >

        <label for="username">
            Username
        </label>

        <input
            type="text"
            name="username"
            id="username"
            required
        >

        <label for="email">
            Email
        </label>

        <input
            type="email"
            name="email"
            id="email"
            required
        >

        <label for="password">
            Password
        </label>

        <input
            type="password"
            name="password"
            id="password"
            minlength="8"
            required
        >

        <label for="role">
            Role
        </label>

        <select
            name="role"
            id="role"
        >

            <option value="editor">
                Editor
            </option>

            <option value="admin">
                Administrator
            </option>

            <option value="user">
                User
            </option>

        </select>

        <button
            type="submit"
            class="create-button"
        >
            Create User
        </button>

    </form>


    <h2>Existing Users</h2>

    <?php if (!empty($users)): ?>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($users as $user): ?>

                    <tr>

                        <td>
                            <?= (int)$user['id'] ?>
                        </td>

                        <td>
                            <strong>
                                <?= e($user['username']) ?>
                            </strong>
                        </td>

                        <td>
                            <?= e($user['email']) ?>
                        </td>

                        <td>

                            <form
                                method="POST"
                                class="inline-form"
                            >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="role"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$user['id'] ?>"
                                >

                                <select
                                    name="role"
                                    class="role-select"
                                    onchange="this.form.submit()"
                                >

                                    <option
                                        value="admin"
                                        <?= $user['role'] === 'admin'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Admin
                                    </option>

                                    <option
                                        value="editor"
                                        <?= $user['role'] === 'editor'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Editor
                                    </option>

                                    <option
                                        value="user"
                                        <?= $user['role'] === 'user'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        User
                                    </option>

                                </select>

                            </form>

                        </td>

                        <td>

                            <?php if ((int)$user['status'] === 1): ?>

                                Active

                            <?php else: ?>

                                Inactive

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= e(
                                date(
                                    'M d, Y',
                                    strtotime($user['created_at'])
                                )
                            ) ?>
                        </td>

                        <td>

                            <?php if (
                                (int)$user['id']
                                !==
                                (int)($_SESSION['user_id'] ?? 0)
                            ): ?>

                                <form
                                    method="POST"
                                    class="inline-form"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="status"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$user['id'] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="<?= (int)$user['status'] === 1
                                            ? 0
                                            : 1
                                        ?>"
                                    >

                                    <?php if (
                                        (int)$user['status'] === 1
                                    ): ?>

                                        <button
                                            type="submit"
                                            class="deactivate-button"
                                        >
                                            Deactivate
                                        </button>

                                    <?php else: ?>

                                        <button
                                            type="submit"
                                            class="activate-button"
                                        >
                                            Activate
                                        </button>

                                    <?php endif; ?>

                                </form>

                            <?php else: ?>

                                Current Account

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p>
            No users have been created yet.
        </p>

    <?php endif; ?>

</div>

</body>

</html>