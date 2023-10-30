TYPO3 Extension calendarize_external

Extension for TYPO3, calendarize, external calendars for users

## Features

## Installation

Simply install the extension with Composer or the [Extension Manager](https://extensions.typo3.org/).

`composer require verdigado/calendarize-external`

## Usage

### ddev local setup

The extension comes with a ready to use DDEV local configuration:

* `git clone` the extension
* `cd calendarize_external`
* Hint: in .ddev/config.yaml set PHP, MariaDB versions according to your needs)

* to use e.g. TYPO3 11.5:

```
     composer require typo3/minimal:"^11.5"
     ddev start && ddev launch
```

* do TYPO3 setup your in web browser
* go to Backend -> Extensions, check or activate the calendarize_external extension

!!! Do not commit the "typo3/minimal" etc. lines from local setup to composer.json !!!

To remove the local ddev setup without removing the local repo:

```
ddev delete -O
rm -rf .build/ composer.lock
```

## Community
