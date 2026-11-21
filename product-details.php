<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once 'config/connect.php';

$db_ready = isset($conn) && !$conn->connect_error;
$req_slug = trim((string)($_GET['slug'] ?? $_GET['slug_url'] ?? ''));
$req_id   = trim((string)($_GET['id'] ?? ''));

$product = null;
if ($db_ready) {
    if ($req_slug !== '') {
        $s = $conn->real_escape_string($req_slug);
        $res = @$conn->query("SELECT p.*, c.categories AS cat_name, c.slug_url AS cat_slug
                              FROM products p
                              LEFT JOIN categories c ON (c.cate_id = p.pro_cate OR c.id = p.pro_cate)
                              WHERE p.is_disabled = 0 AND (p.slug_url = '$s' OR p.pro_name = '$s')
                              LIMIT 1");
        if ($res) $product = $res->fetch_assoc();
    }
    if (!$product && $req_id !== '') {
        if (ctype_digit($req_id)) {
            $pid = (int)$req_id;
            $res = @$conn->query("SELECT p.*, c.categories AS cat_name, c.slug_url AS cat_slug
                                  FROM products p
                                  LEFT JOIN categories c ON (c.cate_id = p.pro_cate OR c.id = p.pro_cate)
                                  WHERE p.is_disabled = 0 AND p.id = $pid
                                  LIMIT 1");
        } else {
            $s = $conn->real_escape_string($req_id);
            $res = @$conn->query("SELECT p.*, c.categories AS cat_name, c.slug_url AS cat_slug
                                  FROM products p
                                  LEFT JOIN categories c ON (c.cate_id = p.pro_cate OR c.id = p.pro_cate)
                                  WHERE p.is_disabled = 0 AND (p.slug_url = '$s' OR p.pro_name LIKE '%$s%')
                                  LIMIT 1");
        }
        if ($res) $product = $res->fetch_assoc();
    }
    // Fallback to latest active product if URL opened directly without params
    if (!$product && $req_slug === '' && $req_id === '') {
        $res = @$conn->query("SELECT p.*, c.categories AS cat_name, c.slug_url AS cat_slug
                              FROM products p
                              LEFT JOIN categories c ON (c.cate_id = p.pro_cate OR c.id = p.pro_cate)
                              WHERE p.is_disabled = 0
                              ORDER BY p.id DESC LIMIT 1");
        if ($res) $product = $res->fetch_assoc();
    }
}

$https_p = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
$base_p  = ($https_p ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';

if ($product) {
    $p_name = trim((string)($product['pro_name'] ?? 'Wholesale Spice'));
    $p_slug = trim((string)($product['slug_url'] ?? ''));
    $p_cat  = trim((string)($product['cat_name'] ?? 'Wholesale Spices'));
    $p_cslug = trim((string)($product['cat_slug'] ?? ''));

    $canonical_url = $p_slug !== ''
        ? $base_p . 'product/' . urlencode($p_slug)
        : $base_p . 'product-details.php?id=' . (int)$product['id'];

    $meta_title = !empty($product['meta_title'])
        ? $product['meta_title']
        : ($p_name . ' Wholesale Supplier in Gujarat | Bulk Rate | AMSB GLOBAL');

    $raw_meta_desc = trim((string)($product['meta_desc'] ?? ''));
    $raw_plain_desc = trim(preg_replace('/\s+/', ' ', strip_tags((string)($product['description'] ?? $product['pro_desc'] ?? ''))));
    $meta_desc = $raw_meta_desc ?: ($raw_plain_desc ? mb_strimwidth($raw_plain_desc, 0, 155, '...') : ('Buy 100% pure ' . $p_name . ' in bulk from AMSB GLOBAL, Wankaner, Morbi, Gujarat. Clean GST billing, courier samples & Pan-India transport dispatch.'));

    $meta_key = !empty($product['meta_key'])
        ? $product['meta_key']
        : ($p_name . ', bulk ' . $p_name . ' supplier Gujarat, ' . $p_cat . ' wholesale Morbi, AMSB Global');

    if (!empty($product['schema_markup'])) {
        $schema = $product['schema_markup'];
    } else {
        $p_schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Product',
                    '@id' => $canonical_url . '#product',
                    'name' => $p_name,
                    'category' => $p_cat,
                    'description' => $meta_desc,
                    'brand' => ['@type' => 'Brand', 'name' => 'AMSB GLOBAL'],
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => $canonical_url,
                        'priceCurrency' => 'INR',
                        'availability' => 'https://schema.org/InStock',
                        'seller' => [
                            '@type' => 'Organization',
                            'name' => 'AMSB GLOBAL',
                            'telephone' => '+91-8200778206'
                        ]
                    ]
                ],
                [
                    '@type' => 'BreadcrumbList',
                    '@id' => $canonical_url . '#breadcrumb',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base_p],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => $p_cat, 'item' => $p_cslug ? $base_p . 'category/' . urlencode($p_cslug) : $base_p . 'products.php'],
                        ['@type' => 'ListItem', 'position' => 3, 'name' => $p_name, 'item' => $canonical_url]
                    ]
                ]
            ]
        ];
        $schema = '<script type="application/ld+json">' . json_encode($p_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
} else {
    http_response_code(404);
    $meta_title = 'Product Not Found | AMSB GLOBAL';
    $meta_desc  = 'Requested wholesale spice product could not be found. Browse our Red Chilli Powder and Turmeric Powder catalogue.';
    $canonical_url = $base_p . 'products.php';
}

include_once 'includes/header.php';
?>

<main id="main" class="pd-page">

<?php if (!$product): ?>
    <section class="sec">
        <div class="wrap">
            <div class="empty-box" data-reveal>
                <i class="fa-solid fa-circle-exclamation"></i>
                <h2>Product Not Found</h2>
                <p>The product you are looking for may have been moved or updated. Browse our active bulk spice catalogue or message Mahir Bhai directly.</p>
                <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:16px;">
                    <a href="products.php" class="btn">Browse All Products</a>
                    <a href="contact.php" class="btn o d">Contact Trade Desk</a>
                </div>
            </div>
        </div>
    </section>
<?php else:
    // Parse Multiple Product Images from DB
    $is_chilli   = stripos($p_name . ' ' . $p_cat, 'chilli') !== false || stripos($p_name, 'mirch') !== false;
    $is_turmeric = stripos($p_name . ' ' . $p_cat, 'turmeric') !== false || stripos($p_name, 'haldi') !== false;

    $default_gallery = $is_turmeric
        ? [un('photo-1615485500704-8e990f9900f7', 900), un('photo-1596040033229-a9821ebd058d', 900)]
        : ($is_chilli
            ? [un('photo-1588252303782-cb80119abd6d', 900), un('photo-1596040033229-a9821ebd058d', 900)]
            : [un('photo-1596040033229-a9821ebd058d', 900), un('photo-1532336414038-cf19250c5757', 900)]);

    $raw_files = preg_split('/[,|;"]+/', trim(stripslashes((string)($product['pro_img'] ?? '')), '[]" '));
    $gallery = [];
    foreach ($raw_files as $rf) {
        $rf = trim($rf);
        if ($rf !== '') {
            $resolved = img($rf, 'products', '');
            if ($resolved !== '') $gallery[] = $resolved;
        }
    }
    if (!$gallery) $gallery = [$default_gallery[0]];

    $desc_html = trim((string)($product['description'] ?? $product['pro_desc'] ?? $product['long_desc'] ?? ''));
    if ($desc_html === '') {
        $desc_html = '<p><strong>' . e($p_name) . '</strong> supplied by <strong>AMSB GLOBAL</strong> (Wankaner, Morbi, Gujarat) is hygienically processed from carefully selected raw spice lots. Every batch is tested for natural colour, rich aroma, low moisture, and zero adulteration before bulk dispatch.</p>
                      <p>Suitable for spice wholesalers, regional masala repackers, namkeen &amp; food processing units, hotels, and catering suppliers across India.</p>';
    }

    // Dynamic Trade Specification Matrix
    if ($is_chilli) {
        $hsn_code = '09042211';
        $specs = [
            ['Product Type', $p_name],
            ['HSN Code (GST)', '09042211'],
            ['Available Varieties', 'Teja S17, Sannam S4, Byadgi, Kashmiri & Custom Blends'],
            ['Natural Colour (ASTA)', '60 ASTA to 160+ ASTA (Grade Dependent)'],
            ['Pungency (Heat)', '15,000 SHU to 85,000+ SHU'],
            ['Mesh / Fineness', '40 Mesh to 80 Mesh (Uniform Sieving)'],
            ['Moisture Content', 'Max 10% – 11%'],
            ['Purity Standard', '100% Pure — Zero Sudan Dye & Zero Added Colour'],
            ['Bulk Packaging', '5 KG, 10 KG, 25 KG & 50 KG Inner-Lined PP/HDPE Bags'],
            ['Minimum Order (MOQ)', '200 KG – 500 KG (Trial) | Up to Full Truck Load']
        ];
    } elseif ($is_turmeric) {
        $hsn_code = '09103030';
        $specs = [
            ['Product Type', $p_name],
            ['HSN Code (GST)', '09103030'],
            ['Origin Varieties', 'Salem, Erode, Rajapuri & Nizamabad'],
            ['Curcumin Content', '2.5% to 5.5%+ (As per Grade)'],
            ['Colour & Aroma', 'Natural Deep Golden Yellow, Fresh Earthy Aroma'],
            ['Mesh / Fineness', '60 Mesh to 100 Mesh (Micro-Fine, Lump-Free)'],
            ['Moisture Content', 'Max 9% – 10%'],
            ['Purity Standard', '100% Pure — Zero Lead Chromate & Zero Starch'],
            ['Bulk Packaging', '5 KG, 10 KG, 25 KG & 50 KG Inner-Lined PP/HDPE Bags'],
            ['Minimum Order (MOQ)', '200 KG – 500 KG (Trial) | Up to Full Truck Load']
        ];
    } else {
        $hsn_code = '0910';
        $specs = [
            ['Product Name', $p_name],
            ['Category', $p_cat],
            ['Quality Grade', '100% Pure Commercial & Wholesale Grade'],
            ['Moisture Control', 'Max 10% (Hygienically Dried & Milled)'],
            ['Adulteration Check', 'Zero Artificial Colour, Zero Fillers or Husk'],
            ['Bulk Packaging', '5 KG, 10 KG, 25 KG & 50 KG Moisture-Safe Bags'],
            ['Billing & Papers', 'GST Tax Invoice + E-Way Bill + Transport LR'],
            ['Dispatch Hub', 'Wankaner, Morbi, Gujarat - 363621']
        ];
    }

    // Fetch Related Products from DB
    $pid_curr = (int)$product['id'];
    $pcate_curr = (int)($product['pro_cate'] ?? 0);
    $related = q("SELECT p.id, p.pro_name, p.slug_url, p.pro_img, c.categories AS cat_label
                  FROM products p
                  LEFT JOIN categories c ON (c.cate_id = p.pro_cate OR c.id = p.pro_cate)
                  WHERE p.is_disabled = 0 AND p.id != $pid_curr AND p.pro_cate = $pcate_curr
                  GROUP BY p.id ORDER BY p.id DESC LIMIT 4");

    if (count($related) < 4) {
        $exclude = [$pid_curr];
        foreach ($related as $rl) $exclude[] = (int)$rl['id'];
        $need = 4 - count($related);
        $more = q("SELECT p.id, p.pro_name, p.slug_url, p.pro_img, c.categories AS cat_label
                   FROM products p
                   LEFT JOIN categories c ON (c.cate_id = p.pro_cate OR c.id = p.pro_cate)
                   WHERE p.is_disabled = 0 AND p.id NOT IN (" . implode(',', $exclude) . ")
                   GROUP BY p.id ORDER BY p.id DESC LIMIT $need");
        $related = array_merge($related, $more);
    }
?>

    <!-- 1. SOFT LIGHT BLUE BREADCRUMB & PRODUCT SHOWCASE HERO -->
    <section class="pd-hero">
        <div class="wrap">
            <nav class="bc" aria-label="Breadcrumb">
                <a href="./"><i class="fa-solid fa-house"></i> Home</a>
                <span>/</span>
                <a href="<?= $p_cslug ? 'category/' . urlencode($p_cslug) : 'products.php' ?>"><?= e($p_cat) ?></a>
                <span>/</span>
                <span class="curr"><?= e($p_name) ?></span>
            </nav>

            <div class="pd-grid">
                <!-- Left: Interactive Gallery -->
                <div class="pd-gallery" data-reveal>
                    <div class="pd-main-img">
                        <img id="pdMainImg" src="<?= e($gallery[0]) ?>" alt="<?= e($p_name) ?> - AMSB GLOBAL Wholesale">
                        <span class="pd-pure-tag"><i class="fa-solid fa-certificate"></i> 100% Pure Wholesale Lot</span>
                    </div>
                    <?php if (count($gallery) > 1): ?>
                    <div class="pd-thumbs">
                        <?php foreach ($gallery as $idx => $gimg): ?>
                        <button type="button" class="pd-thumb <?= $idx === 0 ? 'active' : '' ?>" data-src="<?= e($gimg) ?>" aria-label="View image <?= $idx + 1 ?>">
                            <img src="<?= e($gimg) ?>" alt="<?= e($p_name) ?> thumbnail <?= $idx + 1 ?>">
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Core Trade Info & Instant Actions -->
                <div class="pd-summary" data-reveal>
                    <div class="pd-tags">
                        <span class="pill-primary"><i class="fa-solid fa-tag"></i> <?= e($p_cat) ?></span>
                        <span class="pill-secondary"><i class="fa-solid fa-barcode"></i> HSN: <?= e($hsn_code) ?></span>
                    </div>

                    <h1><?= e($p_name) ?></h1>
                    <p class="pd-lead"><?= e($meta_desc) ?></p>

                    <!-- Quick B2B Spec Tiles -->
                    <div class="pd-quick-specs">
                        <div class="pqs-item">
                            <i class="fa-solid fa-boxes-packing"></i>
                            <div><small>Bulk Packing</small><strong>5 / 10 / 25 / 50 KG</strong></div>
                        </div>
                        <div class="pqs-item">
                            <i class="fa-solid fa-weight-hanging"></i>
                            <div><small>Trial MOQ</small><strong>200 – 500 KG</strong></div>
                        </div>
                        <div class="pqs-item">
                            <i class="fa-solid fa-file-invoice"></i>
                            <div><small>Tax Billing</small><strong>GST + E-Way Bill</strong></div>
                        </div>
                        <div class="pqs-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <div><small>Dispatch Hub</small><strong>Wankaner, Morbi</strong></div>
                        </div>
                    </div>

                    <!-- Rate Transparency Callout -->
                    <div class="pd-rate-box">
                        <div>
                            <span>Daily Wholesale Rate</span>
                            <strong>Quoted Against Quantity &amp; Grade</strong>
                            <small>Mandi rates update daily — request today’s exact ex-Wankaner or delivered rate.</small>
                        </div>
                        <span class="stock-pill"><i class="fa-solid fa-circle-check"></i> Ready Stock</span>
                    </div>

                    <!-- Primary Action Buttons -->
                    <div class="pd-cta-row">
                        <a href="#pd-rfq" class="btn"><i class="fa-solid fa-file-invoice-dollar"></i> Get Today’s Bulk Rate</a>
                        <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), I need bulk wholesale rate & sample details for: ' . $p_name) ?>" target="_blank" rel="noopener" class="btn wa-btn"><i class="fa-brands fa-whatsapp"></i> Order Sample / Rate</a>
                        <a href="tel:+<?= e($tel) ?>" class="btn o"><i class="fa-solid fa-phone"></i> Call <?= e($brand['founder']) ?></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. CUSTOMER TRUST ASSURANCE STRIP -->
    <section class="pd-trust-strip">
        <div class="wrap pd-ts-grid">
            <div class="pd-ts-item"><i class="fa-solid fa-vial-circle-check"></i> <span>Courier Sample Before Bulk Order</span></div>
            <div class="pd-ts-item"><i class="fa-solid fa-ban"></i> <span>Zero Artificial Colour or Starch</span></div>
            <div class="pd-ts-item"><i class="fa-solid fa-video"></i> <span>Live Video Loading Inspection</span></div>
            <div class="pd-ts-item"><i class="fa-solid fa-truck-fast"></i> <span>Daily Pan-India Transport Dispatch</span></div>
        </div>
    </section>

    <!-- 3. DYNAMIC DESCRIPTION + TECHNICAL SPECIFICATIONS TABS/LAYOUT -->
    <section class="sec" id="pd-details">
        <div class="wrap pd-detail-grid">
            <!-- Left: Rich Dynamic Description & Trade Spec Sheet -->
            <div class="pd-main-col">
                <div class="pd-panel" data-reveal>
                    <div class="pdp-head">
                        <span class="hi">Product Overview</span>
                        <h2>About <?= e($p_name) ?></h2>
                    </div>
                    <div class="dyn-richtext">
                        <?= $desc_html ?>
                    </div>
                </div>

                <div class="pd-panel" data-reveal>
                    <div class="pdp-head">
                        <span class="hi">Measurable Wholesale Parameters</span>
                        <h2>Technical &amp; Trade Specification Sheet</h2>
                    </div>
                    <div class="pd-spec-table">
                        <?php foreach ($specs as $sp): ?>
                        <div class="pd-sp-row">
                            <span><?= e($sp[0]) ?></span>
                            <strong><?= e($sp[1]) ?></strong>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Buyer Trust: Quality Check & Packaging Assurance -->
                <div class="pd-panel pd-trust-panel" data-reveal>
                    <div class="pdp-head">
                        <span class="hi">Buyer Protection at AMSB GLOBAL</span>
                        <h2>How We Protect Your Bulk Purchase</h2>
                    </div>
                    <div class="pd-protect-grid">
                        <div class="pp-box">
                            <i class="fa-solid fa-magnifying-glass-chart"></i>
                            <h4>Pre-Dispatch Sample Match</h4>
                            <p>Every packed lot is checked against the physical sample approved by you—no downgrade in bulk quantity.</p>
                        </div>
                        <div class="pp-box">
                            <i class="fa-solid fa-droplet-slash"></i>
                            <h4>Moisture-Lock Inner Liner</h4>
                            <p>Double-layer packaging (food-grade poly liner inside PP/HDPE bag) prevents clumping and aroma loss in transit.</p>
                        </div>
                        <div class="pp-box">
                            <i class="fa-solid fa-scale-unbalanced-flip"></i>
                            <h4>Verified Net Weight</h4>
                            <p>Accurate digital weighing on every bag with weighbridge slip support for full-truck buyers.</p>
                        </div>
                        <div class="pp-box">
                            <i class="fa-solid fa-file-circle-check"></i>
                            <h4>Hassle-Free Interstate Papers</h4>
                            <p>Complete GST Invoice, E-Way Bill, and transport LR shared directly on WhatsApp as soon as the truck rolls out.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Sticky Direct Inquiry Form & Mahir Bhai Trade Desk Card -->
            <aside class="pd-side-col" id="pd-rfq">
                <form class="fm pd-side-form" action="contact.php" method="POST" data-reveal>
                    <h3>Get Bulk Rate for This Product</h3>
                    <p class="fm-sub">Share quantity &amp; city for today’s wholesale price.</p>

                    <label class="w">Product
                        <input name="subject" value="<?= e($p_name) ?>" readonly>
                    </label>
                    <label class="w">Your Name *
                        <input name="name" placeholder="e.g. Mahesh Shah" required>
                    </label>
                    <label class="w">Company / Shop Name *
                        <input name="company" placeholder="e.g. Shah Spice Distributors" required>
                    </label>
                    <label class="w">WhatsApp / Phone *
                        <input type="tel" name="phone" placeholder="+91 98765 43210" required>
                    </label>
                    <label class="w">Email *
                        <input type="email" name="email" placeholder="you@company.com" required>
                    </label>
                    <label>Order Quantity
                        <select name="quantity">
                            <option>200 kg – 500 kg (Trial)</option>
                            <option>1 MT – 5 MT</option>
                            <option>5 MT – 10 MT</option>
                            <option>Full Truck Load (10–25 MT)</option>
                            <option>Monthly Contract</option>
                        </select>
                    </label>
                    <label>Preferred Bag Size
                        <select name="packing">
                            <option>25 KG PP Bag (Lined)</option>
                            <option>50 KG PP Bag (Lined)</option>
                            <option>10 KG Commercial Pack</option>
                            <option>5 KG HoReCa Pack</option>
                            <option>Neutral / Custom Pack</option>
                        </select>
                    </label>
                    <label class="w">Delivery City &amp; Notes *
                        <textarea name="message" rows="3" placeholder="Mention your city and if you need a courier sample first..." required></textarea>
                    </label>
                    <div class="w">
                        <button type="submit" class="btn full-btn"><i class="fa-solid fa-paper-plane"></i> Request Today’s Rate</button>
                    </div>
                </form>

                <!-- Direct Accountability Trade Desk Card -->
                <div class="pd-desk-card" data-reveal>
                    <div class="pdd-top">
                        <div class="pdd-ico"><i class="fa-solid fa-user-tie"></i></div>
                        <div>
                            <small>Direct Wholesale Support</small>
                            <h4><?= e($brand['founder']) ?></h4>
                            <span>AMSB GLOBAL • Wankaner, Morbi</span>
                        </div>
                    </div>
                    <p>Need urgent rate confirmation or want to inspect fresh stock on video call? Reach our trade desk directly.</p>
                    <div class="pdd-links">
                        <a href="tel:+<?= e($tel) ?>"><i class="fa-solid fa-phone"></i> <?= e($disp_phone) ?></a>
                        <a href="mailto:<?= e($mail) ?>"><i class="fa-regular fa-envelope"></i> <?= e($mail) ?></a>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <!-- 4. RELATED PRODUCTS (DYNAMIC) -->
    <?php if ($related): ?>
    <section class="sec alt" id="related-products">
        <div class="wrap">
            <div class="bh" data-reveal>
                <div class="sh">
                    <span class="hi">More Wholesale Options</span>
                    <h2>Related Bulk Spices You May Need</h2>
                </div>
                <a href="<?= $p_cslug ? 'category/' . urlencode($p_cslug) : 'products.php' ?>" class="btn o d">View All <?= e($p_cat) ?></a>
            </div>

            <div class="cat-prod-grid">
                <?php foreach ($related as $i => $rp):
                    $rname = txt($rp['pro_name'] ?? '');
                    if ($rname === '') continue;
                    $rfile = preg_split('/[,|;"]+/', trim(stripslashes((string)($rp['pro_img'] ?? '')), '[]" '))[0] ?? '';
                    $rimg  = img($rfile, 'products', un($is_turmeric ? 'photo-1615485500704-8e990f9900f7' : 'photo-1588252303782-cb80119abd6d', 700));
                    $rurl  = !empty($rp['slug_url']) ? 'product/' . urlencode($rp['slug_url']) : 'product-details.php?id=' . (int)$rp['id'];
                ?>
                <article class="cp-card" data-reveal>
                    <a class="cp-img" href="<?= e($rurl) ?>">
                        <?= pic($rimg, $rname) ?>
                        <span class="cp-badge">Bulk Grade</span>
                    </a>
                    <div class="cp-body">
                        <span class="cp-cat"><?= e($rp['cat_label'] ?: $p_cat) ?></span>
                        <h3><a href="<?= e($rurl) ?>"><?= e($rname) ?></a></h3>
                        <div class="cp-actions" style="margin-top:auto;">
                            <a href="<?= e($rurl) ?>" class="btn cp-view">View Specs</a>
                            <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), please share bulk rate for: ' . $rname) ?>" target="_blank" rel="noopener" class="pc-wa" title="WhatsApp Inquiry">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
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

    // 2. Interactive Product Gallery Thumbnail Switcher
    const mainImg = document.getElementById('pdMainImg');
    const thumbs  = document.querySelectorAll('.pd-thumb');
    thumbs.forEach(btn => {
        btn.addEventListener('click', () => {
            const src = btn.getAttribute('data-src');
            if (mainImg && src) {
                mainImg.style.opacity = '0.35';
                setTimeout(() => {
                    mainImg.src = src;
                    mainImg.style.opacity = '1';
                }, 150);
            }
            thumbs.forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
        });
    });

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