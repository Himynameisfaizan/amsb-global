<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once 'config/connect.php';

$db_ready = isset($conn) && !$conn->connect_error;

// ---- Handle Bulk Inquiry Form POST (From Contact Page OR Any Page on Site) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $honeypot = trim((string)($_POST['website_hp'] ?? ''));
    if ($honeypot !== '') {
        header('Location: contact.php?sent=1');
        exit;
    }

    $in_name     = trim((string)($_POST['name'] ?? ''));
    $in_company  = trim((string)($_POST['company'] ?? ''));
    $in_email    = trim((string)($_POST['email'] ?? ''));
    $in_phone    = trim((string)($_POST['phone'] ?? ''));
    $in_type     = trim((string)($_POST['inquiry_type'] ?? 'Bulk Rate Quotation'));
    $in_subject  = trim((string)($_POST['subject'] ?? 'Red Chilli Powder & Turmeric Powder'));
    $in_qty      = trim((string)($_POST['quantity'] ?? ''));
    $in_packing  = trim((string)($_POST['packing'] ?? ''));
    $in_dest     = trim((string)($_POST['destination'] ?? ''));
    $in_message  = trim((string)($_POST['message'] ?? ''));

    $errors = [];
    if ($in_name === '') $errors[] = 'Please enter your name.';
    if ($in_phone === '' && $in_email === '') $errors[] = 'Please provide a valid phone/WhatsApp number or email.';
    if ($in_email !== '' && !filter_var($in_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($in_message === '') $errors[] = 'Please share your requirement details.';

    if (empty($errors)) {
        // Combine rich B2B metadata neatly so admin panel sees every detail
        $extra_lines = [];
        if ($in_type !== '')    $extra_lines[] = "Inquiry Type: $in_type";
        if ($in_company !== '') $extra_lines[] = "Company/Firm: $in_company";
        if ($in_subject !== '') $extra_lines[] = "Product: $in_subject";
        if ($in_qty !== '')     $extra_lines[] = "Quantity: $in_qty";
        if ($in_packing !== '') $extra_lines[] = "Packing: $in_packing";
        if ($in_dest !== '')    $extra_lines[] = "Delivery City: $in_dest";

        $full_message = $in_message;
        if (!empty($extra_lines)) {
            $full_message = implode(" | ", $extra_lines) . "\n\nBuyer Notes:\n" . $in_message;
        }

        // Auto-detect existing inquiry table in DB & insert dynamically
        $saved_db = false;
        if ($db_ready) {
            $candidate_tables = ['inquiries', 'contact_inquiries', 'enquiries', 'contact_messages', 'contacts_inquiries', 'contact_us', 'messages', 'leads'];
            $found_table = null;
            foreach ($candidate_tables as $tbl) {
                $chk = @$conn->query("SHOW TABLES LIKE '$tbl'");
                if ($chk && $chk->num_rows > 0) {
                    $found_table = $tbl;
                    break;
                }
            }

            if ($found_table) {
                $cols = [];
                if ($cres = @$conn->query("SHOW COLUMNS FROM `$found_table`")) {
                    while ($crow = $cres->fetch_assoc()) $cols[] = $crow['Field'];
                }

                $field_map = [
                    'name'         => $in_name,
                    'full_name'    => $in_name,
                    'client_name'  => $in_name,
                    'company'      => $in_company,
                    'company_name' => $in_company,
                    'email'        => $in_email,
                    'phone'        => $in_phone,
                    'mobile'       => $in_phone,
                    'contact'      => $in_phone,
                    'subject'      => $in_subject,
                    'product'      => $in_subject,
                    'product_name' => $in_subject,
                    'quantity'     => $in_qty,
                    'destination'  => $in_dest,
                    'city'         => $in_dest,
                    'message'      => $full_message,
                    'details'      => $full_message,
                    'inquiry'      => $full_message,
                    'ip_address'   => $_SERVER['REMOTE_ADDR'] ?? ''
                ];

                $ins_cols = [];
                $ins_vals = [];
                $used_msg_col = false;

                foreach ($cols as $col) {
                    $lc = strtolower($col);
                    if (array_key_exists($lc, $field_map)) {
                        if (in_array($lc, ['message', 'details', 'inquiry']) && $used_msg_col) continue;
                        if (in_array($lc, ['message', 'details', 'inquiry'])) $used_msg_col = true;
                        $ins_cols[] = "`$col`";
                        $ins_vals[] = $field_map[$lc];
                    }
                }

                if (!empty($ins_cols)) {
                    $placeholders = implode(', ', array_fill(0, count($ins_cols), '?'));
                    $sql = "INSERT INTO `$found_table` (" . implode(', ', $ins_cols) . ") VALUES ($placeholders)";
                    if ($stmt = @$conn->prepare($sql)) {
                        $types = str_repeat('s', count($ins_vals));
                        $stmt->bind_param($types, ...$ins_vals);
                        $saved_db = @$stmt->execute();
                        $stmt->close();
                    }
                }
            }
        }

        // Prepare instant WhatsApp summary for buyer convenience
        $wa_summary = "Hello Mahir Bhai (AMSB GLOBAL), I just submitted a bulk inquiry on your website.\n"
            . "Name: $in_name" . ($in_company ? " ($in_company)" : "") . "\n"
            . "Product: $in_subject\n"
            . ($in_qty ? "Quantity: $in_qty\n" : "")
            . ($in_dest ? "City: $in_dest\n" : "")
            . "Notes: $in_message";

        $_SESSION['contact_flash'] = [
            'type'       => 'success',
            'title'      => 'Thank You, ' . $in_name . '! Your Bulk Inquiry Has Been Received.',
            'msg'        => 'Mahir Bhai and our Wankaner trade desk will review your requirement and reply with today’s wholesale rate within business hours.',
            'wa_summary' => $wa_summary,
            'saved_db'   => $saved_db
        ];
        header('Location: contact.php?sent=1#inquiry-form');
        exit;
    } else {
        $_SESSION['contact_flash'] = [
            'type'  => 'error',
            'title' => 'Please Check Your Inquiry Details',
            'msg'   => implode(' ', $errors)
        ];
        $_SESSION['contact_old'] = $_POST;
        header('Location: contact.php#inquiry-form');
        exit;
    }
}

$flash = $_SESSION['contact_flash'] ?? null;
$old   = $_SESSION['contact_old'] ?? [];
unset($_SESSION['contact_flash'], $_SESSION['contact_old']);

// ---- Page Meta, Canonical & JSON-LD Schema ----
$https_ct = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
$base_ct  = ($https_ct ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/';
$ct_url   = $base_ct . 'contact.php';

$page_meta = [
    'title' => 'Contact AMSB GLOBAL | Wholesale Spice Trade Desk Wankaner, Morbi (Gujarat)',
    'desc'  => 'Contact Mahir Bhai at AMSB GLOBAL (Wankaner, Morbi, Gujarat - 363621) for today’s bulk wholesale rates and courier samples of pure Red Chilli Powder and Turmeric Powder. Call +91 82007 78206.',
    'key'   => 'Contact AMSB Global, Mahir Bhai Spices Wankaner, Red Chilli Powder Bulk Rate Morbi, Turmeric Powder Wholesale Contact Gujarat',
    'schema' => '<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "ContactPage",
      "@id": "' . $ct_url . '#contactpage",
      "url": "' . $ct_url . '",
      "name": "Contact AMSB GLOBAL Wholesale Spice Trade Desk",
      "description": "Direct trade desk contact for bulk Red Chilli Powder and Turmeric Powder inquiries in Wankaner, Morbi, Gujarat.",
      "inLanguage": "en-IN",
      "isPartOf": { "@id": "' . $base_ct . '#website" }
    },
    {
      "@type": "WholesaleStore",
      "@id": "' . $base_ct . '#organization",
      "name": "AMSB GLOBAL",
      "url": "' . $base_ct . '",
      "telephone": "+91-8200778206",
      "email": "amsbglobal@gmail.com",
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
      "@id": "' . $ct_url . '#breadcrumb",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "' . $base_ct . '" },
        { "@type": "ListItem", "position": 2, "name": "Contact Us", "item": "' . $ct_url . '" }
      ]
    }
  ]
}
</script>'
];

include_once 'includes/header.php';

// Prefill product if linked via ?product=...
$prefill_prod = trim((string)($old['subject'] ?? $_GET['product'] ?? 'Red Chilli Powder & Turmeric Powder'));
?>

<main id="main" class="contact-page">

    <!-- 1. SOFT LIGHT BLUE CONTACT HERO -->
    <section class="page-hero ct-hero">
        <div class="wrap ph-grid">
            <div class="ph-content" data-reveal>
                <nav class="bc" aria-label="Breadcrumb">
                    <a href="./"><i class="fa-solid fa-house"></i> Home</a>
                    <span>/</span>
                    <span class="curr">Contact Trade Desk</span>
                </nav>
                <div class="hero-badges">
                    <span class="pill-primary"><i class="fa-solid fa-headset"></i> Direct Founder Trade Desk</span>
                    <span class="pill-secondary"><i class="fa-solid fa-bolt"></i> Same-Day Rate Response</span>
                </div>
                <h1>Connect Directly With <span class="txt-primary">AMSB GLOBAL’s</span> <span class="txt-secondary">Trade Desk.</span></h1>
                <p class="lead">Need today’s wholesale mandi rate, a physical courier sample, or regular monthly supply of pure Red Chilli Powder and Turmeric Powder? Reach <strong><?= e($brand['founder']) ?></strong> directly in Wankaner, Morbi — zero middlemen, clean GST billing.</p>
                <div class="ph-actions">
                    <a href="tel:+<?= e($tel) ?>" class="btn"><i class="fa-solid fa-phone-volume"></i> Call <?= e($disp_phone) ?></a>
                    <a href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), I want to enquire about today’s wholesale rates for Red Chilli Powder and Turmeric Powder.') ?>" target="_blank" rel="noopener" class="btn wa-btn"><i class="fa-brands fa-whatsapp"></i> WhatsApp Now</a>
                </div>
            </div>

            <!-- Right Instant Contact Directory Card -->
            <div class="ph-card" data-reveal>
                <div class="phc-head">
                    <span>Verified B2B Trade Credentials</span>
                    <h3>AMSB GLOBAL — Wankaner</h3>
                </div>
                <ul class="phc-list">
                    <li><span><i class="fa-solid fa-user-tie"></i> Contact Person</span><strong><?= e($brand['founder']) ?></strong></li>
                    <li><span><i class="fa-solid fa-phone"></i> Phone / WhatsApp</span><strong><a href="tel:+<?= e($tel) ?>"><?= e($disp_phone) ?></a></strong></li>
                    <li><span><i class="fa-regular fa-envelope"></i> Official Email</span><strong><a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a></strong></li>
                    <li><span><i class="fa-solid fa-location-dot"></i> Address</span><strong>Wankaner, Morbi, Gujarat - 363621</strong></li>
                    <li><span><i class="fa-regular fa-clock"></i> Trade Hours</span><strong><?= e($hours) ?></strong></li>
                    <li><span><i class="fa-solid fa-file-invoice"></i> Tax Compliance</span><strong>GST Invoice &amp; E-Way Bill</strong></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- 2. FOUR QUICK CONTACT CHANNELS STRIP -->
    <section class="ct-channels-sec">
        <div class="wrap ct-channels-grid">
            <a class="ct-ch-card" href="tel:+<?= e($tel) ?>" data-reveal>
                <div class="ctc-ico gold-ico"><i class="fa-solid fa-phone-volume"></i></div>
                <small>Direct Phone Line</small>
                <h3><?= e($disp_phone) ?></h3>
                <p>Speak directly with <?= e($brand['founder']) ?> for daily bulk rates.</p>
            </a>

            <a class="ct-ch-card" href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello Mahir Bhai (AMSB GLOBAL), please share today’s wholesale spice rate sheet.') ?>" target="_blank" rel="noopener" data-reveal>
                <div class="ctc-ico wa-ico"><i class="fa-brands fa-whatsapp"></i></div>
                <small>Fastest Response</small>
                <h3>WhatsApp Trade Desk</h3>
                <p>Get stock photos, videos &amp; written rates instantly.</p>
            </a>

            <a class="ct-ch-card" href="mailto:<?= e($mail) ?>" data-reveal>
                <div class="ctc-ico cyan-ico"><i class="fa-regular fa-envelope"></i></div>
                <small>Purchase Orders &amp; RFQ</small>
                <h3><?= e($mail) ?></h3>
                <p>Send formal purchase requirements &amp; GST details.</p>
            </a>

            <a class="ct-ch-card" href="#office-map" data-reveal>
                <div class="ctc-ico navy-ico"><i class="fa-solid fa-location-dot"></i></div>
                <small>Office &amp; Dispatch Hub</small>
                <h3>Wankaner, Morbi</h3>
                <p>Gujarat - 363621 (In-person visits welcome by appointment).</p>
            </a>
        </div>
    </section>

    <!-- 3. MAIN INQUIRY FORM + B2B PROCESS TRUST COLUMN -->
    <section class="sec" id="inquiry-form">
        <div class="wrap ct-main-grid">

            <!-- Left: Comprehensive B2B Inquiry Form -->
            <div class="ct-form-wrap" data-reveal>
                <?php if ($flash): ?>
                <div class="ct-alert ct-alert-<?= e($flash['type']) ?>" role="alert">
                    <div class="cta-ico">
                        <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                    </div>
                    <div>
                        <h4><?= e($flash['title']) ?></h4>
                        <p><?= e($flash['msg']) ?></p>
                        <?php if (!empty($flash['wa_summary'])): ?>
                        <a href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode($flash['wa_summary']) ?>" target="_blank" rel="noopener" class="btn wa-btn" style="margin-top:12px;padding:9px 16px;font-size:0.86rem;">
                            <i class="fa-brands fa-whatsapp"></i> Send Same Inquiry on WhatsApp for Faster Reply
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <form class="fm ct-form" action="contact.php" method="POST">
                    <!-- Honeypot anti-spam field -->
                    <input type="text" name="website_hp" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;">

                    <div class="w">
                        <span class="hi" style="margin-bottom:8px;">Written Wholesale Quotation</span>
                        <h3>Send Your Bulk Requirement</h3>
                        <p class="fm-sub">Fill out the details below so we can quote the most accurate rate for your grade, bag weight, and delivery city.</p>
                    </div>

                    <label>Your Full Name *
                        <input name="name" value="<?= e($old['name'] ?? '') ?>" placeholder="e.g. Rajeshbhai Patel" autocomplete="name" required>
                    </label>

                    <label>Firm / Company Name *
                        <input name="company" value="<?= e($old['company'] ?? '') ?>" placeholder="e.g. Shreeji Spice Traders" autocomplete="organization" required>
                    </label>

                    <label>Mobile / WhatsApp Number *
                        <input type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>" placeholder="+91 98765 43210" autocomplete="tel" required>
                    </label>

                    <label>Business Email *
                        <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" placeholder="you@company.com" autocomplete="email" required>
                    </label>

                    <label>Inquiry Type
                        <select name="inquiry_type">
                            <option>Today’s Bulk Wholesale Rate</option>
                            <option>Request Physical Courier Sample</option>
                            <option>Regular Monthly Contract Supply</option>
                            <option>Custom Mesh / Neutral Repacking</option>
                            <option>General Trade Enquiry</option>
                        </select>
                    </label>

                    <label>Product Required *
                        <input name="subject" value="<?= e($prefill_prod) ?>" placeholder="Red Chilli Powder, Turmeric Powder..." required>
                    </label>

                    <label>Estimated Quantity
                        <select name="quantity">
                            <option>Trial Bulk Lot (200 kg – 500 kg)</option>
                            <option>1 MT to 5 MT</option>
                            <option>Part Truck Load (5 MT – 10 MT)</option>
                            <option>Full Truck Load (10 MT – 25 MT)</option>
                            <option>Monthly Recurring Requirement</option>
                        </select>
                    </label>

                    <label>Preferred Bag Packing
                        <select name="packing">
                            <option>25 KG PP/HDPE Bag (Inner Lined)</option>
                            <option>50 KG PP/HDPE Bag (Inner Lined)</option>
                            <option>10 KG Commercial Pack</option>
                            <option>5 KG HoReCa Pack</option>
                            <option>Unbranded / Custom Packing</option>
                        </select>
                    </label>

                    <label class="w">Delivery City &amp; State
                        <input name="destination" value="<?= e($old['destination'] ?? '') ?>" placeholder="e.g. Ahmedabad, Gujarat / Mumbai, Maharashtra / Jaipur, Rajasthan">
                    </label>

                    <label class="w">Grade, Heat / Curcumin &amp; Additional Notes *
                        <textarea name="message" rows="4" placeholder="Mention preferred variety (Teja S17, Sannam S4, Byadgi, Kashmiri, Salem Haldi), mesh fineness, or if you want courier samples dispatched first..." required><?= e($old['message'] ?? '') ?></textarea>
                    </label>

                    <div class="w">
                        <button type="submit" class="btn full-btn"><i class="fa-solid fa-paper-plane"></i> Submit Bulk Inquiry</button>
                    </div>
                </form>
            </div>

            <!-- Right: Trust, Response Timeline & Logistics Advantage -->
            <div class="ct-side-col">
                <!-- What Happens Next Timeline -->
                <div class="ct-trust-card" data-reveal>
                    <span class="hi">Transparent Response Process</span>
                    <h3>What Happens After You Contact Us?</h3>
                    <ol class="ct-steps">
                        <li>
                            <span class="cts-n">1</span>
                            <div>
                                <strong>Rate &amp; Grade Discussion (2–4 Working Hours)</strong>
                                <p><?= e($brand['founder']) ?> reviews your required grade, bag size, and city, and shares today’s written wholesale quote.</p>
                            </div>
                        </li>
                        <li>
                            <span class="cts-n">2</span>
                            <div>
                                <strong>Physical Sample Verification (24–48 Hours)</strong>
                                <p>Approve our courier sample for natural colour, aroma, heat/curcumin level, and mesh texture before booking bulk stock.</p>
                            </div>
                        </li>
                        <li>
                            <span class="cts-n">3</span>
                            <div>
                                <strong>Lot Photos, GST Billing &amp; Dispatch</strong>
                                <p>Watch live weighing/loading via WhatsApp video if needed. Receive GST invoice, E-Way bill &amp; transport LR immediately.</p>
                            </div>
                        </li>
                    </ol>
                </div>

                <!-- Direct Founder Card -->
                <div class="pd-desk-card" data-reveal>
                    <div class="pdd-top">
                        <div class="pdd-ico"><i class="fa-solid fa-user-tie"></i></div>
                        <div>
                            <small>Direct Trade Accountability</small>
                            <h4><?= e($brand['founder']) ?></h4>
                            <span>AMSB GLOBAL • Wankaner, Morbi (363621)</span>
                        </div>
                    </div>
                    <p>No automated call centres. When you call or message AMSB GLOBAL, you speak directly with the decision-maker managing your spice lots and transport dispatch.</p>
                    <div class="pdd-links">
                        <a href="tel:+<?= e($tel) ?>"><i class="fa-solid fa-phone"></i> <?= e($disp_phone) ?></a>
                        <a href="mailto:<?= e($mail) ?>"><i class="fa-regular fa-envelope"></i> <?= e($mail) ?></a>
                    </div>
                </div>

                <!-- Highway & Transport Advantage -->
                <div class="ct-logistics-card" data-reveal>
                    <h4><i class="fa-solid fa-truck-fast"></i> Fast Road Dispatch From Wankaner, Morbi</h4>
                    <p>Daily part-load and full-truck transport connectivity from our Wankaner hub across Gujarat (Rajkot, Ahmedabad, Surat, Vadodara) and major national spice mandis in Maharashtra, Rajasthan, MP, Delhi NCR, and South India.</p>
                </div>
            </div>

        </div>
    </section>

    <!-- 4. GOOGLE MAP LOCATION SECTION -->
    <section class="sec alt" id="office-map">
        <div class="wrap">
            <div class="sh center" data-reveal>
                <span class="hi">Visit Our Trade Desk</span>
                <h2>Located in Wankaner, Morbi — Gujarat’s Key Commercial Corridor</h2>
                <p>Wholesale buyers and institutional procurement teams are welcome to visit our Wankaner office by prior appointment to inspect fresh stock directly.</p>
            </div>

            <div class="map-card" data-reveal>
                <div class="map-info-bar">
                    <div>
                        <i class="fa-solid fa-location-dot"></i>
                        <span><strong>AMSB GLOBAL</strong> — Wankaner, Morbi, Gujarat - 363621, India</span>
                    </div>
                    <a href="https://maps.google.com/?q=Wankaner,+Morbi,+Gujarat+363621" target="_blank" rel="noopener" class="btn" style="padding:8px 16px;font-size:0.84rem;">
                        <i class="fa-solid fa-diamond-turn-right"></i> Open in Google Maps
                    </a>
                </div>
                <iframe
                    title="AMSB GLOBAL Location - Wankaner, Morbi, Gujarat 363621"
                    src="https://maps.google.com/maps?q=Wankaner,+Morbi,+Gujarat+363621&t=&z=13&ie=UTF8&iwloc=&output=embed"
                    width="100%"
                    height="380"
                    style="border:0;display:block;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
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

    // 2. Mobile Menu Toggle
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