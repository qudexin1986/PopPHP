<?php
/**
 * Pop PHP Framework
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
 * @category   Pop
 * @package    Pop_Project
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Project\Install;

use Pop\Code\Generator,
    Pop\Filter\String,
    Pop\Locale\Locale;

/**
 * @category   Pop
 * @package    Pop_Project
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class Base
{

    /**
     * Install the base folder and file structure
     *
     * @param Pop\Config $install
     * @return void
     */
    public static function install($install)
    {
        echo Locale::factory()->__('Creating base folder and file structure...') . PHP_EOL;

        $folders = array(
            $install->project->base,
            $install->project->base . '/config',
            $install->project->base . '/module',
            $install->project->base . '/module/' . $install->project->name,
            $install->project->base . '/module/' . $install->project->name . '/config',
            $install->project->base . '/module/' . $install->project->name . '/data',
            $install->project->base . '/module/' . $install->project->name . '/src',
            $install->project->base . '/module/' . $install->project->name . '/src/' . $install->project->name,
            $install->project->base . '/module/' . $install->project->name . '/view',
            $install->project->docroot
        );

        foreach ($folders as $folder) {
            if (!file_exists($folder)) {
                mkdir($folder);
            }
        }

        chmod($install->project->base . '/module/' . $install->project->name . '/data', 0777);

        // Create project.config.php file
        $projectCfg = new Generator($install->project->base . '/config/project.config.php');
        $projectCfg->appendToBody('return new Pop\Config(array(', true)
                   ->appendToBody("    'base'      => '" . addslashes(realpath($install->project->base)) . "',")
                   ->appendToBody("    'docroot'   => '" . addslashes(realpath($install->project->docroot)) . "'", false);

        // Add the database config to it
        if (isset($install->databases)) {
            $projectCfg->appendToBody(",")
                       ->appendToBody("    'databases' => array(");
            $databases = $install->databases->asArray();
            $i = 0;
            foreach ($databases as $dbname => $db) {
                $projectCfg->appendToBody("        '" . $dbname . "' => Pop\\Db\\Db::factory('" . $db['type'] . "', array (");
                $j = 0;
                $isSqlite = ($db['type'] == 'Sqlite') ? true : false;
                foreach ($db as $key => $value) {
                    $j++;
                    if ($key != 'type') {
                        if ($isSqlite) {
                            $dbFile = $install->project->base . '/module/' . $install->project->name . '/data/' . basename($value);
                            copy($value, $dbFile);
                            chmod($dbFile, 0777);
                            $dbFile = addslashes(realpath($dbFile));
                            $ary = "            '{$key}' => '{$dbFile}'";
                        } else {
                            $ary = "            '{$key}' => '{$value}'";
                        }
                        if ($j < count($db)) {
                           $ary .= ',';
                        }
                        $projectCfg->appendToBody($ary);
                    }
                }
                $i++;
                $end = ($i < count($databases)) ? '        )),' : '        ))';
                $projectCfg->appendToBody($end);
            }
            $projectCfg->appendToBody('    )', false);
        }

        // Add the controller config to it
        if (isset($install->controllers)) {
            $projectCfg->appendToBody(',' . PHP_EOL . "    'controllers' => array(");
            $i = 0;
            $controllers = $install->controllers->asArray();
            foreach ($controllers as $key => $value) {
                $i++;
                $ctrl = "        '" . $key . "' => '" . $install->project->name . "\\\\Controller\\\\" . ucfirst(String::factory($key)->underscoreToCamelcase()) . "Controller'";
                $ctrl .= ($i < count($controllers)) ? ',' : null;
                $projectCfg->appendToBody($ctrl);
            }
            $projectCfg->appendToBody('    )');
        }

        // Save project config
        $projectCfg->appendToBody('));', false);
        $projectCfg->save();

        // Create the module config file
        $moduleCfg = new Generator($install->project->base . '/module/' . $install->project->name . '/config/module.config.php');
        $moduleCfg->appendToBody('return new Pop\Config(array(')
                  ->appendToBody("    'name'   => '{$install->project->name}',")
                  ->appendToBody("    'base'   => '" . addslashes(realpath($install->project->base . '/module/' . $install->project->name)) . "',")
                  ->appendToBody("    'config' => '" . addslashes(realpath($install->project->base . '/module/' . $install->project->name . '/config')) . "',")
                  ->appendToBody("    'data'   => '" . addslashes(realpath($install->project->base . '/module/' . $install->project->name . '/data')) . "',")
                  ->appendToBody("    'src'    => '" . addslashes(realpath($install->project->base . '/module/' . $install->project->name . '/src')) . "',")
                  ->appendToBody("    'view'   => '" . addslashes(realpath($install->project->base . '/module/' . $install->project->name . '/view')) . "'")
                  ->appendToBody("));", false)
                  ->save();
    }

}
