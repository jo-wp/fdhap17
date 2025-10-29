function isSticky(){
  const nav = document.querySelector(".block-hero__content__navigation");
  if (!nav) return;

  let observer, ro;
  const sentinel = document.createElement("div");
  sentinel.setAttribute("aria-hidden", "true");
  nav.parentNode.insertBefore(sentinel, nav);

  const placeholder = document.createElement("div");
  placeholder.style.display = "none";
  nav.parentNode.insertBefore(placeholder, nav.nextSibling);

  const setPlaceholder = () => {
    placeholder.style.height = `${nav.offsetHeight}px`;
  };

  const activate = () => {
    setPlaceholder();
    placeholder.style.display = "block";
    nav.classList.add("is-sticky");
    nav.style.width = `${placeholder.offsetWidth}px`;
  };

  const deactivate = () => {
    placeholder.style.display = "none";
    nav.classList.remove("is-sticky");
    nav.style.width = "";
  };

  const setupObserver = () => {
    if (window.innerWidth < 1024) {
      // désactivation pour mobile / tablette
      deactivate();
      if (observer) observer.disconnect();
      if (ro) ro.disconnect();
      return;
    }

    observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          deactivate();
        } else {
          activate();
        }
      },
      { rootMargin: "0px", threshold: [0] }
    );
    observer.observe(sentinel);

    ro = new ResizeObserver(() => {
      if (nav.classList.contains("is-sticky")) setPlaceholder();
    });
    ro.observe(nav);
  };

  document.querySelector('nav').addEventListener('click', function (e) {
  if (e.target.tagName === 'A' && e.target.getAttribute('href') === '#') {
    e.preventDefault();
  }
});

  // initialisation + écoute du resize pour bascule responsive
  setupObserver();
  window.addEventListener("resize", setupObserver);
}

export default isSticky;