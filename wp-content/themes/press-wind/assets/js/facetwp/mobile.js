export default function filtreMobile() {
  const wrapperFilters = document.querySelector('.wrapper-filters');
  if (!wrapperFilters) return;

  const buttonFilters = document.querySelectorAll('[data-button="filters"]');
  if (!buttonFilters.length) return;

  buttonFilters.forEach((button) => {
    button.addEventListener('click', () => {
      wrapperFilters.classList.toggle('active');
    });
  });

  const filters = document.querySelector('.block-search .wrapper-filters');

filters.addEventListener('click', function(e) {
  if (!filters.classList.contains('active')) return;

  const rect = filters.getBoundingClientRect();

  const closeX = window.innerWidth - 20 - 42;
  const closeY = 20;
  const size = 42;

  const x = e.clientX;
  const y = e.clientY;

  if (
    x >= closeX &&
    x <= closeX + size &&
    y >= closeY &&
    y <= closeY + size
  ) {
    filters.classList.remove('active');
  }
});
}