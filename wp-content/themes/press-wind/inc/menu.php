<?php 

add_action('after_setup_theme', function () {
  register_nav_menus([
    'primary' => __('Menu principal', 'your-theme'),
  ]);
});

// functions.php

class CM17_Menu_Walker extends Walker_Nav_Menu {
  protected $text_color_class;
  protected $parent_title_for_mobile = '';

  public function __construct($text_color_class = '') {
    $this->text_color_class = trim($text_color_class);
  }

  // <ul> d'un niveau (submenu / submenu-child)
  public function start_lvl( &$output, $depth = 0, $args = null ) {
    $indent = str_repeat("\t", $depth);
    if ($depth === 0) {
      // Niveau 1 => .submenu + header mobile "Retour" + titre parent
      $output .= "\n$indent<ul class=\"submenu  absolute leading-[20px] \">\n";
      $output .= "$indent\t<li class=\"md:hidden flex flex-row flex-wrap gap-[15px] before:!right-[initial] before:rotate-180\">";
      $output .= "<a href=\"#\" class=\"button-back-mobile pl-[20px] font-[400]\">Retour</a>";
      $output .= "<div class=\"text-green\">" . esc_html($this->parent_title_for_mobile) . "</div>";
      $output .= "</li>\n";
    } else {
      // Niveau 2+ => .submenu-child
      $output .= "\n$indent<ul class=\"submenu-child " . ($depth === 1 ? "" : "") . "\">\n";
    }
  }

  public function end_lvl( &$output, $depth = 0, $args = null ) {
    $indent = str_repeat("\t", $depth);
    $output .= "$indent</ul>\n";
  }

  // Chaque <li>
  public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
    $indent = ($depth) ? str_repeat("\t", $depth) : '';

    $has_children = in_array('menu-item-has-children', $item->classes ?? [], true);
    $is_current   = in_array('current-menu-item', $item->classes ?? [], true) || in_array('current-menu-ancestor', $item->classes ?? [], true);

    // Classes <li> top-level (deux variantes selon présence d’enfants)
    $li_classes_top_with_children = "relative max-[1080px]:leading-[20px] leading-[90px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[2px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid
            max-md:before:content-[''] max-md:before:w-[15px] max-md:before:h-[15px] max-md:before:bg-arrow-menu-mobile
            max-md:before:absolute max-md:before:bg-contain max-md:before:bg-no-repeat
            max-md:before:right-[20px]";

    $li_classes_top_no_children = " relative max-[1080px]:leading-[20px] leading-[90px] after:w-0 after:transition-all after:duration-300 after:content-[''] hover:after:absolute hover:after:left-0 hover:after:-bottom-[4px] hover:after:content-[''] hover:after:w-[100%] hover:after:h-[3px] hover:after:bg-orange
            max-md:leading-[initial] max-md:py-[15px] max-md:w-full max-md:border-t-0 max-md:border-l-0 max-md:border-r-0 max-md:border-b max-md:border-black/10 max-md:border-solid";

    // Classes <a> top-level
    $a_classes_top = trim(($this->text_color_class ? $this->text_color_class : '') . " max-md:font-[700] max-[1140px]:text-[13px] text-[16px] relative block font-arial hover:no-underline after:content-[''] max-[1200px]:after:hidden after:bg-arrow-menu after:w-[12px] after:h-[7px] after:block after:absolute after:-right-[20px] after:top-[45%] ");

    // Profondeur 0 : on retient le titre pour l’entête mobile du sous-menu
    if ($depth === 0) {
      $this->parent_title_for_mobile = $item->title;
    }

    // Classes <li> par niveau 
    if ($depth === 0) {
      $li_class = $has_children ? $li_classes_top_with_children : $li_classes_top_no_children;
    } elseif ($depth === 1) {
      // Items du 1er sous-menu
      $li_class = " md:active [&.active]:text-green max-md:[&.active]:text-black ";
      // Si l’item courant => on peut ajouter la classe "active"
      if ($is_current) {
        $li_class .= " active";
      }
    } else {
      // Items du 2e sous-menu
      $li_class = "[&.active]:text-green ";
    }

    $output .= $indent . '<li class="' . $li_class . '">';

    // Lien
    $atts = [];
    $atts['href'] = !empty($item->url) ? $item->url : '#';
    $atts_str = '';
    foreach ($atts as $attr => $value) {
      if (!empty($value)) {
        $atts_str .= ' ' . $attr . '="' . esc_attr($value) . '"';
      }
    }

    // Classes <a> par niveau
    $a_class_final = ($depth === 0) ? $a_classes_top : " no-underline";

    $title = $item->title;

    $output .= '<a class="' . $a_class_final . '"' . $atts_str . '>' . esc_html($title) . '</a>';
  }

  public function end_el( &$output, $item, $depth = 0, $args = null ) {
    $output .= "</li>\n";
  }
}
