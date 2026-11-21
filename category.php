<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once 'config/connect.php';

$db_ready = isset($conn) && !$conn->connect_error;
$cat_slug = trim((string)($_GET['slug'] ?? $_GET['cat'] ?? ''));

$current_cat = null;
$all_cats = [];

if ($db_ready) {
    if ($res = @$conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY id ASC")) {
        while ($row = $res->fetch_assoc()) {
            $all_cats[] = $row;
            if ($cat_slug !== '' && strcasecmp((string)($row['slug_url'] ?? ''), $cat_slug) === 0) {
                $current_cat = $row;
            }
        }
    }
}

// Dynamic Page Title, Meta, Canonical & JSON-LD Schema
$https_c = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
$base_c  = ($https_c ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';

$cat_name = $current_cat ? trim((string)($current_cat['categories'] ?? 'Wholesale Spices')) : 'All Wholesale Spices';
$canonical_url = $current_cat
    ? $base_c . 'category.php?slug=' . urlencode($current_cat['slug_url'])
    : $base_c . 'category.php';

$meta_title = !empty($current_cat['meta_title'])
    ? $current_cat['meta_title']
    : ($cat_name . ' Wholesale Supplier in Gujarat | Bulk Trade Rates | AMSB GLOBAL');

$meta_desc = !empty($current_cat['meta_desc'])
    ? $current_cat['meta_desc']
    : ('Buy 100% pure ' . $cat_name . ' in bulk from AMSB GLOBAL, Wankaner, Morbi (Gujarat). Direct wholesale rates, 5 kg to 50 kg bags, GST invoice, E-Way bill & Pan-India dispatch.');

$meta_key = !empty($current_cat['meta_key'])
    ? $current_cat['meta_key']
    : ($cat_name . ' wholesale Gujarat, bulk ' . $cat_name . ' supplier Morbi, AMSB Global Wankaner');

$schema_data = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'CollectionPage',
            '@id' => $canonical_url . '#collection',
            'url' => $canonical_url,
            'name' => $cat_name . ' - AMSB GLOBAL Wholesale Trade',
            'description' => $meta_desc,
            'inLanguage' => 'en-IN',
            'isPartOf' => ['@id' => $base_c . '#website']
        ],
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical_url . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base_c],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Products', 'item' => $base_c . 'products.php'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $cat_name, 'item' => $canonical_url]
            ]
        ]
    ]
];
$schema = '<script type="application/ld+json">' . json_encode($schema_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

include_once 'includes/header.php';

// ---- Fetch Products for this Category (Dynamic from DB) ----
$products = [];
$fallback_imgs = [
    'photo-1588252303782-cb80119abd6d',
    'photo-1615485500704-8e990f9900f7',
    'photo-1596040033229-a9821ebd058d',
    'photo-1532336414038-cf19250c5757'
];

if ($current_cat) {
    $cid_list = [];
    if (!empty($current_cat['cate_id'])) $cid_list[] = (int)$current_cat['cate_id'];
    if (!empty($current_cat['id']))      $cid_list[] = (int)$current_cat['id'];
    $cid_list = array_unique(array_filter($cid_list));
    $in_sql = $cid_list ? implode(',', $cid_list) : '0';

    $rows = q("SELECT p.*, c.categories AS cat_label, c.slug_url AS cat_slug
               FROM products p
               LEFT JOIN categories c ON (c.cate_id = p.pro_cate OR c.id = p.pro_cate)
               WHERE p.is_disabled = 0 AND p.pro_cate IN ($in_sql)
               GROUP BY p.id
               ORDER BY p.id DESC");
} else {
    $rows = q("SELECT p.*, c.categories AS cat_label, c.slug_url AS cat_slug
               FROM products p
               LEFT JOIN categories c ON (c.cate_id = p.pro_cate OR c.id = p.pro_cate)
               WHERE p.is_disabled = 0
               GROUP BY p.id
               ORDER BY p.id DESC");
}

foreach ($rows as $i => $r) {
    $pname = txt($r['pro_name'] ?? '');
    if ($pname === '') continue;
    $imgs_raw = preg_split('/[,|;"]+/', trim(stripslashes((string)($r['pro_img'] ?? '')), '[]" '));
    $first_img = '';
    foreach ($imgs_raw as $im) {
        if (trim($im) !== '') { $first_img = trim($im); break; }
    }
    $desc_raw = $r['description'] ?? $r['pro_desc'] ?? $r['short_desc'] ?? $r['meta_desc'] ?? '';
    $products[] = [
        'id'    => (int)$r['id'],
        'name'  => $pname,
        'slug'  => trim((string)($r['slug_url'] ?? '')),
        'cat'   => $r['cat_label'] ?: $cat_name,
        'img'   => img($first_img, 'products', un($fallback_imgs[$i % 4], 750)),
        'short' => txt($desc_raw, 115) ?: 'Pure wholesale grade, hygienically processed and packed in 5 kg to 50 kg moisture-safe bulk bags.'
    ];
}

// Tailored Category Intro Copy
$is_chilli   = stripos($cat_name, 'chilli') !== false || stripos($cat_name, 'mirch') !== false;
$is_turmeric = stripos($cat_name, 'turmeric') !== false || stripos($cat_name, 'haldi') !== false;

if ($is_chilli) {
    $cat_tagline = 'Natural ASTA Colour • Custom Heat (SHU) • Zero Sudan Dye';
    $cat_intro   = 'Direct wholesale supply of pure Red Chilli Powder grades including Teja S17, Sannam S4, Byadgi, and Kashmiri blends. Ground cleanly at controlled temperatures in Wankaner, Morbi so natural colour and pungency stay intact.';
} elseif ($is_turmeric) {
    $cat_tagline = 'High Curcumin • Deep Natural Golden Hue • Zero Lead Chromate';
    $cat_intro   = 'Pure, unadulterated Turmeric (Haldi) Powder milled from selected Salem, Erode, and Rajapuri fingers. Fine lump-free mesh, rich earthy aroma, and low moisture for long shelf stability in wholesale trade.';
} else {
    $cat_tagline = '100% Pure Bulk Lots • Sample-Matched Supply • GST & E-Way Billing';
    $cat_intro   = 'Explore our wholesale ' . $cat_name . ' range supplied directly from Wankaner, Morbi (Gujarat). Every lot is checked for cleanliness, natural aroma, moisture control, and exact weight before transport dispatch.';
}

$cat_banner_img = img($current_cat['image'] ?? '', 'categories', un($is_turmeric ? 'photo-1615485500704-8e990f9900f7' : 'photo-1588252303782-cb80119abd6d', 900));
?>

<main id="main" class="cat-page">

    <!-- 1. SOFT LIGHT BLUE CATEGORY HERO -->
    <section class="page-hero cat-hero">
        <div class="wrap ph-grid">
            <div class="ph-content" data-reveal>
                <nav class="bc" aria-label="Breadcrumb">
                    <a href="./"><i class="fa-solid fa-house"></i> Home</a>
                    <span>/</span>
                    <a href="products.php">Products</a>
                    <span>/</span>
                    <span class="curr"><?= e($cat_name) ?></span>
                </nav>
                <div class="hero-badges">
                    <span class="pill-primary"><i class="fa-solid fa-cubes"></i> <?= count($products) ?> Wholesale Variants</span>
                    <span class="pill-secondary"><i class="fa-solid fa-location-dot"></i> Dispatch from Wankaner, Morbi</span>
                </div>
                <h1>Wholesale <span class="txt-primary"><?= e($cat_name) ?></span> <span class="txt-secondary">Bulk Supply</span></h1>
                <p class="cat-subline"><?= e($cat_tagline) ?></p>
                <p class="lead"><?= e($cat_intro) ?></p>
                <div class="ph-actions">
                    <a href="#cat-rfq" class="btn"><i class="fa-solid fa-file-invoice-dollar"></i> Get <?= e($cat_name) ?> Rate</a>
                    <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), please share today’s bulk wholesale rates for ' . $cat_name . '.') ?>" target="_blank" rel="noopener" class="btn o"><i class="fa-brands fa-whatsapp"></i> Ask Sample on WhatsApp</a>
                </div>
            </div>

            <div class="cat-hero-card" data-reveal>
                <div class="chc-img">
                    <?= pic($cat_banner_img, $cat_name . ' Bulk Wholesale Supply - AMSB GLOBAL', true) ?>
                    <span class="chc-pill"><i class="fa-solid fa-shield-halved"></i> 100% Pure &amp; Unadulterated</span>
                </div>
                <div class="chc-bar">
                    <div><small>Standard Packing</small><strong>5 / 10 / 25 / 50 KG</strong></div>
                    <div><small>Trial MOQ</small><strong>200 – 500 KG</strong></div>
                    <div><small>Billing</small><strong>GST + E-Way Bill</strong></div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. CATEGORY SWITCHER & LIVE SEARCH TOOLBAR -->
    <section class="cat-toolbar-sec">
        <div class="wrap cat-toolbar">
            <div class="cat-pills" role="navigation" aria-label="Switch Category">
                <a href="category.php" class="cpill <?= !$current_cat ? 'active' : '' ?>">All Spices</a>
                <?php foreach ($all_cats as $c):
                    $is_act = $current_cat && strcasecmp((string)$c['slug_url'], (string)$current_cat['slug_url']) === 0;
                ?>
                <a href="category.php?slug=<?= urlencode($c['slug_url']) ?>" class="cpill <?= $is_act ? 'active' : '' ?>">
                    <?= e($c['categories']) ?>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="cat-controls">
                <div class="cat-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="catSearch" placeholder="Search grade or product..." aria-label="Search products">
                </div>
                <select id="catSort" aria-label="Sort products">
                    <option value="default">Sort: Latest Added</option>
                    <option value="az">Name: A to Z</option>
                    <option value="za">Name: Z to A</option>
                </select>
            </div>
        </div>
    </section>

    <!-- 3. DYNAMIC PRODUCT CATALOGUE GRID -->
    <section class="sec" id="cat-catalogue">
        <div class="wrap">
            <div class="cat-list-head" data-reveal>
                <div>
                    <span class="hi">Available for Immediate Dispatch</span>
                    <h2><?= e($cat_name) ?> — Bulk Trade Catalogue</h2>
                </div>
                <p class="count-note">Showing <strong id="visibleCount"><?= count($products) ?></strong> wholesale product<?= count($products) === 1 ? '' : 's' ?></p>
            </div>

            <?php if ($products): ?>
            <div class="cat-prod-grid" id="catProdGrid">
                <?php foreach ($products as $p):
                    $detail_url = $p['slug']
                        ? 'product-details.php?slug=' . urlencode($p['slug'])
                        : 'product-details.php?id=' . $p['id'];
                ?>
                <article class="cp-card" data-name="<?= e(strtolower($p['name'])) ?>" data-reveal>
                    <a class="cp-img" href="<?= e($detail_url) ?>">
                        <?= pic($p['img'], $p['name']) ?>
                        <span class="cp-badge">Bulk Grade</span>
                    </a>
                    <div class="cp-body">
                        <span class="cp-cat"><?= e($p['cat']) ?></span>
                        <h3><a href="<?= e($detail_url) ?>"><?= e($p['name']) ?></a></h3>
                        <p class="cp-desc"><?= e($p['short']) ?></p>

                        <div class="cp-specs">
                            <span><i class="fa-solid fa-box"></i> 10 / 25 / 50 KG</span>
                            <span><i class="fa-solid fa-check-double"></i> Sample First</span>
                        </div>

                        <div class="cp-actions">
                            <a href="<?= e($detail_url) ?>" class="btn cp-view">View Specs</a>
                            <button type="button" class="btn d cp-quote" data-quote="<?= e($p['name']) ?>">Get Rate</button>
                            <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), I want today’s bulk wholesale rate & sample for: ' . $p['name']) ?>" target="_blank" rel="noopener" class="pc-wa" title="WhatsApp Bulk Inquiry">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <div class="empty-box" id="noMatchBox" style="display:none;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>No matching product found</h3>
                <p>Try a different keyword or ask Mahir Bhai directly for custom spice grades.</p>
            </div>
            <?php else: ?>
            <div class="empty-box" data-reveal>
                <i class="fa-solid fa-box-open"></i>
                <h3>Products Coming Soon in <?= e($cat_name) ?></h3>
                <p>We regularly supply custom bulk grades on order. Contact our Wankaner trade desk directly for today’s availability and rates.</p>
                <a href="#cat-rfq" class="btn">Request Custom Grade</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 4. CUSTOMER TRUST SECTION: TRADE QUALITY & PURITY BENCHMARKS -->
    <section class="sec alt" id="trade-assurance">
        <div class="wrap">
            <div class="sh center" data-reveal>
                <span class="hi">Why Wholesalers Re-Order Every Month</span>
                <h2>Our 4-Point Trade Quality Guarantee</h2>
                <p>Every bulk lot dispatched from AMSB GLOBAL is backed by honest grading, clean paperwork, and full transparency before the vehicle leaves Wankaner.</p>
            </div>

            <div class="trust-4-grid">
                <div class="t4-card" data-reveal>
                    <div class="t4-ico gold-ico"><i class="fa-solid fa-flask-vial"></i></div>
                    <h3>100% Pure, Zero Mixing</h3>
                    <p>Strictly free from artificial dyes, Sudan colour, lead chromate, starch, or husk fillers. Natural colour and pungency only.</p>
                </div>
                <div class="t4-card" data-reveal>
                    <div class="t4-ico cyan-ico"><i class="fa-solid fa-clipboard-check"></i></div>
                    <h3>Sample = Bulk Lot Match</h3>
                    <p>Approve our courier sample first. Your bulk bags will match the exact mesh size, shade, aroma, and moisture of that sample.</p>
                </div>
                <div class="t4-card" data-reveal>
                    <div class="t4-ico navy-ico"><i class="fa-solid fa-scale-balanced"></i></div>
                    <h3>Exact Net Weight &amp; Packing</h3>
                    <p>Packed in strong PP/HDPE bags with food-grade inner poly liners (5, 10, 25 &amp; 50 kg) so moisture never ruins your godown stock.</p>
                </div>
                <div class="t4-card" data-reveal>
                    <div class="t4-ico gold-ico"><i class="fa-solid fa-truck-fast"></i></div>
                    <h3>Pan-India Transport &amp; GST</h3>
                    <p>Clean GST tax invoice, E-Way bill, and daily transport connectivity from Wankaner (Morbi) to major mandis across India.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. BULK ORDER & RATE INQUIRY BAND -->
    <section class="sec dk" id="cat-rfq">
        <div class="wrap cat-rfq-grid">
            <div class="cr-info" data-reveal>
                <span class="hi">Direct Mill &amp; Trade Desk Pricing</span>
                <h2>Need Today’s Bulk Rate for <?= e($cat_name) ?>?</h2>
                <p>Spice prices fluctuate with daily mandi arrivals. Share your required grade, bag size, and delivery city—<strong><?= e($brand['founder']) ?></strong> will share today’s best wholesale quotation promptly.</p>

                <div class="cr-contact-strip">
                    <div>
                        <small>Call / WhatsApp Trade Desk</small>
                        <a href="tel:+<?= e($tel) ?>"><strong><?= e($disp_phone) ?> (<?= e($brand['founder']) ?>)</strong></a>
                    </div>
                    <div>
                        <small>Dispatch Hub</small>
                        <strong>Wankaner, Morbi, Gujarat - 363621</strong>
                    </div>
                </div>

                <ul class="cr-bullets">
                    <li><i class="fa-solid fa-circle-check"></i> Physical samples dispatched via courier within 24–48 hours</li>
                    <li><i class="fa-solid fa-circle-check"></i> Pre-loading lot photos &amp; live video call inspection available</li>
                    <li><i class="fa-solid fa-circle-check"></i> Custom neutral or private-label bulk packing for repackers</li>
                </ul>
            </div>

            <form class="fm" action="contact.php" method="POST" data-reveal>
                <h3>Quick Wholesale Rate Request</h3>
                <p class="fm-sub">No middlemen. Direct response from AMSB GLOBAL, Wankaner.</p>
                <label>Your Name *
                    <input name="name" placeholder="e.g. Suresh Agrawal" required>
                </label>
                <label>Firm / Company Name *
                    <input name="company" placeholder="e.g. Agrawal Masala Traders" required>
                </label>
                <label>Mobile / WhatsApp *
                    <input type="tel" name="phone" placeholder="+91 98765 43210" required>
                </label>
                <label>Email Address *
                    <input type="email" name="email" placeholder="you@company.com" required>
                </label>
                <label>Selected Category / Product *
                    <input id="catQuoteProduct" name="subject" value="<?= e($cat_name) ?>" required>
                </label>
                <label>Order Quantity
                    <select name="quantity">
                        <option>Trial Bulk Lot (200 kg – 500 kg)</option>
                        <option>1 MT to 5 MT</option>
                        <option>Part Truck Load (5 MT – 10 MT)</option>
                        <option>Full Truck Load (10 MT – 25 MT)</option>
                        <option>Regular Monthly Requirement</option>
                    </select>
                </label>
                <label class="w">Delivery City &amp; Specifications *
                    <textarea name="message" rows="3" placeholder="Mention delivery city, preferred bag size (10/25/50 kg), and grade/mesh requirement..." required></textarea>
                </label>
                <div class="w">
                    <button type="submit" class="btn full-btn"><i class="fa-solid fa-paper-plane"></i> Get Today’s Wholesale Rate</button>
                </div>
            </form>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Scroll Reveal Animations
    const revealEls = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(en => {
                if (en.isIntersecting) { en.target.classList.add('is-visible'); obs.unobserve(en.target); }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
        revealEls.forEach(el => io.observe(el));
    } else {
        revealEls.forEach(el => el.classList.add('is-visible'));
    }

    // 2. Live Search & Sort inside Category
    const searchInput = document.getElementById('catSearch');
    const sortSelect  = document.getElementById('catSort');
    const grid        = document.getElementById('catProdGrid');
    const countEl     = document.getElementById('visibleCount');
    const noMatch     = document.getElementById('noMatchBox');

    if (grid) {
        const originalCards = Array.from(grid.querySelectorAll('.cp-card'));

        function applyFilterAndSort() {
            const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
            const mode = sortSelect ? sortSelect.value : 'default';

            let list = originalCards.slice();
            if (mode === 'az') {
                list.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
            } else if (mode === 'za') {
                list.sort((a, b) => b.dataset.name.localeCompare(a.dataset.name));
            }

            let visible = 0;
            list.forEach(card => {
                grid.appendChild(card);
                const match = q === '' || card.dataset.name.includes(q) || card.textContent.toLowerCase().includes(q);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            if (countEl) countEl.textContent = visible;
            if (noMatch) noMatch.style.display = visible === 0 ? 'block' : 'none';
        }

        if (searchInput) searchInput.addEventListener('input', applyFilterAndSort);
        if (sortSelect)  sortSelect.addEventListener('change', applyFilterAndSort);
    }

    // 3. Quick Rate Button -> Prefill Form & Smooth Scroll
    const quoteInput = document.getElementById('catQuoteProduct');
    const rfqBox     = document.getElementById('cat-rfq');
    document.querySelectorAll('[data-quote]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (quoteInput) quoteInput.value = btn.getAttribute('data-quote');
            if (rfqBox) {
                rfqBox.scrollIntoView({ behavior: 'smooth' });
                setTimeout(() => quoteInput && quoteInput.focus(), 450);
            }
        });
    });

    // 4. Mobile Menu Toggle
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