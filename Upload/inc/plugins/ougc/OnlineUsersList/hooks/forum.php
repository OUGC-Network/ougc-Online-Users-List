<?php

/***************************************************************************
 *
 *    ougc Online Users List plugin (/inc/plugins/ougc/OnlineUsersList/hooks/admin.php)
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

namespace ougc\OnlineUsersList\Hooks\Forum;

use function ougc\OnlineUsersList\Core\cacheGet;
use function ougc\OnlineUsersList\Core\cacheUpdate;
use function ougc\OnlineUsersList\Core\languageLoad;
use function ougc\OnlineUsersList\Core\settingsGet;
use function ougc\OnlineUsersList\Core\templatesGet;

function global_start(): void
{
    global $templatelist;

    if (isset($templatelist)) {
        $templatelist .= ',';
    } else {
        $templatelist = '';
    }

    $templatelist .= '';
}

function pre_output_page(string &$pageContents): string
{
    global $mybb;

    if (empty($mybb->usergroup['canviewonline'])) {
        return $pageContents;
    }

    $cacheData = cacheGet();

    if (!$cacheData || $cacheData['lastUpdate'] < TIME_NOW - (settingsGet('cacheMinutes') * 60)) {
        $cacheData = cacheUpdate();
    }

    $totalMembers = $totalMembersAnonymous = $totalSpiders = 0;
    $onlineMembers = $onlineSpiders = [];

    $spidersCache = $mybb->cache->read('spiders');

    global $lang;

    languageLoad();

    $currentUserID = (int)$mybb->user['uid'];

    foreach ($cacheData['users'] as $userData) {
        $spiderKey = my_strtolower(str_replace('bot=', '', $userData['sid']));

        if (!empty($userData['uid'])) {
            $userID = (int)$userData['uid'];

            if (!empty($userData['invisible'])) {
                ++$totalMembersAnonymous;
            }

            ++$totalMembers;

            if (empty($userData['invisible']) || $mybb->usergroup['canviewwolinvis'] == 1 || $userID === $currentUserID) {
                $invisibleMark = empty($userData['invisible']) ? '' : '*';

                $userName = htmlspecialchars_uni($userData['username']);

                $profileLink = build_profile_link($userName, $userID);

                $userNameFormatted = format_name(
                    $userName,
                    $userData['usergroup'],
                    $userData['displaygroup']
                );

                $profileLinkFormatted = build_profile_link($userNameFormatted, $userID);

                $onlineMembers[] = eval(templatesGet('listUser', false));
            }
        } elseif (my_strpos($userData['sid'], 'bot=') !== false && isset($spidersCache[$spiderKey])) {
            if (settingsGet('orderBy') === 'username') {
                $botKey = $spidersCache[$spiderKey]['name'];
            } else {
                $botKey = $userData['time'];
            }

            $onlineSpiders[$botKey] = format_name(
                $spidersCache[$spiderKey]['name'],
                $spidersCache[$spiderKey]['usergroup']
            );

            ++$totalSpiders;
        }

        if ($userData['location1']) {
            if (isset($cacheData['forumViewers'][$userData['location1']])) {
                ++$cacheData['forumViewers'][$userData['location1']];
            } else {
                $cacheData['forumViewers'][$userData['location1']] = 1;
            }
        }
    }

    if (settingsGet('orderBy') === 'username') {
        ksort($onlineSpiders);
    } else {
        krsort($onlineSpiders);
    }

    $onlineMembers = array_merge($onlineSpiders, $onlineMembers);

    $onlineMembers = empty($onlineMembers) ? '' : implode($lang->comma . ' ', $onlineMembers);

    $totalOnlineUsers = $totalMembers + $cacheData['totalGuests'] + $totalSpiders;

    $usersBit = $totalOnlineUsers ? $lang->ougcOnlineUsersListUsersPlural : $lang->ougcOnlineUsersListUserSingular;

    $membersBit = $totalMembers ? $lang->ougcOnlineUsersListMembersPlural : $lang->ougcOnlineUsersListMembersSingular;

    $membersAnonymousBit = $totalMembersAnonymous ? $lang->ougcOnlineUsersListMembersAnonymousPlural : $lang->ougcOnlineUsersListMembersAnonymousSingular;

    $guestsBit = $cacheData['totalGuests'] ? $lang->ougcOnlineUsersListGuestsPlural : $lang->ougcOnlineUsersListGuestsSingular;

    $onlineNote = $lang->sprintf(
        $lang->ougcOnlineUsersListTableNote,
        my_number_format($totalOnlineUsers),
        $usersBit,
        settingsGet('cutOffMinutes'),
        my_number_format($totalMembers),
        $membersBit,
        my_number_format($totalMembersAnonymous),
        $membersAnonymousBit,
        my_number_format($cacheData['totalGuests']),
        $guestsBit
    );

    return str_replace('<!--ougcOnlineUsersList-->', eval(templatesGet('list')), $pageContents);
}