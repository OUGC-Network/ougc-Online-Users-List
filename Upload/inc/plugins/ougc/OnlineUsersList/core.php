<?php

/***************************************************************************
 *
 *    ougc Online Users List plugin (/inc/plugins/ougc/OnlineUsersList/core.php)
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

namespace ougc\OnlineUsersList\Core;

use MyBB;
use MybbStuff_MyAlerts_AlertManager;
use MybbStuff_MyAlerts_AlertTypeManager;
use MybbStuff_MyAlerts_Entity_Alert;
use pluginSystem;
use postParser;

use function ougc\OnlineUsersList\Hooks\Forum\myalerts_register_client_alert_formatters;

use const ougc\OnlineUsersList\ROOT;
use const TIME_NOW;

const URL = 'online.php';

function hooksAdd(string $namespace): void
{
    global $plugins;

    $namespaceLowercase = strtolower($namespace);
    $definedUserFunctions = get_defined_functions()['user'];

    foreach ($definedUserFunctions as $callable) {
        $namespaceWithPrefixLength = strlen($namespaceLowercase) + 1;

        if (substr($callable, 0, $namespaceWithPrefixLength) == $namespaceLowercase . '\\') {
            $hookName = substr_replace($callable, '', 0, $namespaceWithPrefixLength);

            $priority = substr($callable, -2);

            $isNegative = substr($hookName, -3, 1) === '_';

            if (is_numeric(substr($hookName, -2))) {
                $hookName = substr($hookName, 0, -2);
            } else {
                $priority = 10;
            }

            if ($isNegative) {
                $plugins->add_hook($hookName, $callable, -$priority);
            } else {
                $plugins->add_hook($hookName, $callable, $priority);
            }
        }
    }
}

function languageLoad(bool $isDataHandler = false): bool
{
    global $lang;

    isset($lang->ougcOnlineUsersList) || $lang->load('ougcOnlineUsersList', $isDataHandler);

    return isset($lang->ougcOnlineUsersList);
}

function templatesGetName(string $templateName = ''): string
{
    $templatePrefix = '';

    if ($templateName) {
        $templatePrefix = '_';
    }

    return "ougcOnlineUsersList{$templatePrefix}{$templateName}";
}

function templatesGet(string $templateName = '', bool $enableHTMLComments = true): string
{
    global $templates;

    if (DEBUG) {
        $filePath = ROOT . "/templates/{$templateName}.html";

        $templateContents = file_get_contents($filePath);

        $templates->cache[templatesGetName($templateName)] = $templateContents;
    } elseif (my_strpos($templateName, '/') !== false) {
        $templateName = substr($templateName, strpos($templateName, '/') + 1);
    }

    return $templates->render(templatesGetName($templateName), true, $enableHTMLComments);
}

function settingsGet(string $settingKey = '')
{
    global $mybb;

    return SETTINGS[$settingKey] ?? (
        $mybb->settings['ougcOnlineUsersList_' . $settingKey] ?? false
    );
}

function cacheUpdate(): array
{
    global $mybb, $db, $cache;

    $queryOptions = [];

    if (settingsGet('orderBy') === 'username') {
        $queryOptions['order_by'] = 'u.username ASC, s.time DESC';
    } else {
        $queryOptions['order_by'] = 's.time DESC, u.username ASC';
    }

    $searchSpanTime = TIME_NOW - (settingsGet('cutOffMinutes') * 60);

    $cacheData = [
        'lastUpdate' => TIME_NOW,
        'forumViewers' => [],
        'users' => [],
    ];

    $handledUsers = [];

    if (!empty($mybb->settings['showforumviewing'])) {
        $query = $db->simple_select(
            'sessions',
            'location1, COUNT(DISTINCT ip) AS totalGuests',
            "uid='0' AND location1!='0' AND SUBSTR(sid,4,1)!='=' AND time>'{$searchSpanTime}'",
            ['group_by' => 'location1']
        );

        while ($locationData = $db->fetch_array($query)) {
            if (isset($cacheData['forumViewers'][$locationData['location1']])) {
                $cacheData['forumViewers'][$locationData['location1']] += $locationData['totalGuests'];
            } else {
                $cacheData['forumViewers'][$locationData['location1']] = $locationData['totalGuests'];
            }
        }
    }

    $query = $db->simple_select(
        'sessions',
        'COUNT(DISTINCT ip) AS totalGuests',
        "uid = 0 AND SUBSTR(sid,4,1) != '=' AND time > $searchSpanTime"
    );

    $cacheData['totalGuests'] = (int)$db->fetch_field($query, 'totalGuests');

    $whereClauses = ["(s.uid!='0' OR SUBSTR(s.sid,4,1)='=')", "s.time>'{$searchSpanTime}'"];

    if (!settingsGet('displaySpiders')) {
        $whereClauses[] = "s.sid NOT LIKE 'bot=%'";
    }

    $query = $db->simple_select(
        "sessions s LEFT JOIN {$db->table_prefix}users u ON (s.uid=u.uid)",
        's.sid, s.ip, s.uid, s.time, s.location, s.location1, u.username, u.invisible, u.usergroup, u.displaygroup',
        implode(' AND ', $whereClauses),
        $queryOptions
    );

    $spidersCache = $mybb->cache->read('spiders');

    while ($userData = $db->fetch_array($query)) {
        unset($userData['ip']);

        $cacheData['users'][] = $userData;
    }

    $mybb->cache->update('ougcOnlineUsersList', $cacheData);

    return $cacheData;
}

function cacheGet(): array
{
    global $mybb;

    $cacheData = $mybb->cache->read('ougcOnlineUsersList');

    return is_array($cacheData) ? $cacheData : [];
}