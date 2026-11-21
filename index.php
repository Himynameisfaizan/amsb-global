<?php
// Header = DB connect + SEO + <head> + topbar + navbar
include_once 'includes/header.php';

// Updated: Gold and Cyan category tile colors based on new AMSB GLOBAL logo
$CC  = ['#C89B3C', '#2BB3D6', '#A67C29', '#1A92B3', '#D6AF5C', '#45C2E0']; 
$FCI = [
    'photo-1596040033229-a9821ebd058d', // Spices
    'photo-1588252303782-cb80119abd6d', // Red Chilli
    'photo-1615485500704-8e990f9900f7', // Turmeric
    'photo-1532336414038-cf19250c5757'  // Whole Spices
];

// ---- Hero + About (Dynamic from DB) ----
$B = q("SELECT banner_path, title, description FROM banners ORDER BY display_order ASC, id DESC LIMIT 1");
$hero = img($B[0]['banner_path'] ?? '', 'banners', un('photo-1596040033229-a9821ebd058d', 1000));

$A = q("SELECT title, content, image_url FROM about_sections ORDER BY section_order ASC, id ASC LIMIT 1");
$raw_about_t = txt($A[0]['title'] ?? '');
$raw_about_c = txt($A[0]['content'] ?? '');
$about_t = ($raw_about_t && stripos($raw_about_t, 'Huchol') === false && stripos($raw_about_t, 'Dry Fruits') === false)
    ? $raw_about_t
    : 'Leading Processor & Exporter of Pure Red Chilli Powder & Turmeric Powder from Morbi, Gujarat';
$about_c = ($raw_about_c && stripos($raw_about_c, 'Huchol') === false)
    ? $raw_about_c
    : 'At AMSB GLOBAL, headquartered in Wankaner, Morbi (Gujarat), we specialize in sourcing, hygienically grinding, and supplying 100% pure Red Chilli Powder and high-curcumin Turmeric Powder in bulk. Led by Mahir Bhai, we serve spice wholesalers, food manufacturers, masala brands, and international importers with guaranteed sample-matching quality and zero adulteration.';
$about_i = img($A[0]['image_url'] ?? '', 'about_us', un('photo-1596040033229-a9821ebd058d', 900));

// ---- Categories: Dynamic DB first + Spice Core Aisles ----
$FC = [
    ['Red Chilli Powder', 'red-chilli-powder', 'Teja S17, Sannam S4, Byadgi & Kashmiri grades ground to custom mesh.', 'photo-1588252303782-cb80119abd6d'],
    ['Turmeric Powder',   'turmeric-powder',   'High-curcumin Salem, Erode & Rajapuri haldi powder for bulk trade.', 'photo-1615485500704-8e990f9900f7'],
    ['Ground Spices',     'ground-spices',     'Hygienically milled pure spices with natural aroma & zero added colour.', 'photo-1596040033229-a9821ebd058d'],
    ['Whole Indian Spices','whole-spices',     'Export-cleaned whole chillies, turmeric fingers & seed spices in 25/50 kg bags.', 'photo-1532336414038-cf19250c5757'],
];
$cats = []; $seen = [];
foreach (q("SELECT categories, slug_url, image FROM categories WHERE status=1 ORDER BY id ASC") as $i => $r) {
    $cats[] = [
        $r['categories'],
        $r['slug_url'],
        'Hygienically processed, lab-verified and packed in 5 kg to 50 kg bulk export bags.',
        img($r['image'], 'categories', un($FCI[$i % 4], 700))
    ];
    $seen[strtolower($r['categories'])] = 1;
}
foreach ($FC as $f) {
    if (!isset($seen[strtolower($f[0])]) && count($cats) < 6) {
        $cats[] = [$f[0], $f[1], $f[2], un($f[3], 700)];
    }
}

// ---- Products: Dynamic from DB ----
$FP = [
    ['Pure Red Chilli Powder (Export Grade)', 'Red Chilli Powder', 'red-chilli-powder', 'photo-1588252303782-cb80119abd6d'],
    ['High-Curcumin Turmeric Powder', 'Turmeric Powder', 'turmeric-powder', 'photo-1615485500704-8e990f9900f7'],
    ['Teja S17 High-Heat Chilli Powder', 'Red Chilli Powder', 'red-chilli-powder', 'photo-1596040033229-a9821ebd058d'],
    ['Salem Grade Golden Haldi Powder', 'Turmeric Powder', 'turmeric-powder', 'photo-1532336414038-cf19250c5757'],
];
$P = [];
foreach (q("SELECT p.pro_name, p.pro_img, p.slug_url, c.categories AS cat, c.slug_url AS cslug FROM products p LEFT JOIN categories c ON c.cate_id = p.pro_cate WHERE p.is_disabled = 0 ORDER BY p.id DESC LIMIT 8") as $i => $r) {
    if (txt($r['pro_name']) === '') continue;
    $f = preg_split('/[,|;"]+/', trim(stripslashes((string)$r['pro_img']), '[]" '))[0];
    $P[] = ['n' => txt($r['pro_name']), 'cat' => $r['cat'] ?: 'Indian Spices', 'cs' => $r['cslug'] ?: 'spices', 'img' => img($f, 'products', un($FP[$i % 4][3], 700)), 'slug' => $r['slug_url'] ?? ''];
}
if (count($P) < 4) {
    $P = [];
    foreach ($FP as $x) { $P[] = ['n' => $x[0], 'cat' => $x[1], 'cs' => $x[2], 'img' => un($x[3], 700), 'slug' => '']; }
}
$chips = [];
foreach ($P as $p) $chips[$p['cs']] = $p['cat'];

// ---- Services & Testimonials (Dynamic from DB) ----
$FS = [
    ['Custom Mesh Grinding & Blending', 'Precision grinding (40 to 100 mesh) tailored to your required ASTA colour, SHU pungency, and curcumin percentage.'],
    ['Bulk & Private Label Packaging', 'Available in 5 kg, 10 kg, 25 kg, and 50 kg PP/HDPE/Jute bags with inner liner or custom OEM consumer pouches.'],
    ['Strict Lab & Pre-Loading Inspection', 'Every batch tested for moisture (<10%), purity, and zero artificial dyes before sealing the truck or container.'],
    ['Fast Domestic & Port Dispatch', 'Strategic Wankaner, Morbi location with rapid connectivity to Mundra & Kandla ports plus pan-India transport.'],
];
$S = [];
foreach (q("SELECT service_name, short_desc FROM services ORDER BY id ASC LIMIT 4") as $r) {
    if (txt($r['service_name']) && txt($r['short_desc'])) { $S[] = [txt($r['service_name']), txt($r['short_desc'])]; }
}
foreach ($FS as $f) if (count($S) < 4) $S[] = $f;

$T = [];
foreach (q("SELECT * FROM testimonials WHERE status=1 ORDER BY display_order ASC, test_id DESC LIMIT 6") as $r) {
    $m = txt($r['message'] ?? '');
    if ($m === '') continue;
    $T[] = [txt($r['name'] ?? '') ?: 'Trade Buyer', trim(txt($r['designation'] ?? '') . ' ' . txt($r['client_company'] ?? '')), $m, max(1, min(5, (int)($r['rating'] ?? 5))), !empty($r['image']) ? img($r['image'], 'testimonials', '') : ''];
}
if (count($T) < 2) {
    $T = [
        ['Rajesh Patel', 'Procurement Head, Masala & Food Processing Unit', 'AMSB GLOBAL’s Red Chilli Powder and Turmeric Powder matched our lab sample 100%. Natural colour, strong aroma, and timely dispatch from Wankaner.', 5, ''],
        ['Imran Al-Mansoor', 'Import Director, Gulf Spice Distributors', 'Working with Mahir Bhai has been seamless. Clean export paperwork, moisture-proof 25 kg packaging, and consistent curcumin levels in every lot.', 5, ''],
    ];
}
$Bl = q("SELECT title, slug, author, image, meta_desc, description, created_at FROM blogs WHERE status=1 ORDER BY created_at DESC, blog_id DESC LIMIT 3");
?>

<main id="main">
    <!-- 1. B2B INDUSTRIAL SPICE HERO (Cleaned Up & Simplified) -->
    <section class="hero">
        <div class="wrap hero-grid">
            <div class="hero-txt">
                <div class="hero-badges">
                    <span class="pill-primary"><i class="fa-solid fa-fire-flame-curved"></i> Pure &amp;
                        Unadulterated</span>
                    <span class="pill-secondary"><i class="fa-solid fa-location-dot"></i> Wankaner, Morbi
                        (Gujarat)</span>
                </div>
                <h1>Bulk <span class="txt-primary">Red Chilli</span> &amp; <span class="txt-secondary">Turmeric</span>
                    Powder Exporter.</h1>
                <p class="lead">Direct from Gujarat’s spice processing hub. Hygienically ground, lab-verified for
                    natural colour, heat, and curcumin—packed to your exact specification.</p>

                <div class="cta">
                    <a href="#rfq" class="btn"><i class="fa-solid fa-file-invoice-dollar"></i> Get Bulk Quote</a>
                    <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), I want to enquire about bulk Red Chilli Powder and Turmeric Powder rates.') ?>"
                        target="_blank" rel="noopener" class="btn o"><i class="fa-brands fa-whatsapp"></i> Chat on
                        WhatsApp</a>
                </div>
            </div>

            <!-- Right Showcase Card Stack -->
            <div class="hero-visual">
                <div class="hv-main">
                    <?= pic($hero, 'AMSB GLOBAL Red Chilli Powder and Turmeric Powder Bulk Supply', true) ?>
                    <div class="hv-overlay">
                        <span class="hv-tag">Direct Factory Dispatch • Morbi, Gujarat</span>
                        <h3>Precision-Ground Spices for Global Buyers</h3>
                    </div>
                </div>
                <div class="hv-cards">
                    <div class="hv-card spice-card-1">
                        <div class="hv-icon"><i class="fa-solid fa-Pepper-hot"></i></div>
                        <div>
                            <small>Flagship Line</small>
                            <h4>Red Chilli Powder</h4>
                            <p>Teja S17, Byadgi &amp; Custom ASTA</p>
                        </div>
                    </div>
                    <div class="hv-card spice-card-2">
                        <div class="hv-icon"><i class="fa-solid fa-bowl-food"></i></div>
                        <div>
                            <small>Flagship Line</small>
                            <h4>Turmeric Powder</h4>
                            <p>High Curcumin Salem &amp; Erode</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. B2B CREDIBILITY STRIP -->
    <section class="trust">
        <div class="wrap">
            <div class="tr-grid">
                <div class="tr-item">
                    <i class="fa-solid fa-industry"></i>
                    <div>
                        <h3>Hygienic Grinding Unit</h3>
                        <p>Low-temperature milling that locks in natural essential oils, colour &amp; pungency.</p>
                    </div>
                </div>
                <div class="tr-item">
                    <i class="fa-solid fa-flask-vial"></i>
                    <div>
                        <h3>100% Adulteration-Free</h3>
                        <p>Zero Sudan dye, zero lead chromate, zero added starch or foreign husk.</p>
                    </div>
                </div>
                <div class="tr-item">
                    <i class="fa-solid fa-ship"></i>
                    <div>
                        <h3>Mundra &amp; Kandla Port Proximity</h3>
                        <p>Located in Wankaner, Morbi for fast container stuffing and low inland freight.</p>
                    </div>
                </div>
                <div class="tr-item">
                    <i class="fa-solid fa-file-shield"></i>
                    <div>
                        <h3>Complete Trade Documentation</h3>
                        <p>GST Invoice, E-Way Bill, COA Lab Report, Phytosanitary &amp; Certificate of Origin.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. TECHNICAL LAB SPEC MATRIX -->
    <section class="sec" id="lab-specs">
        <div class="wrap">
            <div class="sh center">
                <span class="hi">Technical Grade Sheet for Wholesale Buyers</span>
                <h2>Our Core Spice Specifications</h2>
                <p>B2B buyers buy on measurable parameters—not vague promises. Review our standard commercial grades for
                    Red Chilli Powder and Turmeric Powder below, or request custom grinding to your lab sheet.</p>
            </div>

            <div class="spec-showcase">
                <div class="spec-box chilli-spec">
                    <div class="spec-head">
                        <div>
                            <span class="spec-badge">HS Code: 09042211</span>
                            <h3>Red Chilli Powder (Lal Mirch)</h3>
                            <p>Available in High-Heat, High-Colour &amp; Balanced Commercial Blends</p>
                        </div>
                        <i class="fa-solid fa-fire-flame-simple spec-ico"></i>
                    </div>
                    <div class="spec-table">
                        <div class="st-row"><span>Varieties Processed</span><strong>Teja (S17), Sannam (S4), Byadgi,
                                Kashmiri</strong></div>
                        <div class="st-row"><span>Colour Value (ASTA)</span><strong>60 ASTA to 160+ ASTA (As per buyer
                                spec)</strong></div>
                        <div class="st-row"><span>Pungency (Heat Level)</span><strong>15,000 to 85,000 SHU</strong>
                        </div>
                        <div class="st-row"><span>Fineness / Mesh Size</span><strong>40 Mesh to 80 Mesh (Custom
                                Sieving)</strong></div>
                        <div class="st-row"><span>Moisture Content</span><strong>Max 10% – 11%</strong></div>
                        <div class="st-row"><span>Total Ash / Acid Insoluble</span><strong>Max 7.0% / Max 1.25%</strong>
                        </div>
                        <div class="st-row"><span>Adulteration Guarantee</span><strong>100% Free from Sudan I–IV &amp;
                                Artificial Dyes</strong></div>
                    </div>
                    <div class="spec-foot">
                        <button type="button" class="btn pc-btn" data-p="Red Chilli Powder - Custom Grade">Get Red
                            Chilli Powder Quote</button>
                        <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai, please share today’s bulk rate and lab specs for Red Chilli Powder.') ?>"
                            target="_blank" rel="noopener" class="btn o"><i class="fa-brands fa-whatsapp"></i> Ask
                            Sample</a>
                    </div>
                </div>

                <div class="spec-box turmeric-spec">
                    <div class="spec-head">
                        <div>
                            <span class="spec-badge">HS Code: 09103030</span>
                            <h3>Turmeric Powder (Haldi Powder)</h3>
                            <p>Pure Sun-Dried &amp; Polished Turmeric Fingers Ground to Fine Mesh</p>
                        </div>
                        <i class="fa-solid fa-sun-plant-wilt spec-ico"></i>
                    </div>
                    <div class="spec-table">
                        <div class="st-row"><span>Origins / Varieties</span><strong>Salem, Erode, Rajapuri &amp;
                                Nizamabad</strong></div>
                        <div class="st-row"><span>Curcumin Content</span><strong>2.5% to 5.5%+ (Lab Tested)</strong>
                        </div>
                        <div class="st-row"><span>Colour &amp; Aroma</span><strong>Natural Deep Golden Yellow, Earthy
                                Aroma</strong></div>
                        <div class="st-row"><span>Fineness / Mesh Size</span><strong>60 Mesh to 100 Mesh
                                (Micro-Fine)</strong></div>
                        <div class="st-row"><span>Moisture Content</span><strong>Max 9% – 10%</strong></div>
                        <div class="st-row"><span>Total Ash / Acid Insoluble</span><strong>Max 7.0% / Max 1.5%</strong>
                        </div>
                        <div class="st-row"><span>Adulteration Guarantee</span><strong>100% Free from Lead Chromate
                                &amp; Added Starch</strong></div>
                    </div>
                    <div class="spec-foot">
                        <button type="button" class="btn pc-btn" data-p="Turmeric Powder - High Curcumin">Get Turmeric
                            Powder Quote</button>
                        <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai, please share today’s bulk rate and curcumin specs for Turmeric Powder.') ?>"
                            target="_blank" rel="noopener" class="btn o"><i class="fa-brands fa-whatsapp"></i> Ask
                            Sample</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. DYNAMIC CATEGORIES SECTION -->
    <section class="sec alt" id="categories">
        <div class="wrap">
            <div class="sh">
                <span class="hi">Complete B2B Product Lines</span>
                <h2>Sourcing Categories at AMSB GLOBAL</h2>
                <p>Explore our product categories below. All items are supplied in commercial wholesale and export
                    quantities.</p>
            </div>
            <div class="cat-grid">
                <?php foreach ($cats as $i => $c): ?>
                <a class="cat" href="category/<?= e($c[1]) ?>" style="--c:<?= $CC[$i % 6] ?>">
                    <div class="ph"><?= pic($c[3], $c[0]) ?></div>
                    <div class="bd">
                        <span class="cat-tag">Bulk Supply</span>
                        <h3><?= e($c[0]) ?></h3>
                        <p><?= e($c[2]) ?></p>
                        <span class="cat-link">Explore Range <i class="fa-solid fa-arrow-right"></i></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 5. DYNAMIC PRODUCTS CATALOGUE -->
    <section class="sec" id="products">
        <div class="wrap">
            <div class="sh">
                <span class="hi">Ready for Immediate Dispatch</span>
                <h2>Export &amp; Wholesale Catalogue</h2>
                <p>Commodity rates move daily with mandi arrivals. Select a product below to request today’s ex-factory
                    Wankaner, FOB Mundra, or CIF quotation.</p>
            </div>
            <div class="chips" role="group" aria-label="Filter products">
                <button type="button" data-f="all" aria-pressed="true">All Products</button>
                <?php foreach ($chips as $cs => $cn): ?>
                <button type="button" data-f="<?= e($cs) ?>" aria-pressed="false"><?= e($cn) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="pg-grid">
                <?php foreach ($P as $p): $u = $p['slug'] ? 'product/' . urlencode($p['slug']) : 'products'; ?>
                <article class="pc" data-c="<?= e($p['cs']) ?>">
                    <a class="ph" href="<?= e($u) ?>">
                        <?= pic($p['img'], $p['n']) ?>
                        <span class="pc-badge">Export Grade</span>
                    </a>
                    <div class="bd">
                        <small class="tag"><?= e($p['cat']) ?></small>
                        <h3><a href="<?= e($u) ?>"><?= e($p['n']) ?></a></h3>
                        <p>Custom mesh &amp; grade options • 10 / 25 / 50 kg bulk bags • Sample available.</p>
                        <div class="pc-actions">
                            <button type="button" class="pc-btn" data-p="<?= e($p['n']) ?>">Request Rate</button>
                            <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), I need bulk price & MOQ for: ' . $p['n']) ?>"
                                target="_blank" rel="noopener" class="pc-wa" title="WhatsApp Inquiry"><i
                                    class="fa-brands fa-whatsapp"></i></a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 6. DYNAMIC ABOUT US + MAHIR BHAI TRADE DESK CARD -->
    <section class="sec alt" id="about">
        <div class="wrap ab">
            <div class="pics">
                <div class="p1"><?= pic($about_i, 'AMSB GLOBAL Spice Processing & Warehouse Wankaner Morbi') ?></div>
                <div class="founder-card">
                    <div class="fc-badge"><i class="fa-solid fa-user-tie"></i> Direct Leadership</div>
                    <h4><?= e($brand['founder']) ?></h4>
                    <p>Founder &amp; Head of B2B Trade, <strong>AMSB GLOBAL</strong></p>
                    <div class="fc-meta">
                        <span><i class="fa-solid fa-location-dot"></i> Wankaner, Morbi, Gujarat - 363621</span>
                        <span><i class="fa-solid fa-phone"></i> <?= e($disp_phone) ?></span>
                    </div>
                </div>
            </div>
            <div>
                <div class="sh">
                    <span class="hi">About AMSB GLOBAL • Wankaner, Morbi</span>
                    <h2><?= e($about_t) ?></h2>
                </div>
                <p class="abt">
                    <?= e($about_c ? mb_strimwidth($about_c, 0, 520, '...') : 'At AMSB GLOBAL, based in Wankaner, Morbi (Gujarat), we process and supply pure Red Chilli Powder and Turmeric Powder for domestic wholesale and global export markets.') ?>
                </p>
                <ul class="pts">
                    <li>
                        <b>100% Sample-Matched Lots</b>
                        Every bag loaded into the truck or container strictly matches the physical sample and lab
                        parameters approved by you.
                    </li>
                    <li>
                        <b>Strategic Gujarat Export Hub</b>
                        Situated in Wankaner, Morbi (363621)—giving our buyers fast access to Gujarat’s premier
                        agricultural belts and Mundra/Kandla seaports.
                    </li>
                    <li>
                        <b>Flexible B2B Packaging</b>
                        Supplied in 5 kg, 10 kg, 25 kg, and 50 kg PP/HDPE/Jute bags, carton boxes, or custom
                        private-label retail packs.
                    </li>
                </ul>
                <div class="ab-actions">
                    <a href="about" class="btn d">Read Company Profile</a>
                    <a href="tel:+<?= e($tel) ?>" class="btn o d"><i class="fa-solid fa-phone"></i> Call
                        <?= e($brand['founder']) ?></a>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. BULK PACKAGING & CONTAINER LOAD CAPACITY -->
    <section class="sec dk" id="packaging">
        <div class="wrap">
            <div class="sh center">
                <span class="hi">Logistics &amp; Export Readiness</span>
                <h2>Bulk Packaging &amp; Container Load Guide</h2>
                <p>We engineer our spice packaging to prevent moisture ingress, aroma loss, and transit damage across
                    long-distance domestic and sea routes.</p>
            </div>
            <div class="pack-grid">
                <div class="pack-card">
                    <span class="pk-tag">Option 01</span>
                    <h3>25 KG / 50 KG PP &amp; HDPE Bags</h3>
                    <p>Heavy-duty woven polypropylene bags with food-grade poly inner liner. Ideal for wholesale
                        traders, masala blenders, and bulk importers.</p>
                    <small>MOQ: 500 KG – 1 MT</small>
                </div>
                <div class="pack-card">
                    <span class="pk-tag">Option 02</span>
                    <h3>5 KG / 10 KG Corrugated Cartons</h3>
                    <p>Double-wall export master cartons with heat-sealed food-grade LDPE liners inside. Protects fine
                        powders from humidity and stacking pressure.</p>
                    <small>Ideal for HoReCa &amp; Food Service</small>
                </div>
                <div class="pack-card">
                    <span class="pk-tag">Option 03</span>
                    <h3>Private Label / OEM Pouches</h3>
                    <p>100g, 200g, 500g, and 1 kg laminated stand-up or pillow pouches printed with your brand name and
                        packed in export master cartons.</p>
                    <small>Custom Branding Supported</small>
                </div>
                <div class="pack-card highlight-pack">
                    <span class="pk-tag">FCL Sea Freight Capacity</span>
                    <h3>Container Load Stuffing</h3>
                    <ul class="load-list">
                        <li><span>20ft FCL (Chilli Powder):</span> <strong>~14 to 16 MT</strong></li>
                        <li><span>20ft FCL (Turmeric Powder):</span> <strong>~17 to 19 MT</strong></li>
                        <li><span>40ft HC Container:</span> <strong>~26 to 28 MT</strong></li>
                        <li><span>Domestic Truck Load:</span> <strong>10 MT to 25 MT</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. DYNAMIC SERVICES + 4-STEP ORDER WORKFLOW -->
    <section class="sec" id="services">
        <div class="wrap">
            <div class="sh">
                <span class="hi">End-to-End B2B Execution</span>
                <h2>What Our Trade Desk Handles</h2>
                <p>From raw whole-spice selection at the mandi to final container sealing at Wankaner, our team manages
                    every stage.</p>
            </div>
            <div class="sv-grid">
                <?php foreach ($S as $idx => $s): ?>
                <div class="sv">
                    <span class="sv-num">0<?= $idx + 1 ?></span>
                    <h3><?= e($s[0]) ?></h3>
                    <p><?= e(mb_strimwidth($s[1], 0, 180, '...')) ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- 4-Step Process Bar -->
            <div class="process-box" id="process">
                <div class="pb-head">
                    <h3>How Your Bulk Order Moves</h3>
                    <span>Simple, transparent 4-step B2B procurement</span>
                </div>
                <ol class="steps">
                    <li class="st">
                        <span class="n">1</span>
                        <h3>Share Requirement</h3>
                        <p>Send us your required spice, ASTA/Curcumin grade, mesh size, pack weight &amp; destination.
                        </p>
                    </li>
                    <li class="st">
                        <span class="n">2</span>
                        <h3>Sample &amp; Quotation</h3>
                        <p>Receive our competitive rate sheet and physical courier sample for lab &amp; kitchen
                            verification.</p>
                    </li>
                    <li class="st">
                        <span class="n">3</span>
                        <h3>Grinding &amp; QC Check</h3>
                        <p>Fresh batch grinding, sieving, moisture check &amp; pre-loading photos/videos shared with
                            you.</p>
                    </li>
                    <li class="st">
                        <span class="n">4</span>
                        <h3>Dispatch &amp; Paperwork</h3>
                        <p>Fast dispatch via transport or Mundra port with GST invoice, E-Way bill &amp; export
                            documents.</p>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <!-- 9. DYNAMIC TESTIMONIALS SECTION -->
    <section class="sec alt" id="testimonials">
        <div class="wrap">
            <div class="sh">
                <span class="hi">Verified Buyer Feedback</span>
                <h2>Trusted by Spice Traders &amp; Importers</h2>
            </div>
            <div class="tc-scroll" tabindex="0">
                <?php foreach ($T as $t): ?>
                <figure class="tc">
                    <span class="stars" aria-label="<?= $t[3] ?> out of 5"><?= str_repeat('★', $t[3]) ?></span>
                    <blockquote>“<?= e($t[2]) ?>”</blockquote>
                    <figcaption class="au">
                        <?= $t[4] ? '<img src="' . e($t[4]) . '" alt="" onerror="this.style.display=\'none\'">' : '<i>' . e(strtoupper(mb_substr($t[0], 0, 1))) . '</i>' ?>
                        <div>
                            <b><?= e($t[0]) ?></b>
                            <?php if ($t[1]): ?><small><?= e($t[1]) ?></small><?php endif; ?>
                        </div>
                    </figcaption>
                </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- 10. DYNAMIC BLOGS SECTION -->
    <?php if ($Bl): ?>
    <section class="sec" id="blogs">
        <div class="wrap">
            <div class="bh">
                <div class="sh">
                    <span class="hi">Spice Market Intelligence</span>
                    <h2>B2B Buying Guides &amp; Export Notes</h2>
                </div>
                <a href="blog.php" class="btn o d">View All Articles</a>
            </div>
            <div class="bg-grid">
                <?php foreach ($Bl as $b):
                $bi = img($b['image'], 'blogs', un('photo-1596040033229-a9821ebd058d', 700));
                $ex = txt($b['meta_desc'], 125) ?: txt($b['description'], 125);
                $u = 'blog/' . urlencode($b['slug']); ?>
                <article class="bl">
                    <a class="im" href="<?= e($u) ?>"><?= pic($bi, $b['title']) ?></a>
                    <div class="bl-body">
                        <small class="meta"><i class="fa-regular fa-calendar"></i>
                            <?= e(date('d M Y', strtotime($b['created_at'] ?: 'now'))) ?> •
                            <?= e($b['author']) ?></small>
                        <h3><a href="<?= e($u) ?>"><?= e($b['title']) ?></a></h3>
                        <p><?= e($ex) ?></p>
                        <a class="rd" href="<?= e($u) ?>">Read Full Guide <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 11. B2B RFQ & DIRECT TRADE DESK CONTACT -->
    <section class="sec rq" id="rfq">
        <div class="wrap rg">
            <div class="rq-info">
                <div class="sh">
                    <span class="hi">Direct Mill Pricing • Zero Middlemen</span>
                    <h2>Get Today’s Bulk Spice Quote</h2>
                    <p>Share your required grade, mesh size, packaging, and destination.
                        <strong><?= e($brand['founder']) ?></strong> and our trade desk respond promptly with current
                        ex-Wankaner or FOB/CIF rates.</p>
                </div>

                <div class="direct-desk-box">
                    <h4><i class="fa-solid fa-headset"></i> Direct B2B Trade Desk</h4>
                    <div class="dd-row"><span>Company:</span> <strong><?= e($brand['name']) ?></strong></div>
                    <div class="dd-row"><span>Contact Person:</span> <strong><?= e($brand['founder']) ?></strong></div>
                    <div class="dd-row"><span>Phone / WhatsApp:</span> <a
                            href="tel:+<?= e($tel) ?>"><strong><?= e($disp_phone) ?></strong></a></div>
                    <div class="dd-row"><span>Email ID:</span> <a
                            href="mailto:<?= e($mail) ?>"><strong><?= e($mail) ?></strong></a></div>
                    <div class="dd-row"><span>Factory &amp; Office:</span> <strong><?= e($brand['address']) ?></strong>
                    </div>
                </div>

                <ul class="pts">
                    <li><b>Courier Samples Dispatched Within 24–48 Hours</b>Test our colour, aroma, and purity before
                        confirming your bulk purchase order.</li>
                    <li><b>Live Video Call Factory &amp; Loading Inspection</b>Watch your lot being packed and loaded in
                        Wankaner, Morbi.</li>
                </ul>
            </div>

            <form class="fm" action="contact.php" method="POST">
                <h3>Send Bulk Trade Inquiry</h3>
                <p class="fm-sub">Fill in your details below for a fast written quotation.</p>
                <label>Your Name *
                    <input name="name" placeholder="e.g. Rajesh Patel" autocomplete="name" required>
                </label>
                <label>Company / Firm Name *
                    <input name="company" placeholder="e.g. Global Spice Traders" autocomplete="organization" required>
                </label>
                <label>Business Email *
                    <input type="email" name="email" placeholder="you@company.com" autocomplete="email" required>
                </label>
                <label>WhatsApp / Mobile Number *
                    <input type="tel" name="phone" placeholder="+91 98765 43210" autocomplete="tel" required>
                </label>
                <label>Product Required *
                    <input id="pi" name="subject" placeholder="Red Chilli Powder, Turmeric Powder..."
                        value="Red Chilli Powder & Turmeric Powder" required>
                </label>
                <label>Estimated Order Quantity
                    <select name="quantity">
                        <option>Trial Bulk Order (500 kg – 1 MT)</option>
                        <option>1 MT to 5 MT</option>
                        <option>Full Truck Load (10 MT – 25 MT)</option>
                        <option>20ft FCL Container (14 – 18 MT)</option>
                        <option>40ft HC Container (26 – 28 MT)</option>
                        <option>Monthly Contract Supply</option>
                    </select>
                </label>
                <label class="w">Delivery City / Destination Port
                    <input name="destination" placeholder="e.g. Ahmedabad / Mumbai / Jebel Ali, UAE">
                </label>
                <label class="w">Grade, Mesh &amp; Packaging Specifications *
                    <textarea name="message" rows="3"
                        placeholder="Specify required ASTA colour / SHU heat / Curcumin %, mesh size, and bag size (10kg / 25kg / 50kg PP or private label)..."
                        required></textarea>
                </label>
                <div class="w">
                    <button type="submit" class="btn full-btn"><i class="fa-solid fa-paper-plane"></i> Request Bulk Rate
                        &amp; Sample</button>
                </div>
            </form>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chipBtns = document.querySelectorAll('.chips button[data-f]');
    const productCards = document.querySelectorAll('.pg-grid .pc[data-c]');
    chipBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.getAttribute('data-f');
            chipBtns.forEach(b => b.setAttribute('aria-pressed', 'false'));
            btn.setAttribute('aria-pressed', 'true');
            productCards.forEach(card => {
                const cat = card.getAttribute('data-c');
                card.style.display = (filter === 'all' || cat === filter) ? '' : 'none';
            });
        });
    });

    const prodInput = document.getElementById('pi');
    const rfqSection = document.getElementById('rfq');
    document.querySelectorAll('[data-p]').forEach(btn => {
        btn.addEventListener('click', () => {
            const prodName = btn.getAttribute('data-p');
            if (prodInput && prodName) {
                prodInput.value = prodName;
            }
            if (rfqSection) {
                rfqSection.scrollIntoView({
                    behavior: 'smooth'
                });
                setTimeout(() => prodInput && prodInput.focus(), 500);
            }
        });
    });

    const tg = document.querySelector('.tg');
    const nav = document.getElementById('nav');
    if (tg && nav) {
        tg.addEventListener('click', () => {
            const expanded = tg.getAttribute('aria-expanded') === 'true';
            tg.setAttribute('aria-expanded', String(!expanded));
            nav.classList.toggle('open');
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>