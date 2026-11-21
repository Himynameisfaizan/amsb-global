<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once 'config/connect.php';

$https_pr = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
$base_pr  = ($https_pr ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';
$prod_url = $base_pr . 'products.php';

// Active filters from URL (supports header dropdown links like products.php?cat=slug&q=name)
$init_cat = trim((string)($_GET['cat'] ?? $_GET['category'] ?? 'all'));
$init_q   = trim((string)($_GET['q'] ?? ''));

$page_meta = [
    'title' => 'Wholesale Spices Catalogue | Bulk Red Chilli & Turmeric Powder | AMSB GLOBAL',
    'desc'  => 'Browse complete B2B wholesale catalogue of 100% pure Red Chilli Powder, Turmeric Powder and Indian spices from AMSB GLOBAL, Wankaner, Morbi (Gujarat). GST billing & Pan-India transport dispatch.',
    'key'   => 'Bulk Red Chilli Powder Catalogue, Turmeric Powder Wholesale Morbi, Indian Spices Bulk Price Gujarat, AMSB Global Products, Wankaner Spice Trader',
    'schema' => '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "CollectionPage",
      "@id": "' . $prod_url . '#collectionpage",
      "url": "' . $prod_url . '",
      "name": "AMSB GLOBAL Wholesale Spices Catalogue",
      "description": "Complete wholesale product range of Red Chilli Powder, Turmeric Powder and pure Indian spices supplied in bulk from Wankaner, Morbi, Gujarat.",
      "inLanguage": "en-IN",
      "isPartOf": { "@id": "' . $base_pr . '#website" }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "' . $prod_url . '#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "' . $base_pr . '" },
        { "@type": "ListItem", "position": 2, "name": "Products", "item": "' . $prod_url . '" }
      ]
    }
  ]
}
</script>'
];

include_once 'includes/header.php';

// ---- Fetch Active Categories (Dynamic from DB) ----
$all_cats = q("SELECT id, cate_id, categories, slug_url FROM categories WHERE status = 1 ORDER BY id ASC");

// ---- Fetch All Active Products (Dynamic from DB) ----
$fallback_imgs = [
    'photo-1588252303782-cb80119abd6d',
    'photo-1615485500704-8e990f9900f7',
    'photo-1596040033229-a9821ebd058d',
    'photo-1532336414038-cf19250c5757'
];

$rows = q("SELECT p.*, c.categories AS cat_label, c.slug_url AS cat_slug
           FROM products p
           LEFT JOIN categories c ON (c.cate_id = p.pro_cate OR c.id = p.pro_cate)
           WHERE p.is_disabled = 0
           GROUP BY p.id
           ORDER BY p.id DESC");

$products = [];
$cat_counts = [];
foreach ($rows as $i => $r) {
    $pname = txt($r['pro_name'] ?? '');
    if ($pname === '') continue;

    $imgs_raw = preg_split('/[,|;"]+/', trim(stripslashes((string)($r['pro_img'] ?? '')), '[]" '));
    $first_img = '';
    foreach ($imgs_raw as $im) {
        if (trim($im) !== '') { $first_img = trim($im); break; }
    }

    $cslug = trim((string)($r['cat_slug'] ?? 'spices')) ?: 'spices';
    $cname = trim((string)($r['cat_label'] ?? 'Wholesale Spices')) ?: 'Wholesale Spices';
    $cat_counts[$cslug] = ($cat_counts[$cslug] ?? 0) + 1;

    $desc_raw = $r['description'] ?? $r['pro_desc'] ?? $r['short_desc'] ?? $r['meta_desc'] ?? '';
    $products[] = [
        'id'       => (int)$r['id'],
        'name'     => $pname,
        'slug'     => trim((string)($r['slug_url'] ?? '')),
        'cat'      => $cname,
        'cat_slug' => $cslug,
        'img'      => img($first_img, 'products', un($fallback_imgs[$i % 4], 750)),
        'short'    => txt($desc_raw, 115) ?: '100% pure bulk grade, hygienically ground and packed in moisture-safe 5 to 50 kg bags.'
    ];
}
?>

<main id="main" class="products-page">

    <!-- 1. SOFT LIGHT BLUE PAGE HERO -->
    <section class="page-hero">
        <div class="wrap ph-grid">
            <div class="ph-content" data-reveal>
                <nav class="bc" aria-label="Breadcrumb">
                    <a href="./"><i class="fa-solid fa-house"></i> Home</a>
                    <span>/</span>
                    <span class="curr">All Wholesale Products</span>
                </nav>
                <div class="hero-badges">
                    <span class="pill-primary"><i class="fa-solid fa-cubes"></i> <?= count($products) ?> Active Bulk Products</span>
                    <span class="pill-secondary"><i class="fa-solid fa-truck-fast"></i> Pan-India Transport Dispatch</span>
                </div>
                <h1>Wholesale <span class="txt-primary">Spice Catalogue</span> for <span class="txt-secondary">Bulk Trade.</span></h1>
                <p class="lead">Explore our pure, unadulterated Red Chilli Powder, Turmeric Powder, and bulk spice lots supplied directly from Wankaner, Morbi (Gujarat). Honest grading, courier samples before bulk orders, and 100% GST + E-Way bill compliance.</p>
                <div class="ph-actions">
                    <a href="#prod-rfq" class="btn"><i class="fa-solid fa-file-invoice-dollar"></i> Request Daily Rate Sheet</a>
                    <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), please share today’s complete wholesale spice rate sheet.') ?>" target="_blank" rel="noopener" class="btn o"><i class="fa-brands fa-whatsapp"></i> WhatsApp Trade Desk</a>
                </div>
            </div>

            <!-- Right Quick Wholesale Buying Terms Card -->
            <div class="ph-card" data-reveal>
                <div class="phc-head">
                    <span>Wholesale Supply Terms</span>
                    <h3>AMSB GLOBAL Trade Desk</h3>
                </div>
                <ul class="phc-list">
                    <li><span><i class="fa-solid fa-weight-hanging"></i> Trial Bulk MOQ</span><strong>200 KG – 500 KG</strong></li>
                    <li><span><i class="fa-solid fa-boxes-packing"></i> Bag Sizes</span><strong>5 / 10 / 25 / 50 KG Lined PP</strong></li>
                    <li><span><i class="fa-solid fa-vial-circle-check"></i> Sample Dispatch</span><strong>24 – 48 Hours via Courier</strong></li>
                    <li><span><i class="fa-solid fa-file-invoice"></i> Tax Paperwork</span><strong>GST Invoice &amp; E-Way Bill</strong></li>
                    <li><span><i class="fa-solid fa-shield-halved"></i> Purity Guarantee</span><strong>Zero Dye, Zero Starch Mixing</strong></li>
                    <li><span><i class="fa-solid fa-phone-volume"></i> Direct Contact</span><strong><?= e($disp_phone) ?> (<?= e($brand['founder']) ?>)</strong></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- 2. STICKY FILTER & SEARCH TOOLBAR -->
    <section class="cat-toolbar-sec">
        <div class="wrap cat-toolbar">
            <div class="cat-pills" id="prodFilterPills" role="group" aria-label="Filter by category">
                <button type="button" class="cpill <?= ($init_cat === '' || strtolower($init_cat) === 'all') ? 'active' : '' ?>" data-cat="all">
                    All Products (<?= count($products) ?>)
                </button>
                <?php foreach ($all_cats as $c):
                    $cslug = trim((string)$c['slug_url']);
                    $cnt   = $cat_counts[$cslug] ?? 0;
                    $is_act = strcasecmp($init_cat, $cslug) === 0;
                ?>
                <button type="button" class="cpill <?= $is_act ? 'active' : '' ?>" data-cat="<?= e(strtolower($cslug)) ?>">
                    <?= e($c['categories']) ?><?= $cnt ? ' (' . $cnt . ')' : '' ?>
                </button>
                <?php endforeach; ?>
            </div>

            <div class="cat-controls">
                <div class="cat-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="prodSearch" value="<?= e($init_q) ?>" placeholder="Search chilli, turmeric, grade..." aria-label="Search products">
                </div>
                <select id="prodSort" aria-label="Sort products">
                    <option value="default">Sort: Latest Added</option>
                    <option value="az">Name: A to Z</option>
                    <option value="za">Name: Z to A</option>
                </select>
            </div>
        </div>
    </section>

    <!-- 3. DYNAMIC ALL PRODUCTS GRID -->
    <section class="sec" id="all-products">
        <div class="wrap">
            <div class="cat-list-head" data-reveal>
                <div>
                    <span class="hi">Ready Stock &amp; Custom Grinding</span>
                    <h2>Complete Bulk Product Range</h2>
                </div>
                <p class="count-note">Showing <strong id="prodVisibleCount"><?= count($products) ?></strong> wholesale item<?= count($products) === 1 ? '' : 's' ?></p>
            </div>

            <?php if ($products): ?>
            <div class="cat-prod-grid" id="allProdGrid">
                <?php foreach ($products as $p):
                    $detail_url = $p['slug']
                        ? 'product/' . urlencode($p['slug'])
                        : 'product-details.php?id=' . $p['id'];
                ?>
                <article class="cp-card"
                         data-cat="<?= e(strtolower($p['cat_slug'])) ?>"
                         data-name="<?= e(strtolower($p['name'])) ?>"
                         data-reveal>
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
                            <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), I want today’s wholesale rate & sample for: ' . $p['name']) ?>" target="_blank" rel="noopener" class="pc-wa" title="WhatsApp Inquiry">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <div class="empty-box" id="prodNoMatch" style="display:none;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>No matching spice found</h3>
                <p>Try clearing your filter or ask Mahir Bhai directly if you need a custom mesh size or blend.</p>
                <button type="button" class="btn" id="resetFiltersBtn">Show All Products</button>
            </div>
            <?php else: ?>
            <div class="empty-box" data-reveal>
                <i class="fa-solid fa-box-open"></i>
                <h3>Catalogue Updating</h3>
                <p>Our wholesale Red Chilli Powder and Turmeric Powder lots are available in ready stock. Reach our Wankaner trade desk for today’s rate sheet.</p>
                <a href="#prod-rfq" class="btn">Request Rate Sheet</a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 4. CUSTOMER TRUST SECTION: QUICK GRADE COMPARISON TABLE -->
    <section class="sec alt" id="grade-guide">
        <div class="wrap">
            <div class="sh center" data-reveal>
                <span class="hi">Buyer Selection Guide</span>
                <h2>Quick Grade Guide for Wholesale Buyers</h2>
                <p>Not sure which grade fits your local market or factory requirement? Compare our flagship Red Chilli Powder and Turmeric Powder parameters at a glance.</p>
            </div>

            <div class="grade-table-wrap" data-reveal>
                <table class="grade-table">
                    <thead>
                        <tr>
                            <th>Spice Category</th>
                            <th>Popular Commercial Grades</th>
                            <th>Key Quality Metric</th>
                            <th>Mesh Size</th>
                            <th>Best Suited For</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Red Chilli Powder</strong></td>
                            <td>Teja S17, Sannam S4, Byadgi, Kashmiri</td>
                            <td>60–160+ ASTA | 15K–85K SHU</td>
                            <td>40 – 80 Mesh</td>
                            <td>Mandi Wholesalers, Masala Brands, Namkeen &amp; HoReCa</td>
                        </tr>
                        <tr>
                            <td><strong>Turmeric Powder</strong></td>
                            <td>Salem, Erode, Rajapuri, Nizamabad</td>
                            <td>2.5% – 5.5%+ Curcumin</td>
                            <td>60 – 100 Mesh</td>
                            <td>Retail Repackers, Distributors, Pickle &amp; Food Units</td>
                        </tr>
                        <tr>
                            <td><strong>Custom Blends</strong></td>
                            <td>Buyer-Specific Heat &amp; Colour Match</td>
                            <td>Matched to Approved Sample</td>
                            <td>Custom Sieved</td>
                            <td>Regional Brand Owners &amp; Commercial Kitchens</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="trust-4-grid" style="margin-top:32px;">
                <div class="t4-card" data-reveal>
                    <div class="t4-ico gold-ico"><i class="fa-solid fa-flask-vial"></i></div>
                    <h3>100% Pure, Zero Mixing</h3>
                    <p>No Sudan dye, no lead chromate, no rice starch or husk fillers—pure natural colour and spice oil.</p>
                </div>
                <div class="t4-card" data-reveal>
                    <div class="t4-ico cyan-ico"><i class="fa-solid fa-video"></i></div>
                    <h3>Live Loading Verification</h3>
                    <p>Request batch photos or a quick WhatsApp video call while your bags are weighed and loaded in Wankaner.</p>
                </div>
                <div class="t4-card" data-reveal>
                    <div class="t4-ico navy-ico"><i class="fa-solid fa-box-open"></i></div>
                    <h3>Moisture-Proof Packing</h3>
                    <p>Inner poly-lined 5, 10, 25 &amp; 50 kg PP/HDPE bags keep powder free-flowing and fresh in storage.</p>
                </div>
                <div class="t4-card" data-reveal>
                    <div class="t4-ico gold-ico"><i class="fa-solid fa-file-invoice"></i></div>
                    <h3>Clean GST &amp; E-Way Papers</h3>
                    <p>Proper GST invoice, E-Way bill, and transport LR shared immediately on every Pan-India dispatch.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. DARK NAVY BULK RATE INQUIRY BAND -->
    <section class="sec dk" id="prod-rfq">
        <div class="wrap cat-rfq-grid">
            <div class="cr-info" data-reveal>
                <span class="hi">Daily Mandi-Linked Wholesale Rates</span>
                <h2>Get Today’s Bulk Rate &amp; Sample Dispatch</h2>
                <p>Select any product above or list your combined requirement below. <strong><?= e($brand['founder']) ?></strong> will share today’s transparent ex-Wankaner or delivered rate directly.</p>

                <div class="cr-contact-strip">
                    <div>
                        <small>Direct Trade Desk</small>
                        <a href="tel:+<?= e($tel) ?>"><strong><?= e($disp_phone) ?> (<?= e($brand['founder']) ?>)</strong></a>
                    </div>
                    <div>
                        <small>Dispatch Location</small>
                        <strong>Wankaner, Morbi, Gujarat - 363621</strong>
                    </div>
                </div>

                <ul class="cr-bullets">
                    <li><i class="fa-solid fa-circle-check"></i> Mixed lots allowed (Red Chilli + Turmeric Powder in same transport)</li>
                    <li><i class="fa-solid fa-circle-check"></i> Courier samples dispatched within 24–48 hours across India</li>
                    <li><i class="fa-solid fa-circle-check"></i> Neutral / unbranded bags available for masala repackers</li>
                </ul>
            </div>

            <form class="fm" action="contact.php" method="POST" data-reveal>
                <h3>Request Wholesale Quotation</h3>
                <p class="fm-sub">Fast, direct response from AMSB GLOBAL, Wankaner.</p>
                <label>Your Name *
                    <input name="name" placeholder="e.g. Rakesh Patel" required>
                </label>
                <label>Firm / Company Name *
                    <input name="company" placeholder="e.g. Patel Spice Traders" required>
                </label>
                <label>Mobile / WhatsApp *
                    <input type="tel" name="phone" placeholder="+91 98765 43210" required>
                </label>
                <label>Email Address *
                    <input type="email" name="email" placeholder="you@company.com" required>
                </label>
                <label>Product(s) Required *
                    <input id="prodQuoteInput" name="subject" value="Red Chilli Powder & Turmeric Powder" required>
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
                <label class="w">Delivery City &amp; Packaging Notes *
                    <textarea name="message" rows="3" placeholder="Mention your city, required pack size (5/10/25/50 kg), and if you need courier samples first..." required></textarea>
                </label>
                <div class="w">
                    <button type="submit" class="btn full-btn"><i class="fa-solid fa-paper-plane"></i> Send Bulk Rate Inquiry</button>
                </div>
            </form>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Scroll Reveal
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

    // 2. Live Category + Search + Sort Filter
    const grid        = document.getElementById('allProdGrid');
    const pills       = document.querySelectorAll('#prodFilterPills .cpill');
    const searchInput = document.getElementById('prodSearch');
    const sortSelect  = document.getElementById('prodSort');
    const countEl     = document.getElementById('prodVisibleCount');
    const noMatchBox  = document.getElementById('prodNoMatch');
    const resetBtn    = document.getElementById('resetFiltersBtn');

    let activeCat = '<?= e(strtolower($init_cat ?: 'all')) ?>';
    const validCats = Array.from(pills).map(p => p.dataset.cat);
    if (!validCats.includes(activeCat)) activeCat = 'all';

    if (grid) {
        const originalCards = Array.from(grid.querySelectorAll('.cp-card'));

        function updateProducts() {
            const q    = (searchInput ? searchInput.value : '').trim().toLowerCase();
            const mode = sortSelect ? sortSelect.value : 'default';

            let list = originalCards.slice();
            if (mode === 'az') list.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
            if (mode === 'za') list.sort((a, b) => b.dataset.name.localeCompare(a.dataset.name));

            let visible = 0;
            list.forEach(card => {
                grid.appendChild(card);
                const catOk  = (activeCat === 'all' || card.dataset.cat === activeCat);
                const textOk = (q === '' || card.dataset.name.includes(q) || card.textContent.toLowerCase().includes(q));
                const show   = catOk && textOk;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            if (countEl) countEl.textContent = visible;
            if (noMatchBox) noMatchBox.style.display = visible === 0 ? 'block' : 'none';
        }

        pills.forEach(btn => {
            btn.addEventListener('click', () => {
                activeCat = btn.dataset.cat;
                pills.forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                updateProducts();
            });
        });

        if (searchInput) searchInput.addEventListener('input', updateProducts);
        if (sortSelect)  sortSelect.addEventListener('change', updateProducts);
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                activeCat = 'all';
                if (searchInput) searchInput.value = '';
                if (sortSelect) sortSelect.value = 'default';
                pills.forEach(p => p.classList.toggle('active', p.dataset.cat === 'all'));
                updateProducts();
            });
        }

        // Run once on load if URL had ?cat= or ?q=
        if (activeCat !== 'all' || (searchInput && searchInput.value.trim() !== '')) {
            updateProducts();
        }
    }

    // 3. Quick Rate Button -> Prefill Form & Scroll
    const quoteInput = document.getElementById('prodQuoteInput');
    const rfqBox     = document.getElementById('prod-rfq');
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