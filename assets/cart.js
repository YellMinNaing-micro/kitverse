const cartPage = document.querySelector('[data-cart-page]');

if (cartPage) {
  const money = (amount) => `${new Intl.NumberFormat('en-US').format(amount)} MMK`;
  const feedback = cartPage.querySelector('[data-cart-feedback]');
  const checkout = cartPage.querySelector('.cart-summary .btn');
  const rows = () => [...cartPage.querySelectorAll('[data-cart-item]:not([hidden])')];
  checkout?.addEventListener('click', (event) => { if (checkout.getAttribute('aria-disabled') === 'true') event.preventDefault(); });

  function refreshCheckoutState() {
    const saving = !!cartPage.querySelector('[data-cart-form][data-saving="1"]');
    checkout?.classList.toggle('cart-checkout-saving', saving);
    checkout?.setAttribute('aria-disabled', saving ? 'true' : 'false');
  }

  function refreshTotals() {
    let subtotal = 0;
    let savings = 0;
    let personalization = 0;
    rows().forEach((row) => {
      const quantity = Number(row.querySelector('[data-cart-quantity]').textContent);
      const unitPrice = Number(row.dataset.unitPrice);
      const listPrice = Number(row.dataset.listPrice);
      const extra = Number(row.dataset.personalization);
      subtotal += listPrice * quantity;
      savings += (listPrice - unitPrice) * quantity;
      personalization += extra * quantity;
      row.querySelector('[data-line-total]').textContent = money((unitPrice + extra) * quantity);
    });
    cartPage.querySelector('[data-cart-subtotal]').textContent = money(subtotal);
    cartPage.querySelector('[data-cart-savings]').textContent = `-${money(savings)}`;
    cartPage.querySelector('[data-cart-personalization]').textContent = money(personalization);
    cartPage.querySelector('[data-cart-total]').textContent = money(subtotal - savings + personalization);
  }

  function setQuantity(row, quantity) {
    const max = Math.min(Number(row.dataset.stock), 20);
    row.querySelector('[data-cart-quantity]').textContent = quantity;
    const minus = row.querySelector('[data-cart-change="minus"]');
    const plus = row.querySelector('[data-cart-change="plus"]');
    minus.value = Math.max(1, quantity - 1);
    plus.value = Math.min(max, quantity + 1);
    minus.disabled = quantity <= 1;
    plus.disabled = quantity >= max;
    refreshTotals();
  }

  cartPage.querySelectorAll('[data-cart-form]').forEach((form) => {
    const row = form.closest('[data-cart-item]');
    form.addEventListener('submit', async (event) => {
      const action = event.submitter?.dataset.cartChange;
      if (!action) return;
      event.preventDefault();
      if (form.dataset.saving === '1') return;

      const previous = Number(row.querySelector('[data-cart-quantity]').textContent);
      const max = Math.min(Number(row.dataset.stock), 20);
      const next = action === 'remove' ? 0 : Math.max(1, Math.min(max, previous + (action === 'plus' ? 1 : -1)));
      if (next === previous) return;

      form.dataset.saving = '1';
      refreshCheckoutState();
      feedback.textContent = '';
      if (next === 0) { row.hidden = true; refreshTotals(); }
      else setQuantity(row, next);

      try {
        const data = new FormData(form);
        data.set('quantity', String(next));
        data.set('ajax', '1');
        const response = await fetch(window.location.href, { method: 'POST', body: data, headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
        if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) throw new Error('Could not update the cart.');
        const result = await response.json();
        if (next === 0) {
          row.remove();
          const badge = document.querySelector('.cart-count');
          if (badge) { badge.textContent = result.itemCount; if (!result.itemCount) badge.remove(); }
          const cartLink = document.querySelector('.cart-icon');
          if (cartLink) cartLink.setAttribute('aria-label', `Cart, ${result.itemCount} items`);
          if (!result.itemCount) { window.location.reload(); return; }
          refreshTotals();
        } else {
          setQuantity(row, Number(result.quantity));
          if (Number(result.quantity) !== next) feedback.textContent = 'Quantity adjusted to available stock.';
        }
      } catch (error) {
        row.hidden = false;
        setQuantity(row, previous);
        feedback.textContent = error.message || 'Could not update the cart.';
      } finally {
        form.dataset.saving = '0';
        refreshCheckoutState();
      }
    });
  });
}
