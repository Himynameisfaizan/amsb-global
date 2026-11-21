<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once 'config/connect.php';

$https_b  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
$base_b   = ($https_b ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';
$blog_url = $base_b . 'blog.php';

$page_meta = [
    'title' => 'Wholesale Spice Buying Guides & Trade Insights | AMSB GLOBAL',
    'desc'  => 'Practical B2B buying guides on Red Chilli Powder grading, Turmeric Curcumin levels, bulk spice packaging, moisture control, and wholesale procurement from AMSB GLOBAL, Gujarat.',
    'key'   => 'Red Chilli Powder Buying Guide, Turmeric Powder Quality Check, Bulk Spice Trade Blog India, Wholesale Spice Packaging Guide, AMSB Global Insights',
    'schema' => '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Blog",
      "@id": "' . $blog_url . '#blog",
      "url": "' . $blog_url . '",
      "name": "AMSB GLOBAL Wholesale Spice Trade Insights",
      "description": "Wholesale spice buying guides, grading tips, and market notes for Red Chilli Powder and Turmeric Powder buyers across India.",
      "inLanguage": "en-IN",
      "publisher": {
        "@type": "Organization",
        "name": "AMSB GLOBAL",
        "url": "' . $base_b . '"
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "' . $blog_url . '#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "' . $base_b . '" },
        { "@type": "ListItem", "position": 2, "name": "Trade Insights", "item": "' . $blog_url . '" }
      ]
    }
  ]
}
</script>'
];

include_once 'includes/header.php';

// ---- Fetch Active Blogs (Dynamic from DB) ----
$raw_blogs = q("SELECT blog_id, title, slug, author, image, description, meta_desc, created_at
                FROM blogs
                WHERE status = 1
                ORDER BY created_at DESC, blog_id DESC");

$blogs = [];
$blog_fallbacks = [
    'photo-1596040033229-a9821ebd058d',
    'photo-1588252303782-cb80119abd6d',
    'photo-1615485500704-8e990f9900f7',
    'photo-1532336414038-cf19250c5757'
];

foreach ($raw_blogs as $i => $b) {
    $title = txt($b['title'] ?? '');
    if ($title === '') continue;

    $plain = txt($b['description'] ?? '');
    $words = str_word_count($plain);
    $read_min = max(2, (int)ceil($words / 220));

    $excerpt = txt($b['meta_desc'] ?? '', 155) ?: txt($plain, 155);
    $slug    = trim((string)($b['slug'] ?? ''));
    $url     = $slug !== '' ? 'blog/' . urlencode($slug) : 'blog-details.php?id=' . (int)$b['blog_id'];

    // Topic tag auto-detection for clean filtering
    $lt = strtolower($title);
    if (strpos($lt, 'chilli') !== false || strpos($lt, 'mirch') !== false) {
        $topic = 'Red Chilli';
    } elseif (strpos($lt, 'turmeric') !== false || strpos($lt, 'haldi') !== false) {
        $topic = 'Turmeric';
    } elseif (strpos($lt, 'pack') !== false) {
        $topic = 'Packaging';
    } else {
        $topic = 'Buying Guide';
    }

    $blogs[] = [
        'id'       => (int)$b['blog_id'],
        'title'    => $title,
        'url'      => $url,
        'author'   => txt($b['author'] ?? '') ?: 'AMSB Trade Desk',
        'date'     => date('d M Y', strtotime($b['created_at'] ?: 'now')),
        'img'      => img($b['image'] ?? '', 'blogs', un($blog_fallbacks[$i % 4], 850)),
        'excerpt'  => $excerpt,
        'read_min' => $read_min,
        'topic'    => $topic
    ];
}

$featured = $blogs[0] ?? null;
$grid_blogs = count($blogs) > 1 ? array_slice($blogs, 1) : [];

// Collect available topic tags
$topics = [];
foreach ($blogs as $bl) {
    $topics[$bl['topic']] = ($topics[$bl['topic']] ?? 0) + 1;
}
?>

<main id="main" class="blog-page">

    <!-- 1. SOFT LIGHT BLUE BLOG HERO -->
    <section class="page-hero">
        <div class="wrap ph-grid">
            <div class="ph-content" data-reveal>
                <nav class="bc" aria-label="Breadcrumb">
                    <a href="./"><i class="fa-solid fa-house"></i> Home</a>
                    <span>/</span>
                    <span class="curr">Trade Insights &amp; Buying Guides</span>
                </nav>
                <div class="hero-badges">
                    <span class="pill-primary"><i class="fa-solid fa-book-open"></i> <?= count($blogs) ?> Practical Trade Guides</span>
                    <span class="pill-secondary"><i class="fa-solid fa-lightbulb"></i> For Wholesale Spice Buyers</span>
                </div>
                <h1>Wholesale Spice <span class="txt-primary">Buying Guides</span> &amp; <span class="txt-secondary">Market Notes.</span></h1>
                <p class="lead">Straightforward, practical knowledge from AMSB GLOBAL’s Wankaner trade desk—covering spice grading, ASTA colour, curcumin checks, bulk bag packaging, and how to verify pure lots before placing a wholesale order.</p>
                <div class="blog-hero-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="blogSearch" placeholder="Search guides (e.g. red chilli, packaging, bulk supplier)..." aria-label="Search articles">
                </div>
            </div>

            <!-- Right Knowledge Highlights Card -->
            <div class="ph-card" data-reveal>
                <div class="phc-head">
                    <span>Inside Our Trade Notes</span>
                    <h3>Smart Procurement Checklist</h3>
                </div>
                <ul class="phc-list">
                    <li><span><i class="fa-solid fa-fire-flame-simple"></i> Chilli Grading</span><strong>Teja S17, Sannam S4, Byadgi &amp; SHU</strong></li>
                    <li><span><i class="fa-solid fa-sun"></i> Haldi Purity</span><strong>Curcumin % &amp; Lead-Free Checks</strong></li>
                    <li><span><i class="fa-solid fa-box"></i> Bulk Packing</span><strong>5 / 10 / 25 / 50 KG Moisture Safety</strong></li>
                    <li><span><i class="fa-solid fa-clipboard-check"></i> Buyer Checklist</span><strong>Sample Testing &amp; Lot Consistency</strong></li>
                    <li><span><i class="fa-solid fa-truck"></i> Trade Logistics</span><strong>GST, E-Way Bill &amp; Transport Tips</strong></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- 2. TOPIC FILTER BAR -->
    <?php if ($blogs): ?>
    <section class="cat-toolbar-sec">
        <div class="wrap cat-toolbar">
            <div class="cat-pills" id="blogTopicPills" role="group" aria-label="Filter articles by topic">
                <button type="button" class="cpill active" data-topic="all">All Articles (<?= count($blogs) ?>)</button>
                <?php foreach ($topics as $tname => $tcnt): ?>
                <button type="button" class="cpill" data-topic="<?= e(strtolower($tname)) ?>">
                    <?= e($tname) ?> (<?= $tcnt ?>)
                </button>
                <?php endforeach; ?>
            </div>
            <p class="count-note">Showing <strong id="blogVisibleCount"><?= count($blogs) ?></strong> guide<?= count($blogs) === 1 ? '' : 's' ?></p>
        </div>
    </section>
    <?php endif; ?>

    <!-- 3. FEATURED LATEST ARTICLE (DYNAMIC) -->
    <?php if ($featured): ?>
    <section class="sec" id="blog-listing">
        <div class="wrap">
            <article class="feat-blog blog-item"
                     data-topic="<?= e(strtolower($featured['topic'])) ?>"
                     data-title="<?= e(strtolower($featured['title'])) ?>"
                     data-reveal>
                <a class="fb-img" href="<?= e($featured['url']) ?>">
                    <?= pic($featured['img'], $featured['title'], true) ?>
                    <span class="fb-badge"><i class="fa-solid fa-star"></i> Latest Trade Guide</span>
                </a>
                <div class="fb-body">
                    <div class="fb-meta">
                        <span class="fb-topic"><?= e($featured['topic']) ?></span>
                        <span><i class="fa-regular fa-calendar"></i> <?= e($featured['date']) ?></span>
                        <span><i class="fa-regular fa-clock"></i> <?= (int)$featured['read_min'] ?> min read</span>
                    </div>
                    <h2><a href="<?= e($featured['url']) ?>"><?= e($featured['title']) ?></a></h2>
                    <p><?= e($featured['excerpt']) ?></p>
                    <div class="fb-foot">
                        <span class="fb-author"><i class="fa-solid fa-pen-nib"></i> By <?= e($featured['author']) ?></span>
                        <a href="<?= e($featured['url']) ?>" class="btn">Read Full Guide <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </div>
            </article>

            <!-- 4. ALL ARTICLES GRID (DYNAMIC) -->
            <?php if ($grid_blogs): ?>
            <div class="cat-list-head" style="margin-top:52px;" data-reveal>
                <div>
                    <span class="hi">More From Our Knowledge Desk</span>
                    <h2>All Wholesale Buying Guides</h2>
                </div>
            </div>

            <div class="blog-grid" id="blogGrid">
                <?php foreach ($grid_blogs as $b): ?>
                <article class="bl-card blog-item"
                         data-topic="<?= e(strtolower($b['topic'])) ?>"
                         data-title="<?= e(strtolower($b['title'])) ?>"
                         data-reveal>
                    <a class="blc-img" href="<?= e($b['url']) ?>">
                        <?= pic($b['img'], $b['title']) ?>
                        <span class="blc-topic"><?= e($b['topic']) ?></span>
                    </a>
                    <div class="blc-body">
                        <div class="blc-meta">
                            <span><i class="fa-regular fa-calendar"></i> <?= e($b['date']) ?></span>
                            <span><i class="fa-regular fa-clock"></i> <?= (int)$b['read_min'] ?> min read</span>
                        </div>
                        <h3><a href="<?= e($b['url']) ?>"><?= e($b['title']) ?></a></h3>
                        <p><?= e($b['excerpt']) ?></p>
                        <div class="blc-foot">
                            <small>By <?= e($b['author']) ?></small>
                            <a href="<?= e($b['url']) ?>" class="rd">Read Guide <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="empty-box" id="blogNoMatch" style="display:none;margin-top:24px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>No matching articles found</h3>
                <p>Try a different keyword or reset the topic filter to browse all trade guides.</p>
                <button type="button" class="btn" id="resetBlogBtn">Show All Articles</button>
            </div>
        </div>
    </section>
    <?php else: ?>
    <section class="sec">
        <div class="wrap">
            <div class="empty-box" data-reveal>
                <i class="fa-regular fa-newspaper"></i>
                <h3>Trade Guides Coming Soon</h3>
                <p>We are publishing fresh buying guides for Red Chilli Powder and Turmeric Powder wholesalers. Meanwhile, browse our live product catalogue or ask Mahir Bhai directly.</p>
                <a href="products.php" class="btn">Explore Wholesale Catalogue</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 5. BUYER TRUST STRIP: TRANSPARENT KNOWLEDGE = TRANSPARENT TRADE -->
    <section class="sec alt" id="trade-trust">
        <div class="wrap">
            <div class="sh center" data-reveal>
                <span class="hi">Why Buyers Trust AMSB GLOBAL</span>
                <h2>Informed Buyers Make Our Best Long-Term Partners</h2>
                <p>We share honest grading and quality-check checklists publicly because our Red Chilli Powder and Turmeric Powder stand up to every test.</p>
            </div>
            <div class="trust-4-grid">
                <div class="t4-card" data-reveal>
                    <div class="t4-ico gold-ico"><i class="fa-solid fa-vial-circle-check"></i></div>
                    <h3>Test Our Sample First</h3>
                    <p>Check colour, heat, mesh, and purity on a physical courier sample before booking your bulk lot.</p>
                </div>
                <div class="t4-card" data-reveal>
                    <div class="t4-ico cyan-ico"><i class="fa-solid fa-scale-balanced"></i></div>
                    <h3>Honest Grade Disclosure</h3>
                    <p>We clearly tell you the exact variety and blend—never selling mixed low-grade stock as premium.</p>
                </div>
                <div class="t4-card" data-reveal>
                    <div class="t4-ico navy-ico"><i class="fa-solid fa-file-invoice"></i></div>
                    <h3>100% GST &amp; E-Way Billing</h3>
                    <p>Clean tax invoices and E-Way bills on every dispatch from Wankaner, Morbi (Gujarat).</p>
                </div>
                <div class="t4-card" data-reveal>
                    <div class="t4-ico gold-ico"><i class="fa-solid fa-headset"></i></div>
                    <h3>Direct Founder Access</h3>
                    <p>Talk directly to <?= e($brand['founder']) ?> on phone or WhatsApp for daily market rates and dispatch updates.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. DIRECT TRADE DESK CTA BANNER -->
    <section class="sec ab-cta-sec" style="padding-top:60px;">
        <div class="wrap">
            <div class="ab-cta-box" data-reveal>
                <div>
                    <span class="cta-tag">Need Bulk Spice Rates or Samples?</span>
                    <h2>Put Our Quality to the Test Today</h2>
                    <p>Ask for today’s wholesale rate sheet for Red Chilli Powder and Turmeric Powder, or request a courier sample pack for your shop or factory.</p>
                </div>
                <div class="cta-btns">
                    <a href="products.php" class="btn"><i class="fa-solid fa-cubes"></i> View Spice Catalogue</a>
                    <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), I read your trade guides and want today’s wholesale rates for Red Chilli & Turmeric Powder.') ?>" target="_blank" rel="noopener" class="btn wa-btn"><i class="fa-brands fa-whatsapp"></i> WhatsApp <?= e($disp_phone) ?></a>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Smooth Scroll Reveal
    const revealEls = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(en => {
                if (en.isIntersecting) { en.target.classList.add('is-visible'); obs.unobserve(en.target); }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -25px 0px' });
        revealEls.forEach(el => io.observe(el));
    } else {
        revealEls.forEach(el => el.classList.add('is-visible'));
    }

    // 2. Live Topic Filter + Search
    const searchInput = document.getElementById('blogSearch');
    const topicPills  = document.querySelectorAll('#blogTopicPills .cpill');
    const items       = document.querySelectorAll('.blog-item');
    const countEl     = document.getElementById('blogVisibleCount');
    const noMatchBox  = document.getElementById('blogNoMatch');
    const resetBtn    = document.getElementById('resetBlogBtn');

    let activeTopic = 'all';

    function filterBlogs() {
        const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
        let visible = 0;

        items.forEach(card => {
            const topicOk = (activeTopic === 'all' || card.dataset.topic === activeTopic);
            const textOk  = (q === '' || card.dataset.title.includes(q) || card.textContent.toLowerCase().includes(q));
            const show    = topicOk && textOk;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (countEl) countEl.textContent = visible;
        if (noMatchBox) noMatchBox.style.display = visible === 0 ? 'block' : 'none';
    }

    topicPills.forEach(btn => {
        btn.addEventListener('click', () => {
            activeTopic = btn.dataset.topic;
            topicPills.forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            filterBlogs();
        });
    });

    if (searchInput) searchInput.addEventListener('input', filterBlogs);
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            activeTopic = 'all';
            if (searchInput) searchInput.value = '';
            topicPills.forEach(p => p.classList.toggle('active', p.dataset.topic === 'all'));
            filterBlogs();
        });
    }

    // 3. Mobile Menu Toggle
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