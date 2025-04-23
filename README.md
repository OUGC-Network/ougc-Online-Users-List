<h3 align="center">ougc Online Users List</h3>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
[![GitHub Issues](https://img.shields.io/github/issues/OUGC-Network/ougc-Online-Users-List.svg)](./issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/OUGC-Network/ougc-Online-Users-List.svg)](./pulls)
[![License](https://img.shields.io/badge/license-GPL-blue)](/LICENSE)

</div>

---

<p align="center"> Show a list of current online users across the forum.
    <br> 
</p>

## 📜 Table of Contents <a name = "table_of_contents"></a>

- [About](#about)
- [Getting Started](#getting_started)
    - [Dependencies](#dependencies)
    - [File Structure](#file_structure)
    - [Install](#install)
    - [Update](#update)
    - [Template Modifications](#template_modifications)
- [Settings](#settings)
    - [File Level Settings](#file_level_settings)
- [Templates](#templates)
- [Built Using](#built_using)
- [Authors](#authors)
- [Acknowledgments](#acknowledgement)
- [Support & Feedback](#support)

## 🚀 About <a name = "about"></a>

Show a list of current online users across the forum.

[Go up to Table of Contents](#table_of_contents)

## 📍 Getting Started <a name = "getting_started"></a>

The following information will assist you into getting a copy of this plugin up and running on your forum.

### Dependencies <a name = "dependencies"></a>

A setup that meets the following requirements is necessary to use this plugin.

- [MyBB](https://mybb.com/) >= 1.8
- PHP >= 7
- [MyBB-PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) >= 13

### File structure <a name = "file_structure"></a>

  ```
   .
   ├── inc
   │ ├── languages
   │ │ ├── english
   │ │ │ ├── admin
   │ │ │ │ ├── ougcOnlineUsersList.lang.php
   │ │ │ ├── ougcOnlineUsersList.lang.php
   │ ├── plugins
   │ │ ├── ougc
   │ │ │ ├── OnlineUsersList
   │ │ │ │ ├── hooks
   │ │ │ │ │ ├── admin.php
   │ │ │ │ │ ├── forum.php
   │ │ │ │ ├── templates
   │ │ │ │ │ ├── list.html
   │ │ │ │ │ ├── listUser.html
   │ │ │ │ ├── settings.json
   │ │ │ │ ├── admin.php
   │ │ │ │ ├── core.php
   │ │ │ ├── ougcOnlineUsersList.php
   ```

### Installing <a name = "install"></a>

Follow the next steps in order to install a copy of this plugin on your forum.

1. Download the latest package from the [MyBB Extend](https://community.mybb.com/mods.php) site or
   from the [repository releases](https://github.com/OUGC-Network/ougc-Online-Users-List/releases/latest).
2. Upload the contents of the _Upload_ folder to your MyBB root directory.
3. Browse to _Configuration » Plugins_ and install this plugin by clicking _Install & Activate_.
4. Browse to _Settings_ to manage the plugin settings.

### Updating <a name = "update"></a>

Follow the next steps in order to update your copy of this plugin.

1. Browse to _Configuration » Plugins_ and deactivate this plugin by clicking _Deactivate_.
2. Follow step 1 and 2 from the [Install](#install) section.
3. Browse to _Configuration » Plugins_ and activate this plugin by clicking _Activate_.
4. Browse to _Settings_ to manage the plugin settings.

### Template Modifications <a name = "template_modifications"></a>

To display online users list it is required that you edit the following template for each of your themes.

1. Place `<!--ougcOnlineUsersList-->` after `{$whosonline}` in the `index_boardstats` template.

Additionally, you could place `<!--ougcOnlineUsersList-->` in any template and it should work. But consider the template
outputs table rows by default, sou you will need to adjust according to your use.

[Go up to Table of Contents](#table_of_contents)

## 🛠 Settings <a name = "settings"></a>

Below you can find a description of the plugin settings.

### Main Settings

- **Displays Spiders** `yesNo`
    - _Display spiders in the online list._
- **Users Order** `select`
    - _List the online users by username or last activity._
- **Cut-off Time** `numeric`
    - _The number of minutes before a user is marked offline. Recommended: 15._
- **Cache Time** `numeric`
    - _The number of minutes before the online users list is refreshed. Recommended: 5._

### File Level Settings <a name = "file_level_settings"></a>

Additionally, you can force your settings by updating the `SETTINGS` array constant in the `ougc\OnlineUsersList\Core`
namespace in the `./inc/plugins/ougcOnlineUsersList.php` file. Any setting set this way will always bypass any front-end
configuration. Use the setting key as shown below:

```PHP
define('ougc\OnlineUsersList\Core\SETTINGS', [
    'allowImports' => false,
    'myAlertsVersion' => '2.1.0'
]);
```

[Go up to Table of Contents](#table_of_contents)

## 📐 Templates <a name = "templates"></a>

The following is a list of templates available for this plugin.

- `ougcOnlineUsersList_list`
    - _front end_;
- `ougcOnlineUsersList_listUser`
    - _front end_;

[Go up to Table of Contents](#table_of_contents)

## ⛏ Built Using <a name = "built_using"></a>

- [MyBB](https://mybb.com/) - Web Framework
- [MyBB PluginLibrary](https://github.com/frostschutz/MyBB-PluginLibrary) - A collection of useful functions for MyBB
- [PHP](https://www.php.net/) - Server Environment

[Go up to Table of Contents](#table_of_contents)

## ✍️ Authors <a name = "authors"></a>

- [@Omar G](https://github.com/Sama34) - Idea & Initial work

See also the list of [contributors](https://github.com/OUGC-Network/ougc-Awards/contributors) who participated in
this
project.

[Go up to Table of Contents](#table_of_contents)

## 🎉 Acknowledgements <a name = "acknowledgement"></a>

- [The Documentation Compendium](https://github.com/kylelobo/The-Documentation-Compendium)

[Go up to Table of Contents](#table_of_contents)

## 🎈 Support & Feedback <a name="support"></a>

This is free development and any contribution is welcome. Get support or leave feedback at the
official [MyBB Community](https://community.mybb.com/thread-159249.html).

Thanks for downloading and using our plugins!

[Go up to Table of Contents](#table_of_contents)