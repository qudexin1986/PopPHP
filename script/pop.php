#!/usr/bin/php
<?php
/**
 * Pop PHP Framework PHP CLI script
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 * Possible arguments
 *
 * -b --build projectname    Build a project based on the project configuration file
 * -c --check                Check the current configuration for required dependencies
 * -h --help                 Display this help
 * -m --map folder file.php  Create a class map file from the source folder and save to the output file
 * -v --version              Display version of Pop PHP Framework
 *
 */

require_once __DIR__ . '/../public/bootstrap.php';

use Pop\Project,
    Pop\Loader\Classmap,
    Pop\Version;

// Write header
echo PHP_EOL . 'Pop PHP Framework CLI script' . PHP_EOL;
echo '============================' . PHP_EOL . PHP_EOL;

if (!empty($argv[1])) {
    // Check for version
    if (($argv[1] == '-v') || ($argv[1] == '--version')) {
        echo 'Version Check' . PHP_EOL;
        echo '-------------' . PHP_EOL;
        echo 'Installed: ' . Version::getVersion() . PHP_EOL;
        echo 'Latest Available: ' . Version::getLatest() . PHP_EOL;
    // Else, display help
    } else if (($argv[1] == '-h') || ($argv[1] == '--help')) {
        echo 'Help' . PHP_EOL;
        echo '----' . PHP_EOL;
        echo ' -b --build ProjectName    Build a project based on the files in the \'config\' folder' . PHP_EOL;
        echo ' -c --check                Check the current configuration for required dependencies' . PHP_EOL;
        echo ' -h --help                 Display this help' . PHP_EOL;
        echo ' -m --map folder file.php  Create a class map file from the source folder and save to the output file' . PHP_EOL;
        echo ' -v --version              Display version of Pop PHP Framework and latest available' . PHP_EOL . PHP_EOL;
    // Else, check dependencies
    } else if (($argv[1] == '-c') || ($argv[1] == '--check')) {
        echo 'Dependencies Check' . PHP_EOL;
        echo '------------------' . PHP_EOL;
        echo Version::check() . PHP_EOL;
    // Else, generate class map
    } else if (($argv[1] == '-m') || ($argv[1] == '--map')) {
        echo 'Generate Class Map File' . PHP_EOL;
        echo '-----------------------' . PHP_EOL;
        // Check if the source folder and output file arguments were passed
        if (empty($argv[2]) || empty($argv[3])) {
            echo Project::cliError(1);
        // Else, check if the source folder exists
        } else if (!file_exists($argv[2])) {
            echo Project::cliError(2);
        // Else, check if the output file ends in '.php'
        } else if (strtolower(substr($argv[3], -4)) != '.php') {
            echo Project::cliError(3);
        // Else, generate the class map file
        } else {
            echo 'Creating class map file \'' . $argv[3] . '\' from source folder \'' . $argv[2] . '\'' . PHP_EOL;
            Classmap::generate($argv[2], $argv[3]);
            echo 'Done.' . PHP_EOL . PHP_EOL;
        }
    // Else, build project
    } else if (($argv[1] == '-b') || ($argv[1] == '--build')) {
        echo 'Build Project' . PHP_EOL;
        echo '-------------' . PHP_EOL;
        // Check if the $name argument was passed
        if (empty($argv[2])) {
            echo Project::cliError(4);
        // Else, run the build process
        } else {
            Project::build($argv[2]);
        }
    } else {
        echo Project::cliError(5, $argv[1]);
    }
} else {
    echo Project::cliError(6);
}
