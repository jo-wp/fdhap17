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
}