<?php
// Page-level SEO & Schema Fallbacks (Database meta/page_schemas will override automatically if filled in Admin)
$https_tmp = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
$base_url_tmp = ($https_tmp ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';
$about_url_tmp = $base_url_tmp . 'about.php';

$page_meta = [
    'title' => 'About AMSB GLOBAL | Bulk Red Chilli & Turmeric Powder Wholesale Trader in Wankaner, Morbi',
    'desc'  => 'Know more about AMSB GLOBAL, led by Mahir Bhai in Wankaner, Morbi (Gujarat). Trusted B2B wholesale supplier of pure Red Chilli Powder and Turmeric Powder for traders, retailers, and food businesses.',
    'key'   => 'About AMSB Global, Spice Wholesale Trader Morbi, Red Chilli Powder Supplier Wankaner, Turmeric Powder Bulk Trader Gujarat, Mahir Bhai Spices',
    'schema' => '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "AboutPage",
      "@id": "' . $about_url_tmp . '#aboutpage",
      "url": "' . $about_url_tmp . '",
      "name": "About AMSB GLOBAL - Wholesale Spice Trader in Wankaner, Morbi, Gujarat",
      "description": "Learn about AMSB GLOBAL, a B2B wholesale spice trading firm in Wankaner, Morbi, Gujarat supplying pure Red Chilli Powder and Turmeric Powder in bulk.",
      "inLanguage": "en-IN",
      "isPartOf": { "@id": "' . $base_url_tmp . '#website" },
      "mainEntity": { "@id": "' . $base_url_tmp . '#organization" }
    },
    {
      "@type": "Organization",
      "@id": "' . $base_url_tmp . '#organization",
      "name": "AMSB GLOBAL",
      "url": "' . $base_url_tmp . '",
      "email": "amsbglobal@gmail.com",
      "telephone": "+91-8200778206",
      "founder": {
        "@type": "Person",
        "name": "Mahir Bhai"
      },
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Wankaner",
        "addressLocality": "Morbi",
        "addressRegion": "Gujarat",
        "postalCode": "363621",
        "addressCountry": "IN"
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "' . $about_url_tmp . '#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "' . $base_url_tmp . '" },
        { "@type": "ListItem", "position": 2, "name": "About Us", "item": "' . $about_url_tmp . '" }
      ]
    }
  ]
}
</script>'
];

include_once 'includes/header.php';

// ---- ONLY DYNAMIC SECTION: Fetch About Us from DB (about_us first, fallback to about_sections) ----
$ab_main = q("SELECT title, content, image_url FROM about_us ORDER BY id ASC LIMIT 1")[0] ?? [];
$ab_sec  = q("SELECT title, content, image_url FROM about_sections ORDER BY section_order ASC, id ASC LIMIT 1")[0] ?? [];

$raw_title = txt($ab_main['title'] ?? '') ?: txt($ab_sec['title'] ?? '');
$raw_html  = trim((string)($ab_main['content'] ?? '')) ?: trim((string)($ab_sec['content'] ?? ''));
$raw_img   = !empty($ab_main['image_url']) ? $ab_main['image_url'] : ($ab_sec['image_url'] ?? '');

// Protect against old dummy records (Huchol / Bhagirath / candle moulds) until admin updates DB
$is_dummy = (
    stripos($raw_title, 'candle') !== false ||
    stripos($raw_title, 'Dry Fruits') !== false ||
    stripos($raw_html, 'Bhagirath') !== false ||
    stripos($raw_html, 'Huchol') !== false
);

$dyn_title = (!$is_dummy && $raw_title !== '')
    ? $raw_title
    : 'Honest Wholesale Trading of Pure Red Chilli Powder & Turmeric Powder from Wankaner, Morbi';

$dyn_content_html = (!$is_dummy && $raw_html !== '')
    ? $raw_html
    : '<p><strong>AMSB GLOBAL</strong> is a dedicated B2B spice trading and bulk supply firm based in <strong>Wankaner, Morbi, Gujarat (363621)</strong>. Under the personal supervision of <strong>Mahir Bhai</strong>, we focus deeply on two essential kitchen and commercial staples—<strong>Red Chilli Powder</strong> and <strong>Turmeric Powder</strong>—instead of spreading ourselves thin across dozens of unrelated products.</p>
       <p>In the wholesale spice trade, a buyer’s biggest headache is inconsistency: the first sample looks bright and aromatic, while the bulk lot arrives dull, damp, or mixed with fillers. We started AMSB GLOBAL to solve that exact problem. Every lot we trade is sourced directly from trusted chilli and turmeric belts, milled hygienically to the required mesh, checked for moisture and natural colour, and supplied with complete GST and E-Way bill paperwork.</p>
       <p>Whether you are a wholesale mandi merchant, a regional spice distributor, a namkeen or food manufacturer, or a hotel and catering supplier, we work as your long-term supply partner—offering fair daily market rates, honest grading, and timely transport dispatch across Gujarat and India.</p>';

$dyn_image = img($raw_img, 'about_us', un('photo-1596040033229-a9821ebd058d', 1000));
?>

<main id="main" class="about-page">

    <!-- 1. PAGE HERO BANNER (Soft Light Blue Matching Homepage Hero) -->
    <section class="page-hero">
        <div class="wrap ph-grid">
            <div class="ph-content" data-reveal>
                <nav class="bc" aria-label="Breadcrumb">
                    <a href="./"><i class="fa-solid fa-house"></i> Home</a>
                    <span>/</span>
                    <span class="curr">About AMSB GLOBAL</span>
                </nav>
                <div class="hero-badges">
                    <span class="pill-primary"><i class="fa-solid fa-handshake"></i> Trusted B2B Spice Trade</span>
                    <span class="pill-secondary"><i class="fa-solid fa-location-dot"></i> Wankaner, Morbi (Gujarat)</span>
                </div>
                <h1>Built on Pure Spices, <span class="txt-primary">Honest Grading</span> &amp; <span class="txt-secondary">Long-Term Trade.</span></h1>
                <p class="lead">Headquartered in Wankaner, Morbi (363621), AMSB GLOBAL supplies 100% pure, unadulterated Red Chilli Powder and Turmeric Powder in bulk to wholesalers, food processors, and trade buyers across India.</p>
                <div class="ph-actions">
                    <a href="contact.php" class="btn"><i class="fa-solid fa-file-invoice-dollar"></i> Request Today’s Rate</a>
                    <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), I visited your About page and want to discuss bulk spice supply.') ?>" target="_blank" rel="noopener" class="btn o"><i class="fa-brands fa-whatsapp"></i> Connect with <?= e($brand['founder']) ?></a>
                </div>
            </div>

            <!-- Right Quick Company Fact Card -->
            <div class="ph-card" data-reveal>
                <div class="phc-head">
                    <span>Company Trade Snapshot</span>
                    <h3>AMSB GLOBAL</h3>
                </div>
                <ul class="phc-list">
                    <li><span><i class="fa-solid fa-briefcase"></i> Business Role</span><strong>B2B Wholesale Trader &amp; Bulk Supplier</strong></li>
                    <li><span><i class="fa-solid fa-pepper-hot"></i> Core Products</span><strong>Red Chilli Powder &amp; Turmeric Powder</strong></li>
                    <li><span><i class="fa-solid fa-user-tie"></i> Trade Head</span><strong><?= e($brand['founder']) ?></strong></li>
                    <li><span><i class="fa-solid fa-map-pin"></i> Hub Location</span><strong>Wankaner, Morbi, Gujarat - 363621</strong></li>
                    <li><span><i class="fa-solid fa-receipt"></i> Billing &amp; Tax</span><strong>100% GST Invoice &amp; E-Way Bill</strong></li>
                    <li><span><i class="fa-solid fa-truck-fast"></i> Dispatch Mode</span><strong>Pan-India Transport (Part &amp; Full Load)</strong></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- 2. DYNAMIC ABOUT US SECTION (Controlled from Admin Panel / DB) -->
    <section class="sec" id="who-we-are">
        <div class="wrap dyn-about-grid">
            <div class="dyn-media" data-reveal>
                <div class="dyn-img-wrap">
                    <?= pic($dyn_image, 'AMSB GLOBAL Bulk Red Chilli Powder and Turmeric Powder Trading - Wankaner, Morbi', true) ?>
                </div>
                <div class="dyn-badge-float">
                    <div class="dbf-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <strong>Zero Adulteration Promise</strong>
                        <span>No artificial colour, no starch, no husk fillers</span>
                    </div>
                </div>
            </div>

            <div class="dyn-text" data-reveal>
                <div class="sh">
                    <span class="hi">Who We Are • Dynamic Profile</span>
                    <h2><?= e($dyn_title) ?></h2>
                </div>
                <div class="dyn-richtext">
                    <?= $dyn_content_html ?>
                </div>
                <div class="dyn-highlights">
                    <div class="dh-item">
                        <i class="fa-solid fa-scale-balanced"></i>
                        <div>
                            <strong>Accurate Net Weight</strong>
                            <span>Digital weighbridge &amp; bag-level weight checks on every dispatch.</span>
                        </div>
                    </div>
                    <div class="dh-item">
                        <i class="fa-solid fa-box-open"></i>
                        <div>
                            <strong>Trade-Ready Packing</strong>
                            <span>5 kg, 10 kg, 25 kg &amp; 50 kg moisture-safe PP/HDPE bags.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. TRADE CREDENTIALS & NUMBERS STRIP (Static) -->
    <section class="ab-stats">
        <div class="wrap stats-grid">
            <div class="stat-box" data-reveal>
                <i class="fa-solid fa-bullseye"></i>
                <strong>2 Core Spices</strong>
                <span>100% dedicated focus on Red Chilli Powder &amp; Turmeric Powder</span>
            </div>
            <div class="stat-box" data-reveal>
                <i class="fa-solid fa-vial-circle-check"></i>
                <strong>100% Pure Lots</strong>
                <span>Free from artificial dyes, lead chromate &amp; starch mixing</span>
            </div>
            <div class="stat-box" data-reveal>
                <i class="fa-solid fa-boxes-stacked"></i>
                <strong>500 KG to 25 MT</strong>
                <span>Flexible order sizes from trial wholesale lots to full truck loads</span>
            </div>
            <div class="stat-box" data-reveal>
                <i class="fa-solid fa-file-invoice"></i>
                <strong>Clean Billing</strong>
                <span>Transparent GST invoicing &amp; E-Way bill on every shipment</span>
            </div>
        </div>
    </section>

    <!-- 4. MISSION, VISION & CORE TRADE VALUES (Static) -->
    <section class="sec alt" id="values">
        <div class="wrap">
            <div class="sh center" data-reveal>
                <span class="hi">What Drives Our Business</span>
                <h2>Our Mission, Vision &amp; Trading Ethics</h2>
                <p>In B2B spice trading, trust takes years to build and one bad batch to lose. These three pillars guide every purchase order we fulfil.</p>
            </div>

            <div class="mv-grid">
                <article class="mv-card" data-reveal>
                    <div class="mv-ico gold-ico"><i class="fa-solid fa-compass"></i></div>
                    <h3>Our Mission</h3>
                    <p>To give spice wholesalers, food manufacturers, and retail brands a dependable Gujarat-based source for pure Red Chilli Powder and Turmeric Powder—at fair market rates, with zero quality surprises between the sample and bulk supply.</p>
                </article>

                <article class="mv-card" data-reveal>
                    <div class="mv-ico cyan-ico"><i class="fa-solid fa-eye"></i></div>
                    <h3>Our Vision</h3>
                    <p>To become the most trusted wholesale trade partner for ground red chilli and turmeric across Gujarat and India, known for transparent grading, clean documentation, and consistent batch-to-batch colour and aroma.</p>
                </article>

                <article class="mv-card" data-reveal>
                    <div class="mv-ico navy-ico"><i class="fa-solid fa-award"></i></div>
                    <h3>Our Core Promise</h3>
                    <p>We never cut corners to quote a cheaper rate by mixing low-grade stems, rice starch, or synthetic colour. If you specify a grade and budget, we tell you honestly what variety fits best—and supply exactly that.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- 5. WHY FOCUS ONLY ON RED CHILLI & TURMERIC? (Static Genuine Differentiator) -->
    <section class="sec" id="specialization">
        <div class="wrap">
            <div class="sh center" data-reveal>
                <span class="hi">Focused Product Expertise</span>
                <h2>Why Trade Buyers Prefer a Specialist Over a General Broker</h2>
                <p>Because our daily buying and quality focus stays on Red Chilli Powder and Turmeric Powder, we understand crop cycles, colour stability, pungency matching, and seasonal pricing better than multi-item brokers.</p>
            </div>

            <div class="compare-grid">
                <div class="cmp-card cmp-chilli" data-reveal>
                    <div class="cmp-top">
                        <span class="cmp-tag">Specialty Line 01</span>
                        <h3>Red Chilli Powder Trade Expertise</h3>
                    </div>
                    <ul class="cmp-list">
                        <li><i class="fa-solid fa-check"></i> <strong>Variety Selection:</strong> Teja S17 (high heat), Sannam S4 (balanced), Byadgi &amp; Kashmiri (deep natural red colour).</li>
                        <li><i class="fa-solid fa-check"></i> <strong>Controlled Grinding:</strong> Ground cleanly so natural chilli oil stays locked in and colour doesn’t burn dark.</li>
                        <li><i class="fa-solid fa-check"></i> <strong>Custom Blending for Buyers:</strong> Need specific heat-to-colour balance for your local market or namkeen plant? We match it.</li>
                        <li><i class="fa-solid fa-check"></i> <strong>Zero Synthetic Colour:</strong> 100% pure chilli without Sudan dyes or oil-polishing shortcuts.</li>
                    </ul>
                </div>

                <div class="cmp-card cmp-turmeric" data-reveal>
                    <div class="cmp-top">
                        <span class="cmp-tag">Specialty Line 02</span>
                        <h3>Turmeric Powder Trade Expertise</h3>
                    </div>
                    <ul class="cmp-list">
                        <li><i class="fa-solid fa-check"></i> <strong>Quality Origins:</strong> Sourced from high-aroma Salem, Erode, Rajapuri, and Nizamabad turmeric fingers.</li>
                        <li><i class="fa-solid fa-check"></i> <strong>Natural Golden Hue:</strong> Rich yellow tint driven by natural curcumin—never artificial lead chromate.</li>
                        <li><i class="fa-solid fa-check"></i> <strong>Fine Uniform Mesh:</strong> Smooth, lump-free grinding (60 to 100 mesh) for instant solubility in cooking and masala blends.</li>
                        <li><i class="fa-solid fa-check"></i> <strong>Low Moisture Storage:</strong> Properly dried before milling so powder doesn’t cake or spoil in godowns.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. OUR 5-STAGE QUALITY & TRADE PROCESS (Static Dark Navy Band) -->
    <section class="sec dk" id="quality-process">
        <div class="wrap">
            <div class="sh center" data-reveal>
                <span class="hi">From Mandi to Your Godown</span>
                <h2>Our 5-Stage Quality &amp; Dispatch Discipline</h2>
                <p>Every wholesale order at AMSB GLOBAL passes through five clear checks before the transport receipt (LR) is cut.</p>
            </div>

            <div class="q-timeline">
                <div class="qt-step" data-reveal>
                    <span class="qt-num">01</span>
                    <h3>Raw Material Selection</h3>
                    <p>We inspect whole chillies and turmeric fingers for dryness, pod maturity, natural colour, and zero fungus before purchasing.</p>
                </div>
                <div class="qt-step" data-reveal>
                    <span class="qt-num">02</span>
                    <h3>Cleaning &amp; Pre-Sorting</h3>
                    <p>Dust, stones, excess stems, and foreign matter are removed thoroughly before the raw lot enters the grinding line.</p>
                </div>
                <div class="qt-step" data-reveal>
                    <span class="qt-num">03</span>
                    <h3>Hygienic Grinding &amp; Sieving</h3>
                    <p>Milled at controlled temperatures and sieved to uniform mesh size so every bag carries consistent texture and aroma.</p>
                </div>
                <div class="qt-step" data-reveal>
                    <span class="qt-num">04</span>
                    <h3>Sample Verification</h3>
                    <p>Each packed lot is matched visually and physically against the buyer’s approved sample; batch photos/videos shared on WhatsApp.</p>
                </div>
                <div class="qt-step" data-reveal>
                    <span class="qt-num">05</span>
                    <h3>Moisture-Safe Packing &amp; Dispatch</h3>
                    <p>Sealed in inner-lined 10/25/50 kg bags and dispatched via reliable road transport from Wankaner with GST invoice &amp; E-Way bill.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. WHO WE SUPPLY TO (B2B Buyer Segments - Static) -->
    <section class="sec" id="who-we-serve">
        <div class="wrap">
            <div class="sh center" data-reveal>
                <span class="hi">Pan-India B2B Supply Network</span>
                <h2>Businesses That Buy in Bulk From AMSB GLOBAL</h2>
                <p>Our pricing, bag sizes, and delivery schedules are built specifically for commercial and wholesale buyers.</p>
            </div>

            <div class="seg-grid">
                <div class="seg-card" data-reveal>
                    <i class="fa-solid fa-store"></i>
                    <h3>Wholesale Spice Merchants</h3>
                    <p>Regular bulk supply in 25 kg and 50 kg bags for mandi traders and kirana wholesalers who need steady margins and repeat quality.</p>
                </div>
                <div class="seg-card" data-reveal>
                    <i class="fa-solid fa-box"></i>
                    <h3>Masala &amp; Retail Repacking Brands</h3>
                    <p>Uniform colour, pungency, and fine mesh tailored for regional spice brands packing 100g to 1 kg consumer pouches.</p>
                </div>
                <div class="seg-card" data-reveal>
                    <i class="fa-solid fa-utensils"></i>
                    <h3>Hotels, Caterers &amp; Cloud Kitchens</h3>
                    <p>Hygienic 5 kg and 10 kg commercial packs for HoReCa kitchens where consistent dish colour and spice levels matter daily.</p>
                </div>
                <div class="seg-card" data-reveal>
                    <i class="fa-solid fa-industry"></i>
                    <h3>Namkeen, Farsan &amp; Food Units</h3>
                    <p>Bulk red chilli and haldi powder graded for snacks, pickles, papad, and ready-mix food manufacturers across Gujarat &amp; India.</p>
                </div>
                <div class="seg-card" data-reveal>
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <h3>Regional Distributors</h3>
                    <p>Dependable weekly or monthly dispatch schedules with transparent daily rates and zero hidden transport surprises.</p>
                </div>
                <div class="seg-card" data-reveal>
                    <i class="fa-solid fa-cart-flatbed"></i>
                    <h3>Supermarkets &amp; Institutional Buyers</h3>
                    <p>Clean GST billing, consistent stock availability, and custom packaging options for bulk institutional procurement.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. LEADERSHIP & DIRECT ACCOUNTABILITY SECTION (Static) -->
    <section class="sec alt" id="leadership">
        <div class="wrap lead-box" data-reveal>
            <div class="lead-badge-col">
                <div class="lb-avatar"><i class="fa-solid fa-user-tie"></i></div>
                <h3><?= e($brand['founder']) ?></h3>
                <span class="lb-role">Founder &amp; Trade Desk Head</span>
                <p class="lb-loc"><i class="fa-solid fa-location-dot"></i> Wankaner, Morbi, Gujarat - 363621</p>
                <div class="lb-links">
                    <a href="tel:+<?= e($tel) ?>"><i class="fa-solid fa-phone"></i> <?= e($disp_phone) ?></a>
                    <a href="mailto:<?= e($mail) ?>"><i class="fa-regular fa-envelope"></i> <?= e($mail) ?></a>
                </div>
            </div>

            <div class="lead-quote-col">
                <span class="hi">A Direct Note From Our Founder</span>
                <h2>“In Spice Trading, One Honest Lot Builds a Ten-Year Relationship.”</h2>
                <p>When you buy Red Chilli Powder or Turmeric Powder in bulk from AMSB GLOBAL, you don’t get passed around a call centre. You deal directly with me and our Wankaner trade desk. We understand how tight margins are in wholesale vyapaar—and how damaging a mixed or inconsistent lot can be to your reputation with your own customers.</p>
                <p>That is why we encourage every new buyer to start with a courier sample or a small trial order. Check our natural colour, smell the freshness, test it in your market, and only then scale to regular bulk supplies. Our goal isn’t a single sale—it is becoming the supplier you call first every season.</p>
                <div class="lead-sign">
                    <strong>— <?= e($brand['founder']) ?></strong>
                    <span>AMSB GLOBAL, Wankaner (Morbi, Gujarat)</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 9. COMMON B2B TRADE FAQS (Static Interactive Accordion) -->
    <section class="sec" id="faq">
        <div class="wrap faq-wrap">
            <div class="sh center" data-reveal>
                <span class="hi">Clear Answers Before You Order</span>
                <h2>Frequently Asked Questions by Trade Buyers</h2>
                <p>Everything wholesale buyers normally ask us on the first phone call—answered clearly upfront.</p>
            </div>

            <div class="faq-list" data-reveal>
                <div class="faq-item active">
                    <button type="button" class="faq-q" aria-expanded="true">
                        <span>What is your Minimum Order Quantity (MOQ) for bulk spice supply?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="faq-a">
                        <p>We support both growing traders and large wholesale buyers. Trial bulk orders generally start from <strong>200 kg to 500 kg</strong> depending on the grade and pack size, scaling smoothly up to 1 MT, 5 MT, and full truck loads (10–25 MT).</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-q" aria-expanded="false">
                        <span>Can we check physical samples before placing a bulk order?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="faq-a">
                        <p>Yes, absolutely. We dispatch representative courier samples of our Red Chilli Powder and Turmeric Powder grades within 24–48 hours so you can verify colour, pungency, mesh fineness, and aroma before confirming your purchase order.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-q" aria-expanded="false">
                        <span>Do you provide GST invoices and E-Way bills for interstate supply?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="faq-a">
                        <p>Yes. Every commercial dispatch from AMSB GLOBAL is accompanied by a proper <strong>GST Tax Invoice, E-Way Bill, and Transport LR copy</strong> for smooth, hassle-free delivery anywhere in Gujarat or across India.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-q" aria-expanded="false">
                        <span>What bulk packaging sizes do you offer?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="faq-a">
                        <p>Standard wholesale supply is available in <strong>5 kg, 10 kg, 25 kg, and 50 kg</strong> heavy-duty PP/HDPE bags with food-grade inner poly liners to keep moisture out during transit and storage. Custom or unbranded neutral packing for repackers is also available.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-q" aria-expanded="false">
                        <span>How is transport arranged from Wankaner, Morbi?</span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="faq-a">
                        <p>Wankaner (Morbi) sits right on major Gujarat highway corridors with daily transport services to Ahmedabad, Rajkot, Surat, Mumbai, Delhi, Rajasthan, MP, Maharashtra, and South India. We can dispatch via our trusted transport partners or load directly onto your preferred transport vehicle.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 10. DIRECT TRADE CTA BANNER (Static) -->
    <section class="sec ab-cta-sec">
        <div class="wrap">
            <div class="ab-cta-box" data-reveal>
                <div>
                    <span class="cta-tag">Ready to Compare Quality &amp; Rates?</span>
                    <h2>Talk Directly to AMSB GLOBAL’s Trade Desk Today</h2>
                    <p>Share your required quantity, spice grade, and delivery city. Get today’s honest wholesale rate and arrange your physical sample dispatch.</p>
                </div>
                <div class="cta-btns">
                    <a href="contact.php" class="btn"><i class="fa-solid fa-paper-plane"></i> Request Bulk Quotation</a>
                    <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), please share today’s wholesale rate for Red Chilli Powder and Turmeric Powder.') ?>" target="_blank" rel="noopener" class="btn wa-btn"><i class="fa-brands fa-whatsapp"></i> WhatsApp <?= e($disp_phone) ?></a>
                </div>
            </div>
        </div>
    </section>

</main>

<!-- Smooth Scroll Reveal + FAQ Accordion + Mobile Nav Support -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Smooth Scroll-Reveal Animations
    const revealEls = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach(el => io.observe(el));
    } else {
        revealEls.forEach(el => el.classList.add('is-visible'));
    }

    // 2. FAQ Accordion
    document.querySelectorAll('.faq-q').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-item');
            const isActive = item.classList.contains('active');
            document.querySelectorAll('.faq-item').forEach(i => {
                i.classList.remove('active');
                i.querySelector('.faq-q').setAttribute('aria-expanded', 'false');
            });
            if (!isActive) {
                item.classList.add('active');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // 3. Mobile Hamburger Menu Toggle (if not already bound in main.js)
    const tg = document.querySelector('.tg');
    const nav = document.getElementById('nav');
    if (tg && nav && !tg.dataset.bound) {
        tg.dataset.bound = '1';
        tg.addEventListener('click', () => {
            const expanded = tg.getAttribute('aria-expanded') === 'true';
            tg.setAttribute('aria-expanded', String(!expanded));
            nav.classList.toggle('open');
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>