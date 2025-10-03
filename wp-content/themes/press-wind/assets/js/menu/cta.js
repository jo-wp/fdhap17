function ctaMobile() {
  const button = document.getElementById("cta-button");
  const hero = document.getElementById("masthead");

  if (!button || !hero) return;

  const observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        // hero visible -> cacher bouton
        button.classList.remove("show");
      } else {
        // hero plus visible -> afficher bouton
        button.classList.add("show");
      }
    },
    { threshold: 0 }
  );

  observer.observe(hero);
}
export default ctaMobile;
