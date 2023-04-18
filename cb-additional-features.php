<?php
/*
Plugin Name: Commons Booking Additional Feature
Description: Dieses Plugin erweitert Common Bookings um die Funktion der Einpflege von Urlaubstagen. Fügt eine andere Buchungshistorie und eine Sidebar hinzu.
Author: Dirk Drutschmann
Version: 0.1.4
*/

use CommonbookingsAdditionalFeatures\Plugin;

if (!defined('WPINC')) {
    die();
}

require 'vendor/autoload.php';

$plugin = new Plugin();

$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://update.dirkdrutschmann.de/cb-additional-features.json',
    __FILE__, //Full path to the main plugin file or functions.php.
    'cb-additional-features'
);