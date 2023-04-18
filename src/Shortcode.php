<?php

namespace CommonbookingsAdditionalFeatures;

if (!defined('WPINC')) {
    die();
}

class Shortcode
{
    public function __construct()
    {
        add_shortcode('cbaf_bookings', [$this, 'bookings']);
        add_shortcode('cbaf_historie_table', [$this, 'historie_table']);
        add_shortcode('cbaf_sidebar', [$this, 'sidebar']);
        add_shortcode('desktoponly', [$this, 'desktop_only']);
        add_shortcode('mobileonly', [$this, 'mobile_only']);
    }
    public function bookings($atts, $content, $tag)
    {
        $userid = get_current_user_id();
        //Parameter für Posts
        $args = [
            'numberposts' => -1,
            'post_status' => 'confirmed',
            'post_type' => 'cb_booking',
        ];

        //Posts holen
        $posts = get_posts($args);

        //Inhalte sammeln
        $bookingArray = [];
        foreach ($posts as $post) {
            if (
                $userid == $post->post_author &&
                time() < get_post_meta($post->ID, 'repetition-end', true)
            ) {
                $bookingArray[] = new Booking($post->ID, $userid);
            }
        }
        usort($bookingArray, [Booking::class, 'comparatorUp']);
        $commonbookings_additional_features_options = get_option(
            'commonbookings_additional_features_option_name'
        );

        $link = get_page_link(
            $commonbookings_additional_features_options['buchung_historie_1']
        );
        return Plugin::template()->render('booking.html.twig', [
            'bookings' => $bookingArray,
            'historie' => $link,
        ]);
    }

    public function historie_table($atts, $content, $tag)
    {
        // Initializing the array that will be used for the table
        $userid = get_current_user_id();

        $args = [
            'numberposts' => -1,
            'post_status' => ['confirmed', 'canceled' , 'publish'],
            'post_type' => 'cb_booking',
        ];

        //Posts holen
        $posts = get_posts($args);

        //Inhalte sammeln
        $bookingArray = [];
        foreach ($posts as $post) {
            if (
                $userid == $post->post_author 
            ) {
                $bookingArray[] = new Booking($post->ID, $userid);
            }
        }
        usort($bookingArray, [Booking::class, 'comparatorDown']);
        $path = plugin_dir_url(__DIR__) . 'assets/js/datatable/de_de.json';
        
        // Now the array is prepared, we just need to serialize and output it
        return Plugin::template()->render('historie.html.twig', [
            'bookings' => $bookingArray,
            'path' => $path,   
        ]);
    }

    public function sidebar($atts, $content, $tag)
    {
        $commonbookings_additional_features_options = get_option(
            'commonbookings_additional_features_option_name'
        ); // Array of All Options
        $buchung_0 = get_page_link(
            $commonbookings_additional_features_options['buchung_0']
        ); // Buchung
        $profil_1 =
            $commonbookings_additional_features_options['profil_2'] == -1
                ? get_site_url() . '/wp-admin/profile.php'
                : get_page_link(
                    $commonbookings_additional_features_options['profil_2']
                ); // Profil
		$konto_3 =  $commonbookings_additional_features_options['konto_3'] == -1
                ? false
                : get_page_link(
                    $commonbookings_additional_features_options['konto_3']
                ); // Konto
        $abmelden_2 = wp_logout_url(get_home_url()); // abmelden
        return Plugin::template()->render('sidebar.html.twig', [
            'user' => is_user_logged_in() ? wp_get_current_user() : false,
            'booking' => $buchung_0,
            'profil' => $profil_1,
            'logout' => $abmelden_2,
            'konto' => $konto_3,
            'login' => get_site_url()  . '/login',
            'register' => wp_registration_url(),
            'commercial' =>
                $commonbookings_additional_features_options['werbung_0'],
        ]);
    }
    public function desktop_only($atts, $content = null)
    {
        if (!wp_is_mobile()) {
            return $content;
        } else {
            return null;
        }
    }
    public function mobile_only($atts, $content = null)
    {
        if (wp_is_mobile()) {
            return $content;
        } else {
            return null;
        }
    }
}
