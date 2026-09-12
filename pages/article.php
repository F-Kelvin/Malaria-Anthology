<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$article_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$article_id) {
    http_response_code(400);
    exit('Invalid article.');
}

/*
 * Retrieve the published article and its category.
 */
$stmt = $pdo->prepare(
    "SELECT
        a.id,
        a.category_id,
        a.title,
        a.slug,
        a.summary,
        a.content,
        a.author,
        a.published_at,
        a.created_at,
        c.name AS category_name,
        c.slug AS category_slug
     FROM articles a
     INNER JOIN categories c
        ON c.id = a.category_id
     WHERE a.id = ?
       AND a.status = 'published'
     LIMIT 1"
);

$stmt->execute([$article_id]);

$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    exit('Article not found.');
}

$page_title = $article['title'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';

?>

<main>

    <article class="article-page">

        <div class="site-container">

            <header class="article-header">

                <p class="article-category">
                    <a href="<?= e(
                        base_url(
                            'pages/category.php?slug=' .
                            urlencode($article['category_slug'])
                        )
                    ) ?>">
                        <?= e($article['category_name']) ?>
                    </a>
                </p>

                <h1><?= e($article['title']) ?></h1>

                <?php if (!empty($article['summary'])): ?>

                    <p class="article-summary">
                        <?= e($article['summary']) ?>
                    </p>

                <?php endif; ?>

                <div class="article-meta">

                    <?php if (!empty($article['author'])): ?>

                        <span>
                            By <?= e($article['author']) ?>
                        </span>

                    <?php endif; ?>

                    <?php if (!empty($article['published_at'])): ?>

                        <span>
                            Published
                            <?= e(
                                date(
                                    'F j, Y',
                                    strtotime($article['published_at'])
                                )
                            ) ?>
                        </span>

                    <?php endif; ?>

                </div>

            </header>


            <div class="article-content">

                <?= $article['content'] ?>

            </div>

        </div>

    </article>

</main>

<?php

require_once __DIR__ . '/../includes/footer.php';