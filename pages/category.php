<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
    http_response_code(400);
    exit('Invalid category.');
}

/*
 * Find the requested category.
 */
$stmt = $pdo->prepare(
    "SELECT id, name, slug, description
     FROM categories
     WHERE slug = ?
     LIMIT 1"
);

$stmt->execute([$slug]);

$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    exit('Category not found.');
}

/*
 * Find articles belonging to this category.
 *
 * We currently expect the category relationship
 * to be represented by articles.category_id.
 */
$stmt = $pdo->prepare(
    "SELECT id, title, slug, summary, created_at
     FROM articles
     WHERE category_id = ?
     AND status = 'published'
     ORDER BY created_at DESC, id DESC"
);

$stmt->execute([$category['id']]);

$articles = $stmt->fetchAll();

$page_title = $category['name'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';

?>

<main>

    <section class="category-header">

        <div class="site-container">

            <h1><?= e($category['name']) ?></h1>

            <?php if (!empty($category['description'])): ?>

                <p>
                    <?= e($category['description']) ?>
                </p>

            <?php endif; ?>

        </div>

    </section>


    <section class="category-articles">

        <div class="site-container">

            <h2>Articles</h2>

            <?php if (!empty($articles)): ?>

                <div class="article-grid">

                    <?php foreach ($articles as $article): ?>

                        <article class="article-card">

                            <h3>
                                <?= e($article['title']) ?>
                            </h3>

                            <?php if (!empty($article['summary'])): ?>
                                <p>
                                    <?= e($article['summary']) ?>
                                </p>
                            <?php endif; ?>

                            <a
                                href="<?= e(
                                    base_url(
                                        'pages/article.php?id=' .
                                        (int) $article['id']
                                    )
                                ) ?>"
                            >
                                Read article
                            </a>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <p>
                    There are currently no articles in this category.
                </p>

            <?php endif; ?>

        </div>

    </section>

</main>

<?php

require_once __DIR__ . '/../includes/footer.php';