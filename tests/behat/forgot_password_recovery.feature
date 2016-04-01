#
# Enhanced authentication.
#
# @author Luke Carrier <luke.carrier@floream.com>
# @copyright 2016 Floream Limited
#

@local_signin
Feature: Forgotten password recovery
  In order to authenticate
  As a Moodle user
  I need to be able to recover lost passwords

  @javascript
  Scenario: Username search is shown only when enabled
    Given I log in as "admin"
    And I navigate to "Better authentication" node in "Site administration > Plugins > Authentication"
    And I set the following fields to these values:
      |  |

  @javascript
  Scenario: Email search is shown only when enabled

  @javascript
  Scenario: Validation failure when searching for empty username

  @javascript
  Scenario: Validation failure when searching for empty email address

  @javascript
  Scenario: Validation failure when searching for invalid email address
