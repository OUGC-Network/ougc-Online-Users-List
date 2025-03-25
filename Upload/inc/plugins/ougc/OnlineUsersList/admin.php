<?php

/***************************************************************************
 *
 *    ougc Online Users List plugin (/inc/plugins/ougc/OnlineUsersList/admin.php)
 *    Author: Omar Gonzalez
 *    Copyright: © 2025 Omar Gonzalez
 *
 *    Website: https://ougc.network
 *
 *    Show a list of current online users across the forum.
 *
 ***************************************************************************
 ****************************************************************************
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ****************************************************************************/

declare(strict_types=1);

namespace ougc\OnlineUsersList\Admin;

use DirectoryIterator;
use stdClass;

use function ougc\OnlineUsersList\Core\cacheUpdate;
use function ougc\OnlineUsersList\Core\languageLoad;

use const ougc\OnlineUsersList\ROOT;

function pluginInfo(): array
{
    global $lang;

    languageLoad();

    return [
        'name' => 'ougc Online Users List',
        'description' => $lang->ougcOnlineUsersListDescription,
        'website' => 'https://ougc.network',
        'author' => 'Omar G.',
        'authorsite' => 'https://ougc.network',
        'version' => '1.8.0',
        'versioncode' => 1800,
        'compatibility' => '18*',
        'codename' => 'ougcOnlineUsersList',
        'pl' => [
            'version' => 13,
            'url' => 'https://community.mybb.com/mods.php?action=view&pid=573'
        ]
    ];
}

function pluginActivate(): void
{
    global $PL, $cache, $lang;

    languageLoad();

    $pluginInfo = pluginInfo();

    loadPluginLibrary();

    $settingsContents = file_get_contents(ROOT . '/settings.json');

    $settingsData = json_decode($settingsContents, true);

    foreach ($settingsData as $settingKey => &$settingData) {
        if (empty($lang->{"setting_ougcOnlineUsersList_{$settingKey}"})) {
            continue;
        }

        if ($settingData['optionscode'] == 'select' || $settingData['optionscode'] == 'checkbox') {
            foreach ($settingData['options'] as $optionKey) {
                $settingData['optionscode'] .= "\n{$optionKey}={$lang->{"setting_ougcOnlineUsersList_{$settingKey}_{$optionKey}"}}";
            }
        }

        $settingData['title'] = $lang->{"setting_ougcOnlineUsersList_{$settingKey}"};

        $settingData['description'] = $lang->{"setting_ougcOnlineUsersList_{$settingKey}_desc"};
    }

    $PL->settings(
        'ougcOnlineUsersList',
        $lang->setting_group_ougcOnlineUsersList,
        $lang->setting_group_ougcOnlineUsersList_desc,
        $settingsData
    );

    $templatesList = [];

    if (file_exists($templateDirectory = ROOT . '/templates')) {
        $templatesDirIterator = new DirectoryIterator($templateDirectory);

        foreach ($templatesDirIterator as $template) {
            if (!$template->isFile()) {
                continue;
            }

            $pathName = $template->getPathname();

            $pathInfo = pathinfo($pathName);

            if ($pathInfo['extension'] === 'html') {
                $templatesList[$pathInfo['filename']] = file_get_contents($pathName);
            }
        }
    }

    if ($templatesList) {
        $PL->templates('ougcOnlineUsersList', 'ougc Online Users List', $templatesList);
    }

    $plugins = $cache->read('ougc_plugins');

    if (empty($plugins)) {
        $plugins = [];
    }

    if (!isset($plugins['OnlineUsersList'])) {
        $plugins['OnlineUsersList'] = $pluginInfo['versioncode'];
    }

    /*~*~* RUN UPDATES START *~*~*/

    /*~*~* RUN UPDATES END *~*~*/

    cacheUpdate();

    $plugins['OnlineUsersList'] = $pluginInfo['versioncode'];

    $cache->update('ougc_plugins', $plugins);
}

function pluginIsInstalled(): bool
{
    global $cache;

    $plugins = $cache->read('ougc_plugins');

    return !empty($plugins['OnlineUsersList']);
}

function pluginUninstall(): void
{
    global $cache;
    global $PL;

    loadPluginLibrary();

    $PL->settings_delete('ougcOnlineUsersList');

    $PL->templates_delete('ougcOnlineUsersList');

    $cache->delete('ougcOnlineUsersList');

    $plugins = $cache->read('ougc_plugins');

    if (empty($plugins)) {
        $plugins = [];
    }

    if (isset($plugins['OnlineUsersList'])) {
        unset($plugins['OnlineUsersList']);
    }

    if (!empty($plugins)) {
        $cache->update('ougc_plugins', $plugins);
    } else {
        $cache->delete('ougc_plugins');
    }
}

function pluginLibraryRequirements(): stdClass
{
    return (object)pluginInfo()['pl'];
}

function loadPluginLibrary(): void
{
    global $PL, $lang;

    languageLoad();

    $fileExists = file_exists(PLUGINLIBRARY);

    if ($fileExists && !($PL instanceof PluginLibrary)) {
        require_once PLUGINLIBRARY;
    }

    if (!$fileExists || $PL->version < pluginLibraryRequirements()->version) {
        flash_message(
            $lang->sprintf(
                $lang->ougcOnlineUsersListPluginLibrary,
                pluginLibraryRequirements()->url,
                pluginLibraryRequirements()->version
            ),
            'error'
        );

        admin_redirect('index.php?module=config-plugins');
    }
}