<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Manage Articles';

$stmt = $pdo->query(
    "SELECT
        a.id,
        a.title,
        a.slug,
        a.author,
        a.status,
        a.published_at,
        c.name AS category_name
     FROM articles a
     LEFT JOIN categories c ON c.id = a.category_id
     ORDER BY a.id DESC"
);

$articles = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main>

    <section class="admin-page">

        <div class="site-container">

            <div class="admin-page-header">

                <div>
                    <h1>Manage Articles</h1>
                    <p>View and manage the Malaria Anthology articles.</p>
                </div>

                <a href="article-create.php">
                    Create Article
                </a>

            </div>

            <?php if (!$articles): ?>

                <p>No articles have been created yet.</p>

            <?php else: ?>

                <div class="article-table-wrapper">

                    <table>

                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Status</th>
                                <th>Published</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($articles as $article): ?>

                            <tr>

                                <td>
                                    <?= e($article['title']) ?>
                                </td>

                                <td>
                                    <?= e($article['category_name'] ?? 'Uncategorized') ?>
                                </td>

                                <td>
                                    <?= e($article['author']) ?>
                                </td>

                                <td>
                                    <?php
                                        $status = $article['status'];

                                        if ($status === 'published') {
                                            $status_label = 'Published';
                                        } elseif ($status === 'draft') {
                                            $status_label = 'Draft';
                                        } elseif ($status === 'archived') {
                                            $status_label = 'Archived';
                                        } else {
                                            $status_label = ucfirst($status);
                                        }
                                    ?>

                                    <?= e($status_label) ?>
                                </td>

                                <td>
                                    <?php if ($article['published_at']): ?>

                                        <?= e(date('M d, Y', strtotime($article['published_at']))) ?>

                                    <?php else: ?>

                                        —

                                    <?php endif; ?>
                                </td>

                                <td>

                                    <a href="../pages/article.php?id=<?= (int) $article['id'] ?>">
                                        View
                                    </a>

                                    &nbsp;|&nbsp;

                                    <a href="article-edit.php?id=<?= (int) $article['id'] ?>">
                                        Edit
                                    </a>

                                    &nbsp;|&nbsp;

                                    <a
                                        href="article-archive.php?id=<?= (int) $article['id'] ?>"
                                        onclick="return confirm('Are you sure you want to archive this article?');"
                                    >
                                        Archive
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>