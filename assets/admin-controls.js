document.querySelectorAll('.admin select').forEach((select, index) => {
  if (!select.options.length) return;

  const name = select.name;
  const selected = select.selectedOptions[0] || select.options[0];
  const value = document.createElement('input');
  value.type = 'hidden';
  value.name = name;
  value.value = select.value;

  const menu = document.createElement('details');
  menu.className = 'admin-select';
  const summary = document.createElement('summary');
  const label = document.createElement('span');
  label.textContent = selected.textContent.trim();
  const chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  chevron.setAttribute('viewBox', '0 0 24 24');
  chevron.setAttribute('aria-hidden', 'true');
  chevron.innerHTML = '<path d="m6 9 6 6 6-6"/>';
  summary.append(label, chevron);

  const options = document.createElement('div');
  options.className = 'admin-select-options';
  options.setAttribute('role', 'group');
  options.setAttribute('aria-label', name.replaceAll('_', ' '));
  Array.from(select.options).forEach((option) => {
    const item = document.createElement('label');
    item.className = 'admin-select-option';
    const radio = document.createElement('input');
    radio.type = 'radio';
    radio.name = `admin-choice-${index}`;
    radio.value = option.value;
    radio.checked = option === selected;
    radio.disabled = option.disabled;
    radio.setAttribute('aria-label', option.textContent.trim());
    const text = document.createElement('span');
    text.textContent = option.textContent.trim();
    item.append(radio, text);
    options.append(item);
  });

  menu.append(summary, options);
  select.after(value, menu);
  select.disabled = true;
  select.hidden = true;

  menu.addEventListener('toggle', () => {
    if (menu.open) document.querySelectorAll('.admin-select[open]').forEach((other) => {
      if (other !== menu) other.open = false;
    });
  });
  menu.addEventListener('change', (event) => {
    if (!(event.target instanceof HTMLInputElement)) return;
    value.value = event.target.value;
    label.textContent = event.target.closest('label').textContent.trim();
    menu.open = false;
    summary.focus();
  });
  select.form?.addEventListener('submit', (event) => {
    if (select.required && !value.value) {
      event.preventDefault();
      menu.open = true;
      summary.focus();
    }
  });
});

document.addEventListener('click', (event) => {
  document.querySelectorAll('.admin-select[open]').forEach((menu) => {
    if (!menu.contains(event.target)) menu.open = false;
  });
});

document.addEventListener('keydown', (event) => {
  if (event.key !== 'Escape') return;
  document.querySelectorAll('.admin-select[open]').forEach((menu) => {
    menu.open = false;
    menu.querySelector('summary').focus();
  });
});

const productImageInput = document.querySelector('[data-product-image]');
const productImagePreview = document.querySelector('[data-image-preview]');
if (productImageInput && productImagePreview) {
  let previewUrl = null;
  productImageInput.addEventListener('change', () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null;
    const file = productImageInput.files?.[0];
    if (!file) return;
    previewUrl = URL.createObjectURL(file);
    const image = document.createElement('img');
    image.src = previewUrl;
    image.alt = 'Selected product image preview';
    productImagePreview.replaceChildren(image);
  });
}

const editSelected = document.querySelector('[data-edit-selected]');
if (editSelected) {
  const productRows = document.querySelectorAll('[data-product-row]');
  productRows.forEach((row) => {
    const radio = row.querySelector('input[name="selected_product"]');
    radio.addEventListener('change', () => {
      productRows.forEach((other) => other.classList.toggle('is-selected', other === row));
      editSelected.disabled = false;
      editSelected.dataset.productId = radio.value;
    });
  });
  editSelected.addEventListener('click', () => {
    if (editSelected.dataset.productId) {
      window.location.href = `?page=admin&tab=products&edit=${encodeURIComponent(editSelected.dataset.productId)}`;
    }
  });
}
