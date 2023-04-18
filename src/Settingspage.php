<?php

namespace CommonbookingsAdditionalFeatures;

class Settingspage
{
    private $commonbookings_additional_features_options;

    public function __construct()
    {
        add_action('admin_menu', [
            $this,
            'commonbookings_additional_features_add_page',
        ]);
        add_action('admin_init', [
            $this,
            'commonbookings_additional_features_page_init',
        ]);
    }

    public function commonbookings_additional_features_add_page()
    {
        add_menu_page(
            'CB Additional Features', // page_title
            'CB Additional Features', // menu_title
            'manage_options', // capability
            'cbadf', // menu_slug
            '', // function
            plugin_dir_url(__DIR__) . 'assets/images/icon.png' // icon_url
            // position
        );

        add_submenu_page(
            'cbadf', //parent slug
            'Sidebar', // page_title
            'Sidebar', // menu_title
            'manage_options', // capability
            'cbadf', //menu_slug
            [$this, 'commonbookings_additional_features_create_page'] // function
        );
        add_submenu_page(
            'cbadf', //parent slug
            'Feiertage', // page_title
            'Feiertage', // menu_title
            'manage_options', // capability
            'cbadf-timeframe', //menu_slug
            [$this, 'common_bookings_additional_features_create_timeframe_page'] // function
        );
    }

    public function commonbookings_additional_features_create_page()
    {
        $this->commonbookings_additional_features_options = get_option(
            'commonbookings_additional_features_option_name'
        ); ?>

<div class="wrap">
    <h2>CommonBookings Additional Features</h2>
    <hr>
    <p></p>
    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('commonbookings_additional_features_option_group');
        do_settings_sections('commonbookings-additional-features-admin');
        submit_button();?>
    </form>
</div>
<?php
    }

    public function commonbookings_additional_features_page_init()
    {
        register_setting(
            'commonbookings_additional_features_option_group', // option_group
            'commonbookings_additional_features_option_name', // option_name
            [$this, 'commonbookings_additional_features_sanitize'] // sanitize_callback
        );

        add_settings_section(
            'commonbookings_additional_features_setting_section', // id
            'Sidebar', // title
            [$this, 'commonbookings_additional_features_section_info'], // callback
            'commonbookings-additional-features-admin' // page
        );

        add_settings_field(
            'werbung_0', // id
            'Werbung', // title
            [$this, 'werbung_0_callback'], // callback
            'commonbookings-additional-features-admin', // page
            'commonbookings_additional_features_setting_section' // section
        );

        add_settings_field(
            'buchung_0', // id
            'meine Buchungen', // title
            [$this, 'buchung_0_callback'], // callback
            'commonbookings-additional-features-admin', // page
            'commonbookings_additional_features_setting_section' // section
        );
        add_settings_field(
            'buchung_historie_1', // id
            'Buchungshistorie', // title
            [$this, 'buchung_historie_1_callback'], // callback
            'commonbookings-additional-features-admin', // page
            'commonbookings_additional_features_setting_section' // section
        );

        add_settings_field(
            'profil_2', // id
            'mein Profil', // title
            [$this, 'profil_2_callback'], // callback
            'commonbookings-additional-features-admin', // page
            'commonbookings_additional_features_setting_section' // section
        );
        add_settings_field(
            'konto_3', // id
            'mein Konto', // title
            [$this, 'konto_3_callback'], // callback
            'commonbookings-additional-features-admin', // page
            'commonbookings_additional_features_setting_section' // section
        );
    }

    public function commonbookings_additional_features_sanitize($input)
    {
        $sanitary_values = [];
        if (isset($input['werbung_0'])) {
            $sanitary_values['werbung_0'] = $input['werbung_0'];
        }
        if (isset($input['buchung_0'])) {
            $sanitary_values['buchung_0'] = $input['buchung_0'];
        }
        if (isset($input['buchung_historie_1'])) {
            $sanitary_values['buchung_historie_1'] =
                $input['buchung_historie_1'];
        }
        if (isset($input['profil_2'])) {
            $sanitary_values['profil_2'] = $input['profil_2'];
        }
        if (isset($input['konto_3'])) {
            $sanitary_values['konto_3'] = $input['konto_3'];
        }
        return $sanitary_values;
    }

    public function commonbookings_additional_features_section_info()
    {
    }

    public function werbung_0_callback()
    {
        printf(
            '<textarea class="large-text" rows="5" name="commonbookings_additional_features_option_name[werbung_0]" id="werbung_0">%s</textarea>',
            isset(
                $this->commonbookings_additional_features_options['werbung_0']
            )
                ? esc_attr(
                    $this->commonbookings_additional_features_options[
                        'werbung_0'
                    ]
                )
                : ''
        );
    }

    public function common_bookings_additional_features_create_timeframe_page()
    {
        if (isset($_SESSION['error']) && $_SESSION['error']) {
            $error = $_SESSION['error'];
            session_destroy();
        } else {
            $error = '';
        }
        if (isset($_SESSION['success']) && $_SESSION['success']) {
            $success = $_SESSION['success'];
            session_destroy();
        } else {
            $success = '';
        }
        $args = [
            'numberposts' => -1,
            'post_type' => 'cb_timeframe',
        ];
        $posts = get_posts($args);
        $timeframes = [];
        foreach ($posts as $post) {
            $meta = get_post_meta($post->ID);
            if (
                $meta['repetition-end'][0] > time() &&
                $meta['repetition-start'][0] < time() &&
                $meta['type'][0] == '2'
            ) {
                $timeframes[] = [
                    'id' => $post->ID,
                    'locationName' => get_the_title($meta['location-id'][0]),
                ];
            }
        }

        echo Plugin::template()->render('holidays.html.twig', [
            'admin_post_url' => admin_url('admin-post.php'),
            'submit' => get_submit_button('Feiertage hinzufügen'),
            'nonce_field' => wp_nonce_field(
                'update_timeframe',
                'timeframe_nonce',
                false
            ),
            'timeframes' => $timeframes,
            'error' => $error,
            'success' => $success,
        ]);
    }

    public function additional_create_sidebar_page()
    {
        $this->additional_options = get_option('additional_option_name'); ?>

<div class="wrap">
    <h2>Sidebar</h2>
    <hr>
    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('additional_option_group');
        do_settings_sections('additional-admin');
        submit_button();?>
    </form>
</div>
<?php
    }

    public function buchung_0_callback()
    {
        ?>
<p>Die Seite auf der der Shortcode "[cbaf_bookings]" aufgerufen wird. Dies gibt eine Übersicht über die getätigten
    Buchungen.</p>
<select name="commonbookings_additional_features_option_name[buchung_0]" id="buchung_0">
    <?php
    $args = [
        'sort_order' => 'asc',
        'sort_column' => 'post_title',
        'hierarchical' => 1,
        'exclude' => '',
        'include' => '',
        'meta_key' => '',
        'meta_value' => '',
        'authors' => '',
        'child_of' => 0,
        'parent' => -1,
        'exclude_tree' => '',
        'number' => '',
        'offset' => 0,
        'post_type' => 'page',
        'post_status' => 'publish',
    ];
    $pages = get_pages($args);
    foreach ($pages as $page) {
        $selected =
            isset($this->commonbookings_additional_features_options) &&
            $this->commonbookings_additional_features_options['buchung_0'] ==
                $page->ID
                ? 'selected'
                : ''; ?>
    <option value="<?php echo $page->ID; ?>" <?php echo $selected; ?>><?php echo get_the_title(
    $page->ID
); ?>
    </option>
    <?php
    }?>

</select> <?php
    }

    public function buchung_historie_1_callback()
    {
        ?>
<p>Die Seite auf der der Shortcode "[cbaf_historie_table]" aufgerufen wird. Dies gibt eine Übersicht über die vergangene
    Buchungen.</p>
<select name="commonbookings_additional_features_option_name[buchung_historie_1]" id="buchung_historie_1">
    <?php
    $args = [
        'sort_order' => 'asc',
        'sort_column' => 'post_title',
        'hierarchical' => 1,
        'exclude' => '',
        'include' => '',
        'meta_key' => '',
        'meta_value' => '',
        'authors' => '',
        'child_of' => 0,
        'parent' => -1,
        'exclude_tree' => '',
        'number' => '',
        'offset' => 0,
        'post_type' => 'page',
        'post_status' => 'publish',
    ];
    $pages = get_pages($args);
    foreach ($pages as $page) {
        $selected =
            isset($this->commonbookings_additional_features_options) &&
            $this->commonbookings_additional_features_options[
                'buchung_historie_1'
            ] == $page->ID
                ? 'selected'
                : ''; ?>
    <option value="<?php echo $page->ID; ?>" <?php echo $selected; ?>><?php echo get_the_title(
    $page->ID
); ?>
    </option>
    <?php
    }?>

</select> <?php
    }
    public function profil_2_callback()
    {
        ?>
<p>Falls ein Plugin die Profilbearbeitung übernimmt, kann hier die Seite eingestellt werden auf der die Profildaten
    geändert werden können.</p>
<select name="commonbookings_additional_features_option_name[profil_2]" id="profil_2">
    <?php $selected =
        isset($this->commonbookings_additional_features_options) &&
        $this->commonbookings_additional_features_options['profil_2'] == '-1'
            ? 'selected'
            : ''; ?>
    <option value="-1" <?php echo $selected; ?>>Standard (WP-Profil)
    </option>
    <?php
    $args = [
        'sort_order' => 'asc',
        'sort_column' => 'post_title',
        'hierarchical' => 1,
        'exclude' => '',
        'include' => '',
        'meta_key' => '',
        'meta_value' => '',
        'authors' => '',
        'child_of' => 0,
        'parent' => -1,
        'exclude_tree' => '',
        'number' => '',
        'offset' => 0,
        'post_type' => 'page',
        'post_status' => 'publish',
    ];
    $pages = get_pages($args);
    foreach ($pages as $page) {
        $selected =
            isset($this->commonbookings_additional_features_options) &&
            $this->commonbookings_additional_features_options['profil_2'] ==
                $page->ID
                ? 'selected'
                : ''; ?>
    <option value="<?php echo $page->ID; ?>" <?php echo $selected; ?>><?php echo get_the_title(
    $page->ID
); ?>
    </option>
    <?php
    }?>

</select> <?php
    }

    public function konto_3_callback()
    {
        ?>
<p>Falls ein Plugin die Profilbearbeitung übernimmt, kann hier die Seite eingestellt werden auf der die Profildaten
geändert werden können.</p>
<select name="commonbookings_additional_features_option_name[konto_3]" id="konto_3">
<?php $selected =
    isset($this->commonbookings_additional_features_options) &&
    $this->commonbookings_additional_features_options['konto_3'] == '-1'
        ? 'selected'
        : ''; ?>
<option value="-1" <?php echo $selected; ?>>ausblenden
</option>
<?php
$args = [
    'sort_order' => 'asc',
    'sort_column' => 'post_title',
    'hierarchical' => 1,
    'exclude' => '',
    'include' => '',
    'meta_key' => '',
    'meta_value' => '',
    'authors' => '',
    'child_of' => 0,
    'parent' => -1,
    'exclude_tree' => '',
    'number' => '',
    'offset' => 0,
    'post_type' => 'page',
    'post_status' => 'publish',
];
$pages = get_pages($args);
foreach ($pages as $page) {
    $selected =
        isset($this->commonbookings_additional_features_options) &&
        $this->commonbookings_additional_features_options['konto_3'] ==
            $page->ID
            ? 'selected'
            : ''; ?>
<option value="<?php echo $page->ID; ?>" <?php echo $selected; ?>><?php echo get_the_title(
    $page->ID
); ?>
</option>
<?php
}?>

</select> <?php
    }
}

/*
 * Retrieve this value with:
 * $commonbookings_additional_features_options = get_option( 'commonbookings_additional_features_option_name' ); // Array of All Options
 * $werbung_0 = $commonbookings_additional_features_options['werbung_0']; // Werbung
 */
