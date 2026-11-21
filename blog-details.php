<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once 'config/connect.php';

$db_ready = isset($conn) && !$conn->connect_error;
$req_slug = trim((string)($_GET['slug'] ?? $_GET['slug_url'] ?? ''));
$req_id   = trim((string)($_GET['id'] ?? ''));

$blog = null;
if ($db_ready) {
    if ($req_slug !== '') {
        $s = $conn->real_escape_string($req_slug);
        $res = @$conn->query("SELECT * FROM blogs WHERE status = 1 AND slug = '$s' LIMIT 1");
        if ($res) $blog = $res->fetch_assoc();
    }
    if (!$blog && $req_id !== '' && ctype_digit($req_id)) {
        $bid = (int)$req_id;
        $res = @$conn->query("SELECT * FROM blogs WHERE status = 1 AND blog_id = $bid LIMIT 1");
        if ($res) $blog = $res->fetch_assoc();
    }
    if (!$blog && $req_slug === '' && $req_id === '') {
        $res = @$conn->query("SELECT * FROM blogs WHERE status = 1 ORDER BY created_at DESC, blog_id DESC LIMIT 1");
        if ($res) $blog = $res->fetch_assoc();
    }
}

$https_bd = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
$base_bd  = ($https_bd ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';

if ($blog) {
    $b_id     = (int)$blog['blog_id'];
    $b_title  = trim((string)($blog['title'] ?? 'Spice Trade Guide'));
    $b_slug   = trim((string)($blog['slug'] ?? ''));
    $b_author = trim((string)($blog['author'] ?? '')) ?: 'AMSB Trade Desk';
    $b_date   = !empty($blog['created_at']) ? date('Y-m-d', strtotime($blog['created_at'])) : date('Y-m-d');
    $b_disp_d = !empty($blog['created_at']) ? date('d M Y', strtotime($blog['created_at'])) : date('d M Y');

    $canonical_url = $b_slug !== ''
        ? $base_bd . 'blog-details.php?slug=' . urlencode($b_slug)
        : $base_bd . 'blog-details.php?id=' . $b_id;

    $meta_title = !empty($blog['meta_title'])
        ? $blog['meta_title']
        : ($b_title . ' | AMSB GLOBAL Spice Trade Guide');

    $plain_text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string)($blog['description'] ?? '')))));
    $meta_desc  = !empty($blog['meta_desc'])
        ? $blog['meta_desc']
        : ($plain_text ? mb_strimwidth($plain_text, 0, 155, '...') : ($b_title . ' - Wholesale spice buying guide by AMSB GLOBAL, Wankaner, Morbi.'));

    $meta_key = !empty($blog['meta_key'])
        ? $blog['meta_key']
        : ($b_title . ', wholesale spice trade India, red chilli powder bulk, turmeric powder supplier Gujarat, AMSB Global');

    // Use DB schema_markup if clean (ignore old dummy Bhagirath schema if still in DB)
    $db_b_schema = trim((string)($blog['schema_markup'] ?? ''));
    if ($db_b_schema !== '' && stripos($db_b_schema, 'bhagirathenterprises') === false) {
        $schema = $db_b_schema;
    } else {
        $art_schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Article',
                    '@id' => $canonical_url . '#article',
                    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical_url],
                    'headline' => $b_title,
                    'description' => $meta_desc,
                    'datePublished' => $b_date,
                    'dateModified' => $b_date,
                    'inLanguage' => 'en-IN',
                    'author' => ['@type' => 'Person', 'name' => $b_author],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => 'AMSB GLOBAL',
                        'url' => $base_bd
                    ]
                ],
                [
                    '@type' => 'BreadcrumbList',
                    '@id' => $canonical_url . '#breadcrumb',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base_bd],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Trade Insights', 'item' => $base_bd . 'blogs.php'],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $b_title, 'item' => $canonical_url]
                    ]
                ]
            ]
        ];
        $schema = '<script type="application/ld+json">' . json_encode($art_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
} else {
    http_response_code(404);
    $meta_title    = 'Article Not Found | AMSB GLOBAL';
    $meta_desc     = 'Requested trade guide could not be found. Browse all wholesale spice buying guides from AMSB GLOBAL.';
    $canonical_url = $base_bd . 'blogs.php';
}

include_once 'includes/header.php';
?>

<style>
    /* --- Fix: Long Blog Content & Reveal Visibility --- */
.bd-content-card,
.bd-content-card[data-reveal],
.bd-featured-img[data-reveal] {
    opacity: 1 !important;
    transform: none !important;
}

/* Sticky sidebar + long page smooth scrolling fix */
body {
    overflow-x: clip;
}
</style>

<!-- Reading Progress Indicator -->
<div class="read-progress" id="readProgress" aria-hidden="true"></div>

<main id="main" class="bd-page">

<?php if (!$blog): ?>
    <section class="sec">
        <div class="wrap">
            <div class="empty-box" data-reveal>
                <i class="fa-regular fa-newspaper"></i>
                <h2>Trade Guide Not Found</h2>
                <p>The article you are looking for may have been moved or updated. Explore all our wholesale spice guides or connect directly with our Wankaner trade desk.</p>
                <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:16px;">
                    <a href="blogs.php" class="btn">All Trade Insights</a>
                    <a href="products.php" class="btn o d">Browse Spices</a>
                </div>
            </div>
        </div>
    </section>
<?php else:
    $read_min = max(2, (int)ceil(str_word_count($plain_text) / 220));
    $lt = strtolower($b_title);
    if (strpos($lt, 'chilli') !== false || strpos($lt, 'mirch') !== false) {
        $topic = 'Red Chilli Guide';
    } elseif (strpos($lt, 'turmeric') !== false || strpos($lt, 'haldi') !== false) {
        $topic = 'Turmeric Guide';
    } elseif (strpos($lt, 'pack') !== false) {
        $topic = 'Bulk Packaging';
    } else {
        $topic = 'Wholesale Buying Guide';
    }

    $b_img = img($blog['image'] ?? '', 'blogs', un('photo-1596040033229-a9821ebd058d', 1100));

    // Prev & Next Articles (Dynamic)
    $prev_post = q("SELECT blog_id, title, slug FROM blogs WHERE status = 1 AND blog_id < $b_id ORDER BY blog_id DESC LIMIT 1")[0] ?? null;
    $next_post = q("SELECT blog_id, title, slug FROM blogs WHERE status = 1 AND blog_id > $b_id ORDER BY blog_id ASC LIMIT 1")[0] ?? null;

    // Recent Posts for Sidebar & Related Bottom Grid (Dynamic)
    $recent_posts = q("SELECT blog_id, title, slug, author, image, meta_desc, description, created_at
                       FROM blogs
                       WHERE status = 1 AND blog_id != $b_id
                       ORDER BY created_at DESC, blog_id DESC
                       LIMIT 4");
?>

    <!-- 1. SOFT LIGHT BLUE ARTICLE HERO -->
    <section class="bd-hero">
        <div class="wrap bd-hero-inner" data-reveal>
            <nav class="bc" aria-label="Breadcrumb">
                <a href="./"><i class="fa-solid fa-house"></i> Home</a>
                <span>/</span>
                <a href="blogs.php">Trade Insights</a>
                <span>/</span>
                <span class="curr"><?= e(mb_strimwidth($b_title, 0, 55, '...')) ?></span>
            </nav>

            <div class="hero-badges">
                <span class="pill-primary"><i class="fa-solid fa-bookmark"></i> <?= e($topic) ?></span>
                <span class="pill-secondary"><i class="fa-regular fa-clock"></i> <?= (int)$read_min ?> Min Read</span>
            </div>

            <h1><?= e($b_title) ?></h1>

            <div class="bd-meta-bar">
                <div class="bd-meta-items">
                    <span><i class="fa-solid fa-pen-nib"></i> By <strong><?= e($b_author) ?></strong></span>
                    <span><i class="fa-regular fa-calendar"></i> Published <?= e($b_disp_d) ?></span>
                    <span><i class="fa-solid fa-building"></i> AMSB GLOBAL Trade Desk</span>
                </div>
                <div class="bd-share">
                    <span>Share:</span>
                    <a href="https://wa.me/?text=<?= urlencode($b_title . ' - ' . $canonical_url) ?>" target="_blank" rel="noopener" title="Share on WhatsApp" class="sh-wa"><i class="fa-brands fa-whatsapp"></i></a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($canonical_url) ?>" target="_blank" rel="noopener" title="Share on LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                    <button type="button" id="copyLinkBtn" title="Copy Article Link"><i class="fa-solid fa-link"></i></button>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. MAIN ARTICLE BODY + STICKY B2B SIDEBAR -->
    <section class="sec bd-body-sec">
        <div class="wrap bd-grid">

            <!-- Left Main Column -->
            <article class="bd-main">
                <!-- Featured Image -->
                <figure class="bd-featured-img" data-reveal>
                    <?= pic($b_img, $b_title, true) ?>
                </figure>

                <!-- Dynamic Article Content -->
                <div class="bd-content-card" data-reveal>
                    <div class="dyn-richtext bd-prose" id="articleContent">
                        <?= $blog['description'] ?>
                    </div>
                </div>

                <!-- Trade Desk Accountability / Author Trust Box -->
                <div class="bd-author-box" data-reveal>
                    <div class="bda-ico"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="bda-info">
                        <span class="bda-tag">Verified Wholesale Trade Knowledge</span>
                        <h3>Published by AMSB GLOBAL — Wankaner, Morbi (Gujarat)</h3>
                        <p>Led by <strong><?= e($brand['founder']) ?></strong>, AMSB GLOBAL supplies 100% pure Red Chilli Powder and Turmeric Powder in bulk (5 kg to 50 kg lined bags) with clean GST billing, E-Way bills, and Pan-India transport dispatch.</p>
                        <div class="bda-links">
                            <a href="products.php" class="btn"><i class="fa-solid fa-cubes"></i> Browse Wholesale Spices</a>
                            <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), I read "' . $b_title . '" and want today’s bulk spice rates.') ?>" target="_blank" rel="noopener" class="btn wa-btn"><i class="fa-brands fa-whatsapp"></i> Ask <?= e($brand['founder']) ?> on WhatsApp</a>
                        </div>
                    </div>
                </div>

                <!-- Previous / Next Navigation -->
                <?php if ($prev_post || $next_post): ?>
                <nav class="bd-prev-next" aria-label="Article navigation" data-reveal>
                    <?php if ($prev_post):
                        $pu = !empty($prev_post['slug']) ? 'blog-details.php?slug=' . urlencode($prev_post['slug']) : 'blog-details.php?id=' . (int)$prev_post['blog_id'];
                    ?>
                    <a class="pn-card pn-prev" href="<?= e($pu) ?>">
                        <small><i class="fa-solid fa-arrow-left"></i> Previous Guide</small>
                        <strong><?= e(mb_strimwidth(txt($prev_post['title']), 0, 75, '...')) ?></strong>
                    </a>
                    <?php else: ?>
                    <div></div>
                    <?php endif; ?>

                    <?php if ($next_post):
                        $nu = !empty($next_post['slug']) ? 'blog-details.php?slug=' . urlencode($next_post['slug']) : 'blog-details.php?id=' . (int)$next_post['blog_id'];
                    ?>
                    <a class="pn-card pn-next" href="<?= e($nu) ?>">
                        <small>Next Guide <i class="fa-solid fa-arrow-right"></i></small>
                        <strong><?= e(mb_strimwidth(txt($next_post['title']), 0, 75, '...')) ?></strong>
                    </a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>
            </article>

            <!-- Right Sticky Sidebar -->
            <aside class="bd-sidebar">
                <!-- Auto Table of Contents (Populated via JS if article has H2 headings) -->
                <div class="sb-card toc-card" id="tocCard" style="display:none;" data-reveal>
                    <h4><i class="fa-solid fa-list-ul"></i> Inside This Guide</h4>
                    <ul class="toc-list" id="tocList"></ul>
                </div>

                <!-- Quick Bulk Rate Inquiry Card -->
                <div class="sb-card sb-rfq-card" data-reveal>
                    <span class="sb-pill">Direct Mill &amp; Trade Rates</span>
                    <h4>Need Pure Red Chilli or Turmeric Powder in Bulk?</h4>
                    <p>Get today’s wholesale rate and request a courier sample from Wankaner, Morbi.</p>
                    <form class="sb-form" action="contact.php" method="POST">
                        <input type="hidden" name="subject" value="Inquiry via Blog: <?= e(mb_strimwidth($b_title, 0, 60, '')) ?>">
                        <input name="name" placeholder="Your Name *" required>
                        <input name="company" placeholder="Firm / Shop Name *" required>
                        <input type="tel" name="phone" placeholder="Mobile / WhatsApp *" required>
                        <input type="email" name="email" placeholder="Business Email *" required>
                        <select name="quantity">
                            <option>Trial Bulk (200 kg – 500 kg)</option>
                            <option>1 MT – 5 MT</option>
                            <option>Full Truck Load (10–25 MT)</option>
                            <option>Monthly Supply</option>
                        </select>
                        <textarea name="message" rows="2" placeholder="Required spice, bag size & city *" required></textarea>
                        <button type="submit" class="btn full-btn"><i class="fa-solid fa-paper-plane"></i> Get Today’s Rate</button>
                    </form>
                </div>

                <!-- Recent Guides (Dynamic) -->
                <?php if ($recent_posts): ?>
                <div class="sb-card" data-reveal>
                    <h4><i class="fa-regular fa-newspaper"></i> Recent Trade Guides</h4>
                    <div class="sb-recent-list">
                        <?php foreach ($recent_posts as $rp):
                            $rpu = !empty($rp['slug']) ? 'blog-details.php?slug=' . urlencode($rp['slug']) : 'blog-details.php?id=' . (int)$rp['blog_id'];
                            $rpi = img($rp['image'] ?? '', 'blogs', un('photo-1596040033229-a9821ebd058d', 300));
                        ?>
                        <a class="sb-rec-item" href="<?= e($rpu) ?>">
                            <img src="<?= e($rpi) ?>" alt="<?= e($rp['title']) ?>" loading="lazy">
                            <div>
                                <strong><?= e(mb_strimwidth(txt($rp['title']), 0, 62, '...')) ?></strong>
                                <small><?= e(date('d M Y', strtotime($rp['created_at'] ?: 'now'))) ?></small>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Core Categories Quick Links -->
                <?php if (!empty($nav_cats)): ?>
                <div class="sb-card" data-reveal>
                    <h4><i class="fa-solid fa-layer-group"></i> Wholesale Categories</h4>
                    <ul class="sb-cat-list">
                        <?php foreach ($nav_cats as $cslug => $cd): ?>
                        <li>
                            <a href="category.php?slug=<?= urlencode($cslug) ?>">
                                <span><?= e($cd['name']) ?></span>
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </aside>

        </div>
    </section>

    <!-- 3. RELATED GUIDES BOTTOM GRID -->
    <?php if ($recent_posts): ?>
    <section class="sec alt">
        <div class="wrap">
            <div class="bh" data-reveal>
                <div class="sh">
                    <span class="hi">Keep Reading</span>
                    <h2>More Wholesale Buying Guides</h2>
                </div>
                <a href="blogs.php" class="btn o d">View All Guides</a>
            </div>
            <div class="blog-grid">
                <?php foreach (array_slice($recent_posts, 0, 3) as $rb):
                    $rbu = !empty($rb['slug']) ? 'blog-details.php?slug=' . urlencode($rb['slug']) : 'blog-details.php?id=' . (int)$rb['blog_id'];
                    $rbi = img($rb['image'] ?? '', 'blogs', un('photo-1588252303782-cb80119abd6d', 700));
                    $rbx = txt($rb['meta_desc'] ?? '', 130) ?: txt($rb['description'] ?? '', 130);
                ?>
                <article class="bl-card" data-reveal>
                    <a class="blc-img" href="<?= e($rbu) ?>">
                        <?= pic($rbi, $rb['title']) ?>
                        <span class="blc-topic">Trade Guide</span>
                    </a>
                    <div class="blc-body">
                        <div class="blc-meta">
                            <span><i class="fa-regular fa-calendar"></i> <?= e(date('d M Y', strtotime($rb['created_at'] ?: 'now'))) ?></span>
                        </div>
                        <h3><a href="<?= e($rbu) ?>"><?= e($rb['title']) ?></a></h3>
                        <p><?= e($rbx) ?></p>
                        <div class="blc-foot">
                            <small>By <?= e(txt($rb['author'] ?? '') ?: 'AMSB Desk') ?></small>
                            <a href="<?= e($rbu) ?>" class="rd">Read Guide <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php endif; ?>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Scroll Reveal
const revealEls = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(en => {
                if (en.isIntersecting) {
                    en.target.classList.add('is-visible');
                    obs.unobserve(en.target);
                }
            });
        }, { threshold: 0, rootMargin: '0px 0px -10px 0px' });
        revealEls.forEach(el => io.observe(el));
    } else {
        revealEls.forEach(el => el.classList.add('is-visible'));
    }

    // 2. Reading Progress Bar
    const bar = document.getElementById('readProgress');
    if (bar) {
        window.addEventListener('scroll', () => {
            const h = document.documentElement;
            const max = h.scrollHeight - h.clientHeight;
            const pct = max > 0 ? Math.min(100, (h.scrollTop / max) * 100) : 0;
            bar.style.width = pct + '%';
        }, { passive: true });
    }

    // 3. Auto Table of Contents from Article H2 Headings + Wrap Tables for Mobile Scroll
    const prose = document.getElementById('articleContent');
    const tocCard = document.getElementById('tocCard');
    const tocList = document.getElementById('tocList');
    if (prose) {
        // Wrap raw tables in scrollable wrapper
        prose.querySelectorAll('table').forEach(tbl => {
            const wrap = document.createElement('div');
            wrap.className = 'prose-table-wrap';
            tbl.parentNode.insertBefore(wrap, tbl);
            wrap.appendChild(tbl);
        });

        const h2s = prose.querySelectorAll('h2');
        if (h2s.length >= 2 && tocCard && tocList) {
            h2s.forEach((h, i) => {
                const text = h.textContent.trim();
                if (!text) return;
                const id = 'sec-' + (i + 1);
                h.id = id;
                const li = document.createElement('li');
                const a  = document.createElement('a');
                a.href = '#' + id;
                a.textContent = text;
                li.appendChild(a);
                tocList.appendChild(li);
            });
            if (tocList.children.length > 0) tocCard.style.display = 'block';
        }
    }

    // 4. Copy Link Button
    const copyBtn = document.getElementById('copyLinkBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', () => {
            navigator.clipboard && navigator.clipboard.writeText(window.location.href).then(() => {
                copyBtn.innerHTML = '<i class="fa-solid fa-check"></i>';
                setTimeout(() => { copyBtn.innerHTML = '<i class="fa-solid fa-link"></i>'; }, 1800);
            });
        });
    }

    // 5. Mobile Menu Toggle
    const tg = document.querySelector('.tg');
    const nav = document.getElementById('nav');
    if (tg && nav && !tg.dataset.bound) {
        tg.dataset.bound = '1';
        tg.addEventListener('click', () => {
            const exp = tg.getAttribute('aria-expanded') === 'true';
            tg.setAttribute('aria-expanded', String(!exp));
            nav.classList.toggle('open');
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>