<?php
/** Explicit, reversible repair for the legacy Ecom/ecom identifier mismatch. */
add_action( 'admin_menu', function () {
    add_management_page( 'Ecom template repair', 'Ecom template repair', 'edit_theme_options', 'ecom-template-repair', 'ecom_render_template_repair' );
} );

function ecom_repair_records() {
    return get_posts( array(
        'post_type' => array( 'wp_template', 'wp_template_part', 'wp_global_styles' ),
        'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
        'posts_per_page' => -1,
    ) );
}

function ecom_normalize_template_references( $content ) {
    return preg_replace_callback( '/<!--\s+wp:template-part\s+(\{.*?\})\s*\/-->/s', function ( $match ) {
        $attrs = json_decode( $match[1], true );
        if ( ! is_array( $attrs ) || ! isset( $attrs['theme'] ) || 'Ecom' !== $attrs['theme'] ) {
            return $match[0];
        }
        $attrs['theme'] = 'ecom';
        return '<!-- wp:template-part ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ' /-->';
    }, $content );
}

function ecom_render_template_repair() {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( 'You do not have permission to repair theme templates.' );
    }
    $backup_key = 'ecom_template_repair_backup_v1';
    $message = '';
    $error = '';
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
        check_admin_referer( 'ecom_template_repair' );
        $action = isset( $_POST['ecom_action'] ) ? sanitize_key( wp_unslash( $_POST['ecom_action'] ) ) : '';
        if ( 'restore' === $action ) {
            $backup = get_option( $backup_key );
            if ( ! is_array( $backup ) ) {
                $error = 'No repair backup is available.';
            } else {
                foreach ( $backup['terms'] as $term ) {
                    $result = wp_update_term( $term['term_id'], 'wp_theme', array( 'name' => $term['name'] ) );
                    if ( is_wp_error( $result ) ) { $error = $result->get_error_message(); break; }
                }
                if ( ! $error ) {
                    foreach ( $backup['posts'] as $post ) {
                        $result = wp_update_post( wp_slash( array( 'ID' => $post['ID'], 'post_content' => $post['post_content'] ) ), true );
                        if ( is_wp_error( $result ) ) { $error = $result->get_error_message(); break; }
                    }
                }
                if ( ! $error ) {
                    foreach ( $backup['options'] as $key => $value ) { update_option( $key, $value ); }
                    $message = 'Pre-repair template contents and theme associations restored.';
                }
            }
        } elseif ( 'repair' === $action ) {
            if ( 'ecom' !== strtolower( get_stylesheet() ) ) {
                $error = 'Activate the theme installed in the ecom folder before running this repair.';
            } else {
                $terms = get_terms( array( 'taxonomy' => 'wp_theme', 'hide_empty' => false ) );
                if ( is_wp_error( $terms ) ) {
                    $error = $terms->get_error_message();
                } else {
                    $term_changes = array();
                    foreach ( $terms as $term ) {
                        if ( 'Ecom' === $term->name ) {
                            $term_changes[] = array( 'term_id' => $term->term_id, 'name' => $term->name );
                        }
                    }
                    $post_changes = array();
                    foreach ( ecom_repair_records() as $post ) {
                        $new_content = ecom_normalize_template_references( $post->post_content );
                        if ( $new_content !== $post->post_content ) {
                            $post_changes[] = array( 'ID' => $post->ID, 'post_content' => $post->post_content, 'new_content' => $new_content );
                        }
                    }
                    $options = array( 'stylesheet' => get_option( 'stylesheet' ), 'template' => get_option( 'template' ) );
                    // Preserve the first backup. Never replace it on a second click.
                    if ( false === get_option( $backup_key, false ) ) {
                        $saved = add_option( $backup_key, array( 'created' => current_time( 'mysql' ), 'terms' => $term_changes, 'posts' => $post_changes, 'options' => $options ), '', false );
                        if ( ! $saved ) { $error = 'Could not create the backup. No changes were made.'; }
                    }
                    if ( ! $error ) {
                        foreach ( $term_changes as $term ) {
                            $result = wp_update_term( $term['term_id'], 'wp_theme', array( 'name' => 'ecom' ) );
                            if ( is_wp_error( $result ) ) { $error = $result->get_error_message(); break; }
                        }
                        if ( ! $error ) {
                            foreach ( $post_changes as $post ) {
                                $result = wp_update_post( wp_slash( array( 'ID' => $post['ID'], 'post_content' => $post['new_content'] ) ), true );
                                if ( is_wp_error( $result ) ) { $error = $result->get_error_message(); break; }
                            }
                        }
                        if ( ! $error ) {
                            foreach ( $options as $key => $value ) {
                                if ( 'Ecom' === $value ) { update_option( $key, 'ecom' ); }
                            }
                            $message = sprintf( 'Repair complete: %d theme associations and %d saved template references updated. Reload the Site Editor and check the frontend.', count( $term_changes ), count( $post_changes ) );
                        }
                    }
                }
            }
        }
    }
    echo '<div class="wrap"><h1>Ecom template repair</h1>';
    if ( $message ) { echo '<div class="notice notice-success"><p>' . esc_html( $message ) . '</p></div>'; }
    if ( $error ) { echo '<div class="notice notice-error"><p>' . esc_html( $error ) . ' A repair backup, if created, remains available below.</p></div>'; }
    echo '<p>Run this on the local source site before making a new Duplicator package.</p>';
    echo '<p>This normalizes the legacy <code>Ecom</code> theme association and saved template-part references to the installed folder identifier <code>ecom</code>. It preserves reusable parts, their content, products, pages and design settings. It does not reset templates.</p>';
    echo '<p>A backup of the affected records is saved in this WordPress database before changes. Keep your original Duplicator archive as a separate full-site backup.</p>';
    echo '<form method="post">';
    wp_nonce_field( 'ecom_template_repair' );
    echo '<input type="hidden" name="ecom_action" value="repair">';
    submit_button( 'Back up and repair template associations' );
    echo '</form>';
    if ( is_array( get_option( $backup_key ) ) ) {
        echo '<hr><h2>Undo this repair</h2><p>Restore the original affected records. This will also undo later edits to those same template contents.</p><form method="post">';
        wp_nonce_field( 'ecom_template_repair' );
        echo '<input type="hidden" name="ecom_action" value="restore">';
        submit_button( 'Restore pre-repair records', 'secondary' );
        echo '</form>';
    }
    echo '</div>';
}
