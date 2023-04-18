<?php

namespace CommonbookingsAdditionalFeatures;

if (!defined('WPINC')) {
    die();
}

class Inventar
{
    public $bikename;
    public $bikeid;
    public $locationname;
    public $locationid;

    public function __construct(string $name, int $id, string $lname, int $lid)
    {
        $this->bikeid = $id;
        $this->bikename = $name;
        $this->locationname = $lname;
        $this->locationid = $lid;
    }
}

function insertTimeFrame(
    $title,
    $location_id,
    $item_id,
    $startdate,
    $enddate,
    $start = null,
    $end = null,
    $grid,
    $weekdays,
    $maxdays = '2'
) {
    $my_page = [
        'post_title' => $title, // Titel
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'cb_timeframe',
        'post_author' => get_current_user_id(),
    ];
    // Insert the post into the database
    $id = wp_insert_post($my_page, true);

    add_post_meta($id, 'type', 2);
    add_post_meta($id, 'timeframe-max-days', $maxdays);
    add_post_meta($id, 'timeframe-repetition', 'w');

    add_post_meta($id, 'show-booking-codes', 'on');
    add_post_meta($id, 'create-booking-codes', 'on');
    add_post_meta($id, 'location-id', $location_id);
    add_post_meta($id, 'item-id', $item_id);
    add_post_meta($id, 'weekdays', $weekdays);
    if ($grid == 0) {
        add_post_meta($id, 'grid', $grid);
        add_post_meta($id, 'full-day', 'on');
    } else {
        add_post_meta($id, 'grid', $grid);
        add_post_meta($id, 'start-time', $start);
        add_post_meta($id, 'end-time', $end);
    }
    generate(
        $id,
        strtotime($startdate),
        strtotime($enddate),
        $location_id,
        $item_id
    );
    add_post_meta($id, 'repetition-start', strtotime($startdate));
    add_post_meta($id, 'repetition-end', strtotime($enddate));
    add_post_meta($id, 'prevent_delete_meta_movetotrash ', '26b4fa0a51');
}



function generate($id, $start, $end, $location_id, $item_id)
{
    $codes = [
        'Lastenrad',
        'VCD',
        'autofrei',
        'klimaneutral',
        'kostenlos',
        'Car Sharing',
        'S-Bahn',
        'U-Bahn',
        'Energiereferat',
        'Klimaschutz',
        'Klimaschutzplan',
        'ohne Stau',
        'frische Luft',
        'schadstofffrei',
        'Radfahren',
        'Radlerin',
        'Frankfurt',
        'Radrennen',
        'Umzug',
        'Warentransport',
        'Stadt für Alle',
        'Pariser Klimaschutzabkommen',
        'CO2',
        'Umweltschutz',
        'Luftreinhaltung',
        'emmissionsfrei',
        'Grüngürtel',
        'Günthersburgpark',
        'Wallanlagen',
        'Taunusanlage',
        'Alleenring',
        'Frankenallee',
        'Berger Straße',
        'Leipziger Straße',
        'sattelfest',
        'Dynamo',
        'Scheibenbremse',
        'Schutzblech',
        'Fahrradschlauch',
        'Alu-Rahmen',
        'Rücklicht',
        'Radschnellweg',
        'Fahrradweg',
        'Schutzstreifen',
        'Bike Lane',
        'Velo',
        'Cargo Bike',
        'Bike-Night',
        'Critical Mass',
        'Alte Oper',
        'Zoo',
        'Palmengarten',
        'Schauspielhaus',
        'Main',
        'Gerbermühle',
        'Altstadt',
        'Bahnhofsviertel',
        'Bergen-Enkheim',
        'Berkersheim',
        'Bockenheim',
        'Bonames',
        'Bornheim',
        'Dornbusch',
        'Eckenheim',
        'Eschersheim',
        'Fechenheim',
        'Frankfurter Berg',
        'Gallus',
        'Ginnheim',
        'Griesheim',
        'Gutleutviertel',
        'Harheim',
        'Hausen',
        'Heddernheim',
        'Höchst',
        'Innenstadt',
        'Kalbach-Riedberg',
        'Nied',
        'Nieder-Erlenbach',
        'Nieder-Eschbach',
        'Niederrad',
        'Niederursel',
        'Nordend-Ost',
        'Nordend-West',
        'Oberrad',
        'Ostend',
        'Praunheim',
        'Preungesheim',
        'Riederwald',
        'Rödelheim',
        'Sachsenhausen-Nord',
        'Sachsenhausen-Süd',
        'Schwanheim',
        'Seckbach',
        'Sindlingen',
        'Sossenheim',
        'Unterliederbach',
        'Westend-Nord',
        'Westend-Süd',
        'Zeilsheim',
        'Arthur-von-Weinberg-Steg',
        'Carl-Ulrich-Brücke',
        'Kaiserleibrücke',
        'Staustufe Offenbach',
        'Osthafen',
        'Schmickbrücke',
        'Honsellbrücke',
        'Innenstadt',
        'Osthafenbrücke',
        'Deutschherrnbrücke',
        'City-Tunnel',
        'Flößerbrücke',
        'Ignatz-Bubis-Brücke',
        'Alte Brücke',
        'Eiserner Steg',
        'Untermainbrücke',
        'Holbeinsteg',
        'Friedensbrücke',
        'Westhafenbrücke',
        'Flusskrebssteg',
        'Main-Neckar-Brücke',
        'Alte Niederräder Brücke',
        'Neue Niederräder Brücke',
        'Europabrücke',
        'Staustufe Griesheim',
        'Schwanheimer Brücke',
        'Mainfähre Höchst',
        'Leunabrücke',
        'Sindlinger Mainbrücke',
        'Museumsufer',
        'Museum Angewandte Kunst',
        'Archäologisches Museum',
        'Deutsches Architekturmuseum',
        'Bibelhaus Erlebnismuseum',
        'Deutsches Museum für Kochkunst und Tafelkultur',
        'Deutsches Werbemuseum',
        'Dialogmuseum',
        'Dommuseum',
        'Experiminta',
        'Explora',
        'Feuerwehrmuseum',
        'Deutsches Filmmuseum',
        'Feldbahnmuseum',
        'Geldmuseum',
        'Goethe-Haus',
        'Hammermuseum',
        'Heimatmuseum Bergen-Enkheim',
        'Heimatmuseum Seckbach',
        'Historisches Museum',
        'Jüdisches Museum',
        'Karmeliterkloster',
        'Museum für Kommunikation',
        'Kriminalmuseum',
        'Liebieghaus',
        'Museum an der Kreuzkirche',
        'Museum Giersch',
        'Petrihaus',
        'Rententurm',
        'Deutsches Romantik-Museum',
        'Senckenberg Naturmuseum',
        'Steinhausen-Haus',
        'Technische Sammlung Hochhut',
        'Museum für Uhren',
        'Schmuck und Kunst',
        'Verkehrsmuseum',
        'Weltkulturen Museum',
    ];

    global $wpdb;

    $begin = new DateTime();
    $begin->setTimestamp($start);
    $ende = new DateTime();
    $ende->setTimestamp($end);
    $codeLength = count($codes) - 1;
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($begin, $interval, $ende);

    foreach ($period as $dt) {
        $wpdb->insert('ml_cb_bookingcodes', [
            'timeframe' => $id,
            'date' => $dt->format('Y-m-d'),
            'location' => $location_id,
            'item' => $item_id,
            'code' => $codes[rand(0, $codeLength)],
        ]);
    }
}
