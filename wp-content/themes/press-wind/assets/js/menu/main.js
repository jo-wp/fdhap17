function menuMobile() {

  // Mobile uniquement : Tailwind md = 768px
  const MOBILE_QUERY = '(max-width: 767.98px)';
  const mq = window.matchMedia(MOBILE_QUERY);

  const rootNav = document.querySelector('nav');
  if (!rootNav) return;

  let bound = false;

  const qsAll = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  function closeAllSubmenus() {
    qsAll('.submenu.active', rootNav).forEach(el => el.classList.remove('active'));
    qsAll('.submenu-child.active', rootNav).forEach(el => el.classList.remove('active'));
  }

  function onTopClick(e) {
    const a = e.currentTarget;
    const li = a.parentElement;
    const submenu = li.querySelector(':scope > ul.submenu');
    if (!submenu) return; // pas de sous-menu -> navigation normale

    e.preventDefault();

    const isOpen = submenu.classList.contains('active');
    closeAllSubmenus();
    if (!isOpen) submenu.classList.add('active');
  }

  function onSecondClick(e) {
    const a = e.currentTarget;
    if (a.classList.contains('button-back-mobile')) return; // géré ailleurs

    const li = a.parentElement;
    const child = li.querySelector(':scope > ul.submenu-child');
    if (!child) return; // pas d'enfant -> navigation normale

    e.preventDefault();

    // fermer les autres submenu-child du même niveau
    qsAll(':scope > li > ul.submenu-child', li.parentElement).forEach(s => s.classList.remove('active'));
    child.classList.add('active');
  }

  function onBackClick(e) {
    e.preventDefault();
    const submenu = e.currentTarget.closest('ul.submenu');
    if (!submenu) return;

    qsAll('.submenu-child.active', submenu).forEach(el => el.classList.remove('active'));
    submenu.classList.remove('active');
  }

  function onDocClick(e) {
    if (!e.target.closest('nav')) closeAllSubmenus();
  }

  function bind() {
    if (bound) return;
    bound = true;

    // Liens de 1er niveau
    qsAll(':scope > ul > li > a', rootNav).forEach(a => {
      a.addEventListener('click', onTopClick);
    });

    // Liens dans les .submenu (2e niveau + bouton retour)
    qsAll('ul.submenu > li > a', rootNav).forEach(a => {
      if (a.classList.contains('button-back-mobile')) {
        a.addEventListener('click', onBackClick);
      } else {
        a.addEventListener('click', onSecondClick);
      }
    });

    document.addEventListener('click', onDocClick);
  }

  function unbind() {
    if (!bound) return;
    bound = false;

    qsAll(':scope > ul > li > a', rootNav).forEach(a => {
      a.removeEventListener('click', onTopClick);
    });
    qsAll('ul.submenu > li > a', rootNav).forEach(a => {
      if (a.classList.contains('button-back-mobile')) {
        a.removeEventListener('click', onBackClick);
      } else {
        a.removeEventListener('click', onSecondClick);
      }
    });
    document.removeEventListener('click', onDocClick);

    // Nettoyer les états actifs quand on quitte le mobile
    closeAllSubmenus();
  }

  function handleChange(e) {
    if (e.matches) bind(); else unbind();
  }

  // Écoute des changements de breakpoint
  if (mq.addEventListener) {
    mq.addEventListener('change', handleChange);
  } else {
    // anciens navigateurs
    mq.addListener(handleChange);
  }
  // Init
  handleChange(mq);
};

export default menuMobile;
