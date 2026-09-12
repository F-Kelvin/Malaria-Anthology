<?php

declare(strict_types=1);

$page_title = 'Home';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';

try {
    $stmt = $pdo->query(
        "SELECT id, name, slug, description
         FROM categories
         ORDER BY id ASC"
    );

    $categories = $stmt->fetchAll();

} catch (PDOException $e) {
    $categories = [];
}

?>

<main>

    <!-- Hero Section -->
    <section class="hero-section">

        <div class="site-container">

            <h1>Malaria Anthology</h1>

            <p>
                Explore information, research, case studies and
                experiences relating to malaria.
            </p>

        </div>

    </section>


    <!-- Categories Section -->
    <section
        id="categories"
        class="categories-section"
    >

        <div class="site-container">

            <h2>Explore the Anthology</h2>

            <?php if (!empty($categories)): ?>

                <div class="category-grid">

                    <?php foreach ($categories as $category): ?>

                        <article class="category-card">

                            <h3>
                                <?= e($category['name']) ?>
                            </h3>

                            <?php if (!empty($category['description'])): ?>

                                <p>
                                    <?= e($category['description']) ?>
                                </p>

                            <?php endif; ?>

                            <a
                                href="<?= e(
                                    base_url(
                                        'pages/category.php?slug=' .
                                        urlencode($category['slug'])
                                    )
                                ) ?>"
                            >
                                Explore category
                            </a>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <p>
                    No categories are currently available.
                </p>

            <?php endif; ?>

        </div>

    </section>

</main>

<?php

require_once __DIR__ . '/includes/footer.php';