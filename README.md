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

To enable these settings, you'll need to add/change the following Moodle configuration options:

| Option | Value |
| --- | --- |
| `alternateloginurl` | `/local/signin/login.php` |
| `forgottenpasswordurl` | `/local/signin/forgot.php` |

### Advanced: co-branding

To enable co-branding behaviour facilitating redirecting users to the login
process on different domains (e.g. for white labelling), implement the
`\local_signin\domainfinder\user_domain_interface` interface and set the
following options in `/config.php`:

```php
$CFG->local_signin_defaultdomain = 'defaultlogindomain.com';
$CFG->local_signin_domainfinder = '\\local_yourplugin\\your_user_domain';
```

`defaultdomain` should be the canonical/default home of your platform when no
valid `Host` header was provided by the client. `domainfinder` should contain
the fully-qualified name of a `user_domain_interface` implementation that
resolves the user's inputted email address or username to a domain. In the event
that none is found, we fall back to providing the default.
