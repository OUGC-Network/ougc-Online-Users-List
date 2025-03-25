<?php

/***************************************************************************
 *
 *    ougc Online Users List plugin (/inc/plugins/ougcOnlineUsersList.php)
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

use function ougc\OnlineUsersList\Admin\pluginUninstall;
use function ougc\OnlineUsersList\Core\hooksAdd;
use function ougc\OnlineUsersList\Core\cacheUpdate;
use function ougc\OnlineUsersList\Admin\pluginInfo;
use function ougc\OnlineUsersList\Admin\pluginActivate;
use function ougc\OnlineUsersList\Admin\pluginDeactivate;
use function ougc\OnlineUsersList\Admin\pluginIsInstalled;

use const ougc\OnlineUsersList\ROOT;

defined('IN_MYBB') || die('This file cannot be accessed directly.');

// You can uncomment the lines below to avoid storing some settings in the DB
define('ougc\OnlineUsersList\Core\SETTINGS', [
    //'key' => '',
]);

define('ougc\OnlineUsersList\Core\DEBUG', false);

define('ougc\OnlineUsersList\ROOT', MYBB_ROOT . 'inc/plugins/ougc/OnlineUsersList');

require_once ROOT . '/core.php';

defined('PLUGINLIBRARY') || define('PLUGINLIBRARY', MYBB_ROOT . 'inc/plugins/pluginlibrary.php');

if (defined('IN_ADMINCP')) {
    require_once ROOT . '/admin.php';
    require_once ROOT . '/hooks/admin.php';

    hooksAdd('ougc\OnlineUsersList\Hooks\Admin');
} else {
    require_once ROOT . '/hooks/forum.php';

    hooksAdd('ougc\OnlineUsersList\Hooks\Forum');
}

function ougcOnlineUsersList_info(): array
{
    return pluginInfo();
}

function ougcOnlineUsersList_activate(): void
{
    pluginActivate();
}

function ougcOnlineUsersList_is_installed(): bool
{
    return pluginIsInstalled();
}

function ougcOnlineUsersList_uninstall(): void
{
    pluginUninstall();
}

function update_ougcOnlineUsersList(): void
{
    cacheUpdate();
}