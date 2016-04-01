# Enhanced authentication

A replacement for Moodle's login and forgotten password pages with improved
logging that's easier to theme.

* * *

## Installation

1. Drop this repository into `/local/auth`.
2. Run the Moodle upgrade process.

## Configuration

This plugin provides local replacements for the log in and forgotten password
pages. To enable them, you'll need to change the following Moodle configuration
options:

| Option | Value |
| --- | --- |
| `alternateloginurl` | `/local/signin/login.php` |
| `forgottenpasswordurl` | `/local/signin/forgot.php` |
