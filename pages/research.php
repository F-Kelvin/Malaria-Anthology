<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Research';

$stmt = $pdo->query(
    "SELECT
        a.id,
        a.title,
        a.slug,
        a.summary,
        a.published_at,
        c.name AS category_name
     FROM articles a
     LEFT JOIN categories c ON c.id = a.category_id
     WHERE a.status = 'published'
     AND a.content_type = 'research'
     ORDER BY a.published_at DESC, a.id DESC"
);

$articles = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';

?>

<main>

    <section class="page-header">

        <div class="site-container">

            <h1>Research</h1>

            <p>
                Explore published research and evidence relating to malaria.
            </p>

        </div>

    </section>


    <section class="article-list-section">

        <div class="site-container">

            <h2>Research Articles</h2>

            <?php if (!empty($articles)): ?>

                <div class="article-grid">

                    <?php foreach ($articles as $article): ?>

                        <article class="article-card">

                            <?php if (!empty($article['category_name'])): ?>

                                <p>
                                    <?= e($article['category_name']) ?>
                                </p>

                            <?php endif; ?>


                            <h3>
                                <?= e($article['title']) ?>
                            </h3>


                            <?php if (!empty($article['summary'])): ?>

                                <p>
                                    <?= e($article['summary']) ?>
                                </p>

                            <?php endif; ?>


                            <?php if (!empty($article['published_at'])): ?>

                                <small>
                                    Published
                                    <?= e(
                                        date(
                                            'M d, Y',
                                            strtotime($article['published_at'])
                                        )
                                    ) ?>
                                </small>

                            <?php endif; ?>


                            <p>
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
                            </p>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <p>
                    No research articles have been published yet.
                </p>

            <?php endif; ?>

        </div>

    </section>

</main>

<?php

require_once __DIR__ . '/../includes/footer.php';