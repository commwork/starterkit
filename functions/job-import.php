<?php
/**
 * Job Import from Bewerber 3 Portal
 * 
 * Imports jobs from the XML feed and creates/updates WordPress posts
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/** Taxonomy slugs (must match register_job_taxonomies in functions.php). */
if (!defined('AWO_JOB_TAX_ORT')) {
    define('AWO_JOB_TAX_ORT', 'job_ort');
    define('AWO_JOB_TAX_BERUFSBEZEICHNUNG', 'job_berufsbezeichnung');
}
if (!defined('AWO_JOB_TAX_KATEGORIE')) {
    define('AWO_JOB_TAX_KATEGORIE', 'job_kategorie');
}

/**
 * Create or resolve a term by name and assign it to the post (replaces existing terms in that taxonomy).
 *
 * @param int    $post_id  Post ID.
 * @param string $taxonomy Taxonomy slug.
 * @param string $name     Term label from feed.
 */
function awo_job_import_set_single_term($post_id, $taxonomy, $name) {
    $name = trim(wp_strip_all_tags((string) $name));
    if ($name === '') {
        wp_set_object_terms($post_id, array(), $taxonomy);
        return;
    }

    $existing = get_term_by('name', $name, $taxonomy);
    if ($existing && !is_wp_error($existing)) {
        $term_id = (int) $existing->term_id;
    } else {
        $inserted = wp_insert_term($name, $taxonomy);
        if (is_wp_error($inserted)) {
            if ($inserted->get_error_code() === 'term_exists') {
                $term_id = (int) $inserted->get_error_data();
            } else {
                error_log('Job Import Error: term for ' . $taxonomy . ' - ' . $inserted->get_error_message());
                return;
            }
        } else {
            $term_id = (int) $inserted['term_id'];
        }
    }

    wp_set_object_terms($post_id, array($term_id), $taxonomy, false);
}

/**
 * Extract first image URL from HTML content.
 *
 * @param string $content HTML content.
 * @return string|false
 */
function awo_get_first_image_url_from_content($content) {
    if (!is_string($content) || $content === '') {
        return false;
    }

    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
        return esc_url_raw(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'));
    }

    return false;
}

/**
 * Set featured image from first content image.
 *
 * @param int    $post_id  Post ID.
 * @param string $content  HTML content.
 * @param string $job_id   External job ID for logging.
 * @return bool
 */
function awo_set_job_thumbnail_from_content($post_id, $content, $job_id = '') {
    $image_url = awo_get_first_image_url_from_content($content);
    if (!$image_url) {
        return false;
    }

    $existing_attachment_ids = get_posts(array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => '_awo_job_import_image_source',
        'meta_value' => $image_url,
    ));

    if (!empty($existing_attachment_ids)) {
        return set_post_thumbnail($post_id, (int) $existing_attachment_ids[0]);
    }

    if (!function_exists('media_handle_sideload')) {
        require_once ABSPATH . 'wp-admin/includes/media.php';
    }
    if (!function_exists('download_url')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    if (!function_exists('wp_generate_attachment_metadata')) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $tmp_file = download_url($image_url, 30);
    if (is_wp_error($tmp_file)) {
        error_log('Job Import Error: Failed to download image for job ' . $job_id . ' - ' . $tmp_file->get_error_message());
        return false;
    }

    $mime = wp_get_image_mime($tmp_file);
    $mime_to_ext = array(
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    );
    $extension = isset($mime_to_ext[$mime]) ? $mime_to_ext[$mime] : 'jpg';

    $file_array = array(
        'name' => 'job-' . sanitize_file_name((string) $job_id) . '-featured.' . $extension,
        'tmp_name' => $tmp_file,
    );

    $attachment_id = media_handle_sideload($file_array, $post_id);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp_file);
        error_log('Job Import Error: Failed to sideload image for job ' . $job_id . ' - ' . $attachment_id->get_error_message());
        return false;
    }

    update_post_meta((int) $attachment_id, '_awo_job_import_image_source', $image_url);
    return set_post_thumbnail($post_id, (int) $attachment_id);
}

/**
 * Trash imported jobs that are no longer present in the Bewerber XML feed.
 *
 * Only posts with `_job_external_id` are considered (manually created jobs are untouched).
 *
 * @param string[] $feed_job_ids External job IDs from the current feed.
 * @return array{removed: int, errors: int}
 */
function awo_job_import_remove_stale_jobs( array $feed_job_ids ) {
    $feed_job_ids = array_values( array_filter( array_map( 'strval', $feed_job_ids ) ) );

    if ( empty( $feed_job_ids ) ) {
        $existing_imported = get_posts(
            array(
                'post_type'      => 'jobs',
                'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_query'     => array(
                    array(
                        'key'     => '_job_external_id',
                        'compare' => 'EXISTS',
                    ),
                ),
            )
        );

        if ( ! empty( $existing_imported ) ) {
            error_log( 'Job Import Warning: Feed returned no job IDs; skipping stale cleanup to prevent mass deletion.' );
        }

        return array(
            'removed' => 0,
            'errors'  => 0,
        );
    }

    $feed_lookup  = array_fill_keys( $feed_job_ids, true );

    $removed = 0;
    $errors  = 0;

    $stale_posts = get_posts(
        array(
            'post_type'      => 'jobs',
            'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'     => '_job_external_id',
                    'compare' => 'EXISTS',
                ),
            ),
        )
    );

    foreach ( $stale_posts as $post_id ) {
        $external_id = (string) get_post_meta( (int) $post_id, '_job_external_id', true );
        if ( $external_id === '' || isset( $feed_lookup[ $external_id ] ) ) {
            continue;
        }

        $trashed = wp_trash_post( (int) $post_id );
        if ( ! $trashed ) {
            $errors++;
            error_log( 'Job Import Error: Failed to trash stale job post ' . $post_id . ' (external ID: ' . $external_id . ')' );
            continue;
        }

        $removed++;
    }

    return array(
        'removed' => $removed,
        'errors'  => $errors,
    );
}

/**
 * Fetch and parse jobs from Bewerber 3 XML feed
 */
function awo_import_jobs_from_xml() {
    $xml_url = 'https://awovorpo.pi-asp.de/bewerber-web//public/JobPortalData?companyEid=ST201&tenant=&lang=D&jobPortal=HOMEPAGE';
    
    // Fetch XML data
    $response = wp_remote_get($xml_url, array(
        'timeout' => 30,
        'sslverify' => true,
    ));
    
    // Check for errors
    if (is_wp_error($response)) {
        error_log('Job Import Error: ' . $response->get_error_message());
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $http_code = wp_remote_retrieve_response_code($response);
    
    if ($http_code !== 200) {
        error_log('Job Import Error: HTTP ' . $http_code);
        return false;
    }
    
    // Parse XML
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($body);
    
    if ($xml === false) {
        $errors = libxml_get_errors();
        error_log('Job Import Error: Failed to parse XML - ' . print_r($errors, true));
        libxml_clear_errors();
        return false;
    }
    
    $imported_count = 0;
    $updated_count  = 0;
    $error_count    = 0;
    $feed_job_ids   = array();

    // Process each job
    foreach ($xml->job as $job) {
        $job_id = (string) $job->id;
        if ( $job_id !== '' ) {
            $feed_job_ids[] = $job_id;
        }
        $reference_number = (string) $job->referenceNumber;
        $title = (string) $job->title;
        $short_name = (string) $job->shortName;
        $content = (string) $job->content;
        $location = (string) $job->location;
        $country = (string) $job->country;
        $hr_consultant = (string) $job->hrConsultant;
        $range = (string) $job->range;
        $external_url = (string) $job->url;
        $creation_date = (string) $job->creationDate;
        $last_update = (string) $job->lastUpdate;
        $selection1 = (string) $job->selection1;
        $selection2 = (string) $job->selection2;
        $selection4 = (string) $job->selection4;
        
        // Check if job already exists by meta field
        $existing_posts = get_posts(array(
            'post_type' => 'jobs',
            'meta_key' => '_job_external_id',
            'meta_value' => $job_id,
            'posts_per_page' => 1,
            'post_status' => 'any',
        ));
        
        // Prepare post data
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_excerpt' => $short_name,
            'post_type' => 'jobs',
            'post_status' => 'publish',
        );
        
        // Set post date from creation date if available
        if (!empty($creation_date)) {
            $post_data['post_date'] = date('Y-m-d H:i:s', strtotime($creation_date));
        }
        
        // Update existing post or create new one
        if (!empty($existing_posts)) {
            $post_id = $existing_posts[0]->ID;
            $post_data['ID'] = $post_id;
            $result = wp_update_post($post_data, true);
            
            if (is_wp_error($result)) {
                error_log('Job Import Error: Failed to update post - ' . $result->get_error_message());
                $error_count++;
                continue;
            }
            $updated_count++;
        } else {
            $post_id = wp_insert_post($post_data, true);
            
            if (is_wp_error($post_id)) {
                error_log('Job Import Error: Failed to create post - ' . $post_id->get_error_message());
                $error_count++;
                continue;
            }
            $imported_count++;
        }
        
        // Save meta fields
        update_post_meta($post_id, '_job_external_id', $job_id);
        update_post_meta($post_id, '_job_reference_number', $reference_number);
        update_post_meta($post_id, '_job_location', $location);
        update_post_meta($post_id, '_job_country', $country);
        update_post_meta($post_id, '_job_hr_consultant', $hr_consultant);
        update_post_meta($post_id, '_job_range', $range);
        update_post_meta($post_id, '_job_external_url', $external_url);
        update_post_meta($post_id, '_job_creation_date', $creation_date);
        update_post_meta($post_id, '_job_last_update', $last_update);
        update_post_meta($post_id, '_job_selection1', $selection1);
        update_post_meta($post_id, '_job_selection2', $selection2);
        update_post_meta($post_id, '_job_selection4', $selection4);

        awo_job_import_set_single_term($post_id, AWO_JOB_TAX_KATEGORIE, $selection1);
        awo_job_import_set_single_term($post_id, AWO_JOB_TAX_ORT, $location);
        awo_job_import_set_single_term($post_id, AWO_JOB_TAX_BERUFSBEZEICHNUNG, $selection2);

        // Set featured image from first image found in imported HTML content.
        awo_set_job_thumbnail_from_content($post_id, $content, $job_id);
    }

    $cleanup       = awo_job_import_remove_stale_jobs( $feed_job_ids );
    $removed_count = (int) $cleanup['removed'];
    $error_count  += (int) $cleanup['errors'];
    
    // Log results
    error_log(sprintf(
        'Job Import completed: %d imported, %d updated, %d removed, %d errors',
        $imported_count,
        $updated_count,
        $removed_count,
        $error_count
    ));
    
    // Store last import time
    update_option('awo_job_import_last_run', current_time('mysql'));
    update_option('awo_job_import_stats', array(
        'imported' => $imported_count,
        'updated'  => $updated_count,
        'removed'  => $removed_count,
        'errors'   => $error_count,
    ));
    
    return array(
        'imported' => $imported_count,
        'updated'  => $updated_count,
        'removed'  => $removed_count,
        'errors'   => $error_count,
    );
}

/**
 * Schedule hourly job import
 */
function awo_schedule_job_import() {
    if (!wp_next_scheduled('awo_hourly_job_import')) {
        wp_schedule_event(time(), 'hourly', 'awo_hourly_job_import');
    }
}
add_action('wp', 'awo_schedule_job_import');

/**
 * Hook into the scheduled event
 */
add_action('awo_hourly_job_import', 'awo_import_jobs_from_xml');

/**
 * Manual import trigger (can be called via admin or WP-CLI)
 */
function awo_trigger_manual_job_import() {
    return awo_import_jobs_from_xml();
}

/**
 * Whether the current user may view or trigger the job import admin UI (Editor and above).
 */
function awo_current_user_can_job_import() {
    return current_user_can( 'edit_others_posts' );
}

/**
 * Add admin notice for manual import
 */
function awo_job_import_admin_notice() {
    if (isset($_GET['awo_import_jobs']) && $_GET['awo_import_jobs'] === '1' && awo_current_user_can_job_import()) {
        $result = awo_trigger_manual_job_import();
        if ($result !== false) {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo sprintf(
                esc_html__( 'Stellenimport erfolgreich! %d neu, %d aktualisiert, %d entfernt, %d Fehler.', 'starterkit' ),
                $result['imported'],
                $result['updated'],
                $result['removed'],
                $result['errors']
            );
            echo '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Stellenimport fehlgeschlagen. Bitte die Fehlerprotokolle prüfen.', 'starterkit' ) . '</p></div>';
        }
    }
}
add_action('admin_notices', 'awo_job_import_admin_notice');

/**
 * Admin URL for the job import page.
 *
 * @param array<string, string> $query_args Optional query arguments.
 * @return string
 */
function awo_job_import_admin_url( $query_args = array() ) {
    return add_query_arg(
        array_merge( array( 'page' => 'awo-import-jobs' ), $query_args ),
        admin_url( 'admin.php' )
    );
}

/**
 * Add admin page for manual import (top-level menu).
 */
function awo_add_job_import_admin_page() {
    add_menu_page(
        __( 'Stellenimport', 'starterkit' ),
        __( 'Stellenimport', 'starterkit' ),
        'edit_others_posts',
        'awo-import-jobs',
        'awo_job_import_admin_page',
        'dashicons-update',
        58
    );
}
add_action('admin_menu', 'awo_add_job_import_admin_page');

/**
 * Admin page callback
 */
function awo_job_import_admin_page() {
    if (!awo_current_user_can_job_import()) {
        wp_die( esc_html__( 'Sie haben nicht die erforderlichen Berechtigungen, um auf diese Seite zuzugreifen.', 'starterkit' ) );
    }
    
    $last_run = get_option('awo_job_import_stats', array());
    $last_run_time = get_option('awo_job_import_last_run', '');
    if ($last_run_time === '' || $last_run_time === 'Never') {
        $last_run_time = __( 'Noch nie', 'starterkit' );
    }
    $next_run = wp_next_scheduled('awo_hourly_job_import');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Stellenimport aus Bewerber 3', 'starterkit' ); ?></h1>
        
        <div class="card">
            <h2><?php esc_html_e( 'Import-Status', 'starterkit' ); ?></h2>
            <p><strong><?php esc_html_e( 'Letzter Lauf:', 'starterkit' ); ?></strong> <?php echo esc_html($last_run_time); ?></p>
            <?php if (!empty($last_run)): ?>
                <p><strong><?php esc_html_e( 'Letztes Ergebnis:', 'starterkit' ); ?></strong></p>
                <ul>
                    <li><?php esc_html_e( 'Neu importiert:', 'starterkit' ); ?> <?php echo esc_html( $last_run['imported'] ?? 0 ); ?></li>
                    <li><?php esc_html_e( 'Aktualisiert:', 'starterkit' ); ?> <?php echo esc_html( $last_run['updated'] ?? 0 ); ?></li>
                    <li><?php esc_html_e( 'Entfernt (nicht mehr im Feed):', 'starterkit' ); ?> <?php echo esc_html( $last_run['removed'] ?? 0 ); ?></li>
                    <li><?php esc_html_e( 'Fehler:', 'starterkit' ); ?> <?php echo esc_html( $last_run['errors'] ?? 0 ); ?></li>
                </ul>
            <?php endif; ?>
            
            <?php if ($next_run): ?>
                <p><strong><?php esc_html_e( 'Nächster geplanter Lauf:', 'starterkit' ); ?></strong> <?php echo esc_html( date_i18n( 'j. F Y, H:i', $next_run ) ); ?></p>
            <?php else: ?>
                <p><strong><?php esc_html_e( 'Nächster geplanter Lauf:', 'starterkit' ); ?></strong> <?php esc_html_e( 'Nicht geplant', 'starterkit' ); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2><?php esc_html_e( 'Manueller Import', 'starterkit' ); ?></h2>
            <p><?php esc_html_e( 'Klicken Sie auf die Schaltfläche unten, um den Stellenimport manuell auszulösen.', 'starterkit' ); ?></p>
            <a href="<?php echo esc_url( awo_job_import_admin_url( array( 'awo_import_jobs' => '1' ) ) ); ?>" class="button button-primary">
                <?php esc_html_e( 'Stellen jetzt importieren', 'starterkit' ); ?>
            </a>
        </div>
        
        <div class="card">
            <h2><?php esc_html_e( 'Import-Einstellungen', 'starterkit' ); ?></h2>
            <p><strong><?php esc_html_e( 'XML-Feed-URL:', 'starterkit' ); ?></strong></p>
            <code>https://awovorpo.pi-asp.de/bewerber-web//public/JobPortalData?companyEid=ST201&tenant=&lang=D&jobPortal=HOMEPAGE</code>
            <p><strong><?php esc_html_e( 'Zeitplan:', 'starterkit' ); ?></strong> <?php esc_html_e( 'Stündlich (WordPress-Cron)', 'starterkit' ); ?></p>
            <p><?php esc_html_e( 'Stellen, die im XML-Feed fehlen (z. B. Entwurf in Bewerber 3), werden beim Sync automatisch in den Papierkorb verschoben.', 'starterkit' ); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Helper function to get job meta field
 * 
 * @param int $post_id Post ID
 * @param string $field Meta field name (without _job_ prefix)
 * @return string|false Meta value or false if not found
 */
function awo_get_job_meta($post_id, $field) {
    return get_post_meta($post_id, '_job_' . $field, true);
}
