<?php
$destinations = get_terms([
  'taxonomy' => 'destination',   // le slug de ta taxonomie
  'hide_empty' => false,           // true = exclut les termes sans campings
]);
?>
<div class="">
  <form action="/tous-les-campings-de-charente-maritime" method="GET"
    class="bg-white flex flex-row justify-around max-w-[1300px] mx-auto rounded-[40px] p-[6px] gap-[40px]">
    <div class="items-search-bar max-w-[230px] rounded-[40px] tax-destination bg-[#F6F6F6] px-[30px] py-[5px]">
      <p class="font-arial text-[14px] font-[700] m-0 p-0"><?= __('Destination', 'fdhpa17'); ?></p>
      <?php if (!is_wp_error($destinations) && !empty($destinations)): ?>
        <select class="bg-[#F6F6F6] border-0 w-full" name="tax-destination" id="#">
          <?php foreach ($destinations as $destination): ?>
            <option value="<?= esc_attr($destination->slug); ?>">
              <?= esc_html($destination->name); ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>
    <div class="items-search-bar tax-destination flex flex-row">
      <div class="max-w-[230px] rounded-l-[40px]  bg-[#F6F6F6] px-[30px] py-[5px]">
        <p class="font-arial text-[14px] font-[700] m-0 p-0"><?= __('Arrivée', 'fdhpa17'); ?></p>
        <input class="bg-[#F6F6F6] border-0 w-full text-[#333333]" type="date" id="start" name="trip-start"
          value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" max="2050-12-31" />
      </div>
      <div class="max-w-[230px] rounded-r-[40px]  bg-[#F6F6F6] px-[30px] py-[5px]">
        <p class="font-arial text-[14px] font-[700] m-0 p-0"><?= __('Départ', 'fdhpa17'); ?></p>
        <input class="bg-[#F6F6F6] border-0 w-full text-[#333333]" type="date" id="end" name="trip-end"
          value="<?= date('Y-m-d', strtotime('+7 days')) ?>" min="<?= date('Y-m-d', strtotime('+1 days')) ?>"
          max="2050-12-31" />
      </div>
    </div>
    <div class="items-search-bar max-w-[230px] rounded-[40px] tax-destination bg-[#F6F6F6] px-[30px] py-[5px]">
      <p class="font-arial text-[14px] font-[700] m-0 p-0"><?= __('Voyageurs', 'fdhpa17'); ?></p>
      <select class="bg-[#F6F6F6] border-0 w-full" name="tax-destination" id="#">
        <option class="bg-none text-[#333333]">Ajoutez des voyageurs</option>
      </select>
    </div>
    <div
      class="items-search-bar max-w-[230px] flex flex-row items-center rounded-[40px] tax-destination px-[30px] py-[5px]">
      <input type="checkbox" name="online-reservation" id="" />
      <p class="font-arial text-[#757575] text-[14px] font-[400] m-0 p-0">
        <?= __('Réservable sur Campings.online', 'fdhpa17'); ?>
      </p>
    </div>
    <div class="items-search-bar max-w-[230px] flex flex-row items-center rounded-[40px] tax-destination  py-[5px]">
      <input type="submit" name="online-reservation" id="" value="" class="bg-orange border-0 w-[56px] h-[56px]
      rounded-[50%] bg-zoom bg-[20px_20px] bg-no-repeat" />
    </div>
  </form>
</div>