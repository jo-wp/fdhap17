wp eval '
$tz = wp_timezone();
$now = new DateTime("now", $tz);
$start = (clone $now)->setTime(0,0,0)->modify("-1 day");
$end   = (clone $now)->setTime(23,59,59);

$args = [
  "post_type"       => "attachment",
  "post_status"     => "inherit",
  "post_mime_type"  => "image",
  "fields"          => "ids",
  "posts_per_page"  => 500,
  "orderby"         => "ID",
  "order"           => "ASC",
  "date_query"      => [[
    "column"    => "post_date",
    "after"     => $start->format("Y-m-d H:i:s"),
    "before"    => $end->format("Y-m-d H:i:s"),
    "inclusive" => true,
  ]],
  "paged" => 1,
];

$deleted = 0;
do {
  $q = new WP_Query($args);
  foreach ($q->posts as $id) {
    // Supprime l\'attachment et le fichier physique
    if ( wp_delete_attachment($id, true) ) {
      echo "deleted $id" . PHP_EOL;
      $deleted++;
    }
  }
  $args["paged"]++;
  wp_reset_postdata();
} while ($q->have_posts());

echo "DELETED=$deleted" . PHP_EOL;
'
