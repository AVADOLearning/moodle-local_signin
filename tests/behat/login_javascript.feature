#
# Local Signin - login
#
# @author jonathan Shad <jonathan.shad@avadolearning.com>
# @copyright 2017 AVADO Limited
#
@local_signin
Feature: Log in to platform
  In order to view my course
  As a user
  I need to log in to the platform, having had my domain and credentials validated.

  Background:
    Given I log in as "admin"
    And the following "users" exist:
      | username | firstname | lastname | email                | password |
      | student1 | Student   | 1        | student1@example.com | pass1    |
      | student2 | Student   | 2        | student2@example.com | pass2    |
    And the following "cohorts" exist:
      | idnumber | name     |
      | cht1     | Cohort 1 |
      | cht2     | Cohort 2 |
    And I add "student1@example.com" user to "Cohort 1" cohort members
    And I add "student2@example.com" user to "Cohort 2" cohort members
    And I visit the local URL "/local/brandmanager/manage_brand.php?"
    And I set the following fields to these values:
      | Name | Brand1 |
    And I press "Save changes"
    And I click on "Manage brand cohorts" "link" in the "Brand1" "table_row"
    And I should see "Manage brand cohorts"
    And I expand the "Selected Cohorts" autocomplete menu
    And I click on "Cohort 1" item in the autocomplete menu
    And I press "Save changes"
    And I click on "Back to brands" "link"
    And I click on "Manage domains" "link" in the "Brand1" "table_row"
    And I should see "Add new domain"
    And I set the following fields to these values:
      | domain | http://192.168.120.50 |
    And I press "Save changes"
    And I click on "Back to brands" "link"
    And I press "New brand"
    And I set the following fields to these values:
      | Name | Brand2 |
    And I press "Save changes"
    And I click on "Manage brand cohorts" "link" in the "Brand2" "table_row"
    And I should see "Manage brand cohorts"
    And I expand the "Selected Cohorts" autocomplete menu
    And I click on "Cohort 2" item in the autocomplete menu
    And I press "Save changes"
    And I click on "Back to brands" "link"
    And I click on "Manage domains" "link" in the "Brand2" "table_row"
    And I should see "Add new domain"
    And I set the following fields to these values:
      | domain | otherdomain |
    And I press "Save changes"
    And I click on "Back to brands" "link"
    And I log out
    And I visit the local URL "/local/signin/index.php"

  @javascript
  Scenario: 01. Successful login, without redirect.
    Given I should see "Username"
    And I should not see "Password"
    And I set the following fields to these values:
      | username | student1 |
    And I press "Proceed"
    And I should see "Password"
    And I set the following fields to these values:
      | password | pass1 |
    And I press "Log In"
    And I should see "Dashboard"
    And I should see "Site home"

  @javascript
  Scenario: 02. Redirect if on wrong domain.
    Given I set the following fields to these values:
      | username | student2 |
    And I press "Proceed"
    Then I should see "http://otherdomain/behat/local/signin/index.php?username=student2"

#    TODO: Needs alternate test environment to be sent to.
#    Then I should see "Username"
#    And I should see "Proceed"

  @javascript
  Scenario: 03. Forgot username.
    Given I click on "Forgotten your username?" "link"
    Then I should see "Search by username"
    And I should see "Search by email address"

  @javascript
  Scenario: 04. User doesn't exist.
    Given I set the following fields to these values:
      | username | unknownuser |
    And I press "Proceed"
    Then I should see "That username does not seem to exist."

  @javascript
  Scenario: 05. Invalid username/email.
    Given I set the following fields to these values:
      | username | invalid user |
    And I press "Proceed"
    Then I should see "That username/email doesn't look quite right, please double-check and try again."

  @javascript
  Scenario: 06. Username not provided.
    Given I press "Proceed"
    Then I should not see "Password"
    And I should see "Username"

  @javascript
  Scenario: 07. Forgot password.
    Given I set the following fields to these values:
      | username | student1 |
    And I press "Proceed"
    Then I should see "Password"
    And I click on "Forgotten your password?" "link"
    Then I should see "Search by username"
    And I should see "Search by email address"

  @javascript
  Scenario: 08. Change username.
    Given I set the following fields to these values:
      | username | student1 |
    And I press "Proceed"
    Then I should see "Password"
    And I click on "Change your username?" "link"
    Then I should see "Username"
    And I should not see "Password"

  @javascript
  Scenario: 09. Log in as guest.
    Given I set the following fields to these values:
      | username | guest |
    And I press "Proceed"
    Then I should see "You are currently using guest access"

  @javascript
  Scenario: 10. Remember username.
    Given I set the following fields to these values:
      | username | student1 |
    And I click on "rememberme" "checkbox"
    And I press "submitusername"
    And I set the field "password" to "pass1"
    And I press "submitpassword"
    And I log out
    And I visit the local URL "/local/signin/index.php"
    Then I should see "Username"
    And "username" "field" should exist
    And the field "username" matches value "student1"
    And I should see "Password"
    And "password" "field" should exist

  @javascript
  Scenario: 11. Redirect to URL.
    Given I visit the local URL "/local/signin/index.php?returnurl=/user/profile.php"
    And I set the field "username" to "student1"
    And I press "Proceed"
    And I set the field "password" to "pass1"
    And I press "Log In"
    Then the URL path should be "/user/profile.php"
