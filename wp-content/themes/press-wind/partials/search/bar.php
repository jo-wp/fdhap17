<?php

$position_css = (is_front_page() || is_page('carte-camping'))? 'relative' : 'md:!absolute md:bottom-[50px]' ;
$bg_css = (!is_front_page())? 'md:bg-[#F6F6F6]' : 'md:bg-white';
$margin_css = (!is_front_page())? 'mb-[60px]' : '' ; 
$destinations = get_terms([
  'taxonomy' => 'destination',   
  'hide_empty' => false,           
]);
?>
<div class="search-bar <?= $position_css ?> max-md:w-full  md:left-0 md:right-0 md:mx-[30px] <?= $margin_css; ?>">
  <div class="<?= $bg_css; ?> max-md:[&_input]:bg-white [&_input]:bg-[#F6F6F6] [&_input]:border-0 max-md:[&_select]:bg-white  [&_select]:bg-[#F6F6F6] [&_select]:border-0 [&_select]:w-full text-[#333333] [&_.facetwp-facet]:mb-0 flex flex-col md:flex-row justify-center md:justify-around max-w-[1300px] mx-auto rounded-[40px] p-[6px] max-[1300px]:gap-[20px] max-md:gap-[7px] gap-[40px] max-md:mx-[40px]">
    <div class="items-search-bar md:max-w-[230px] rounded-[10px] md:rounded-[40px] tax-destination max-md:bg-white bg-[#F6F6F6] px-[32px] py-[5px]">
      <p class="font-arial text-[14px] font-[700] m-0 p-0 max-md:hidden"><?= __('Destination', 'fdhpa17'); ?></p>
      <?= do_shortcode( '[facetwp facet="destination"]' ); ?>
    </div>
    <div class="items-search-bar tax-destination flex flex-col md:flex-row max-md:flex-wrap max-md:gap-[7px]">
      <div class="md:max-w-[230px] rounded-[10px] md:rounded-l-[40px] max-md:bg-white  bg-[#F6F6F6] px-[30px] py-[5px] max-md:w-[80%] max-md:[&_input]:w-full">
        <p class="font-arial text-[14px] font-[700] m-0 p-0 max-md:hidden"><?= __('Arrivée', 'fdhpa17'); ?></p>
        <?= do_shortcode( '[facetwp facet="date_start"]' ); ?>
      </div>
      <div class="md:max-w-[230px] rounded-[10px] md:rounded-r-[40px] max-md:bg-white  bg-[#F6F6F6] px-[30px] py-[5px] max-md:w-[80%] max-md:[&_input]:w-full">
        <p class="font-arial text-[14px] font-[700] m-0 p-0 max-md:hidden"><?= __('Départ', 'fdhpa17'); ?></p>
        <?= do_shortcode( '[facetwp facet="date_end"]' ); ?>
      </div>
    </div>
    <div class="items-search-bar md:max-w-[230px] rounded-[10px] md:rounded-[40px] max-md:bg-white tax-destination bg-[#F6F6F6] px-[30px] py-[5px]">
      <p class="font-arial text-[14px] font-[700] m-0 p-0 max-md:hidden"><?= __('Voyageurs', 'fdhpa17'); ?></p>
      <select class="bg-[#F6F6F6] border-0 w-full" name="tax-destination" id="#">
        <option class="bg-none text-[#333333]">Voyageurs</option>
        <option class="bg-none text-[#333333]" value="1">1 voyageur</option>
        <option class="bg-none text-[#333333]" value="2">2 voyageurs</option>
        <option class="bg-none text-[#333333]" value="3">3 voyageurs</option>
        <option class="bg-none text-[#333333]" value="4">4 voyageurs</option>
        <option class="bg-none text-[#333333]" value="5">5 voyageurs</option>
        <option class="bg-none text-[#333333]" value="6">6 voyageurs</option>
        <option class="bg-none text-[#333333]" value="7">7 voyageurs</option>
        <option class="bg-none text-[#333333]" value="8">8 voyageurs</option>
        <option class="bg-none text-[#333333]" value="9">9 voyageurs</option>
        <option class="bg-none text-[#333333]" value="10">10 voyageurs</option>
      </select>
    </div>
    <div
      class="items-search-bar max-w-[230px] min-w-[120px] flex flex-row items-center rounded-[40px] tax-destination max-[900px]:px-[15px] px-[20px] py-[10px]
      [&_.facetwp-display-value]:font-arial max-md:[&_.facetwp-display-value]:text-center [&_.facetwp-display-value]:text-white md:[&_.facetwp-display-value]:text-[#757575] [&_.facetwp-display-value]:text-[14px] [&_.facetwp-display-value]:font-[400] [&_.facetwp-display-value]:m-0 [&_.facetwp-display-value]:p-0">
      <?= do_shortcode('[facetwp facet="ctoutvert_checkbox"]') ?>
    </div>
    <?php if(!is_page('carte-camping') && !is_front_page()): ?>
    <?php endif; ?>
    
    <div class="items-search-bar md:max-w-[230px] flex flex-row items-center rounded-[40px] tax-destination py-[5px]">
      <input type="submit" data-href="/carte-camping/" name="online-reservation" id="" value="Rechercher" class="!bg-orange border-0 w-[56px] h-[56px]
      fwp-submit
      rounded-[50%] max-md:bg-arrow-button md:bg-zoom max-md:bg-[250px_20px] md:bg-[20px_20px] bg-no-repeat
      cursor-pointer hover:!bg-green transition-all
      max-md:w-full max-md:rounded-[10px]
      max-md:text-[16px]
      max-md:text-white
      max-md:bg-orange
      md:text-[0px]" />
    </div>
</div>
</div>

