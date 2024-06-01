<?php
/**
 * Version metadata for the local_etutorsync plugin.
 *
 * @package   local_etutorsync
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 20240601;
$plugin->requires = 2023100902.00; // Moodle 4.0
$plugin->component = 'local_etutorsync';
$plugin->maturity = MATURITY_BETA; // see https://moodledev.io/docs/apis/commonfiles/version.php#maturity

$plugin->dependencies = [
	'qtype_coderunner' => 2023090800,
	'qbank_customfields' => 2023100900
];