function displayCardMapSearchCampings(){
  console.log('Featherlight ready');

  // Optionnel : forcer l’ouverture via JS
  jQuery('.button-map').on('click', function (e) {
    e.preventDefault();
    jQuery.featherlight(jQuery('#mapBlockSearchCampings'));
  });
}
export default displayCardMapSearchCampings;