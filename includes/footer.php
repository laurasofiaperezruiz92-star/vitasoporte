<?php if (empty($fullBleed)): ?></main><?php endif; ?>
<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <strong>VitaSoporte</strong><br>
            <?= e(APP_SLOGAN) ?>
        </div>
        <div>
            WhatsApp: +<?= e(WHATSAPP_NUMERO) ?><br>
            Tienda física: <a href="<?= e(TIENDA_MAPS_URL) ?>" target="_blank" rel="noopener"><?= e(TIENDA_UBICACION) ?></a><br>
            &copy; <?= date('Y') ?> VitaSoporte · Compra varios productos en un solo pedido
        </div>
    </div>
</footer>
</body>
</html>
