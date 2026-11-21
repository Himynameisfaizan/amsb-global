<?php include 'config/connect.php'; ?>

<footer class="site-footer">
    <div class="wrap fg">
        <div class="f-brand-col">
            <a class="brand" href="./">
                <?= mark(42) ?>
                <span style="color:#fff;"><b><?= e($brand['name']) ?></b><small
                        style="color:var(--gold);"><?= e($brand['suffix']) ?></small></span>
            </a>
            <p class="fabout">Manufacturer, wholesaler and exporter of pure Red Chilli Powder, Turmeric Powder and
                Indian Spices in bulk from <?= e($brand['city']) ?>.</p>
            <div class="soc">
                <?php foreach (['facebook' => 'fa-facebook-f', 'instagram' => 'fa-instagram', 'twitter' => 'fa-x-twitter', 'linkdin' => 'fa-linkedin-in'] as $k => $ic): if (!empty($ct[$k])): ?>
                <a href="<?= e($ct[$k]) ?>" target="_blank" rel="noopener" aria-label="<?= e($k) ?>"><i
                        class="fa-brands <?= $ic ?>"></i></a>
                <?php endif; endforeach; ?>
            </div>
        </div>
        <div>
            <h4>Quick Links</h4>
            <ul>
                <li><a href="about.php">About Us</a></li>
                <li><a href="products.php">All Products</a></li>
                <li><a href="service.php">B2B Services</a></li>
                <li><a href="blog.php">Trade Insights</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="privacy-policy.php">Privacy Policy</a></li>
                <li><a href="terms-condition.php">Terms & Conditions</a></li>
            </ul>
        </div>
        <div>
            <h4>Spice Categories</h4>
            <ul>
                <?php foreach ($nav_cats as $slug => $cd): ?>
                <li><a href="category.php?slug=<?= e($slug) ?>"><?= e($cd['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div>
            <h4>Direct Trade Desk</h4>
            <ul class="fc">
                <?php if ($ct['address']): ?><li><i class="fa-solid fa-location-dot"></i>
                    <span><?= e($ct['address']) ?></span>
                </li><?php endif; ?>
                <li><i class="fa-solid fa-phone"></i> <a href="tel:+<?= e($tel) ?>"><?= e($disp_phone) ?></a></li>
                <li><i class="fa-regular fa-envelope"></i> <a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a></li>
                <li><i class="fa-regular fa-clock"></i> <span><?= e($hours) ?></span></li>
            </ul>
        </div>
    </div>
    <div class="wrap fb">
        <span>&copy; <?= date('Y') ?> <?= e($brand['name']) ?>. All rights reserved. Designed & Developed by <a href="https://www.digitalwebtrackers.com/" target="_blank" rel="noopener" style="color: white;">Digital Web Trackers</a></span>
        <?php if (!empty($brand['licences'])): ?>
        <span class="lic">
            <?php foreach ($brand['licences'] as $l): ?><em><?= e($l) ?></em><?php endforeach; ?>
        </span>
        <?php endif; ?>
    </div>
</footer>

<!-- Floating WhatsApp -->
<a class="wa" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
    href="https://wa.me/<?= e($wa) ?>?text=<?= rawurlencode($brand['wa_msg'] ?? 'Hello') ?>">
    <i class="fa-brands fa-whatsapp"></i>
</a>

<script src="<?= e(asset('assets/js/main.js')) ?>"></script>
</body>

</html>