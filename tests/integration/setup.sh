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
  wp core install --url=http://localhost:8080 --title="WP Starter Test" \
    --admin_user=admin --admin_password=local-test-only \
    --admin_email=admin@example.test --skip-email
fi

wp theme activate wp-starter
wp plugin activate agency-core
wp plugin activate ezhost-updater
wp eval 'if ( ! has_filter( "update_plugins_updates.ezhostforall.invalid" ) || ! has_filter( "update_themes_updates.ezhostforall.invalid" ) || ! has_filter( "upgrader_pre_download" ) ) { WP_CLI::error( "EZ Host updater hooks missing" ); } WP_CLI::success( "Updater registered without configuration" );'
wp rewrite structure '/%postname%/' --hard

create_page() {
  local slug="$1" title="$2" content="$3"
  if [[ -z "$(wp post list --post_type=page --name="$slug" --field=ID)" ]]; then
    wp post create --post_type=page --post_status=publish \
      --post_title="$title" --post_name="$slug" --post_content="$content"
  fi
}

create_page about "About" '<!-- wp:paragraph --><p>Example page content.</p><!-- /wp:paragraph -->'
create_page react-example "React example" '[wp_starter_react_example label="Clicks" start="0"]'
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
wp option update agency_core_services_enabled 1
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
