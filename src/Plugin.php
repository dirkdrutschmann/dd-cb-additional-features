<?php

namespace CommonbookingsAdditionalFeatures;

if (!defined('WPINC')) {
    die();
}

class Plugin
{
    public function __construct()
    {
        add_filter('https_ssl_verify', '__return_false');
        //* Maybe you want to require more files here
        add_action('wp_enqueue_scripts', [$this, 'scripts']);
        add_action('admin_enqueue_scripts', [$this, 'select']);
        $shortcodes = new Shortcode();
        $cronjob = new Cronjob();
        $handler = new Handler();
        if (is_admin()) {
            $commonbookings_additional_features = new Settingspage();
            add_action('wp_loaded', [$this, 'register_my_session']);
        }
    }

    function register_my_session()
    {
        if (!session_id()) {
            session_start();
        }
    }
    public function select()
    {
        wp_enqueue_script('jquery');
        wp_register_style(
            'select-stylesheet',
            plugin_dir_url(__DIR__) . 'assets/js/select2/css/select2.min.css'
        );
        wp_enqueue_style('select-stylesheet');
        wp_enqueue_script(
            'select-js',
            plugin_dir_url(__DIR__) .
                'assets/js/select2/js/select2.full.min.js',
            ['jquery'],
            '',
            false
        );
    }

    public function scripts()
    {
        wp_enqueue_script('jquery');
        wp_register_style(
            'my-stylesheet',
            plugin_dir_url(__DIR__) . 'assets/css/styles.css'
        );
        wp_enqueue_style('my-stylesheet');

        wp_enqueue_script(
            'datatable-js',
            plugin_dir_url(__DIR__) . 'assets/js/datatable/datatables.js',
            ['jquery'],
            '',
            false
        );
    }

    public static function getBootstrap()
    {
        wp_register_style(
            'bootstrap-style',
            plugin_dir_url(__DIR__) . 'assets/css/bootstrap.min.css',
            [],
            '5.1.3'
        );
        wp_enqueue_style('bootstrap-style');

        wp_enqueue_script(
            'bootstrap-script',
            plugin_dir_url(__DIR__) . 'assets/js/bootstrap.min.js',
            ['jquery'],
            '5.1.3',
            true
        );
    }

    public static function template()
    {
        $loader = new \Twig\Loader\FilesystemLoader(
            plugin_dir_path(__DIR__) . 'templates'
        );
        return new \Twig\Environment($loader);
    }
}
