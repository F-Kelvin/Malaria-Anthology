<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    exit('Invalid article ID.');
}

/*
 * Get the article
 */
$stmt = $pdo->prepare("
    SELECT
        id,
        category_id,
        content_type,
        title,
        slug,
        summary,
        content,
        author,
        status,
        published_at
    FROM articles
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    exit('Article not found.');
}

/*
 * Get categories
 */
$stmt = $pdo->query("
    SELECT id, name
    FROM categories
    ORDER BY id ASC
");

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

/*
 * Process update
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $category_id  = (int)($_POST['category_id'] ?? 0);
    $content_type = $_POST['content_type'] ?? 'research';
    $title        = trim($_POST['title'] ?? '');
    $slug         = trim($_POST['slug'] ?? '');
    $summary      = trim($_POST['summary'] ?? '');
    $content      = trim($_POST['content'] ?? '');
    $author       = trim($_POST['author'] ?? '');
    $status       = $_POST['status'] ?? 'draft';

    $valid_content_types = [
        'research',
        'case_study',
        'human_experience'
    ];

    if (
        $category_id <= 0 ||
        $title === '' ||
        $slug === '' ||
        $content === ''
    ) {

        $error = 'Please fill in the category, title, slug, and content.';

    } elseif (!in_array($content_type, $valid_content_types, true)) {

        $error = 'Invalid content type.';

    } elseif (!in_array($status, ['draft', 'published', 'archived'], true)) {

        $error = 'Invalid article status.';

    } else {

        try {

            /*
             * Preserve the original publication date if
             * the article was already published.
             */
            if ($status === 'published') {

                $published_at = $article['published_at']
                    ?: date('Y-m-d H:i:s');

            } else {

                $published_at = null;
            }

            $stmt = $pdo->prepare("
                UPDATE articles
                SET
                    category_id = :category_id,
                    content_type = :content_type,
                    title = :title,
                    slug = :slug,
                    summary = :summary,
                    content = :content,
                    author = :author,
                    status = :status,
                    published_at = :published_at
                WHERE id = :id
            ");

            $stmt->execute([
                ':category_id'  => $category_id,
                ':content_type' => $content_type,
                ':title'        => $title,
                ':slug'         => $slug,
                ':summary'      => $summary,
                ':content'      => $content,
                ':author'       => $author,
                ':status'       => $status,
                ':published_at' => $published_at,
                ':id'           => $id
            ]);

            $success = 'Article updated successfully.';

            /*
             * Refresh the article data so the form
             * displays the saved values.
             */
            $stmt = $pdo->prepare("
                SELECT
                    id,
                    category_id,
                    content_type,
                    title,
                    slug,
                    summary,
                    content,
                    author,
                    status,
                    published_at
                FROM articles
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([$id]);

            $article = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            if ($e->getCode() === '23000') {

                $error = 'That slug already exists. Please use a different slug.';

            } else {

                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

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
        Edit Article | Malaria Anthology
    </title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        h1 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 18px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        textarea {
            min-height: 180px;
            resize: vertical;
        }

        .success {
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

        button {
            margin-top: 25px;
            padding: 12px 22px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="container">

    <a
        class="back"
        href="articles.php"
    >
        ← Back to Articles
    </a>

    <h1>Edit Article</h1>

    <?php if ($success): ?>

        <div class="success">
            <?= e($success) ?>
        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="error">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label for="category_id">
            Category
        </label>

        <select
            name="category_id"
            id="category_id"
            required
        >

            <?php foreach ($categories as $category): ?>

                <option
                    value="<?= (int)$category['id'] ?>"
                    <?= (int)$article['category_id'] === (int)$category['id']
                        ? 'selected'
                        : ''
                    ?>
                >

                    <?= e($category['name']) ?>

                </option>

            <?php endforeach; ?>

        </select>


        <label for="content_type">
            Content Type
        </label>

        <select
            name="content_type"
            id="content_type"
            required
        >

            <option
                value="research"
                <?= ($article['content_type'] ?? 'research') === 'research'
                    ? 'selected'
                    : ''
                ?>
            >
                Research
            </option>

            <option
                value="case_study"
                <?= ($article['content_type'] ?? '') === 'case_study'
                    ? 'selected'
                    : ''
                ?>
            >
                Case Study
            </option>

            <option
                value="human_experience"
                <?= ($article['content_type'] ?? '') === 'human_experience'
                    ? 'selected'
                    : ''
                ?>
            >
                Human Experience
            </option>

        </select>


        <label for="title">
            Article Title
        </label>

        <input
            type="text"
            name="title"
            id="title"
            value="<?= e($article['title']) ?>"
            required
        >


        <label for="slug">
            Slug
        </label>

        <input
            type="text"
            name="slug"
            id="slug"
            value="<?= e($article['slug']) ?>"
            required
        >


        <label for="summary">
            Summary
        </label>

        <textarea
            name="summary"
            id="summary"
        ><?= e($article['summary'] ?? '') ?></textarea>


        <label for="content">
            Article Content
        </label>

        <textarea
            name="content"
            id="content"
            required
        ><?= e($article['content']) ?></textarea>


        <label for="author">
            Author
        </label>

        <input
            type="text"
            name="author"
            id="author"
            value="<?= e($article['author'] ?? '') ?>"
        >


        <label for="status">
            Status
        </label>

        <select
            name="status"
            id="status"
        >

            <option
                value="draft"
                <?= $article['status'] === 'draft'
                    ? 'selected'
                    : ''
                ?>
            >
                Draft
            </option>

            <option
                value="published"
                <?= $article['status'] === 'published'
                    ? 'selected'
                    : ''
                ?>
            >
                Published
            </option>

            <option
                value="archived"
                <?= $article['status'] === 'archived'
                    ? 'selected'
                    : ''
                ?>
            >
                Archived
            </option>

        </select>


        <button type="submit">
            Save Changes
        </button>

    </form>

</div>

</body>

</html>