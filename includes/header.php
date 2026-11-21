<?php
/** Global header: DB connect, helpers, SEO engine, navigation - AMSB GLOBAL */
if (session_status() === PHP_SESSION_NONE) session_start();
include 'config/connect.php';
if (is_file(__DIR__ . '/../config/site.php')) {
    require_once __DIR__ . '/../config/site.php';
}
$ok = isset($conn) && !$conn->connect_error;

// ---- Brand Configuration Override for AMSB GLOBAL ----
$brand = [
    'name'    => 'AMSB GLOBAL',
    'suffix'  => 'Spice Manufacturers & Bulk Exporters',
    'founder' => 'Mahir Bhai',
    'city'    => 'Wankaner, Morbi (Gujarat)',
    'address' => 'Wankaner, Morbi, Gujarat - 363621, India',
    'phone'   => '8200778206',
    'email'   => 'amsbglobal@gmail.com',
    'hours'   => 'Mon - Sat: 9:00 AM - 8:00 PM IST',
    'licences'=> ['FSSAI Certified', 'ISO 9001:2015', 'Export House'],
    'wa_msg'  => 'Hello Mahir Bhai (AMSB GLOBAL), I want to enquire about bulk spices.'
];

// ---- Helpers ----
function q($sql) { global $conn, $ok; $r = []; if ($ok && ($res = @$conn->query($sql))) while ($x = $res->fetch_assoc()) $r[] = $x; return $r; }
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function txt($s, $n = 0) { $s = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string)$s)))); return $n ? mb_strimwidth($s, 0, $n, '...') : $s; }
function un($id, $w = 800) { return "https://images.unsplash.com/$id?auto=format&fit=crop&w=$w&q=80"; }
function asset($p) { $f = dirname(__DIR__) . '/' . $p; return $p . (is_file($f) ? '?v=' . filemtime($f) : ''); }
function pic($src, $alt, $eager = false) { return '<img src="' . e($src) . '" alt="' . e($alt) . '"' . ($eager ? '' : ' loading="lazy"') . ' onerror="this.style.display=\'none\'">'; }
function img($f, $d, $fb) {
    global $site;
    $f = trim((string)$f);
    if ($f === '') return $fb;
    if (preg_match('#^https?://#', $f)) return $f;
    $c = ltrim($f, '/'); $b = basename($c);
    $root = dirname(__DIR__) . '/'; $base = rtrim((string)($site ?? ''), '/') . '/';
    if ($base === '/') $base = '';
    foreach ([$c, "admin/$c", "uploads/$d/$b", "admin/uploads/$d/$b", "assets/img/uploads/$d/$b", "assets/images/$b", "admin/assets/img/uploads/$d/$b", "admin/assets/img/uploads/$b"] as $p)
        if (is_file($root . $p)) return $base . $p;
    return $fb;
}

// Updated fallback mark to match AMSB Logo Colors (Deep Slate background with Gold and Cyan highlights)
function mark($w = 42) {
    return '<svg viewBox="0 0 52 52" width="' . $w . '" height="' . $w . '" aria-hidden="true">
        <rect x="2" y="2" width="48" height="48" rx="12" fill="#0F172A" stroke="#C89B3C" stroke-width="2"/>
        <path d="M15 36C15 23 23 14 38 13C37 27 29 36 17 37C16 37 15.5 36.5 15 36Z" fill="#C89B3C"/>
        <path d="M20 38C25 30 31 24 39 20C37 31 30 38 20 38Z" fill="#2BB3D6"/>
        <circle cx="35" cy="17" r="2.5" fill="#FFFFFF"/>
    </svg>';
}

// ---- Contact settings ----
$db_ct = q("SELECT email, phone, wp_number, working_hours, address, facebook, instagram, twitter, linkdin FROM contacts LIMIT 1")[0] ?? [];
$ct = array_merge([
    'email' => $brand['email'],
    'phone' => $brand['phone'],
    'wp_number' => $brand['phone'],
    'working_hours' => $brand['hours'],
    'address' => $brand['address'],
    'facebook' => '', 'instagram' => '', 'twitter' => '', 'linkdin' => ''
], array_filter($db_ct));

$digits = function ($s) { $d = preg_replace('/\D/', '', (string)$s); return strlen($d) === 10 ? '91' . $d : $d; };
$mail = $ct['email'] ?: $brand['email'];
$tel  = $digits($ct['phone']) ?: '918200778206';
$wa   = $digits($ct['wp_number']) ?: $tel;
$disp_phone = preg_match('/^91(\d{5})(\d{5})$/', $tel, $m) ? "+91 $m[1] $m[2]" : '+' . $tel;
$hours = $ct['working_hours'] ?: $brand['hours'];

$logo = img(q("SELECT logo_path FROM logos WHERE location='header' AND is_active=1 ORDER BY id DESC LIMIT 1")[0]['logo_path'] ?? '', 'logo', '');

// ---- Nav categories ----
$nav_cats = [];
foreach (q("SELECT cate_id, categories AS category_name, slug_url AS slug FROM categories WHERE status=1 ORDER BY id ASC") as $c) {
    $subs = q("SELECT categories AS subcat_name, slug_url AS slug FROM sub_categories WHERE cate_id=" . (int)$c['cate_id'] . " AND status=1");
    $nav_cats[$c['slug']] = ['name' => $c['category_name'], 'subs' => $subs];
}

// ---- SEO ----
$current_page_name = basename($_SERVER['PHP_SELF']);
if (empty($meta_title)) {
    $meta_title = 'AMSB GLOBAL | Red Chilli Powder & Turmeric Powder Manufacturer & Exporter in Morbi, Gujarat';
    $meta_desc  = 'AMSB GLOBAL is a trusted B2B manufacturer, wholesaler and exporter of pure Red Chilli Powder, Turmeric Powder and Indian Spices in bulk.';
    $meta_key   = 'Red Chilli Powder Exporter Gujarat, Turmeric Powder Wholesale Morbi, AMSB Global Wankaner';
    $schema     = '';
    if ($ok) {
        $pg = $conn->real_escape_string($current_page_name);
        $md = q("SELECT meta_title, meta_desc, meta_key FROM meta WHERE page_url='$pg' LIMIT 1")[0] ?? [];
        $meta_title = ($md['meta_title'] ?? '') ?: $meta_title;
        $meta_desc  = ($md['meta_desc'] ?? '') ?: $meta_desc;
        $meta_key   = ($md['meta_key'] ?? '') ?: $meta_key;
        $schema     = q("SELECT schema_markup FROM page_schemas WHERE page_url='$pg' LIMIT 1")[0]['schema_markup'] ?? '';
    }
}

if (!isset($canonical_url)) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
    $qs = '';
    foreach (['slug', 'slug_url', 'cat'] as $k) if (!empty($_GET[$k])) { $qs = '?' . $k . '=' . urlencode(trim($_GET[$k])); break; }
    $canonical_url = ($https ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) . $qs;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($meta_title) ?></title>
    <meta name="description" content="<?= e($meta_desc) ?>">
    <meta name="keywords" content="<?= e($meta_key) ?>">
    <link rel="canonical" href="<?= e($canonical_url) ?>">
    <?php if (!empty($schema)) echo $schema; ?>
    <link rel="icon" href="assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/style/style.css')) ?>">
</head>

<body>
    <a class="skip" href="#main">Skip to content</a>

    <div class="topbar">
        <div class="wrap">
            <div class="tl">
                <span class="tb-badge"><i class="fa-solid fa-certificate"></i> B2B Spice Exporter</span>
                <a href="mailto:<?= e($mail) ?>"><i class="fa-regular fa-envelope"></i> <?= e($mail) ?></a>
                <a href="tel:+<?= e($tel) ?>"><i class="fa-solid fa-phone"></i> <?= e($disp_phone) ?>
                    (<?= e($brand['founder']) ?>)</a>
            </div>
            <div class="tr-info">
                <span><i class="fa-solid fa-location-dot"></i> <?= e($brand['address']) ?></span>
            </div>
        </div>
    </div>

    <header class="site-header" id="top">
        <div class="wrap nav-wrap">
            <a class="brand" href="./">
                <!-- Agar logo upload kiya hai to wo dikhega, warna default icon -->
                <?= $logo ? '<img src="' . e($logo) . '" alt="' . e($brand['name']) . '">' : mark(42) ?>

                <!-- Agar logo upload NAHI kiya hai, tabhi ye text dikhega -->
                <?php if (!$logo): ?>
                <span>
                    <b><?= e($brand['name']) ?></b>
                    <small><?= e($brand['suffix']) ?></small>
                </span>
                <?php endif; ?>
            </a>
            <button class="tg" aria-label="Menu" aria-expanded="false" aria-controls="nav"><i></i></button>
            <nav id="nav">
                <ul>
                    <li><a href="./">Home</a></li>
                    <li><a href="about.php">Company</a></li>
                    <li class="has-dropdown">
                        <a href="products.php">Our Spices <i class="fa-solid fa-chevron-down dd-icon"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="products.php" class="all">View Complete Catalogue</a></li>
                            <?php foreach ($nav_cats as $slug => $cd): ?>
                            <li class="<?= $cd['subs'] ? 'has-sub-dropdown' : '' ?>">
                                <a href="category.php?slug=<?= e($slug) ?>"><?= e($cd['name']) ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="#lab-specs">Lab Specs</a></li>
                    <li><a href="service.php">B2B Services</a></li>
                </ul>
                <div class="nav-actions">
                    <a href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener" class="btn wa-btn"><i
                            class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                    <a href="#rfq" class="btn">Bulk Quote</a>
                </div>
            </nav>
        </div>
    </header>