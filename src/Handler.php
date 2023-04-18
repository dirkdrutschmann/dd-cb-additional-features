<?php

namespace CommonbookingsAdditionalFeatures;

class Handler
{
    public function __construct()
    {
        add_action('admin_post_update_timeframe', [$this, 'logitout']);
        add_action('admin_post_add_reparature', [$this, 'reparature']);
        add_action('admin_post_delete_holidays', [$this, 'delete_holidays']);
        add_action('admin_post_delete_timeframes', [
            $this,
            'delete_timeframes',
        ]);
        add_action('admin_post_delete_options_cb_additional', [
            $this,
            'delete_options_cb_additional',
        ]);
        add_action('admin_post_update_statistik', [$this, 'update_statistik']);
    }

    public function logitout()
    {

        if (
            !isset($_POST['timeframe_nonce']) ||
            !wp_verify_nonce($_POST['timeframe_nonce'], 'update_timeframe')
        ) {
            $_SESSION['error'] = "Leider ist ein Fehler aufgetreten, bitte Formular erneut senden!";
            exit();
        } else {
            $result = ["success" => 0, "exists" => 0];
            foreach ($_POST['timeframes'] as $timeframe) {
                $results = Holiday::addHolidays($timeframe);
                $result["success"] += $results['success'];
                $result["exists"] += $results['exists'];
            }
            $retunString  = "";
            if($result["success"] > 0){
                $retunString .= $result["success"]." Feiertage wurden erfolgreich eingetragen. ";
            }
            if($result["exists"] > 0){
                $retunString .= $result["exists"]." Feiertage bereits vorhanden. ";
            }
            $_SESSION['success'] = $retunString;
            wp_redirect(admin_url('admin.php?page=cbadf-timeframe'));
        }
    }

    public function reparature()
    {
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        error_log('repature!');
        if (isset($_POST['start']) && isset($_POST['end'])) {
            $items = [];
            $comment = '';
            if (isset($_POST['info'])) {
                if (!empty($_POST['info'])) {
                    $comment = $_POST['info'];
                }
            }
            global $wpdb;
            $cbItems = $wpdb->get_results(
                'SELECT post_title , ID from ' .
                    $table_prefix .
                    "posts WHERE post_type = 'cb_item' order by post_title ASC"
            );
            foreach ($cbItems as $item) {
                if (isset($_POST[$item->ID])) {
                    $items[] = $item->ID;
                }
            }
            error_log(print_r($items, 1));
            $cbLocation = $wpdb->get_results(
                'SELECT post_title , ID from ' .
                    $table_prefix .
                    "posts WHERE post_type = 'cb_location' order by post_title ASC"
            );
            $bookingArray = [];
            $deleteBooking = [];
            $sql =
                'SELECT * FROM ' .
                $table_prefix .
                "posts WHERE `post_type`= 'cb_timeframe' and post_title LIKE '%Buchung%' and post_status = 'confirmed'";
            $results = $wpdb->get_results($sql);
            foreach ($results as $result) {
                $bookingArray[] = new Booking(
                    $result->ID,
                    $result->post_author
                );
            }

            foreach ($items as $item_id) {
                $location_id = '';
                foreach ($cbLocation as $location) {
                    $meta_value = $wpdb->get_results(
                        'SELECT meta_value from ' .
                            $table_prefix .
                            "postmeta WHERE meta_key = 'item-id' AND post_id IN (SELECT post_id FROM " .
                            $table_prefix .
                            'postmeta WHERE post_id IN (SELECT ID FROM ' .
                            $table_prefix .
                            "posts WHERE post_type = 'cb_timeframe' AND post_status IN ('publish')) AND meta_key = 'location-id' AND meta_value = '" .
                            $location->ID .
                            "')"
                    );
                    if (isset($meta_value[0]->meta_value)) {
                        if ($item_id == $meta_value[0]->meta_value) {
                            $location_id = $location->ID;
                        }
                    }
                }

                $my_page = [
                    'post_title' =>
                        'Reparatur ' . $_POST['start'] . ' - ' . $_POST['end'], // Titel
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_type' => 'cb_timeframe',
                    'post_author' => get_current_user_id(),
                ];
                // Insert the post into the database
                $id = wp_insert_post($my_page, true);

                add_post_meta($id, 'type', 5);
                add_post_meta($id, 'timeframe-max-days', 3);
                if (!empty($comment)) {
                    add_post_meta($id, 'comment', $comment);
                }

                add_post_meta($id, 'show-booking-codes', 'on');
                add_post_meta($id, 'create-booking-codes', 'on');
                add_post_meta($id, 'location-id', $location_id);
                add_post_meta($id, 'item-id', $item_id);
                add_post_meta($id, 'grid', 0);
                add_post_meta($id, 'full-day', 'on');
                add_post_meta(
                    $id,
                    'repetition-start',
                    strtotime($_POST['start'])
                );
                add_post_meta($id, 'repetition-end', strtotime($_POST['end']));
                add_post_meta(
                    $id,
                    'prevent_delete_meta_movetotrash ',
                    'cdcdf5e73e'
                );
                foreach ($bookingArray as $booking) {
                    $between = false;
                    if (
                        strtotime($_POST['start']) <=
                            strtotime($booking->dateStart) &&
                        strtotime($_POST['end']) >=
                            strtotime($booking->dateStart)
                    ) {
                        $between = true;
                    } elseif (
                        strtotime($_POST['start']) <=
                            strtotime($booking->dateEnd) &&
                        strtotime($_POST['end']) >= strtotime($booking->dateEnd)
                    ) {
                        $between = true;
                    }
                    if ($between) {
                        if (
                            $booking->itemID == $item_id &&
                            $booking->locationID == $location_id
                        ) {
                            $deleteBooking[] = $booking;
                        }
                    }
                }
            }
        }

        foreach ($deleteBooking as $booking) {
            $to = get_userdata($booking->userId)->user_email;
            $subject =
                get_bloginfo('name') .
                ' - Stornierung deiner Buchung - ' .
                $booking->itemName .
                ' - ' .
                $booking->locationName;
            $body =
                'Hallo ' .
                get_user_meta($booking->userId, 'first_name', true) .
                ',<br >
		<br >
		leider müssen wir dir mitteilen, dass wir deine Buchung<br >
		<br >
		Artikel: ' .
                $booking->itemName .
                '<br>
		Standort: ' .
                $booking->locationName .
                '<br><br>
		Abholung: ' .
                $booking->dateStart .
                '<br>
		Rückgabe: ' .
                $booking->dateEnd .
                '<br><br>
		stornieren müssen.<br><br>

		Das Fahrrad benötigt eine Reparatur und ist dehalb leider nicht ausleihbar.';
            if (!empty($_POST['info'])) {
                $body .= '<br><br>Reparaturinfo: ' . $_POST['info'];
            }

            $body .= '<br><br>Vielen Dank, das Team.<br >';
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            $headers[] =
                'From: ' .
                get_bloginfo('name') .
                '<' .
                get_bloginfo('admin_email') .
                '>';
            $mail = get_post_meta(
                $booking->locationID,
                '_cb_location_email',
                true
            );
            $mails = explode(';', $mail);
            foreach ($mails as $email) {
                $headers[] = 'BCC: ' . $email;
            }

            wp_mail($to, $subject, $body, $headers);
            $cbLocation = $wpdb->get_results(
                'UPDATE ' .
                    $table_prefix .
                    "posts SET `post_status` ='canceled'  WHERE `ID` = " .
                    $booking->bookingID
            );
            update_post_meta($booking->bookingID, 'type', 6);
        }
        wp_redirect(admin_url('admin.php?page=cbadf-reparatur'));
    }

    public function delete_holidays()
    {
        error_log('holiday delete');
        global $wpdb;
        $sql =
            'SELECT post_id FROM ml_postmeta WHERE post_id IN (SELECT ID FROM ml_posts WHERE post_type = "cb_timeframe" AND post_status IN ("publish")) AND meta_key = "type" AND meta_value = "3"';

        $results = $wpdb->get_results($sql);

        foreach ($results as $result) {
            wp_delete_post($result->post_id, true);
        }
        wp_redirect(
            admin_url(
                'options-general.php?page=common-bookings-additional-features'
            )
        );
    }

    public function delete_timeframes()
    {
        error_log('timeframe delete');
        global $wpdb;
        $sql =
            'SELECT post_id FROM ml_postmeta WHERE post_id IN (SELECT ID FROM ml_posts WHERE post_type = "cb_timeframe" AND post_status IN ("publish")) AND meta_key = "type" AND meta_value = "2"';

        $results = $wpdb->get_results($sql);

        foreach ($results as $result) {
            wp_delete_post($result->post_id, true);
        }

        wp_redirect(
            admin_url(
                'options-general.php?page=common-bookings-additional-features'
            )
        );
    }

    public function delete_options_cb_additional()
    {
        delete_option('common_bookings_additional_features_option_name');
        wp_redirect(
            admin_url(
                'options-general.php?page=common-bookings-additional-features'
            )
        );
    }
    public function update_statistik()
    {
        global $wpdb;
        //Datenbank generieren wenn noch nicht exisitiert

        $wpdb->query(
            $wpdb->prepare(
                'CREATE TABLE IF NOT EXISTS ' .
                    DB_NAME .
                    '.`ml_cb_statistik` ( `year` INT NOT NULL , `month` INT NOT NULL , `day` INT NOT NULL , `item` INT NOT NULL , `location` INT NOT NULL , `booked` INT NOT NULL , `canceled` INT NOT NULL ) ENGINE = InnoDB COMMENT = %s',
                'Statistik'
            )
        );

        $bookingArray = [];
        $sql =
            "SELECT * FROM ml_posts WHERE `post_type`= 'cb_timeframe' and post_title LIKE '%Buchung%' and post_status = 'confirmed'";
        $results = $wpdb->get_results($sql);
        foreach ($results as $result) {
            $bookingArray[] = new Booking($result->ID, $result->post_author);
        }

        $bookings = [
            8468,
            8464,
            8443,
            8438,
            8436,
            8434,
            8431,
            8429,
            8427,
            8425,
            8423,
            8404,
            8402,
            8389,
            8385,
            8383,
            8378,
            8376,
            8374,
            8368,
            8366,
            8364,
            8347,
            8345,
            8343,
            8322,
            8311,
            8288,
            8286,
            8265,
            8263,
            8247,
            8239,
            8231,
            8229,
            8219,
            8215,
            8213,
            8211,
            8203,
            8195,
            8194,
            8193,
            8192,
            8190,
            8189,
            8186,
            8181,
            8180,
            8178,
            8177,
            8176,
            8175,
            8173,
            8172,
            8171,
            8169,
            8168,
            8165,
            8163,
            8162,
            8160,
            8159,
            8158,
            8157,
            8156,
            8155,
            8153,
            8151,
            8149,
            8144,
            8143,
            8141,
            8140,
            8139,
            8137,
            8136,
            8135,
            8130,
            8129,
            8128,
            8124,
            8122,
            8121,
            8118,
            8117,
            8116,
            8115,
            8114,
            8113,
            8112,
            8110,
            8107,
            8106,
            8103,
            8102,
        ];
        foreach ($bookingArray as $booking) {
            if (in_array($booking->bookingID, $bookings)) {
                $date = date_create(".$booking->dateStart.");
                $dater = $date->format('Y-m-d');

                $Codes = $wpdb->get_results(
                    "SELECT code from ml_cb_bookingcodes WHERE `date` = '" .
                        $dater .
                        "' and `location` = '" .
                        $booking->locationID .
                        "' and `item` = '" .
                        $booking->itemID .
                        "'"
                );
                $bookingCode = $Codes[0]->code;

                $to = 'buchung@main-lastenrad.de'; //get_userdata($booking->userId)->user_email;
                $subject =
                    'Deine Buchung von ' .
                    $booking->itemName .
                    ' am Standort ' .
                    $booking->locationName .
                    ' von ' .
                    $booking->dateStart .
                    ' bis ' .
                    $booking->dateEnd;
                $body =
                    'Hallo ' .
                    get_user_meta($booking->userId, 'first_name', true) .
                    ',<br >
                    <br >
                    vielen Dank für deine Buchung von ' .
                    $booking->itemName .
                    ' von ' .
                    $booking->dateStart .
                    ' bis ' .
                    $booking->dateEnd .
                    '.<br>
                    <br >
                    Abholung: <strong>' .
                    $booking->dateStart .
                    '</strong><br>
                    Rückgabe: <strong>' .
                    $booking->dateEnd .
                    '</strong><br><br>
                    ' .
                    get_post_meta(
                        $booking->locationID,
                        '_cb_location_pickupinstructions',
                        true
                    ) .
                    '<br><br>
                    Dein Buchungscode lautet: ' .
                    $bookingCode .
                    '<br>
                    <br>
                    <strong>Standort</strong><br>
                    ' .
                    $booking->locationName .
                    '<br>
                    ' .
                    $booking->locationStreet .
                    '<br>
                    ' .
                    $booking->locationPostcode .
                    ' ' .
                    $booking->locationCity .
                    '<br>
                    ' .
                    get_post_meta(
                        $booking->locationID,
                        '_cb_location_contact',
                        true
                    ) .
                    '<br>
                    <br>
                    <strong>Klicke hier, um deine Buchung zu sehen und zu stornieren: <a href="' .
                    $booking->link .
                    '">Link zu deiner Buchung</a></strong><br>
                    <br>
                    <strong>Hinweis:</strong> Du musst eingeloggt sein, um deine Buchung sehen zu können.<br>
                    Wenn dich der Link zur Hauptseite führt,
                    logge dich zunächst ein und klicke dann erneut auf den Link.<br>
                    <br>
            
            
                    <strong>Deine Daten:</strong><br>
                    Stelle bitte sicher, dass Du hier die gleichen Angaben wie aus Deinem Ausweis verwendest, 
                    sonst kannst Du das Lastenrad nicht ausleihen. 
                    Login: ' .
                    get_user_meta($booking->userId, 'nickname', true) .
                    '<br>
                    Name: ' .
                    get_user_meta($booking->userId, 'first_name', true) .
                    ' ' .
                    get_user_meta($booking->userId, 'last_name', true) .
                    '<br>
                    <br>
                    Vielen Dank, das Team von Main-Lastenrad.de';

                $headers = ['Content-Type: text/html; charset=UTF-8'];
                $headers[] =
                    'From: MAIN-LASTENRAD.DE <buchung@main-lastenrad.de>';
                $to = get_userdata($booking->userId)->user_email;
                $mail = get_post_meta(
                    $booking->locationID,
                    '_cb_location_email',
                    true
                );
                $mails = explode(',', $mail);
                foreach ($mails as $email) {
                    $headers[] = 'BCC: ' . $email;
                }
                $headers[] = 'BCC: lastenrad@dirkdrutschmann.de';

                wp_mail($to, $subject, $body, $headers);
            }
        }
        wp_redirect(
            admin_url(
                'options-general.php?page=common-bookings-additional-features-statistik'
            )
        );
    }
}
