<?php

namespace CommonbookingsAdditionalFeatures;

if (!defined('WPINC')) {
    die();
}

class Holiday
{
    public static function addHolidays($timeframe)
    {
        $meta = get_post_meta($timeframe);

        $item = $meta['item-id'][0];
        $location = $meta['location-id'][0];
        $startdate = $meta['repetition-start'][0];
        $first_year = date('Y', $startdate);
        $enddate = $meta['repetition-end'][0];
        $second_year = date('Y', $enddate);
        $year = [];
        if ($first_year == $second_year) {
            $year[] = $first_year;
        } else {
            $year[] = $first_year;
            $year[] = $second_year;
        }
        $region = Region::get(
            get_post_meta($location)['_cb_location_postcode'][0]
        );
        $result = ['success' => 0, 'exists' => 0];
        foreach ($year as $single_year) {
            $content = json_decode(
                file_get_contents(
                    'https://feiertage-api.de/api/?jahr=' .
                        $single_year .
                        '&nur_land=' .
                        $region
                ),
                true
            );

            foreach ($content as $key => $feiertage) {
                foreach ($feiertage as $key2 => $day) {
                    if ($key2 == 'datum') {
                        if (
                            strtotime($day) >= $startdate &&
                            strtotime($day) <= $enddate
                        ) {
                            $args = [
                                'numberposts' => -1,
                                'post_type' => 'cb_timeframe',
                            ];
                            $posts = get_posts($args);
                            $found = false;
                            foreach ($posts as $post) {
                                $meta = get_post_meta($post->ID);
                                if (
                                    $meta['type'][0] == 3 &&
                                    $meta['repetition-start'][0] ==
                                        strtotime($day) &&
                                    $meta['location-id'][0] == $location &&
                                    $meta['item-id'][0] == $item
                                ) {
                                    $found = true;
                                }
                            }
                            if (!$found) {
                                $result['success']++;
                                Holiday::insertHoliday(
                                    $key . ' @ ' . get_the_title($location),
                                    $location,
                                    $item,
                                    strtotime($day)
                                );
                            } else {
                                $result['exists']++;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    public static function insertHoliday($title, $location_id, $item_id, $date)
    {
        $my_page = [
            'post_title' => $title, // Titel
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'cb_timeframe',
            'post_author' => get_current_user_id(),
        ];
        // Insert the post into the database
        $id = wp_insert_post($my_page, true);

        add_post_meta($id, 'type', 3);
        add_post_meta($id, 'timeframe-max-days', 2);
        add_post_meta($id, 'location-id', $location_id);
        add_post_meta($id, 'item-id', $item_id);
        add_post_meta($id, 'timeframe-repetition', 'norep');
        add_post_meta($id, 'full-day', 'on');
        add_post_meta($id, 'grid', 0);

        add_post_meta($id, 'repetition-start', $date);
        add_post_meta($id, 'repetition-end', $date);
        add_post_meta($id, 'prevent_delete_meta_movetotrash ', '9f29580b48');
    }
}
