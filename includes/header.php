<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? 'Malaria Anthology';

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
        <?= e($page_title) ?> | Malaria Anthology
    </title>

    <meta
        name="description"
        content="Malaria Anthology - information, research, case studies and human experiences relating to malaria."
    >

    <link
        rel="stylesheet"
        href="<?= e(base_url('assets/css/style.css')) ?>"
    >

</head>

<body>

<header class="site-header">

    <div class="site-container">

        <a
            class="site-logo"
            href="<?= e(base_url()) ?>"
        >
            Malaria Anthology
        </a>


    </div>

</header>