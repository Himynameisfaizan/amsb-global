<?php
include_once 'includes/header1.php';

// ---- 1. Dynamic Banners (Hero Slider) ----
$banners = q("SELECT id, banner_path, title, description, link_url FROM banners WHERE status = 1 ORDER BY display_order ASC, id DESC LIMIT 5");
if (!$banners) {
    $banners = q("SELECT id, banner_path, title, description, link_url FROM banners ORDER BY display_order ASC, id DESC LIMIT 3");
}
$slides = [];
$banner_fb = ['photo-1596040033229-a9821ebd058d', 'photo-1588252303782-cb80119abd6d', 'photo-1615485500704-8e990f9900f7'];
foreach ($banners as $i => $b) {
    $slides[] = [
        'img'   => img($b['banner_path'] ?? '', 'banners', un($banner_fb[$i % 3], 1600)),
        'title' => txt($b['title'] ?? ''),
        'desc'  => txt($b['description'] ?? ''),
        'link'  => trim((string)($b['link_url'] ?? '')) ?: url('products/')
    ];
}
if (!$slides) {
    $slides[] = [
        'img'   => un('photo-1596040033229-a9821ebd058d', 1600),
        'title' => '100% Pure Red Chilli Powder & Turmeric Powder in Bulk',
        'desc'  => 'Direct wholesale supply from Wankaner, Morbi (Gujarat) with GST invoice, E-Way bill & Pan-India road transport.',
        'link'  => url('products/')
    ];
}

// ---- 2. Dynamic About Section ----
$A = q("SELECT title, content, image_url FROM about_sections ORDER BY section_order ASC, id ASC LIMIT 1")[0] ?? [];
$about_desc = txt($A['content'] ?? '');
if ($about_desc === '' || stripos($about_desc, 'Huchol') !== false) {
    $about_desc = 'AMSB GLOBAL is a reliable B2B wholesale spice trading firm headquartered in Wankaner, Morbi, Gujarat, India. Led by Mahir Bhai, we specialize in delivering 100% pure, unadulterated Red Chilli Powder and High-Curcumin Turmeric Powder to wholesalers, masala repackers, namkeen units, and HoReCa buyers across India. With a strong focus on natural colour, honest grading, and timely GST + E-Way bill dispatch, we are committed to being your trusted long-term trade partner.';
}
$about_img = img($A['image_url'] ?? '', 'about_us', un('photo-1588252303782-cb80119abd6d', 1100));

// ---- 3. Dynamic Products ("Check Our Products" - Clean 3-Column Showcase) ----
$prod_fb = [
    'photo-1588252303782-cb80119abd6d',
    'photo-1615485500704-8e990f9900f7',
    'photo-1596040033229-a9821ebd058d',
    'photo-1532336414038-cf19250c5757'
];
$prods = [];
foreach (q("SELECT p.id, p.pro_name, p.short_desc, p.description, p.pro_img, p.slug_url, c.categories AS cat
            FROM products p
            LEFT JOIN categories c ON (c.cate_id = p.pro_cate OR c.id = p.pro_cate)
            WHERE p.is_disabled = 0
            GROUP BY p.id
            ORDER BY p.id ASC
            LIMIT 6") as $i => $r) {
    $n = txt($r['pro_name'] ?? '');
    if ($n === '') continue;
    $raw_im = preg_split('/[,|;"]+/', trim(stripslashes((string)($r['pro_img'] ?? '')), '[]" '))[0] ?? '';
    $short  = txt($r['short_desc'] ?? '', 105) ?: txt($r['description'] ?? '', 105) ?: '100% pure wholesale spice grade packed in moisture-safe 5 to 50 kg lined bags.';
    $prods[] = [
        'name'  => $n,
        'short' => $short,
        'img'   => img($raw_im, 'products', un($prod_fb[$i % 4], 700)),
        'url'   => !empty($r['slug_url']) ? url('product/' . urlencode($r['slug_url'])) : url('product-details.php?id=' . (int)$r['id'])
    ];
}

// ---- 4. Dynamic Categories (Full-Bleed Image Overlay Tiles) ----
$cat_fb = [
    'photo-1588252303782-cb80119abd6d',
    'photo-1615485500704-8e990f9900f7',
    'photo-1596040033229-a9821ebd058d',
    'photo-1532336414038-cf19250c5757'
];
$cats = [];
foreach (q("SELECT categories, slug_url, image FROM categories WHERE status = 1 ORDER BY id ASC LIMIT 8") as $i => $c) {
    $cats[] = [
        'name' => txt($c['categories']),
        'url'  => url('category/' . urlencode($c['slug_url']) . '/'),
        'img'  => img($c['image'] ?? '', 'categories', un($cat_fb[$i % 4], 800))
    ];
}
?>

<main id="main" class="home-apex">

    <!-- 1. FULL-WIDTH DYNAMIC HERO BANNER SLIDER -->
    <section class="ax-hero" aria-label="Featured Wholesale Spices">
        <div class="ax-slider" id="heroSlider">
            <?php foreach ($slides as $idx => $sl): ?>
            <div class="ax-slide <?= $idx === 0 ? 'active' : '' ?>">
                <img class="ax-slide-bg" src="<?= e($sl['img']) ?>" alt="<?= e($sl['title'] ?: 'AMSB GLOBAL Bulk Spices') ?>" <?= $idx === 0 ? '' : 'loading="lazy"' ?>>
                <div class="ax-slide-shade"></div>
                <?php if ($sl['title'] !== ''): ?>
                <div class="wrap ax-slide-inner">
                    <div class="ax-hero-box">
                        <span class="ax-eyebrow"><i class="fa-solid fa-certificate"></i> Wankaner, Morbi (Gujarat) • B2B Spice Trade</span>
                        <?php if ($idx === 0): ?>
                        <h1><?= e($sl['title']) ?></h1>
                        <?php else: ?>
                        <h2><?= e($sl['title']) ?></h2>
                        <?php endif; ?>
                        <?php if ($sl['desc']): ?>
                        <p><?= e($sl['desc']) ?></p>
                        <?php endif; ?>
                        <div class="ax-hero-badges">
                            <span><i class="fa-solid fa-award"></i> Pure Quality</span>
                            <span><i class="fa-solid fa-ban"></i> Zero Dyes</span>
                            <span><i class="fa-solid fa-file-invoice"></i> GST &amp; E-Way</span>
                            <span><i class="fa-solid fa-truck-fast"></i> Pan-India</span>
                        </div>
                        <div class="ax-hero-btns">
                            <a href="<?= e($sl['link']) ?>" class="btn">Explore Products</a>
                            <a href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode($brand['wa_msg']) ?>" target="_blank" rel="noopener" class="btn wa-btn"><i class="fa-brands fa-whatsapp"></i> WhatsApp <?= e($brand['founder']) ?></a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <?php if (count($slides) > 1): ?>
            <button type="button" class="ax-arrow ax-prev" id="heroPrev" aria-label="Previous slide"><i class="fa-solid fa-chevron-left"></i></button>
            <button type="button" class="ax-arrow ax-next" id="heroNext" aria-label="Next slide"><i class="fa-solid fa-chevron-right"></i></button>
            <?php endif; ?>
        </div>

        <!-- Bottom Spice Ticker Strip (Matching Reference Bottom Bar) -->
        <div class="ax-hero-strip">
            <div class="wrap ax-strip-row">
                <a href="<?= e(url('category/red-chilli-powder/')) ?>"><i class="fa-solid fa-pepper-hot"></i> RED CHILLI POWDER</a>
                <a href="<?= e(url('category/turmeric-powder/')) ?>"><i class="fa-solid fa-sun"></i> TURMERIC POWDER</a>
                <a href="<?= e(url('product/teja-s17-high-heat-chilli-powder')) ?>"><i class="fa-solid fa-fire-flame-curved"></i> TEJA S17 CHILLI</a>
                <a href="<?= e(url('product/kashmiri-chilli-powder-high-colour')) ?>"><i class="fa-solid fa-droplet"></i> KASHMIRI MIRCH</a>
                <a href="<?= e(url('product/salem-grade-golden-haldi-powder')) ?>"><i class="fa-solid fa-mortar-pestle"></i> SALEM GOLDEN HALDI</a>
            </div>
        </div>
    </section>

    <!-- 2. 50/50 SPLIT DARK NAVY ABOUT BAND (Dynamic from about_sections) -->
    <section class="ax-about-split" id="about">
        <div class="ax-ab-left">
            <div class="ax-ab-inner">
                <span class="ax-ab-hi">Hi, We Are <?= e($brand['name']) ?> !</span>
                <p><?= e($about_desc) ?></p>
                <div class="ax-ab-meta">
                    <span><i class="fa-solid fa-user-tie"></i> Led by <?= e($brand['founder']) ?></span>
                    <span><i class="fa-solid fa-location-dot"></i> Wankaner, Morbi - 363621</span>
                </div>
                <a href="<?= e(url('about/')) ?>" class="btn ax-ab-btn">Read Company Profile <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
        <div class="ax-ab-right">
            <?= pic($about_img, 'AMSB GLOBAL Bulk Red Chilli Powder & Turmeric Powder Trading in Wankaner, Morbi') ?>
        </div>
    </section>

    <!-- 3. CHECK OUR PRODUCTS (Clean Centered 3-Column Showcase - Dynamic) -->
    <section class="ax-sec ax-products" id="products">
        <div class="wrap">
            <div class="ax-heading">
                <h2>Check Our Products</h2>
                <div class="ax-divider"><span></span></div>
            </div>

            <div class="ax-prod-grid">
                <?php foreach ($prods as $p): ?>
                <article class="ax-pcard">
                    <a class="ax-pimg" href="<?= e($p['url']) ?>">
                        <?= pic($p['img'], $p['name']) ?>
                    </a>
                    <h3><a href="<?= e($p['url']) ?>"><?= e($p['name']) ?></a></h3>
                    <p><?= e($p['short']) ?></p>
                    <a class="ax-read-btn" href="<?= e($p['url']) ?>">READ MORE</a>
                </article>
                <?php endforeach; ?>
            </div>

            <div class="ax-center-cta">
                <a href="<?= e(url('products/')) ?>" class="btn d">View Complete Wholesale Catalogue</a>
            </div>
        </div>
    </section>

    <!-- 4. FULL-WIDTH IMAGE OVERLAY CATEGORIES GRID (Dynamic) -->
    <?php if ($cats): ?>
    <section class="ax-cat-section" id="categories" aria-label="Spice Categories">
        <div class="ax-cat-grid">
            <?php foreach ($cats as $c): ?>
            <a class="ax-cat-tile" href="<?= e($c['url']) ?>">
                <?= pic($c['img'], $c['name']) ?>
                <div class="ax-cat-overlay">
                    <h3><?= e($c['name']) ?></h3>
                    <span>View Bulk Grades <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- 5. WHY WE'RE YOUR PERFECT CHOICE (50/50 Split + Trust Seal + AMSB Soft Cyan Panel) -->
    <section class="ax-why-wrap" id="why-us">
        <div class="ax-trust-seal" aria-label="100% Pure Wholesale Trade Verified">
            <div class="ats-ring">
                <i class="fa-solid fa-star"></i>
                <strong>100% PURE</strong>
                <small>GST VERIFIED TRADE</small>
            </div>
        </div>

        <div class="ax-why-grid">
            <div class="ax-why-left">
                <?= pic(un('photo-1588252303782-cb80119abd6d', 1000), 'Fresh Red Chillies and Pure Spices - AMSB GLOBAL') ?>
                <div class="ax-why-oval">
                    <h2>Why We’re Your<br>Perfect Choice</h2>
                </div>
            </div>

            <div class="ax-why-right">
                <div class="ax-feat-grid">
                    <div class="ax-feat">
                        <div class="ax-fico"><i class="fa-solid fa-thumbs-up"></i></div>
                        <span>Top Quality At Competitive Wholesale Prices</span>
                    </div>
                    <div class="ax-feat">
                        <div class="ax-fico"><i class="fa-solid fa-user-tie"></i></div>
                        <span>Direct Founder Trade Desk (<?= e($brand['founder']) ?>)</span>
                    </div>
                    <div class="ax-feat">
                        <div class="ax-fico"><i class="fa-solid fa-leaf"></i></div>
                        <span>Zero Artificial Colors, Sudan Dye or Starch</span>
                    </div>
                    <div class="ax-feat">
                        <div class="ax-fico"><i class="fa-solid fa-box-open"></i></div>
                        <span>Moisture-Free Inner-Lined Packing (5–50 KG)</span>
                    </div>
                    <div class="ax-feat">
                        <div class="ax-fico"><i class="fa-solid fa-truck-fast"></i></div>
                        <span>Fast Pan-India Road Transport Dispatch</span>
                    </div>
                    <div class="ax-feat">
                        <div class="ax-fico"><i class="fa-solid fa-file-invoice"></i></div>
                        <span>100% Clean GST Invoice &amp; E-Way Bill</span>
                    </div>
                    <div class="ax-feat">
                        <div class="ax-fico"><i class="fa-solid fa-vial-circle-check"></i></div>
                        <span>Courier Samples Dispatched in 24–48 Hours</span>
                    </div>
                    <div class="ax-feat">
                        <div class="ax-fico"><i class="fa-solid fa-scale-balanced"></i></div>
                        <span>Exact Net Weight &amp; Sample-Matched Lots</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. WHOLESALE SPICE GRADE & PAN-INDIA TRADE MATRIX (Reference Chart Style in AMSB Gold/Cyan Theme) -->
    <section class="ax-sec ax-chart-sec" id="grade-chart">
        <div class="wrap">
            <div class="ax-chart-box">
                <div class="ax-chart-head">
                    <h2>AMSB GLOBAL Wholesale Trade &amp; Spice Specification Chart</h2>
                    <p>Transparent commercial parameters, HSN billing codes, bulk bag options &amp; dispatch timelines from Wankaner, Morbi (Gujarat)</p>
                </div>

                <div class="ax-table-scroll">
                    <table class="ax-table">
                        <thead>
                            <tr>
                                <th>Product Line</th>
                                <th>Commercial Grade / Variety</th>
                                <th>HSN Code</th>
                                <th>ASTA / Curcumin</th>
                                <th>Heat / Shade</th>
                                <th>Mesh Size</th>
                                <th>Moisture</th>
                                <th>Bulk Packing</th>
                                <th>Trial MOQ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ax-tgroup" rowspan="3">Red Chilli Powder<br><small>(Lal Mirch)</small></td>
                                <td><strong>Teja (S17) High-Heat</strong></td>
                                <td>09042211</td>
                                <td>45 – 65 ASTA</td>
                                <td>75K – 95K+ SHU</td>
                                <td>60 – 80 Mesh</td>
                                <td>Max 10%</td>
                                <td>10 / 25 / 50 KG</td>
                                <td>200 – 500 KG</td>
                            </tr>
                            <tr>
                                <td><strong>Sannam (S4) Commercial</strong></td>
                                <td>09042211</td>
                                <td>60 – 100 ASTA</td>
                                <td>20K – 45K SHU</td>
                                <td>40 – 80 Mesh</td>
                                <td>Max 10%</td>
                                <td>5 / 10 / 25 / 50 KG</td>
                                <td>200 – 500 KG</td>
                            </tr>
                            <tr>
                                <td><strong>Kashmiri / Byadgi Colour</strong></td>
                                <td>09042211</td>
                                <td>120 – 160+ ASTA</td>
                                <td>Below 15K SHU</td>
                                <td>60 Mesh</td>
                                <td>Max 10.5%</td>
                                <td>5 / 10 / 25 KG</td>
                                <td>200 – 500 KG</td>
                            </tr>
                            <tr>
                                <td class="ax-tgroup" rowspan="2">Turmeric Powder<br><small>(Haldi)</small></td>
                                <td><strong>High-Curcumin Grade</strong></td>
                                <td>09103030</td>
                                <td>3.5% – 5.5%+</td>
                                <td>Deep Golden</td>
                                <td>60 – 100 Mesh</td>
                                <td>Max 9.5%</td>
                                <td>5 / 10 / 25 / 50 KG</td>
                                <td>200 – 500 KG</td>
                            </tr>
                            <tr>
                                <td><strong>Salem / Erode Golden</strong></td>
                                <td>09103030</td>
                                <td>2.5% – 3.5%</td>
                                <td>Bright Yellow</td>
                                <td>60 – 80 Mesh</td>
                                <td>Max 10%</td>
                                <td>5 / 10 / 25 / 50 KG</td>
                                <td>200 – 500 KG</td>
                            </tr>
                            <tr>
                                <td class="ax-tgroup" rowspan="2">Pan-India Dispatch<br><small>(From Wankaner)</small></td>
                                <td><strong>Part Truck Load (PTL)</strong></td>
                                <td>GST + E-Way</td>
                                <td colspan="2">Gujarat: 1–2 Days | Metro &amp; Interstate: 3–5 Days</td>
                                <td>Daily Transport</td>
                                <td>Inner-Lined</td>
                                <td>200 KG – 5 MT</td>
                                <td>Courier Sample Ready</td>
                            </tr>
                            <tr>
                                <td><strong>Full Truck Load (FTL)</strong></td>
                                <td>GST + E-Way</td>
                                <td colspan="2">Direct Factory Loading + Weighbridge Slip + Video Check</td>
                                <td>All India Mandis</td>
                                <td>Custom / Neutral</td>
                                <td>10 MT – 25 MT</td>
                                <td>Monthly Rate Lock</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. COMPACT DIRECT WHOLESALE RATE CTA BAR -->
    <section class="ax-cta-strip">
        <div class="wrap ax-cta-flex">
            <div>
                <span class="ax-cta-tag">Direct Mill Rates • Zero Middlemen</span>
                <h2>Need Today’s Wholesale Rate or Courier Sample?</h2>
                <p>Call or WhatsApp <strong><?= e($brand['founder']) ?> (<?= e($disp_phone) ?>)</strong> directly at our Wankaner, Morbi trade desk.</p>
            </div>
            <div class="ax-cta-btns">
                <a href="<?= e(url('contact/')) ?>" class="btn"><i class="fa-solid fa-file-invoice-dollar"></i> Request Bulk Quote</a>
                <a href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode($brand['wa_msg']) ?>" target="_blank" rel="noopener" class="btn wa-btn"><i class="fa-brands fa-whatsapp"></i> WhatsApp Now</a>
            </div>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Hero Banner Slider
    const slides = document.querySelectorAll('#heroSlider .ax-slide');
    const prevBtn = document.getElementById('heroPrev');
    const nextBtn = document.getElementById('heroNext');
    let cur = 0, timer;

    function showSlide(n) {
        if (!slides.length) return;
        slides[cur].classList.remove('active');
        cur = (n + slides.length) % slides.length;
        slides[cur].classList.add('active');
    }
    function startAuto() {
        if (slides.length > 1) timer = setInterval(() => showSlide(cur + 1), 5500);
    }
    if (prevBtn) prevBtn.addEventListener('click', () => { clearInterval(timer); showSlide(cur - 1); startAuto(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { clearInterval(timer); showSlide(cur + 1); startAuto(); });
    startAuto();

    // 2. Mobile Menu Toggle
    const tg = document.getElementById('mobToggle');
    const mobNav = document.getElementById('mobNav');
    if (tg && mobNav) {
        tg.addEventListener('click', () => {
            const open = mobNav.classList.toggle('open');
            tg.setAttribute('aria-expanded', String(open));
        });
    }
});
</script>

<?php include 'includes/footer1.php'; ?>