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
    Given the following "users" exist:
      | username   | firstname | lastname | email                | password |
      | student1   | Student   | 1        | student1@example.com | pass1    |
      | student2   | Student   | 2        | student2@example.com | pass2    |
      | cohortless | Student   | 3        | student3@example.com | pass3    |
    And the following "cohorts" exist:
      | idnumber | name     |
      | cht1     | Cohort 1 |
      | cht2     | Cohort 2 |
    And the following "cohort members" exist:
      | user     | cohort |
      | student1 | cht1   |
      | student2 | cht2   |
    And the following "local_brandmanager" "brand" exist:
      | name   |
      | Brand1 |
      | Brand2 |
    And the following "bmdisco_cohort" "brand_cohort" exist:
      | brand  | cohort |
      | Brand1 | cht1   |
      | Brand2 | cht2   |
    And the following "bmdisco_domain" "brand_domain" exist:
      | brand  | domain         | defaultdomain |
      | Brand1 | 192.168.120.50 | 1             |
      | Brand2 | redirected.one | 1             |
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
    Given the following "core" configuration values are set:
      | local_signin_userdomain | bmdisco_domain\user_domain |
    And I set the following fields to these values:
      | username | student2 |
    And I press "Proceed"
    Then the full URL should be "http://redirected.one/behat/local/signin/index.php?username=student2"

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
  Scenario: 05. Username not provided.
    Given I press "Proceed"
    Then I should not see "Password"
    And I should see "Username"

  @javascript
  Scenario: 06. Forgot password.
    Given I set the following fields to these values:
      | username | student1 |
    And I press "Proceed"
    Then I should see "Password"
    And I click on "Forgotten your password?" "link"
    Then I should see "Search by username"
    And I should see "Search by email address"

  @javascript
  Scenario: 07. Change username.
    Given I set the following fields to these values:
      | username | student1 |
    And I press "Proceed"
    Then I should see "Password"
    And I click on "Change your username?" "link"
    Then I should see "Username"
    And I should not see "Password"

  @javascript
  Scenario: 08. Log in as guest.
    Given I set the following fields to these values:
      | username | guest |
    And I press "Proceed"
    Then I should see "You are currently using guest access"

  @javascript
  Scenario: 09. Remember username.
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
  Scenario: 10. Redirect to URL.
    Given I visit the local URL "/local/signin/index.php?returnurl=/user/profile.php"
    And I set the field "username" to "student1"
    And I press "Proceed"
    And I set the field "password" to "pass1"
    And I press "Log In"
    Then the URL path should be "/user/profile.php"

  @javascript
  Scenario: 11. A cohortless user is redirected to the default wwwroot of bmdisco_domain.
    Given the following "bmdisco_domain" configuration values are set:
      | defaultwwwroot | www.google.com |
    And the following "core" configuration values are set:
      | local_signin_userdomain | bmdisco_domain\user_domain |
    And I set the field "username" to "cohortless"
    And I press "Proceed"
    Then the full URL should be "http://www.google.com/behat/local/signin/index.php?username=cohortless"