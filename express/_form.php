<?php

/**
 * Form for creating or editing an express order
 * @var array      $order
 * @var array      $lines
 * @var array      $products
 * @var bool       $delivery_allowed
 * @var array|null $statuses
 */
?>
<div class="form-group">
    <label>Customer Name</label>
    <input type="text" name="customer_name" value="<?= h($order['customer_name']) ?>" required>
</div>

<div class="form-group">
    <label>Phone number</label>
    <input type="tel" name="phone" value="<?= h($order['phone'] ?? '') ?>" required>
</div>

<div class="form-group">
    <label>Grocery list</label>

    <div id="lines">
        <?php foreach ($lines as $l): ?>
            <div class="line">
                <select name="product_id[]">
                    <option value="">Select product...</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"
                            <?= (int)$l['product_id'] === (int)$p['id'] ? 'selected' : '' ?>>
                            <?= h($p['name']) ?> (<?= h($p['product_code']) ?>)
                            - <?= money($p['price']) ?>
                            - <?= (int)$p['quantity'] > 0 ? (int)$p['quantity'] . ' in stock' : 'OUT OF STOCK' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <input type="number" name="qty[]" min="1" placeholder="Qty"
                    value="<?= $l['quantity'] > 0 ? (int)$l['quantity'] : '' ?>">
                <br><br>
                <button type="button" class="button-danger" onclick="removeLine(this)">Remove</button>
                <br><br>
            </div>
        <?php endforeach; ?>
    </div>
    <hr>
    <hr>
    <button type="button" class="button-secondary" onclick="addLine()">+ Add product</button>
</div>

<div class="form-group">
    <label>Fulfilment</label>

    <label>
        <input type="radio" name="fulfilment" value="Curbside"
            <?= $order['delivery_method'] !== 'Delivery' ? 'checked' : '' ?>>
        Curbside pickup
    </label>

    <label>
        <input type="radio" name="fulfilment" value="Delivery"
            <?= $order['delivery_method'] === 'Delivery' ? 'checked' : '' ?>
            <?= $delivery_allowed ? '' : 'disabled' ?>>
        Home delivery (+<?= money(DELIVERY_FEE) ?>)
    </label>

    <?php if (!$delivery_allowed): ?>
        <small>
            <em>Home delivery is not available at this time.</em>
        </small>
    <?php endif; ?>
</div>

<?php if ($delivery_allowed): ?>
    <div class="form-group">
        <label>Delivery address (home delivery only)</label>
        <input type="text" name="delivery_address" value="<?= h($order['delivery_address'] ?? '') ?>">
    </div>
<?php endif; ?>

<?php if (isset($statuses)): ?>
    <div class="form-group">
        <label>Status</label>
        <select name="status">
            <?php foreach ($statuses as $s): ?>
                <option value="<?= h($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>>
                    <?= h($s) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
<?php endif; ?>

<script>
    // Add and remove lines of products in the order form
    function addLine() {
        var box = document.getElementById('lines');
        var copy = box.querySelector('.line').cloneNode(true);
        copy.querySelector('select').selectedIndex = 0;
        copy.querySelector('input').value = '';
        box.appendChild(copy);
    }

    function removeLine(btn) {
        var box = document.getElementById('lines');
        var row = btn.closest('.line');
        if (box.querySelectorAll('.line').length > 1) {
            row.remove();
        } else {
            row.querySelector('select').selectedIndex = 0;
            row.querySelector('input').value = '';
        }
    }
</script>