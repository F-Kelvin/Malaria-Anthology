<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

$page_title = 'Manage Categories';

$message = '';
$error = '';

/*
 * Create category
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $slug === '') {

            $error = 'Category name and slug are required.';

        } else {

            try {

                $stmt = $pdo->prepare("
                    INSERT INTO categories
                    (name, slug, description)
                    VALUES
                    (:name, :slug, :description)
                ");

                $stmt->execute([
                    ':name' => $name,
                    ':slug' => $slug,
                    ':description' => $description !== ''
                        ? $description
                        : null
                ]);

                $message = 'Category created successfully.';

            } catch (PDOException $e) {

                if ($e->getCode() === '23000') {

                    $error = 'That category name or slug already exists.';

                } else {

                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }

    /*
     * Delete category
     */
    if ($action === 'delete') {

        $id = filter_input(
            INPUT_POST,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$id) {

            $error = 'Invalid category ID.';

        } else {

            try {

                /*
                 * Do not delete a category that still has articles.
                 */
                $stmt = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM articles
                    WHERE category_id = ?
                ");

                $stmt->execute([$id]);

                $articleCount = (int)$stmt->fetchColumn();

                if ($articleCount > 0) {

                    $error =
                        'This category cannot be deleted because it has '
                        . $articleCount
                        . ' article(s) assigned to it.';

                } else {

                    $stmt = $pdo->prepare("
                        DELETE FROM categories
                        WHERE id = ?
                    ");

                    $stmt->execute([$id]);

                    $message = 'Category deleted successfully.';
                }

            } catch (PDOException $e) {

                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

/*
 * Get categories
 */
$stmt = $pdo->query("
    SELECT
        id,
        name,
        slug,
        description,
        created_at
    FROM categories
    ORDER BY id ASC
");

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        Manage Categories | Malaria Anthology
    </title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1100px;
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

        form {
            margin-top: 15px;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 11px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        button {
            margin-top: 15px;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .create-button {
            background: #222;
            color: #fff;
        }

        .delete-button {
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
            vertical-align: top;
        }

        th {
            background: #f0f2f4;
        }

        .description {
            max-width: 350px;
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

    <h1>Manage Categories</h1>

    <p>
        Create and manage the categories used throughout the
        Malaria Anthology.
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


    <h2>Add New Category</h2>

    <form method="POST">

        <input
            type="hidden"
            name="action"
            value="create"
        >

        <label for="name">
            Category Name
        </label>

        <input
            type="text"
            name="name"
            id="name"
            placeholder="Example: Malaria Prevention"
            required
        >

        <label for="slug">
            Slug
        </label>

        <input
            type="text"
            name="slug"
            id="slug"
            placeholder="example-malaria-prevention"
            required
        >

        <label for="description">
            Description
        </label>

        <textarea
            name="description"
            id="description"
            placeholder="Brief description of this category."
        ></textarea>

        <button
            type="submit"
            class="create-button"
        >
            Create Category
        </button>

    </form>


    <h2>Existing Categories</h2>

    <?php if (!empty($categories)): ?>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($categories as $category): ?>

                    <tr>

                        <td>
                            <?= (int)$category['id'] ?>
                        </td>

                        <td>
                            <strong>
                                <?= e($category['name']) ?>
                            </strong>
                        </td>

                        <td>
                            <?= e($category['slug']) ?>
                        </td>

                        <td class="description">
                            <?= e($category['description'] ?? '') ?>
                        </td>

                        <td>

                            <form
                                method="POST"
                                onsubmit="return confirm(
                                    'Are you sure you want to delete this category?'
                                );"
                            >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="delete"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$category['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="delete-button"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p>
            No categories have been created yet.
        </p>

    <?php endif; ?>

</div>

</body>

</html>