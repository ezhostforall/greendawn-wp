#!/usr/bin/env bash
# Set up a disposable integration-test WordPress. Run from the repository, not inside a devcontainer.
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/../.."
compose=(docker compose -f tests/integration/compose.yml)

"${compose[@]}" up -d --wait

container_id=$("${compose[@]}" ps -q wordpress)
if [[ -z "$container_id" ]]; then
  echo "WordPress test container did not start." >&2
  exit 1
fi

# Install WP-CLI in the disposable container only. No Dockerfile or root Codespace is required.
cli_file=$(mktemp)
trap 'rm -f "$cli_file"' EXIT
curl --fail --location --silent --show-error --retry 3 \
  https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o "$cli_file"
docker cp "$cli_file" "$container_id:/tmp/wp-cli.phar"

wp() {
  "${compose[@]}" exec -T wordpress php /tmp/wp-cli.phar --allow-root --path=/var/www/html "$@"
}

# The official WordPress PHP/Apache image has the mysqli PHP extension, but does
# not necessarily include the mysqlcheck/mysql client binary required by WP-CLI's
# "wp db check". Check the *actual PHP connection* instead of a missing CLI utility.
check_wordpress_database() {
  "${compose[@]}" exec -T wordpress php -r '
    if (!is_file("/var/www/html/wp-config.php")) {
      fwrite(STDERR, "WordPress wp-config.php has not been generated yet.\n");
      exit(1);
    }
    if (!extension_loaded("mysqli")) {
      fwrite(STDERR, "WordPress PHP image is missing mysqli.\n");
      exit(1);
    }
    $address = getenv("WORDPRESS_DB_HOST") ?: "db:3306";
    $parts = explode(":", $address, 2);
    $host = $parts[0];
    $port = isset($parts[1]) ? (int) $parts[1] : 3306;
    mysqli_report(MYSQLI_REPORT_OFF);
    $db = mysqli_init();
    $db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 3);
    if (!@$db->real_connect(
      $host,
      getenv("WORDPRESS_DB_USER"),
      getenv("WORDPRESS_DB_PASSWORD"),
      getenv("WORDPRESS_DB_NAME"),
      $port
    )) {
      fwrite(STDERR, "WordPress database connection failed: " . mysqli_connect_error() . PHP_EOL);
      exit(1);
    }
    if (!$db->query("SELECT 1")) {
      fwrite(STDERR, "WordPress database query failed: " . $db->error . PHP_EOL);
      exit(1);
    }
    $db->close();
  '
}

ready=0
for i in $(seq 1 15); do
  if db_output=$(check_wordpress_database 2>&1); then
    ready=1
    break
  fi
  # Show errors immediately and periodically: no more silent 3-minute timeouts.
  if (( i == 1 || i % 5 == 0 )); then
    echo "WordPress database connection attempt $i/15 failed: $db_output" >&2
  fi
  sleep 2
done
if [[ "$ready" -ne 1 ]]; then
  echo "Disposable WordPress database connection could not be established." >&2
  echo "The test stack uses shared network namespaces; check the WordPress PHP configuration and MariaDB status." >&2
  "${compose[@]}" ps >&2 || true
  "${compose[@]}" logs --tail=35 wordpress db >&2 || true
  exit 1
fi

# Verify the WP-CLI PHAR itself separately, so CLI errors are never mistaken for DB errors.
wp cli info
echo "WordPress database connection and WP-CLI are ready."

if ! wp core is-installed >/dev/null 2>&1; then
  wp core install --url=http://localhost:8080 --title="Greendawn Test" \
    --admin_user=admin --admin_password=local-test-only \
    --admin_email=admin@example.test --skip-email
fi

wp theme activate greendawn
wp plugin activate greendawn-core
wp plugin activate ezhost-updater
wp plugin install contact-form-7 --version=6.2.4.1 --activate
wp eval '$previous = get_option( "greendawn_core_callback_form", 0 ); if ( ! $previous ) { update_option( "greendawn_core_callback_enabled", true ); } $id = \Greendawn_Core\Modules\Lead_Capture::provision(); if ( ! $id || ( ! $previous && get_option( "greendawn_core_callback_enabled" ) ) || $id !== \Greendawn_Core\Modules\Lead_Capture::provision() ) { WP_CLI::error( "Callback provisioning must be idempotent and new forms must remain disabled for review." ); } update_option( "greendawn_core_callback_enabled", true );'
docker cp tests/fixtures/lead-capture.php "$container_id:/var/www/html/wp-content/plugins/lead-capture-test-fixture.php"
wp plugin activate lead-capture-test-fixture
wp eval 'if ( ! has_filter( "update_plugins_updates.ezhostforall.invalid" ) || ! has_filter( "update_themes_updates.ezhostforall.invalid" ) || ! has_filter( "upgrader_pre_download" ) ) { WP_CLI::error( "EZ Host updater hooks missing" ); } WP_CLI::success( "Updater registered without configuration" );'
wp rewrite structure '/%postname%/' --hard

# Core models must work before SCF is installed, and consume legacy WXR metadata.
docker cp tests/integration/content-foundation.php "$container_id:/tmp/content-foundation.php"
docker cp tests/fixtures/charger-import.xml "$container_id:/tmp/charger-import.xml"
wp eval-file /tmp/content-foundation.php native
wp plugin install wordpress-importer --activate
wp import /tmp/charger-import.xml --authors=skip
# An unrelated blog prefix must never leak into charger detail permalinks.
wp rewrite structure '/news/%postname%/' --hard
wp eval-file /tmp/content-foundation.php imported
wp plugin install secure-custom-fields --version=6.9.8 --activate
wp eval-file /tmp/content-foundation.php scf
wp plugin deactivate secure-custom-fields
wp rewrite structure '/%postname%/' --hard

create_page() {
  local slug="$1" title="$2" content="$3"
  if [[ -z "$(wp post list --post_type=page --name="$slug" --field=ID)" ]]; then
    wp post create --post_type=page --post_status=publish \
      --post_title="$title" --post_name="$slug" --post_content="$content"
  fi
}

create_page about "About" '<!-- wp:paragraph --><p>Example page content.</p><!-- /wp:paragraph -->'
create_page react-example "React example" '[greendawn_react_example label="Clicks" start="0"]'
create_page callback-home "Callback homepage" ''
callback_page_id=$(wp post list --post_type=page --name=callback-home --field=ID)
wp post meta update "$callback_page_id" _wp_page_template templates/greendawn-homepage.php
create_page callback-inner "Callback inner page" '<p>Enquiry test page.</p>'
create_page editor-parity "Editor parity" "$(cat tests/fixtures/editor-parity.html)"
create_page header-options "Header options" '<!-- wp:paragraph --><p>Optional header fixture.</p><!-- /wp:paragraph --><!-- wp:spacer {"height":"1800px"} --><div style="height:1800px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->'
create_page header-empty "No menus" '<!-- wp:paragraph --><p>No assigned menus.</p><!-- /wp:paragraph -->'
for slug in layout-primitives layout-brand-a layout-brand-b; do
  create_page "$slug" 'Layout and typography' "$(cat tests/fixtures/layout-demo.html)"
done

# This scenario plugin is never packaged or copied to the permanent staging host.
docker cp tests/fixtures/header-options.php "$container_id:/var/www/html/wp-content/plugins/header-test-fixture.php"
wp plugin activate header-test-fixture
docker cp tests/fixtures/layout-brands.php "$container_id:/var/www/html/wp-content/plugins/layout-test-fixture.php"
wp plugin activate layout-test-fixture
docker cp tests/fixtures/layout-demo.svg "$container_id:/var/www/html/layout-demo.svg"

if [[ -z "$(wp post list --post_type=post --name=hello-starter --field=ID)" ]]; then
  wp post create --post_status=publish --post_title="Hello starter" --post_name=hello-starter \
    --post_content='<!-- wp:paragraph --><p>Example post content.</p><!-- /wp:paragraph -->'
fi

if wp menu list --fields=name --format=csv | grep -qx Primary; then wp menu delete Primary; fi
wp menu create Primary
for slug in about react-example; do
  id=$(wp post list --post_type=page --name="$slug" --field=ID)
  wp menu item add-post Primary "$id"
done
services=$(wp menu item add-custom Primary Services 'http://localhost:8080/about/#services' --porcelain)
advisory=$(wp menu item add-custom Primary Advisory 'http://localhost:8080/about/#advisory' --parent-id="$services" --porcelain)
wp menu item add-custom Primary 'Service detail with a longer descriptive label' 'http://localhost:8080/about/#detail' --parent-id="$advisory"
resources=$(wp menu item add-custom Primary Resources 'http://localhost:8080/about/#resources' --porcelain)
wp menu item add-custom Primary Contact 'http://localhost:8080/about/#contact' --parent-id="$resources"
wp menu location assign Primary primary
if wp menu list --fields=name --format=csv | grep -qx Utility; then wp menu delete Utility; fi
wp menu create Utility
support=$(wp menu item add-custom Utility Support 'http://localhost:8080/about/#support' --porcelain)
wp menu item add-custom Utility 'Utility child omitted' 'http://localhost:8080/about/#utility-child' --parent-id="$support"
wp menu location assign Utility utility
# Exercise real service routes and native attachment metadata, without requiring SCF.
wp option update greendawn_core_services_enabled 1
create_page services "Services" '<p>Services overview fixture.</p>'
services_page_id=$(wp post list --post_type=page --name=services --field=ID)
wp post meta update "$services_page_id" _wp_page_template templates/services.php

create_service() {
  local slug="$1" title="$2" status="$3"
  if [[ -z "$(wp post list --post_type=service --name="$slug" --post_status="$status" --field=ID)" ]]; then
    wp post create --post_type=service --post_status="$status" --post_name="$slug" \
      --post_title="$title" --post_excerpt="Example service summary."
  fi
}
create_service web-design "Web Design" publish
create_service hosting "Hosting" publish
create_service draft-service "Draft service" draft
service_id=$(wp post list --post_type=service --name=web-design --field=ID)
if [[ -z "$(wp post meta get "$service_id" _thumbnail_id 2>/dev/null || true)" ]]; then
  # Match the staging uploads so the 640×360 hard crop has no responsive siblings.
  wp eval '$image = imagecreatetruecolor(1376, 768); imagefill($image, 0, 0, imagecolorallocate($image, 120, 128, 135)); imagejpeg($image, "/tmp/service-media.jpg");'
  wp media import /tmp/service-media.jpg --post_id="$service_id" --featured_image --alt="Service fixture image"
fi
# Reuse the responsive media fixture on the two fixed Page templates.
create_page about-template "About template" '<p>About story fallback.</p>'
create_page contact-template "Contact template" ''
hero_id=$(wp post meta get "$service_id" _thumbnail_id)
for template in about contact; do
  page_id=$(wp post list --post_type=page --name="$template-template" --field=ID)
  wp post meta update "$page_id" _wp_page_template "templates/$template.php"
  wp post meta update "$page_id" _thumbnail_id "$hero_id"
done
create_page business-home "Business home" '<p>Business homepage fixture.</p>'
business_home_id=$(wp post list --post_type=page --name=business-home --field=ID)
wp post meta update "$business_home_id" _wp_page_template templates/business-homepage.php
wp post meta update "$business_home_id" _thumbnail_id "$hero_id"

create_page greendawn-home "Greendawn home" ''
greendawn_home_id=$(wp post list --post_type=page --name=greendawn-home --field=ID)
wp post meta update "$greendawn_home_id" _wp_page_template templates/greendawn-homepage.php
create_page greendawn-home-sticky "Greendawn sticky header" ''
greendawn_sticky_home_id=$(wp post list --post_type=page --name=greendawn-home-sticky --field=ID)
wp post meta update "$greendawn_sticky_home_id" _wp_page_template templates/greendawn-homepage.php

# Mirror the Media Library path supplied for the default homepage photograph.
wp eval '
  $name = "greendawn-engineers-electrical-distribution-1600.webp";
  $relative = "2026/11/" . $name;
  $uploads = wp_get_upload_dir();
  if ( attachment_url_to_postid( $uploads["baseurl"] . "/" . $relative ) ) {
    return;
  }
  $file = $uploads["basedir"] . "/" . $relative;
  wp_mkdir_p( dirname( $file ) );
  if ( ! copy( get_theme_file_path( "/assets/home/" . $name ), $file ) ) {
    WP_CLI::error( "Could not seed the Greendawn photograph." );
  }
  $id = wp_insert_attachment( array( "post_title" => "Greendawn engineers", "post_mime_type" => "image/webp" ), $file, 0, true );
  if ( is_wp_error( $id ) ) {
    WP_CLI::error( $id->get_error_message() );
  }
  require_once ABSPATH . "wp-admin/includes/image.php";
  wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
  update_post_meta( $id, "_wp_attachment_image_alt", "Greendawn engineers working on commercial electrical distribution equipment" );
'
# The approved project photograph is upload-ready documentation, not a theme fallback.
docker cp docs/astro-reference/media/johnsons-dealership-ev-charging-1200.webp "$container_id:/tmp/johnsons-dealership-ev-charging-1200.webp"
wp eval '
  $relative = "2026/11/johnsons-dealership-ev-charging-1200.webp";
  $uploads = wp_get_upload_dir();
  if ( ! attachment_url_to_postid( $uploads["baseurl"] . "/" . $relative ) ) {
    $file = $uploads["basedir"] . "/" . $relative;
    wp_mkdir_p( dirname( $file ) );
    if ( ! copy( "/tmp/johnsons-dealership-ev-charging-1200.webp", $file ) ) {
      WP_CLI::error( "Could not seed the Johnsons project photograph." );
    }
    $id = wp_insert_attachment( array( "post_title" => "Johnsons dealership charging", "post_mime_type" => "image/webp" ), $file, 0, true );
    if ( is_wp_error( $id ) ) {
      WP_CLI::error( $id->get_error_message() );
    }
    require_once ABSPATH . "wp-admin/includes/image.php";
    wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
    update_post_meta( $id, "_wp_attachment_image_alt", "Vehicle connected to EV charging equipment at a Johnsons Cars dealership" );
  }
'
# Import the homepage photographs using normal WordPress thumbnail/scaling behaviour.
docker cp docs/astro-reference/media "$container_id:/tmp/greendawn-home-media"
wp eval '
  $photographs = array(
    "commercial-ev-fleet-charging-bays-1200.webp" => "Completed EV charging bays at a commercial site",
    "commercial-ev-workplace-charging-1200.webp" => "Wall-mounted EV charger beside a parked vehicle",
    "commercial-ev-destination-hospitality-1200.webp" => "EV chargers installed outside a hotel",
    "commercial-ev-multi-site-charging-768.webp" => "Completed EV charging installation with multiple marked bays",
    "commercial-ev-dealership-charging-1200.webp" => "EV charging equipment installed at a Ford dealership",
    "commercial-ev-electrical-connection-1200.webp" => "New electrical connection for a commercial EV charging project",
    "commercial-ev-site-survey-1024.webp" => "Completed paving and ducting area for an EV charging installation",
    "salvation-army-ev-charging-666.webp" => "Commercial EV chargers installed for The Salvation Army",
  );
  $uploads = wp_get_upload_dir();
  require_once ABSPATH . "wp-admin/includes/image.php";
  foreach ( $photographs as $name => $alt ) {
    $relative = "2026/11/" . $name;
    $file = $uploads["basedir"] . "/" . $relative;
    if ( attachment_url_to_postid( $uploads["baseurl"] . "/" . $relative ) ) {
      continue;
    }
    wp_mkdir_p( dirname( $file ) );
    if ( ! copy( "/tmp/greendawn-home-media/" . $name, $file ) ) {
      WP_CLI::error( "Could not seed the homepage photograph: " . $name );
    }
    $id = wp_insert_attachment( array( "post_title" => $alt, "post_mime_type" => "image/webp" ), $file, 0, true );
    if ( is_wp_error( $id ) ) {
      WP_CLI::error( $id->get_error_message() );
    }
    wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
    update_post_meta( $id, "_wp_attachment_image_alt", $alt );
  }
'
create_page greendawn-home-edited "Greendawn home edited" ''
greendawn_edited_id=$(wp post list --post_type=page --name=greendawn-home-edited --field=ID)
wp post meta update "$greendawn_edited_id" _wp_page_template templates/greendawn-homepage.php
wp post meta update "$greendawn_edited_id" greendawn_home_headline "Charging around your site."
wp post meta update "$greendawn_edited_id" greendawn_home_headline_accent ""
wp post meta update "$greendawn_edited_id" greendawn_home_hero_image "$hero_id"
wp post meta update "$greendawn_edited_id" greendawn_home_consult_label "Assess"
wp post meta update "$greendawn_edited_id" greendawn_home_featured_image "$hero_id"
wp post meta update "$greendawn_edited_id" greendawn_home_featured_title "A programme shaped around your estate."
wp post meta update "$greendawn_edited_id" greendawn_home_featured_sites_value "150"
wp post meta update "$greendawn_edited_id" greendawn_home_survey_price "£250"
wp post meta update "$greendawn_edited_id" greendawn_home_survey_outcome_1 "Assess the electrical capacity at your site"
wp post meta update "$greendawn_edited_id" greendawn_home_survey_guide_url "https://example.com/survey-guide/"
wp post meta update "$greendawn_edited_id" greendawn_home_projects_url "https://greendawn.co.uk/gallery/?view=estate#projects"
wp post meta update "$greendawn_edited_id" greendawn_home_system_technology_url "https://www.greendawn.co.uk/guides/ac-vs-dc-ev-charging/"
wp post meta update "$greendawn_edited_id" greendawn_home_solution_fleet_title "Charging for your fleet"
wp post meta update "$greendawn_edited_id" greendawn_home_solution_fleet_copy "A charging plan for your vehicles and operating hours."
wp post meta update "$greendawn_edited_id" greendawn_home_solution_fleet_link "See our fleet service"
wp post meta update "$greendawn_edited_id" greendawn_home_solution_fleet_url "https://www.greendawn.co.uk/fleet-charging-solutions/?source=home#details"
wp post meta update "$greendawn_edited_id" greendawn_home_solution_fleet_image "$hero_id"
wp post meta update "$greendawn_edited_id" greendawn_home_aftercare_title "Support around your charging operation."
wp post meta update "$greendawn_edited_id" greendawn_home_aftercare_intro ""
wp post meta update "$greendawn_edited_id" greendawn_home_aftercare_monitoring_title "Charger monitoring and reporting"
wp post meta update "$greendawn_edited_id" greendawn_home_aftercare_statement "Clear support for every stage of operation."
wp post meta update "$greendawn_edited_id" greendawn_home_aftercare_cta_label "Review our aftercare"
wp post meta update "$greendawn_edited_id" greendawn_home_aftercare_cta_url "https://www.greendawn.co.uk/aftercare/?view=plans#scope"
wp post meta update "$greendawn_edited_id" greendawn_home_aftercare_support_label "Speak to customer care"
wp post meta update "$greendawn_edited_id" greendawn_home_aftercare_support_url "https://greendawn.co.uk/customer-care/?from=home#help"
wp post meta update "$greendawn_edited_id" greendawn_home_proof_title "Coordinated charging across your sites."
wp post meta update "$greendawn_edited_id" greendawn_home_proof_project_title "Fleet charging programme"
wp post meta update "$greendawn_edited_id" greendawn_home_proof_result "52 sites · 74 chargepoints"
wp post meta update "$greendawn_edited_id" greendawn_home_proof_caption "Fleet programme delivered together"
wp post meta update "$greendawn_edited_id" greendawn_home_proof_copy "A coordinated programme for a varied fleet estate."
wp post meta update "$greendawn_edited_id" greendawn_home_proof_link "See our fleet projects"
wp post meta update "$greendawn_edited_id" greendawn_home_proof_image "$hero_id"
wp post meta update "$greendawn_edited_id" greendawn_home_insights_title "Guidance for your charging project."
wp post meta update "$greendawn_edited_id" greendawn_home_insights_link "Read our charging guides"
wp post meta update "$greendawn_edited_id" greendawn_home_insights_url "https://www.greendawn.co.uk/blog/?from=home#guides"
wp post meta update "$greendawn_edited_id" greendawn_home_insight_power_title "Plan your site power"
wp post meta update "$greendawn_edited_id" greendawn_home_insight_power_label ""
wp post meta update "$greendawn_edited_id" greendawn_home_insight_power_url "https://greendawn.co.uk/guides/load-balancing-explained/?from=home#capacity"
wp post meta update "$greendawn_edited_id" greendawn_home_insight_power_image "$hero_id"
wp post meta update "$greendawn_edited_id" greendawn_home_insight_survey_label "Survey planning"
wp post meta update "$greendawn_edited_id" greendawn_home_insight_survey_url "https://example.com/technical-guide/"
wp post meta update "$greendawn_edited_id" greendawn_home_insight_fleet_title "Charging around your fleet"
wp post meta update "$greendawn_edited_id" greendawn_home_closing_eyebrow "Your next charging project"
wp post meta update "$greendawn_edited_id" greendawn_home_closing_title "Start your charging project."
wp post meta update "$greendawn_edited_id" greendawn_home_closing_intro "Tell us about your vehicles, site and operating hours."
wp post meta update "$greendawn_edited_id" greendawn_home_closing_copy ""
wp post meta update "$greendawn_edited_id" greendawn_home_closing_cta_label "Plan your project"
wp post meta update "$greendawn_edited_id" greendawn_home_closing_phone_prompt "Call our team"
wp post meta update "$greendawn_edited_id" greendawn_home_closing_phone "+44 345 512 0044"



# Simulate an unavailable photograph on one disposable fixture page only.
docker cp tests/fixtures/missing-proof-media.php "$container_id:/var/www/html/wp-content/plugins/greendawn-test-media.php"
wp plugin activate greendawn-test-media
create_page greendawn-home-no-proof-image "Greendawn proof without a photograph" ''
greendawn_no_proof_id=$(wp post list --post_type=page --name=greendawn-home-no-proof-image --field=ID)
wp post meta update "$greendawn_no_proof_id" _wp_page_template templates/greendawn-homepage.php
# An unusable phone value must not create an empty or invalid call link.
wp post meta update "$greendawn_no_proof_id" greendawn_home_closing_phone "Ask the team"
create_page greendawn-home-no-insight-images "Greendawn insights without photographs" ''
greendawn_no_insights_id=$(wp post list --post_type=page --name=greendawn-home-no-insight-images --field=ID)
wp post meta update "$greendawn_no_insights_id" _wp_page_template templates/greendawn-homepage.php


# Exercise WordPress's assigned Posts page and published editorial templates.
create_page starter-home "Starter home" '<p>Static homepage fixture.</p>'
create_page blog "Blog" ''
create_page policy-example "Policy example" '<!-- wp:paragraph --><p>Policy body fixture.</p><!-- /wp:paragraph -->'
wp option update page_on_front "$(wp post list --post_type=page --name=starter-home --field=ID)"
wp option update page_for_posts "$(wp post list --post_type=page --name=blog --field=ID)"
wp option update show_on_front page
wp option update posts_per_page 2
editorial_post_id=$(wp post list --post_type=post --name=editorial-sample --field=ID)
if [[ -z "$editorial_post_id" ]]; then
  editorial_post_id=$(wp post create --post_status=publish --post_title="Editorial sample" --post_name=editorial-sample \
    --post_content="$(cat tests/fixtures/editor-parity.html)" --porcelain)
fi
wp post meta update "$editorial_post_id" _thumbnail_id "$hero_id"
wp rewrite flush

echo "Disposable WordPress integration environment ready at http://localhost:8080"
echo "This local-only server is for CI/integration tests, not a persistent development site."

# Sitewide footer scenarios: no persistent site content or menu assignments are changed.
docker cp tests/fixtures/footer-options.php "$container_id:/var/www/html/wp-content/plugins/footer-test-fixture.php"
wp plugin activate footer-test-fixture
wp eval '
  foreach ( array( "footer-options", "footer-long", "footer-empty", "footer-no-core", "greendawn-home-global-phone" ) as $slug ) {
    $page = get_page_by_path( $slug );
    $id = $page ? $page->ID : wp_insert_post( array( "post_type" => "page", "post_status" => "publish", "post_name" => $slug, "post_title" => "Footer integration fixture", "post_content" => "<p>Footer content fixture.</p>" ) );
    if ( "greendawn-home-global-phone" === $slug ) {
      update_post_meta( $id, "_wp_page_template", "templates/greendawn-homepage.php" );
    }
  }
  $menus = array(
    "footer" => array( array( "Company overview", "https://www.greendawn.co.uk/about/?from=footer#team" ), array( "Our people", home_url( "/about/#people" ) ) ),
    "footer_secondary" => array( array( "Charge your workplace", home_url( "/services/" ) ) ),
    "footer_support" => array( array( "Customer care", home_url( "/contact/" ) ) ),
    "footer_legal" => array( array( "Policy details", home_url( "/policy-example/" ) ) ),
    "footer_social" => array( array( "Company network", "https://www.linkedin.com/company/greendawnpower/" ) ),
  );
  $locations = array();
  foreach ( $menus as $location => $items ) {
    $name = "Fixture " . $location;
    $existing = wp_get_nav_menu_object( $name );
    if ( $existing ) { wp_delete_nav_menu( $existing->term_id ); }
    $menu = wp_create_nav_menu( $name );
    $locations[ $location ] = $menu;
    $first = 0;
    foreach ( $items as $item ) {
      $id = wp_update_nav_menu_item( $menu, 0, array( "menu-item-title" => $item[0], "menu-item-url" => $item[1], "menu-item-type" => "custom", "menu-item-status" => "publish" ) );
      if ( ! $first ) { $first = $id; }
    }
    wp_update_nav_menu_item( $menu, 0, array( "menu-item-title" => "Nested footer item omitted", "menu-item-url" => home_url( "/about/#nested" ), "menu-item-parent-id" => $first, "menu-item-type" => "custom", "menu-item-status" => "publish" ) );
  }
  update_option( "greendawn_footer_test_menus", $locations );
'

# Exercise SCF editing first, then render native fallback without SCF in the browser suite.
wp plugin activate secure-custom-fields
docker cp tests/integration/about-page.php "$container_id:/tmp/about-page.php"
wp eval-file /tmp/about-page.php
wp plugin deactivate secure-custom-fields
docker cp tests/fixtures/about-options.php "$container_id:/var/www/html/wp-content/plugins/about-test-fixture.php"
wp plugin activate about-test-fixture
