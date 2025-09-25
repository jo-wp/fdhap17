<?php

$position_css = (!is_front_page())? 'relative' : 'md:!absolute' ;
$bg_css = (!is_front_page())? 'md:bg-[#F6F6F6]' : 'md:bg-white';
$margin_css = (!is_front_page())? 'mb-[60px]' : '' ; 
$destinations = get_terms([
  'taxonomy' => 'destination',   
  'hide_empty' => false,           
]);
?>
<div class=" md:bottom-[50px] md:left-0 md:right-0 md:mx-auto <?= $margin_css; ?>">
  <form action="/tous-les-campings-de-charente-maritime" method="GET"
    class="<?= $bg_css; ?> flex flex-col md:flex-row justify-center md:justify-around max-w-[1300px] mx-auto rounded-[40px] p-[6px] max-[1300px]:gap-[20px] gap-[40px]">
    <div class="items-search-bar md:max-w-[230px] rounded-[40px] tax-destination bg-[#F6F6F6] px-[30px] py-[5px]">
      <p class="font-arial text-[14px] font-[700] m-0 p-0"><?= __('Destination', 'fdhpa17'); ?></p>
      <?php if (!is_wp_error($destinations) && !empty($destinations)): ?>
        <select class="bg-[#F6F6F6] border-0 w-full" name="_destination" id="#">
          <option>Toutes</option>
          <?php foreach ($destinations as $destination): ?>
            <option value="<?= esc_attr($destination->slug); ?>" <?php if(isset($_GET['_destination'])): 
               ($_GET['_destination']==$destination->slug)? 'selected="selected"' : ''; endif; ?>>
              <?= esc_html($destination->name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>
    <div class="items-search-bar tax-destination flex flex-row">
      <div class="md:max-w-[230px] rounded-l-[40px]  bg-[#F6F6F6] px-[30px] py-[5px]">
        <p class="font-arial text-[14px] font-[700] m-0 p-0"><?= __('Arrivée', 'fdhpa17'); ?></p>
        <input class="bg-[#F6F6F6] border-0 w-full text-[#333333]" type="date" id="start" name="trip-start"
          value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" max="2050-12-31" />
      </div>
      <div class="md:max-w-[230px] rounded-r-[40px]  bg-[#F6F6F6] px-[30px] py-[5px]">
        <p class="font-arial text-[14px] font-[700] m-0 p-0"><?= __('Départ', 'fdhpa17'); ?></p>
        <input class="bg-[#F6F6F6] border-0 w-full text-[#333333]" type="date" id="end" name="trip-end"
          value="<?= date('Y-m-d', strtotime('+7 days')) ?>" min="<?= date('Y-m-d', strtotime('+1 days')) ?>"
          max="2050-12-31" />
      </div>
    </div>
    <div class="items-search-bar md:max-w-[230px] rounded-[40px] tax-destination bg-[#F6F6F6] px-[30px] py-[5px]">
      <p class="font-arial text-[14px] font-[700] m-0 p-0"><?= __('Voyageurs', 'fdhpa17'); ?></p>
      <select class="bg-[#F6F6F6] border-0 w-full" name="tax-destination" id="#">
        <option class="bg-none text-[#333333]">Ajoutez des voyageurs</option>
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
      class="items-search-bar max-w-[230px] flex flex-row items-center rounded-[40px] tax-destination max-[900px]:px-[15px] px-[30px] py-[5px]">
      <input type="checkbox" name="online-reservation" id="" />
      <p class="font-arial max-md:text-center text-white md:text-[#757575] text-[14px] font-[400] m-0 p-0">
        <?= __('Réservable sur Campings.online', 'fdhpa17'); ?>
      </p>
    </div>
    <div class="items-search-bar md:max-w-[230px] flex flex-row items-center rounded-[40px] tax-destination  py-[5px]">
      <input type="submit" name="online-reservation" id="" value="" class="bg-orange border-0 w-[56px] h-[56px]
      rounded-[50%] bg-zoom bg-[20px_20px] bg-no-repeat
      cursor-pointer hover:bg-green transition-all
      max-md:w-full max-md:rounded-[10px]
      max-md:text-[16px]
      max-md:text-white
      max-md:bg-orange " />
    </div>
  </form>
</div>

