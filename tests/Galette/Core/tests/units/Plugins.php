<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Plugins tests
 *
 * PHP version 5
 *
 * Copyright © 2013-2023 The Galette Team
 *
 * This file is part of Galette (https://galette.eu).
 *
 * Galette is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Galette is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Galette. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Core
 * @package   GaletteTests
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2013-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://galette.eu
 * @since     2013-01-13
 */

namespace Galette\Core\test\units;

use PHPUnit\Framework\TestCase;

/**
 * Plugins tests class
 *
 * @category  Core
 * @name      Plugins
 * @package   GaletteTests
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2013-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://galette.eu
 * @since     2013-01-13
 */
class Plugins extends TestCase
{
    private \Galette\Core\Db $zdb;
    private \Galette\Core\Preferences $preferences;
    private \Galette\Core\Plugins $plugins;

    private array $plugin2 = array(
        'root'          => 'plugin-test2',
        'name'          => 'Galette Test2 Plugin',
        'desc'          => 'Test two plugin',
        'author'        => 'Johan Cwiklinski',
        'version'       => '1.0',
        'acls'          => [
            'plugin2_root'  => 'member',
            'plugin2_admin' => 'staff'
        ],
        'date'          => '2013-12-15',
        'priority'      => 1000,
        'root_writable' => true,
        'route'         => 'plugin2'
    );

    /**
     * Get instanciated plugins instance
     *
     * @return Galette\Core\Plugins
     */
    private function getPlugins()
    {
        $plugins = new \Galette\Core\Plugins();
        $plugins->autoload(GALETTE_PLUGINS_PATH);
        $plugins->loadModules($this->preferences, GALETTE_PLUGINS_PATH);
        return $plugins;
    }

    /**
     * Set up tests
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->zdb = new \Galette\Core\Db();
        $this->preferences = new \Galette\Core\Preferences($this->zdb);

        $this->plugins = $this->getPlugins();

        $this->plugin2['root'] = GALETTE_PLUGINS_PATH .
            $this->plugin2['root'];
    }

    /**
     * Tear down tests
     *
     * @return void
     */
    public function tearDown(): void
    {
        if (TYPE_DB === 'mysql') {
            $this->assertSame([], $this->zdb->getWarnings());
        }
    }

    /**
     * Tests plugins load
     *
     * @return void
     */
    public function testLoadModules()
    {
        $plugins = $this->getPlugins();
        $this->assertCount(3, $this->plugins->getModules());

        $loaded_plugin = $this->plugins->getModules('plugin-test2');
        $loaded_plugin['date'] = $this->plugin2['date'];

        $this->assertSame($this->plugin2, $loaded_plugin);
    }

    /**
     * Test module existence
     *
     * @return void
     */
    public function testModuleExists()
    {
        $this->assertTrue($this->plugins->moduleExists('plugin-test2'));
        $this->assertFalse($this->plugins->moduleExists('plugin-disabled'));
    }

    /**
     * Test disabled plugin
     *
     * @return void
     */
    public function testDisabledModules()
    {
        $disabled_modules = $this->plugins->getDisabledModules();
        $this->assertTrue(isset($disabled_modules['plugin-disabled']));
        $this->assertTrue(isset($disabled_modules['plugin-unversionned']));
        $this->assertTrue(isset($disabled_modules['plugin-oldversion']));
    }

    /**
     * Test module root
     *
     * @return void
     */
    public function testModuleRoot()
    {
        $this->assertSame($this->plugin2['root'], $this->plugins->moduleRoot('plugin-test2'));
    }

    /**
     * Test templates path
     *
     * @return void
     */
    /*public function testGetTemplatesPath()
    {
        //FIXME:
        //  - at the moment, there is no config for preferences, so default theme is empty
        //  - remove global $preferences to have this one working as expected...
        $this->variable($this->plugins->getTemplatesPath('plugin-test2'))
            ->isIdenticalTo($this->plugin2['root'] . '/templates/');
    }*/

    /**
     * Test reset modules list
     *
     * @return void
     */
    public function testResetModulesList()
    {
        $this->plugins->resetModulesList();

        $this->assertEmpty($this->plugins->getModules());
    }

    /**
     * Test plugin (des)activation
     *
     * @return void
     */
    public function testModuleActivation()
    {
        $plugins = $this->getPlugins();
        $modules = $plugins->getModules();
        $this->assertCount(3, $modules);
        $this->assertTrue(isset($modules['plugin-test2']));
        $plugins->deactivateModule('plugin-test2');

        $plugins = $this->getPlugins();
        $modules = $plugins->getModules();
        $this->assertCount(2, $modules);
        $this->assertFalse(isset($module['plugin-test2']));
        $plugins->activateModule('plugin-test2');

        $plugins = $this->getPlugins();
        $modules = $plugins->getModules();
        $this->assertCount(3, $modules);
        $this->assertTrue(isset($modules['plugin-test2']));

        $plugins = $this->getPlugins();
        $this->expectExceptionMessage(_T('No such module.'));
        $plugins->deactivateModule('nonexistant');

        $plugins = $this->getPlugins();
        $this->expectExceptionMessage(_T('No such module.'));
        $plugins->activateModule('nonexistant');
    }

    /**
     * Test if plugin needs database
     *
     * @return void
     */
    public function testNeedDatabse()
    {
        $this->assertTrue($this->plugins->needsDatabase('plugin-db'));
        $this->assertFalse($this->plugins->needsDatabase('plugin-test2'));

        $plugins = $this->getPlugins();
        $this->expectExceptionMessage(_T('Module does not exists!'));
        $plugins->needsDatabase('nonexistant');
    }
}
