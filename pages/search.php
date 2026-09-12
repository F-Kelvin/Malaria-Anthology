<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Search';

$query = trim($_GET['q'] ?? '');

$articles = [];

if ($query !== '') {

    $searchTerm = '%' . $query . '%';

    $stmt = $pdo->prepare(
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
         AND (
             a.title LIKE :title
             OR a.summary LIKE :summary
             OR a.content LIKE :content
             OR c.name LIKE :category
         )
         ORDER BY a.published_at DESC, a.id DESC"
    );

    $stmt->execute([
        ':title'    => $searchTerm,
        ':summary'  => $searchTerm,
        ':content'  => $searchTerm,
        ':category' => $searchTerm
    ]);

    $articles = $stmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navigation.php';

?>

<main>

    <section class="page-header">

        <div class="site-container">

            <h1>Search</h1>

            <p>
                Search the Malaria Anthology for published articles and information.
            </p>

        </div>

    </section>


    <section class="search-section">

        <div class="site-container">

            <form method="GET" action="search.php">

                <label for="search">
                    Search articles
                </label>

                <input
                    type="search"
                    name="q"
                    id="search"
                    value="<?= e($query) ?>"
                    placeholder="Search for malaria..."
                    required
                >

                <button type="submit">
                    Search
                </button>

            </form>


            <?php if ($query !== ''): ?>

                <h2>
                    Search Results
                </h2>

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
                        No published articles were found for
                        <?= e($query) ?>.
                    </p>

                <?php endif; ?>

            <?php endif; ?>

        </div>

    </section>

</main>

<?php

require_once __DIR__ . '/../includes/footer.php';
?>
```
