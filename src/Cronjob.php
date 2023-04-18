<?php

namespace CommonbookingsAdditionalFeatures;

if (!defined('WPINC')) {
    die();
}

class Cronjob
{
    public function __construct()
    {
        add_filter('cron_schedules', [$this, 'add_cron_interval']);
        add_action('my_hourly_event', [$this, 'update_db_hourly']);

        if (!wp_next_scheduled('my_hourly_event')) {
            wp_schedule_event(time(), '30_minutes', 'my_hourly_event');
        }
    }
    public function add_cron_interval($schedules)
    {
        $schedules['30_minutes'] = [
            'interval' => 30 * 60,
            'display' => esc_html__('Every 30 Minutes'),
        ];
        return $schedules;
    }

    /*
    public static function deactivate() {
        wp_clear_scheduled_hook('my_hourly_event');
    }*/

    public function update_db_hourly()
    {
        error_log('Cronjob gestartet!');

        global $wpdb;
        $sql =
            'SELECT post_id FROM ml_postmeta WHERE post_id IN (SELECT ID FROM ml_posts WHERE post_type = "cb_timeframe" AND post_status IN ("confirmed","canceled","pending")) AND meta_key = "repetition-end" AND meta_value < (UNIX_TIMESTAMP()-(60*60*24*12*7))';

        $results = $wpdb->get_results($sql);

        foreach ($results as $result) {
            wp_delete_post($result->post_id, true);
        }

        $sql =
            "SELECT post_id FROM ml_postmeta WHERE  meta_key = 'repetition-end' AND meta_value < (UNIX_TIMESTAMP()-(60*60*24*365)) AND post_id IN (SELECT post_id FROM ml_postmeta WHERE meta_key = 'type' AND meta_value = 2)";

        $results = $wpdb->get_results($sql);

        foreach ($results as $result) {
            wp_delete_post($result->post_id, true);
        }
        /*
        $sql =
            'SELECT post_id, ml_posts.post_author FROM ml_postmeta LEFT JOIN ml_posts ON ml_posts.ID = ml_postmeta.post_id WHERE post_id IN (SELECT ID FROM ml_posts WHERE post_type = "cb_timeframe" AND post_status IN ("confirmed","pending")) AND meta_key = "repetition-end" AND meta_value < UNIX_TIMESTAMP()';

        $results = $wpdb->get_results($sql);
        foreach ($results as $result) {
            if (get_post_meta($result->post_id, 'email_sended', true) !== '1') {
                $booking = new Booking($result->post_id, $result->post_author);
                $to = get_userdata($result->post_author)->user_email;
                $subject = 'Main-Lastenrad - Deine Buchung';
                $body =
                    'Hallo ' .
                    get_user_meta($result->post_author, 'first_name', true) .
                    ',<br >
          <br >
          Du bist mit ' .
                    $booking->itemName .
                    ' unterwegs gewesen. Sollte dir etwas aufgefallen sein, das nicht in Ordnung war, schreib bitte an service@main-lastenrad.de<br >
          Das Leihen ist kostenlos. Leider geht immer mal wieder etwas kaputt. Für Reparaturen und die Wartung der Räder sind wir auf Spenden angewiesen.<br >
          Unterstütze uns bitte mit einer Spende, egal wie groß oder klein, über <a href="www.betterplace.org/p92718" title="Jetzt spenden für „Main-Lastenrad - Kostenloser Lastenradverleih in Frankfurt und Offenbach“ auf betterplace.org!" target="_blank" >www.betterplace.org/p92718</a><br><br> oder direkt an den<br >
          <br >
          <strong>VCD Hessen e. V.</strong><br >
          IBAN DE27 5009 0500 0000 9532 40 <br >
          BIC GENODEF1S12 Sparda Hessen eG <br >
          Stichwort: Spende Main-Lastenrad<br >
          <br >
          <strong>Standort:</strong>     ' .
                    $booking->locationName .
                    '<br>
          <strong>Artikel:</strong>     ' .
                    $booking->itemName .
                    '<br>
          <strong>Ausleihdatum:</strong>     ' .
                    $booking->dateStart .
                    '<br>
          <strong>Buchungsdauer:</strong>     ' .
                    $booking->buchungsDauer .
                    '<br>
          <br>
          <u>Die Spende ist steuerlich abzugsfähig!</u><br >
          <br >
          Wird eine Spendenquittung gewünscht bitte die Adresse angeben.<br >
          <br >
          Vielen Dank, das Team.<br >
		      <a title="Jetzt spenden für „Main-Lastenrad - Kostenloser Lastenradverleih in Frankfurt und Offenbach“ auf betterplace.org!" target="_blank" href="https://www.betterplace.org/de/projects/92718-main-lastenrad-kostenloser-lastenradverleih-in-frankfurt-und-offenbach?utm_campaign=donate_btn&amp;utm_content=project%2392718&amp;utm_medium=external_banner&amp;utm_source=projects"><img style="border:0px" alt="Jetzt Spenden! Das Spendenformular wird von betterplace.org bereit gestellt." width="160" height="100" src="https://betterplace-assets.betterplace.org/static-images/projects/donation-button-de.png"/></a>';
                $headers = ['Content-Type: text/html; charset=UTF-8'];
                $headers[] =
                    'From: MAIN-LASTENRAD.DE <buchung@main-lastenrad.de>';

                wp_mail($to, $subject, $body, $headers);

                add_post_meta($result->post_id, 'email_sended', 1, true);
            }
        }*/
    }
}
