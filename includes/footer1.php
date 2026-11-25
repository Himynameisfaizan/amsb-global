<footer class="site-footer ax-footer">
    <div class="wrap fg">
        <!-- Col 1: Brand & Tagline -->
        <div class="f-brand-col">
            <a class="brand" href="<?= e(url()) ?>">
                <?= $logo ? '<img src="' . e($logo) . '" alt="' . e($brand['name']) . '">' : mark(46) ?>
                <?php if (!$logo): ?>
                <span style="color:#fff;">
                    <b><?= e($brand['name']) ?></b>
                    <small style="color:var(--primary);"><?= e($brand['suffix']) ?></small>
                </span>
                <?php endif; ?>
            </a>
            <p class="fabout">We believe in pure quality and honest vyapaar. <strong>AMSB GLOBAL</strong> is a trusted B2B wholesale supplier of pure Red Chilli Powder and High-Curcumin Turmeric Powder from <?= e($brand['city']) ?>.</p>
            <p class="fweb"><i class="fa-solid fa-envelope"></i> <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a></p>
            <div class="soc">
                <a href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode($brand['wa_msg']) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                <a href="tel:+<?= e($tel) ?>" aria-label="Call AMSB GLOBAL"><i class="fa-solid fa-phone"></i></a>
                <?php foreach (['facebook' => 'fa-facebook-f', 'instagram' => 'fa-instagram', 'twitter' => 'fa-x-twitter', 'linkdin' => 'fa-linkedin-in'] as $k => $ic): if (!empty($ct[$k])): ?>
                <a href="<?= e($ct[$k]) ?>" target="_blank" rel="noopener" aria-label="<?= e($k) ?>"><i class="fa-brands <?= $ic ?>"></i></a>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <!-- Col 2: Information -->
        <div>
            <h4>Information</h4>
            <ul>
                <li><a href="<?= e(url()) ?>">Home</a></li>
                <li><a href="<?= e(url('about/')) ?>">About Us</a></li>
                <li><a href="<?= e(url('blog/')) ?>">Trade Blogs</a></li>
                <li><a href="<?= e(url('location/')) ?>">Supply Locations</a></li>
                <li><a href="<?= e(url('contact/')) ?>">Bulk Inquiry</a></li>
                <li><a href="<?= e(url('privacy-policy/')) ?>">Privacy Policy</a></li>
                <li><a href="<?= e(url('terms-and-conditions/')) ?>">Terms &amp; Conditions</a></li>
            </ul>
        </div>

        <!-- Col 3: Products (Dynamic Categories) -->
        <div>
            <h4>Products</h4>
            <ul>
                <li><a href="<?= e(url('products/')) ?>">All Wholesale Spices</a></li>
                <?php foreach ($nav_cats as $slug => $cd): ?>
                <li><a href="<?= e(url('category/' . urlencode($slug) . '/')) ?>"><?= e($cd['name']) ?></a></li>
                <?php endforeach; ?>
                <li><a href="<?= e(url('shipping-policy/')) ?>">Shipping &amp; Dispatch</a></li>
                <li><a href="<?= e(url('refund-policy/')) ?>">Refund &amp; Cancellation</a></li>
            </ul>
        </div>

        <!-- Col 4: Office Address & Trade Desk -->
        <div>
            <h4>Office Address</h4>
            <ul class="fc">
                <li>
                    <i class="fa-solid fa-location-dot"></i>
                    <span><strong class="fc-sub">Gujarat (India)</strong><?= e($ct['address']) ?></span>
                </li>
                <li>
                    <i class="fa-solid fa-user-tie"></i>
                    <span><strong class="fc-sub">Trade Desk</strong><?= e($brand['founder']) ?></span>
                </li>
                <li>
                    <i class="fa-solid fa-phone"></i>
                    <a href="tel:+<?= e($tel) ?>"><?= e($disp_phone) ?></a>
                </li>
                <li>
                    <i class="fa-regular fa-clock"></i>
                    <span><?= e($hours) ?></span>
                </li>
            </ul>
        </div>
    </div>

    <div class="fb-bar">
        <div class="wrap fb">
            <span>Copyright &copy; <?= date('Y') ?> <strong><?= e($brand['name']) ?></strong>. All Rights Reserved.</span>
            <span class="lic">
                <?php foreach ($brand['licences'] as $l): ?><em><?= e($l) ?></em><?php endforeach; ?>
            </span>
        </div>
    </div>
</footer>

<!-- Floating WhatsApp & Back to Top -->
<a class="wa" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
   href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode($brand['wa_msg'] ?? 'Hello') ?>">
    <i class="fa-brands fa-whatsapp"></i>
</a>
<a class="btt" href="#top" id="backToTop" aria-label="Back to top"><i class="fa-solid fa-arrow-up"></i></a>

<!-- Same-Page Hash Scroll Fix (Works with <base> tag across Blog TOC & #rfq) -->
<script>
document.addEventListener('click', function (ev) {
    const a = ev.target.closest('a[href*="#"]');
    if (!a) return;
    const raw = a.getAttribute('href') || '';
    const isHashOnly = raw.startsWith('#');
    const samePage = isHashOnly || (a.origin === window.location.origin && a.pathname === window.location.pathname && a.search === window.location.search);
    if (!samePage) return;

    const hash = isHashOnly ? raw : a.hash;
    if (hash && hash.length > 1) {
        const target = document.querySelector(hash);
        if (target) {
            ev.preventDefault();
            const y = target.getBoundingClientRect().top + window.scrollY - 90;
            window.scrollTo({ top: y, behavior: 'smooth' });
            if (hash !== '#top') history.replaceState(null, '', window.location.pathname + window.location.search + hash);
        }
    }
});
const mobBtn = document.getElementById('mobToggle');
const mobMenu = document.getElementById('mobNav');
if (mobBtn && mobMenu && !mobBtn.dataset.bound) {
    mobBtn.dataset.bound = '1';
    mobBtn.addEventListener('click', () => {
        const isOpen = mobMenu.classList.toggle('open');
        mobBtn.setAttribute('aria-expanded', String(isOpen));
    });
}
</script>
<?php if (is_file(dirname(__DIR__) . '/assets/js/main.js')): ?>
<script src="<?= e(asset('assets/js/main.js')) ?>"></script>
<?php endif; ?>
</body>
</html>