# Enhanced authentication

A replacement for Moodle's login and forgotten password pages with improved
logging that's easier to theme.

* * *

## Installation

1. Drop this repository into `/local/signin`.
2. Run the Moodle upgrade process.

## Configuration

This plugin provides local replacements for the log in and forgotten password
pages.

It also facilitates redirecting users to the login process on different domains (e.g. for white labelling), via a configuration setting set to the name of a class implementing the `\local_signin\interfaces\user_domain_interface` interface.

To enable these settings, you'll need to add/change the following Moodle configuration (`$CFG`)
options:

| Option | Value |
| --- | --- |
| `alternateloginurl` | `/local/signin/login.php` |
| `forgottenpasswordurl` | `/local/signin/forgot.php` |
| `local_signin_userdomain` | `bmdisco_domain\\user_domain` |

