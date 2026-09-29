document.querySelectorAll('[data-filter-dropdown]').forEach((dropdown) => {
  dropdown.addEventListener('toggle', () => {
    if (!dropdown.open) return;
    document.querySelectorAll('[data-filter-dropdown]').forEach((other) => {
      if (other !== dropdown) other.open = false;
    });
  });

  dropdown.addEventListener('change', (event) => {
    const radio = event.target;
    if (!(radio instanceof HTMLInputElement) || radio.type !== 'radio') return;
    dropdown.querySelector('summary span').textContent = radio.closest('label').innerText.trim();
    dropdown.open = false;
    dropdown.querySelector('summary').focus();
  });
});

document.addEventListener('click', (event) => {
  document.querySelectorAll('[data-filter-dropdown][open]').forEach((dropdown) => {
    if (!dropdown.contains(event.target)) dropdown.open = false;
  });
});

document.addEventListener('keydown', (event) => {
  if (event.key !== 'Escape') return;
  document.querySelectorAll('[data-filter-dropdown][open]').forEach((dropdown) => {
    dropdown.open = false;
    dropdown.querySelector('summary').focus();
  });
});
