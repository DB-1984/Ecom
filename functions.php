<?php

require_once get_theme_file_path( 'assets/includes/contact_form.php' );

function ecom_enqueue_styles() {

    $style_path = get_stylesheet_directory() . '/style.css';

    wp_enqueue_style(
            'ecom-style',
            get_stylesheet_uri(),
            array(),
            file_exists( $style_path ) ? filemtime( $style_path ) : wp_get_theme()->get( 'Version' )
        );

    $relative = '/assets/css/ecom-front.css';
    $path     = get_stylesheet_directory() . $relative;

    if ( file_exists( $path ) ) {
        wp_enqueue_style(
            'ecom-front',
            get_stylesheet_directory_uri() . $relative,
            array( 'ecom-style' ),
            filemtime( $path )
        );
    }

    $relative = '/assets/css/ecom-product.css';
    $path     = get_stylesheet_directory() . $relative;

    if ( file_exists( $path ) ) {
        wp_enqueue_style(
            'ecom-product',
            get_stylesheet_directory_uri() . $relative,
            array( 'ecom-style' ),
            filemtime( $path )
        );
    }


    $relative = '/assets/css/ecom-cart.css';
    $path     = get_stylesheet_directory() . $relative;

    if ( file_exists( $path ) ) {
        wp_enqueue_style(
            'ecom-cart',
            get_stylesheet_directory_uri() . $relative,
            array( 'ecom-style' ),
            filemtime( $path )
        );
    }

    $relative = '/assets/css/ecom-checkout.css';
    $path     = get_stylesheet_directory() . $relative;

    if ( file_exists( $path ) ) {
        wp_enqueue_style(
            'ecom-checkout',
            get_stylesheet_directory_uri() . $relative,
            array( 'ecom-style' ),
            filemtime( $path )
        );
    }

    $relative = '/assets/css/ecom-page.css';
    $path     = get_stylesheet_directory() . $relative;

    if ( file_exists( $path ) ) {
        wp_enqueue_style(
            'ecom-page',
            get_stylesheet_directory_uri() . $relative,
            array( 'ecom-style' ),
            filemtime( $path )
        );
    }
}

add_action( 'enqueue_block_assets', 'ecom_enqueue_styles' );

add_action( 'init', function () {
	register_block_type(
		get_theme_file_path( 'build/contact-form' )
	);
} );