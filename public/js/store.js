document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
        return;
    }
    if (form.hasAttribute('data-once')) {
        if (form.dataset.submitting === 'true') {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = 'true';
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.dataset.originalLabel = button.textContent;
            button.textContent = 'Memproses…';
            button.disabled = true;
        }
    }
});

// Browsers can restore a submitted page from the back/forward cache.
window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submitting="true"]').forEach((form) => {
        delete form.dataset.submitting;
        const button = form.querySelector('button[data-original-label]');
        if (button) {
            button.textContent = button.dataset.originalLabel;
            button.disabled = false;
        }
    });
});

// Format Rupiah helper
function formatRupiah(number) {
    return 'Rp ' + Number(number).toLocaleString('id-ID');
}

// 1. LIVE SUBTOTAL PADA HALAMAN DETAIL MENU (PRODUCT SHOW)
document.addEventListener('DOMContentLoaded', () => {
    const productForm = document.getElementById('product-form');
    const basePriceEl = document.getElementById('base-price');
    const quantityInput = document.getElementById('quantity');
    const liveTotalEl = document.getElementById('live-total-preview');

    if (productForm && basePriceEl && quantityInput && liveTotalEl) {
        const basePrice = parseInt(basePriceEl.dataset.price || '0', 10);
        const addonCheckboxes = productForm.querySelectorAll('.addon-checkbox');

        function updateProductSubtotal() {
            let addonsTotal = 0;
            addonCheckboxes.forEach((cb) => {
                if (cb.checked) {
                    addonsTotal += parseInt(cb.dataset.price || '0', 10);
                }
            });

            const qty = Math.max(1, parseInt(quantityInput.value || '1', 10));
            const total = (basePrice + addonsTotal) * qty;

            liveTotalEl.textContent = formatRupiah(total);
        }

        addonCheckboxes.forEach((cb) => cb.addEventListener('change', updateProductSubtotal));
        quantityInput.addEventListener('input', updateProductSubtotal);
        updateProductSubtotal();
    }

    // 2. CHECKOUT INTERACTION (FULFILLMENT TOGGLE & GRAND TOTAL CALCULATION)
    const checkoutForm = document.getElementById('checkout-form');
    const summaryPanel = document.getElementById('checkout-summary');

    if (checkoutForm && summaryPanel) {
        const subtotal = parseInt(summaryPanel.dataset.subtotal || '0', 10);
        const deliveryFee = parseInt(summaryPanel.dataset.deliveryFee || '10000', 10);

        const fulfillmentRadios = checkoutForm.querySelectorAll('input[name="fulfillment_type"]');
        const deliveryContainer = document.getElementById('delivery-address-container');
        const deliveryTextarea = document.getElementById('delivery_address');

        const cardCash = document.getElementById('card-cash');
        const cashRadio = checkoutForm.querySelector('input[name="payment_method"][value="cash_on_pickup"]');
        const qrisRadio = checkoutForm.querySelector('input[name="payment_method"][value="qris"]');

        const summaryFeeEl = document.getElementById('summary-delivery-fee');
        const summaryGrandTotalEl = document.getElementById('summary-grand-total');
        const btnTotalDisplay = document.getElementById('btn-total-display');

        function updateCheckoutState() {
            const selectedFulfillment = checkoutForm.querySelector('input[name="fulfillment_type"]:checked')?.value || 'pickup';
            const isDelivery = selectedFulfillment === 'delivery';

            if (isDelivery) {
                // Show delivery address
                if (deliveryContainer) deliveryContainer.style.display = 'flex';
                if (deliveryTextarea) deliveryTextarea.required = true;

                // Disable cash on pickup, force QRIS
                if (cardCash) {
                    cardCash.classList.add('disabled');
                    cardCash.style.pointerEvents = 'none';
                }
                if (cashRadio) cashRadio.disabled = true;
                if (qrisRadio) qrisRadio.checked = true;

                // Update summary fee & total
                const grandTotal = subtotal + deliveryFee;
                if (summaryFeeEl) summaryFeeEl.textContent = formatRupiah(deliveryFee);
                if (summaryGrandTotalEl) summaryGrandTotalEl.textContent = formatRupiah(grandTotal);
                if (btnTotalDisplay) btnTotalDisplay.textContent = formatRupiah(grandTotal);
            } else {
                // Hide delivery address
                if (deliveryContainer) deliveryContainer.style.display = 'none';
                if (deliveryTextarea) deliveryTextarea.required = false;

                // Enable cash on pickup
                if (cardCash) {
                    cardCash.classList.remove('disabled');
                    cardCash.style.pointerEvents = 'auto';
                }
                if (cashRadio) cashRadio.disabled = false;

                // Update summary fee & total
                if (summaryFeeEl) summaryFeeEl.textContent = 'Rp 0';
                if (summaryGrandTotalEl) summaryGrandTotalEl.textContent = formatRupiah(subtotal);
                if (btnTotalDisplay) btnTotalDisplay.textContent = formatRupiah(subtotal);
            }

            // Sync visual active class on choice cards
            document.querySelectorAll('.choice-card').forEach((card) => {
                const radio = card.querySelector('input[type="radio"]');
                if (radio && radio.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });
        }

        fulfillmentRadios.forEach((r) => r.addEventListener('change', updateCheckoutState));
        checkoutForm.querySelectorAll('input[name="payment_method"]').forEach((r) => {
            r.addEventListener('change', updateCheckoutState);
        });

        updateCheckoutState();
    }

    // 3. COPY NOMINAL BUTTON PADA HALAMAN RINCIAN PESANAN
    const copyBtn = document.getElementById('copy-amount-btn');
    const nominalEl = document.getElementById('order-nominal');

    if (copyBtn && nominalEl) {
        const status = document.getElementById('copy-amount-status');
        let resetTimer;
        const fallbackCopy = (text) => {
            const input = document.createElement('textarea');
            input.value = text;
            input.className = 'sr-only';
            input.setAttribute('readonly', '');
            document.body.appendChild(input);
            input.select();
            try {
                return document.execCommand('copy');
            } finally {
                input.remove();
                copyBtn.focus({ preventScroll: true });
            }
        };
        copyBtn.addEventListener('click', async () => {
            const nominal = nominalEl.dataset.nominal;
            if (!nominal) return;
            clearTimeout(resetTimer);
            copyBtn.classList.remove('is-copied');
            copyBtn.title = 'Salin nominal';
            copyBtn.setAttribute('aria-label', 'Salin nominal pembayaran');
            if (status) status.textContent = '';
            let copied = false;
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(nominal);
                    copied = true;
                }
            } catch (_) {
                // Fall back when browser clipboard permission is unavailable.
            }
            if (!copied) {
                try { copied = fallbackCopy(nominal); } catch (_) { copied = false; }
            }
            if (!copied) {
                if (status) status.textContent = 'Nominal gagal disalin. Pilih angka nominal dan salin secara manual.';
                copyBtn.title = 'Gagal menyalin. Salin nominal secara manual.';
                return;
            }
            copyBtn.classList.add('is-copied');
            copyBtn.title = 'Nominal tersalin';
            copyBtn.setAttribute('aria-label', 'Nominal pembayaran tersalin');
            if (status) status.textContent = 'Nominal pembayaran tersalin.';
            resetTimer = setTimeout(() => {
                copyBtn.classList.remove('is-copied');
                copyBtn.title = 'Salin nominal';
                copyBtn.setAttribute('aria-label', 'Salin nominal pembayaran');
            }, 2000);
        });
    }
});
