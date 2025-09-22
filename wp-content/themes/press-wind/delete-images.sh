wp eval '
$tz = wp_timezone();
$now = new DateTime("now", $tz);
$start = (clone $now)->setTime(0,0,0)->modify("-1 day"); // hier 00:00
$end   = $now;

$args = [
  "post_type"                => "attachment",
  "post_status"              => "any",
  "post_mime_type"           => "image",
  "fields"                   => "ids",
  "posts_per_page"           => 500,
  "orderby"                  => "ID",
  "order"                    => "ASC",
  "date_query"               => [[
    "column"    => "post_date",
    "after"     => $start->format("Y-m-d H:i:s"),
    "before"    => $end->format("Y-m-d H:i:s"),
    "inclusive" => true,
  ]],
  "paged"                    => 1,
  "no_found_rows"            => true,
  "update_post_term_cache"   => false,
  "update_post_meta_cache"   => false,
  "cache_results"            => false,
];

$deleted = 0;
do {
  $q = new WP_Query($args);
  $count = count($q->posts);

  foreach ($q->posts as $id) {
    if ( wp_delete_attachment($id, true) ) {
      echo "deleted $id" . PHP_EOL;
      $deleted++;
    } else {
      echo "failed  $id" . PHP_EOL;
    }
  }

  $args["paged"]++;
  wp_reset_postdata();
} while ($count > 0);

echo "DELETED=$deleted" . PHP_EOL;
'
