<?php
/** Global header: DB connect, helpers, Auto SEO + Schema + Canonical engine - AMSB GLOBAL */
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../config/connect.php';
if (is_file(__DIR__ . '/../config/site.php')) {
    require_once __DIR__ . '/../config/site.php';
}
$ok = isset($conn) && !$conn->connect_error;

// ---- Brand Configuration for AMSB GLOBAL (B2B Wholesale Trade) ----
$brand = [
    'name'     => 'AMSB GLOBAL',
    'suffix'   => 'Wholesale Spice Traders & Suppliers',
    'founder'  => 'Mahir Bhai',
    'city'     => 'Wankaner, Morbi (Gujarat)',
    'address'  => 'Wankaner, Morbi, Gujarat - 363621, India',
    'phone'    => '8200778206',
    'email'    => 'amsbglobal@gmail.com',
    'hours'    => 'Mon - Sat: 9:00 AM - 8:00 PM IST',
    'licences' => ['100% Pure Spices', 'GST Registered', 'Pan-India Supply'],
    'wa_msg'   => 'Hello Mahir Bhai (AMSB GLOBAL), I want to enquire about wholesale Red Chilli Powder and Turmeric Powder rates.'
];

// ---- Auto Base Path & Base URL (XAMPP Localhost & Live Hostinger dono par automatic) ----
$doc_root  = rtrim(str_replace('\\', '/', (string)(realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ($_SERVER['DOCUMENT_ROOT'] ?? ''))), '/');
$proj_root = rtrim(str_replace('\\', '/', (string)realpath(dirname(__DIR__))), '/');
$base_path = '/';
if ($doc_root !== '' && stripos($proj_root, $doc_root) === 0) {
    $rel = trim(substr($proj_root, strlen($doc_root)), '/');
    $base_path = $rel !== '' ? '/' . $rel . '/' : '/';
}
$https_now = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? '') == 443)
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$scheme   = $https_now ? 'https://' : 'http://';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_url = $scheme . $host . $base_path;

// ---- Helpers ----
if (!function_exists('url')) {
    function url($path = '') {
        global $base_path;
        $path = (string)$path;
        if ($path === '' || $path === '/') return $base_path;
        if ($path[0] === '#') return $base_path . $path;
        return $base_path . ltrim($path, '/');
    }
}
if (!function_exists('q')) {
    function q($sql) {
        global $conn, $ok;
        $r = [];
        if ($ok && ($res = @$conn->query($sql))) {
            while ($x = $res->fetch_assoc()) $r[] = $x;
        }
        return $r;
    }
}
if (!function_exists('e')) {
    function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('txt')) {
    function txt($s, $n = 0) {
        $s = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string)$s))));
        return $n ? mb_strimwidth($s, 0, $n, '...') : $s;
    }
}
if (!function_exists('un')) {
    function un($id, $w = 800) { return "https://images.unsplash.com/$id?auto=format&fit=crop&w=$w&q=80"; }
}
if (!function_exists('asset')) {
    function asset($p) {
        global $base_path;
        $p = ltrim((string)$p, '/');
        $f = dirname(__DIR__) . '/' . $p;
        return $base_path . $p . (is_file($f) ? '?v=' . filemtime($f) : '');
    }
}
if (!function_exists('pic')) {
    function pic($src, $alt, $eager = false) {
        return '<img src="' . e($src) . '" alt="' . e($alt) . '"' . ($eager ? '' : ' loading="lazy"') . ' onerror="this.style.display=\'none\'">';
    }
}
if (!function_exists('img')) {
    function img($f, $d, $fb) {
        global $base_path;
        $f = trim((string)$f);
        if ($f === '') return $fb;
        if (preg_match('#^https?://#', $f)) return $f;

        $c = ltrim($f, '/');
        $b = basename($c);
        $root = dirname(__DIR__) . '/';

        // Singular aur plural dono folder names auto-check honge
        $dirs = [$d];
        $pair = [
            'categories' => 'category',
            'category'   => 'categories',
            'products'   => 'product',
            'product'    => 'products',
            'blogs'      => 'blog',
            'blog'       => 'blogs',
            'banners'    => 'banner',
            'banner'     => 'banners',
        ];
        if (isset($pair[$d])) {
            $dirs[] = $pair[$d];
        }

        $paths = [
            $c,
            "admin/$c",
            "uploads/$b",
            "admin/uploads/$b",
            "assets/images/$b",
            "admin/assets/img/uploads/$b",
        ];

        foreach ($dirs as $dir) {
            $paths[] = "uploads/$dir/$b";
            $paths[] = "admin/uploads/$dir/$b";
            $paths[] = "assets/img/uploads/$dir/$b";
            $paths[] = "admin/assets/img/uploads/$dir/$b";
            $paths[] = "admin/assets/uploads/$dir/$b";
        }

        foreach (array_unique($paths) as $p) {
            if (is_file($root . $p)) {
                return $base_path . $p;
            }
        }
        return $fb;
    }
}
if (!function_exists('mark')) {
    function mark($w = 42) {
        return '<svg viewBox="0 0 52 52" width="' . $w . '" height="' . $w . '" aria-hidden="true">
            <rect x="2" y="2" width="48" height="48" rx="12" fill="#081225" stroke="#C89B3C" stroke-width="2"/>
            <path d="M15 36C15 23 23 14 38 13C37 27 29 36 17 37C16 37 15.5 36.5 15 36Z" fill="#C89B3C"/>
            <path d="M20 38C25 30 31 24 39 20C37 31 30 38 20 38Z" fill="#2BB3D6"/>
            <circle cx="35" cy="17" r="2.5" fill="#FFFFFF"/>
        </svg>';
    }
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

if (stripos($ct['email'], 'bharat') !== false || stripos($ct['email'], 'example') !== false) $ct['email'] = $brand['email'];
if (strpos($ct['phone'], '99240') !== false || strpos($ct['phone'], '98000') !== false) { $ct['phone'] = $brand['phone']; $ct['wp_number'] = $brand['phone']; }
if (stripos($ct['address'], 'Ambaliyasan') !== false || empty($ct['address'])) $ct['address'] = $brand['address'];

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

// ---- Clean Canonical URL Engine (Auto for Every Page) ----
if (!isset($canonical_url)) {
    $clean_uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $clean_uri = preg_replace('#/index\.php$#i', '/', $clean_uri);
    $canonical_url = $scheme . $host . $clean_uri;
}

// ---- Auto Meta & Schema Engine (Priority: 1. Dynamic Page Vars -> 2. DB meta/page_schemas -> 3. Page $page_meta -> 4. Global Default) ----
$current_page_name = basename($_SERVER['PHP_SELF']);
$db_meta   = [];
$db_schema = '';

if ($ok) {
    $pg        = $conn->real_escape_string($current_page_name);
    $db_meta   = q("SELECT meta_title, meta_desc, meta_key FROM meta WHERE page_url='$pg' LIMIT 1")[0] ?? [];
    $db_schema = q("SELECT schema_markup FROM page_schemas WHERE page_url='$pg' LIMIT 1")[0]['schema_markup'] ?? '';
}

$default_title = 'AMSB GLOBAL | Bulk Red Chilli Powder & Turmeric Powder Wholesale Supplier in Morbi, Gujarat';
$default_desc  = 'AMSB GLOBAL (Wankaner, Morbi, Gujarat) is a trusted B2B wholesale supplier and trader of pure Red Chilli Powder and Turmeric Powder across India.';
$default_key   = 'Red Chilli Powder Wholesale Gujarat, Turmeric Powder Bulk Supplier Morbi, AMSB Global Wankaner, Indian Spices B2B Trade';

$fb_title  = $page_meta['title']  ?? ($page_meta_title ?? $default_title);
$fb_desc   = $page_meta['desc']   ?? ($page_meta_desc  ?? $default_desc);
$fb_key    = $page_meta['key']    ?? ($page_meta_key   ?? $default_key);
$fb_schema = $page_meta['schema'] ?? ($page_schema     ?? '');

$meta_title = !empty($meta_title) ? $meta_title : (($db_meta['meta_title'] ?? '') ?: $fb_title);
$meta_desc  = !empty($meta_desc)  ? $meta_desc  : (($db_meta['meta_desc'] ?? '')  ?: $fb_desc);
$meta_key   = !empty($meta_key)   ? $meta_key   : (($db_meta['meta_key'] ?? '')   ?: $fb_key);

// Global Organization JSON-LD fallback if neither DB nor page provides schema
$default_org_schema = '<script type="application/ld+json">' . json_encode([
    '@context'  => 'https://schema.org',
    '@type'     => 'WholesaleStore',
    'name'      => $brand['name'],
    'url'       => $base_url,
    'telephone' => '+' . $tel,
    'email'     => $mail,
    'founder'   => ['@type' => 'Person', 'name' => $brand['founder']],
    'address'   => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'Wankaner',
        'addressLocality' => 'Morbi',
        'addressRegion'   => 'Gujarat',
        'postalCode'      => '363621',
        'addressCountry'  => 'IN'
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';

$schema = !empty($schema) ? $schema : ($db_schema ?: ($fb_schema ?: $default_org_schema));
?>
<!DOCTYPE html>
<html lang="en-IN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="<?= e($base_url) ?>">
    <title><?= e($meta_title) ?></title>
    <meta name="description" content="<?= e($meta_desc) ?>">
    <meta name="keywords" content="<?= e($meta_key) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e($canonical_url) ?>">
    <link rel="icon" href="<?= e(asset('assets/img/favicon.png')) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_IN">
    <meta property="og:site_name" content="<?= e($brand['name']) ?>">
    <meta property="og:title" content="<?= e($meta_title) ?>">
    <meta property="og:description" content="<?= e($meta_desc) ?>">
    <meta property="og:url" content="<?= e($canonical_url) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($meta_title) ?>">
    <meta name="twitter:description" content="<?= e($meta_desc) ?>">
    <?php if (!empty($schema)) echo "\n    " . $schema . "\n"; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/style/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/style/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/style/about.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/style/product.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/style/blogs.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/style/contact.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/style/custom.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/style/policy.css')) ?>">
    <?php foreach (($extra_css ?? []) as $css): ?>
    <link rel="stylesheet" href="<?= e(asset($css)) ?>">
    <?php endforeach; ?>
</head>

<body>
    <a class="skip" href="#main">Skip to content</a>

    <div class="topbar">
        <div class="wrap">
            <div class="tl">
                <span class="tb-badge"><i class="fa-solid fa-handshake"></i> B2B Spice Trade</span>
                <a href="mailto:<?= e($mail) ?>"><i class="fa-regular fa-envelope"></i> <?= e($mail) ?></a>
                <a href="tel:+<?= e($tel) ?>"><i class="fa-solid fa-phone"></i> <?= e($disp_phone) ?> (<?= e($brand['founder']) ?>)</a>
            </div>
            <div class="tr-info">
                <span><i class="fa-solid fa-location-dot"></i> <?= e($brand['address']) ?></span>
            </div>
        </div>
    </div>

    <header class="site-header" id="top">
        <div class="wrap nav-wrap">
            <a class="brand" href="<?= e(url()) ?>">
                <?= $logo ? '<img src="' . e($logo) . '" alt="' . e($brand['name']) . '">' : mark(42) ?>
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
                    <li><a href="<?= e(url()) ?>">Home</a></li>
                    <li><a href="<?= e(url('about/')) ?>">About Us</a></li>
                    <li class="has-dropdown">
                        <a href="<?= e(url('products/')) ?>">Our Spices <i class="fa-solid fa-chevron-down dd-icon"></i></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= e(url('products/')) ?>" class="all">View Complete Catalogue</a></li>
                            <?php foreach ($nav_cats as $slug => $cd): ?>
                            <li class="<?= $cd['subs'] ? 'has-sub-dropdown' : '' ?>">
                                <a href="<?= e(url('category/' . urlencode($slug) . '/')) ?>"><?= e($cd['name']) ?></a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="<?= e(url('blog/')) ?>">Blogs</a></li>
                    <li><a href="<?= e(url('contact/')) ?>">Contact</a></li>
                </ul>
                <div class="nav-actions">
                    <a href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode($brand['wa_msg']) ?>" target="_blank" rel="noopener" class="btn wa-btn"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                    <a href="<?= e(url('contact/')) ?>" class="btn">Bulk Quote</a>
                </div>
            </nav>
        </div>
    </header>