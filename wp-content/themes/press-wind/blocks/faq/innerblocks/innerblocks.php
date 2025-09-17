<?php
$template = $args['template'];
$allowedBlocks = $args['allowedBlocks'];
?>
<InnerBlocks
  class="animateFade fadeOutAnimation [&_p]:m-0 max-md:text-center [&_p]:text-[20px] md:[&_p]:text-[32px] [&_p]:font-[400] [&_p]:text-primary [&_p]:text-center [&_p]:font-arial
  [&_h2]:text-black [&_h2]:mt-0 [&_h2]:mb-0 [&_h2]:text-center
  max-md:[&_h2]:text-[24px] [&_h2]:text-[36px] [&_h2]:font-[700] [&_h2]:font-ivymode
     [&_h2_sub]:mt-0 [&_h2_sub]:mb-0 [&_h2_sub]:text-center
  max-md:[&_h2_sub]:text-[24px] [&_h2_sub]:text-[36px] [&_h2_sub]:font-[400] [&_h2_sub]:font-arial
  [&_h2_sub]:block [&_h2_sub]:text-green
  "
  template="<?= htmlspecialchars(json_encode($template)); ?>"
  allowedBlocks="<?= htmlspecialchars(json_encode($allowedBlocks)); ?>" />