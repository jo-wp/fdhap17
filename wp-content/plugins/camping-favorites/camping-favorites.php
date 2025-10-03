<?php
/**
 * Plugin Name: Camping Favorites (LocalStorage)
 * Description: Ajout/suppression/affichage de favoris (CPT "camping") stockés dans localStorage (persistant côté navigateur).
 * Version: 1.0.0
 * Author: BeeCom (Jordan)
 * Text Domain: camping-favorites
 */

if (!defined('ABSPATH'))
  exit;

if (!class_exists('Camping_Favorites')) {

  class Camping_Favorites
  {

    const VERSION = '1.0.0';
    const LS_KEY = 'camping_favs'; // clé localStorage

    public function __construct()
    {
      add_action('init', [$this, 'register_shortcodes']);
      add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
      // Optionnel : injecter un bouton automatiquement sur les single "camping"
      add_filter('the_content', [$this, 'maybe_append_button_on_single'], 20);
      // Exposer une fonction globale pour thème/plugins
      add_action('after_setup_theme', [$this, 'register_template_tag']);
      add_action('rest_api_init', [$this, 'register_rest_fields']);

    }

    /**
     * Enqueue JS + expose quelques réglages au front.
     */
    public function enqueue_assets()
    {
      // Script principal (inline pour simplicité de déploiement)
      $handle = 'camping-favorites';
      wp_register_script($handle, '', [], self::VERSION, true);

      $data = [
        'lsKey' => self::LS_KEY,
        'cpt' => 'camping',
        'restUrl' => esc_url_raw(get_rest_url(null, 'wp/v2/camping')),
        'assets' => [
          'heart' => get_theme_file_uri('/assets/media/heart.png'),
          'heartLiked' => get_theme_file_uri('/assets/media/heart-liked.png'),
          'star' => get_theme_file_uri('/assets/media/star.svg'),
          'marker' => get_theme_file_uri('/assets/media/marker-v2.svg'),
        ],
        'texts' => [
          'add' => __('Ajouter aux favoris', 'camping-favorites'),
          'remove' => __('Retirer des favoris', 'camping-favorites'),
          'added' => __('Ajouté', 'camping-favorites'),
          'empty' => __('Aucun favori pour le moment.', 'camping-favorites'),
          'listTitle' => __('Mes campings favoris', 'camping-favorites'),
          'removeItem' => __('Retirer', 'camping-favorites'),
        ],
      ];

      wp_add_inline_script($handle, 'window.CAMPING_FAVS_DATA = ' . wp_json_encode($data) . ';', 'before');
      wp_add_inline_script($handle, $this->get_inline_js());
      wp_enqueue_script($handle);

$heart       = get_stylesheet_directory_uri() . '/assets/media/heart.png';
$heartLiked  = get_stylesheet_directory_uri() . '/assets/media/heart-liked.png';

$css = <<<CSS
.camping-fav-btn.is-active img {
  content: url('{$heartLiked}');
}
.camping-fav-btn:not(.is-active) img {
  content: url('{$heart}');
}
.camping-favs-list{list-style:none;padding:0;margin:.5rem 0 0}
.camping-favs-list li{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.5rem 0;border-bottom:1px solid #eee}
.camping-favs-empty{opacity:.75}
.camping-favs-wrap h3{margin:0 0 .5rem}
button.camping-fav-remove{border:1px solid #ddd;border-radius:4px;background:#fff;padding:.25rem .5rem;cursor:pointer}
CSS;
      wp_register_style('camping-favorites', false, [], self::VERSION);
      wp_add_inline_style('camping-favorites', $css);
      wp_enqueue_style('camping-favorites');
    }

    /**
     * Shortcodes.
     */
    public function register_shortcodes()
    {
      add_shortcode('camping_favorites', [$this, 'shortcode_list']);
      add_shortcode('camping_fav_button', [$this, 'shortcode_button']);
    }

    /**
     * [camping_favorites] — conteneur que le JS remplira via REST en lisant localStorage.
     */
    public function shortcode_list($atts = [])
    {
      $atts = shortcode_atts([
        'title' => '',
      ], $atts, 'camping_favorites'); 

      ob_start(); ?>
      <div class="camping-favs-wrap container-huge text-center" data-camping-favs>
        <h2 class="text-center mb-[50px]"><?php echo esc_html($atts['title'] ?: __('Mes campings favoris', 'camping-favorites')); ?>
          </h3>
          <div class="camping-favs-output">
            <p class="camping-favs-empty"><?php esc_html_e('Chargement...', 'camping-favorites'); ?></p>
          </div>
      </div>
      <?php
      return ob_get_clean();
    }

    public function register_rest_fields()
    {
      // Meta utiles
      register_rest_field('camping', 'cf_meta', [
        'get_callback' => function ($obj) {
          $id = (int) $obj['id'];
          return [
            'latitude' => get_post_meta($id, 'latitude', true),
            'longitude' => get_post_meta($id, 'longitude', true),
            'price_mini' => get_post_meta($id, 'price_mini', true),
            'commune' => get_post_meta($id, 'commune', true),
            'code_postal' => get_post_meta($id, 'code_postal', true),
          ];
        },
        'schema' => null,
      ]);

      // Parent "destination"
      register_rest_field('camping', 'cf_destination_parent', [
        'get_callback' => function ($obj) {
          $id = (int) $obj['id'];
          $terms = get_the_terms($id, 'destination');
          if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $t) {
              if ($t->parent) {
                $parent = get_term($t->parent);
                if ($parent && !is_wp_error($parent)) {
                  return $parent->name;
                }
              }
            }
          }
          return '';
        },
        'schema' => null,
      ]);

      // Étoiles (1 à 4)
      register_rest_field('camping', 'cf_stars', [
        'get_callback' => function ($obj) {
          $id = (int) $obj['id'];
          $allowed_slugs = ['1-etoile', '2-etoiles', '3-etoiles', '4-etoiles'];
          $terms = get_the_terms($id, 'etoile');
          if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
              if (in_array($term->slug, $allowed_slugs, true)) {
                return (int) preg_replace('/\D+/', '', $term->name); // "3 étoiles" -> 3
              }
            }
          }
          return 0;
        },
        'schema' => null,
      ]);

      // Vignette (medium_large si possible)
      register_rest_field('camping', 'cf_thumb', [
        'get_callback' => function ($obj) {
          $id = (int) $obj['id'];
          $url = get_the_post_thumbnail_url($id, 'medium_large');
          if (!$url)
            $url = get_the_post_thumbnail_url($id, 'medium');
          return $url ?: '';
        },
        'schema' => null,
      ]);
    }


    /**
     * [camping_fav_button id="123" add="Ajouter..." remove="Retirer..."]
     */
    public function shortcode_button($atts = [])
    {
      $atts = shortcode_atts([
        'id' => 0,
        'add' => __('Ajouter aux favoris', 'camping-favorites'),
        'remove' => __('Retirer des favoris', 'camping-favorites'),
      ], $atts, 'camping_fav_button');

      $id = absint($atts['id']);
      if (!$id)
        return '';

      return $this->render_button($id, $atts['add'], $atts['remove']);
    }

    /**
     * Méthode de rendu du bouton (réutilisable partout).
     */
    public function render_button($post_id, $label_add = null, $label_remove = null)
    {
      $label_add = $label_add ?: __('Ajouter aux favoris', 'camping-favorites');
      $label_remove = $label_remove ?: __('Retirer des favoris', 'camping-favorites');

      $is_single = is_singular('camping') && get_the_ID() === $post_id;

      ob_start(); ?>
      <button type="button" class="camping-fav-btn" data-camping-id="<?php echo esc_attr($post_id); ?>"
        data-label-add="<?php echo esc_attr($label_add); ?>" data-label-remove="<?php echo esc_attr($label_remove); ?>"
        aria-pressed="false">
        <span class="txt"><?php echo esc_html($label_add); ?></span>
      </button>
      <?php
      return ob_get_clean();
    }

    /**
     * Injecter automatiquement un bouton en bas du contenu des single "camping".
     */
    public function maybe_append_button_on_single($content)
    {
      if (is_singular('camping') && in_the_loop() && is_main_query()) {
        $id = get_the_ID();
        $content .= '<div class="camping-fav-auto">' . $this->render_button($id) . '</div>';
      }
      return $content;
    }

    /**
     * Fournir une fonction globale simple : camping_favorites_button( $id, $label_add, $label_remove )
     */
    public function register_template_tag()
    {
      if (!function_exists('camping_favorites_button')) {
        function camping_favorites_button($id = null, $label_add = null, $label_remove = null)
        {
          $id = $id ? absint($id) : get_the_ID();
          if (!$id)
            return '';
          $plugin = Camping_Favorites::instance();
          echo $plugin->render_button($id, $label_add, $label_remove); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
      }
    }

    /**
     * Singleton pour accéder à l'instance depuis la fonction template tag.
     */
    private static $instance = null;
    public static function instance()
    {
      if (null === self::$instance)
        self::$instance = new self();
      return self::$instance;
    }

    /**
     * JS embarqué.
     */
    private function get_inline_js()
    {
      return <<<JS
(function(){
  var D = window.CAMPING_FAVS_DATA || {};
  var LS_KEY = D.lsKey || 'camping_favs';

  function nowISO(){ return new Date().toISOString(); }

  function read(){
    try{
      var raw = localStorage.getItem(LS_KEY);
      if(!raw) return [];
      var arr = JSON.parse(raw);
      return Array.isArray(arr) ? arr : [];
    }catch(e){ return []; }
  }
  function write(arr){
    try{ localStorage.setItem(LS_KEY, JSON.stringify(arr)); }catch(e){}
  }
  function uniqById(items){
    var map = {}; var out=[];
    items.forEach(function(it){ if(!map[it.id]){ map[it.id]=1; out.push(it); }});
    return out;
  }

  var API = {
    list: function(){ return read(); },
    has: function(id){ id = parseInt(id,10); return read().some(function(it){ return it.id===id; }); },
    add: function(id){
      id = parseInt(id,10);
      if(!id) return;
      var arr = read();
      if(!arr.some(function(it){return it.id===id;})){
        arr.push({ id:id, added_at: nowISO() });
        write( uniqById(arr) );
      }
    },
    remove: function(id){
      id = parseInt(id,10);
      var arr = read().filter(function(it){ return it.id!==id; });
      write(arr);
    },
    toggle: function(id){
      if(API.has(id)) API.remove(id); else API.add(id);
    }
  };

  // Expose global pour réutilisation partout
  window.CampingFavs = API;

  // Mettre à jour l'état visuel des boutons
 function syncButtonState(btn){
  var id = parseInt(btn.getAttribute('data-camping-id'),10);
  var addLabel    = btn.getAttribute('data-label-add') || (D.texts && D.texts.add) || 'Ajouter aux favoris';
  var removeLabel = btn.getAttribute('data-label-remove') || (D.texts && D.texts.remove) || 'Retirer des favoris';
  var txt = btn.querySelector('.txt') || btn;
  var img = btn.querySelector('img');

  var normalIcon = btn.getAttribute('data-icon') || (D.assets && D.assets.heart) || '';
  var activeIcon = btn.getAttribute('data-icon-active') || (D.assets && D.assets.heartLiked) || normalIcon;

  if(API.has(id)){
    btn.classList.add('is-active');
    btn.setAttribute('aria-pressed','true');
    if (txt) txt.textContent = removeLabel;
    if (img && activeIcon) img.src = activeIcon;
  } else {
    btn.classList.remove('is-active');
    btn.setAttribute('aria-pressed','false');
    if (txt) txt.textContent = addLabel;
    if (img && normalIcon) img.src = normalIcon;
  }
}


  function bindButtons(context){
    (context || document).querySelectorAll('a.camping-fav-btn[data-camping-id]').forEach(function(btn){
      if(btn.__campFavBound) return;
      btn.__campFavBound = true;
      syncButtonState(btn);
      btn.addEventListener('click', function(e){
        e.preventDefault();
        console.log('unblock');
        var id = parseInt(btn.getAttribute('data-camping-id'),10);
        API.toggle(id);
        syncButtonState(btn);
        // rafraîchir la liste si présente
        renderList();
      });
    });
  }

  // Rendu de la liste des favoris dans [camping_favorites]
 function renderList(){
  var wrap = document.querySelector('[data-camping-favs]');
  if(!wrap) return;
  var out = wrap.querySelector('.camping-favs-output');
  if(!out) return;

  var items = API.list();
  if(!items.length){
    out.innerHTML = '<p class="camping-favs-empty">'+ ((D.texts && D.texts.empty) || 'Aucun favori pour le moment.') +'</p>';
    return;
  }

  var ids = items.map(function(it){ return it.id; }).join(',');
  var url = (D.restUrl || '') + '?include=' + encodeURIComponent(ids) + '&per_page=100&_embed=1';

  fetch(url, { credentials: 'same-origin' })
    .then(function(r){ return r.ok ? r.json() : []; })
    .then(function(posts){
      var byId = {}; posts.forEach(function(p){ byId[p.id] = p; });

      function esc(s){
        return String(s == null ? '' : s)
          .replace(/&/g,'&amp;').replace(/</g,'&lt;')
          .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
      }

      function starIcons(n){
        var src = (D.assets && D.assets.star) || '';
        var html=''; for(var i=0;i<(n||0);i++){
          html += '<img class="max-w-[13px]" src="'+esc(src)+'" alt="Étoile" />';
        }
        return html;
      }

      function thumbUrl(p){
        if (p.cf_thumb) return p.cf_thumb;
        // fallback via _embedded
        try {
          var fm = p._embedded['wp:featuredmedia'][0];
          return (fm.media_details.sizes.medium_large && fm.media_details.sizes.medium_large.source_url)
              || fm.source_url || '';
        } catch(e){ return ''; }
      }

      function renderCard(p, addedAt){
        var meta   = p.cf_meta || {};
        var thumb  = thumbUrl(p);
        var prix   = meta.price_mini;
        var stars  = p.cf_stars || 0;
        var destP  = p.cf_destination_parent || '';
        var heart  = (D.assets && D.assets.heart) || '';
        var heartA = (D.assets && D.assets.heartLiked) || heart;

        return ''+
        '<div class="md:flex-1 bloc-camping-associated__items__item js-camping-item " '+
             'data-lat="'+esc(meta.latitude||'')+'" '+
             'data-lng="'+esc(meta.longitude||'')+'" '+
             'data-title="'+esc(p.title && p.title.rendered ? p.title.rendered.replace(/<[^>]*>/g,'') : '')+'" '+
             'data-url="'+esc(p.link)+'">'+

          '<div class="image-featured min-h-[150px] max-h-[290px] min-w-auto bg-center bg-cover rounded-[10px] max-md:min-w-[250px]" '+
               'style="background-image:url(\''+esc(thumb)+'\');">'+
            '<div class="flex flex-row justify-between items-center py-[12px] px-[14px]">'+
              (prix ? '<span class="bg-green text-white font-arial text-[14px] px-[20px] py-[8px] rounded-full">À partir de '+esc(prix)+' €</span>' : '')+
              '<a href="#" class="camping-fav-btn" '+
                 'data-camping-id="'+p.id+'" '+
                 'data-label-add="Ajouter aux favoris" '+
                 'data-label-remove="Retirer des favoris" '+
                 'data-icon="'+esc(heart)+'" '+
                 'data-icon-active="'+esc(heartA)+'" '+
                 'aria-pressed="false">'+
                '<img src="'+esc(heart)+'" alt="">'+
                '<span class="txt" style="display:none;">Ajouter aux favoris</span>'+
              '</a>'+
            '</div>'+
          '</div>'+

          '<div class="informations mt-[20px]">'+
            '<h3 class="font-arial text-[22px] font-[700] text-black m-0 mb-[5px]">'+
              esc(p.title && p.title.rendered ? p.title.rendered.replace(/<[^>]*>/g,'') : '')+
            '</h3>'+
            '<div class="stars">'+ starIcons(stars) +'</div>'+
            '<div class="location flex flex-row justify-between mb-[30px] items-center">'+
              '<div class="flex flex-row gap-[8px]">'+
                (D.assets && D.assets.marker ? '<img src="'+esc(D.assets.marker)+'" alt="icon localisation">' : '')+
                '<p class="text-[#000] font-arial text-[14px]">'+
                  esc(meta.commune||'')+' - '+esc(meta.code_postal||'')+
                '</p>'+
              '</div>'+
              '<div><p class="text-black font-arial text-[12px]">'+esc(destP)+'</p></div>'+
            '</div>'+
            '<div>'+
              '<a href="'+esc(p.link)+'" class="button button--bg-orange !text-[14px] !no-underline little-padding-x">Voir le camping</a>'+
            '</div>'+
          '</div>'+
        '</div>';
      }

      // Conserver l'ordre d'ajout
      var html = '<div class="grid gap-[30px] grid-cols-1 md:grid-cols-4">';
      items.forEach(function(it){
        var p = byId[it.id];
        if (p) html += renderCard(p, it.added_at);
      });
      html += '</div>';

      out.innerHTML = html;

      // binder les coeurs
      bindButtons(out);
    })
    .catch(function(){
      out.innerHTML = '<p class="camping-favs-empty">Impossible de charger vos favoris.</p>';
    });
}


  // Init
  document.addEventListener('DOMContentLoaded', function(){
    bindButtons(document);
    renderList();

    // MutationObserver pour capter du contenu chargé dynamiquement
    var obs = new MutationObserver(function(muts){
      bindButtons(document);
    });
    obs.observe(document.documentElement, { childList:true, subtree:true });
  });
})();
JS;
    }

  }

  // Bootstrap
  Camping_Favorites::instance();
}
