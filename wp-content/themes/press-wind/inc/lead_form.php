<?php

// Shortcode : [lead_form_cm]
function rp_lead_form_cm_shortcode($atts)
{
  $atts = shortcode_atts([
    'button_label' => __('JE VALIDE', 'fdhpa17'),
    'consent_text' => __('J\'accepte de recevoir les actualités et offres exclusives de Camping Atlantique. *', 'fdhpa17'),
    'email_placeholder' => __('Entrer votre adresse mail', 'fdhpa17'),
    'email_label' => __('Email', 'fdhpa17'),
    'profil_label' => __('Profil', 'fdhpa17'),
  ], $atts, 'lead_form_cm');

  wp_enqueue_script(
    'createsend-form-logic',
    'https://js.createsend1.com/javascript/copypastesubscribeformlogic.js',
    [],
    null,
    true
  );

  ob_start(); ?>
  <div class="rp-lead-form">
    <div>
      <div>
        <p></p>
      </div>
      <form class="js-cm-form" id="subForm" action="https://www.createsend.com/t/subscribeerror?description="
        method="post"
        data-id="A61C50BEC994754B1D79C5819EC1255C06C50CF5C69C43EC2939C1C2B3E4194C320E85FEF56BC9E42C992C1D59E10516A191697525C0AADD4EAB1DE9A5F39C8D">
        <div class="flex flex-col md:flex-row flex-wrap gap-[20px] md:gap-[100px]">
          <div class="flex-1">
            <h2 class="text-orange  max-md:text-center text-[24px] md:text-[36px] font-ivymode"><?= __('Petit guide du campeur', 'fdhpa17') ?></h2>
            <p class="text-black font-arial text-[14px] md:text-[16px]">
              <?= __('Conseils et astuces camping, activités de pleine nature, itinéraires et lieux à visiter de La Rochelle à Royan, en passant par la Saintonge et les îles : un guide réalisé et offert par la Fédération des Campings de Charente-Maritime à tous les amoureux de camping.', 'fdhpa17') ?>
            </p>
            <label for="fieldEmail" class="hidden"><?php echo $atts['email_label']; ?></label>
            <input autocomplete="Email" class="js-cm-email-input qa-input-email w-full mt-[20px] md:mt-[50px] !pr-[0px]" id="fieldEmail" maxlength="200"
              name="cm-tlutvj-tlutvj" placeholder="<?php echo $atts['email_placeholder']; ?>" required type="email">
          </div>
          <div class="flex-1">
            <fieldset class="m-0 p-0">
              <label>Vous êtes : <sup class=" text-red-600 ">*</sup></label>
              <div>
                <input id="6897425" name="cm-fo-dlluljl" type="checkbox" value="6897425">
                <label for="6897425">Camping-cariste</label>
              </div>
              <div>
                <input id="6897426" name="cm-fo-dlluljl" type="checkbox" value="6897426">
                <label for="6897426">Entre amis</label>
              </div>
              <div>
                <input id="6897427" name="cm-fo-dlluljl" type="checkbox" value="6897427">
                <label for="6897427">En couple</label>
              </div>
              <div>
                <input id="6897428" name="cm-fo-dlluljl" type="checkbox" value="6897428">
                <label for="6897428">En famille</label>
              </div>
            </fieldset>

            <div class="mt-[20px] md:mt-[50px]">
              <div>
                <div>
                  <input id="cm-privacy-consent" name="cm-privacy-consent" required type="checkbox">
                  <label for="cm-privacy-consent"><?php echo $atts['consent_text']; ?></label>
                </div>
                <input id="cm-privacy-consent-hidden" name="cm-privacy-consent-hidden" type="hidden" value="true">
              </div>
            </div>

          </div>
        </div>
        <div class="mt-[20px] md:mt-[50px] flex flex-row">
          <div class="md:flex-1"></div>
          <div class="md:flex-1 max-md:w-full max-md:text-center">
            <button type="submit"><?php echo $atts['button_label']; ?></button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php
  return ob_get_clean();
}
add_shortcode('lead_form_cm', 'rp_lead_form_cm_shortcode');
