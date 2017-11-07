@local_signin @no_javascript
Feature: Log in to platform
  In order to view my course
  As a user
  I need to log in to the platform, having had my domain and credentials validated.

  Background:
    Given I log in as "admin"
    And the following "users" exist:
      | username  | firstname | lastname | email                | password | deleted | suspended |
      | student1  | Student   | 1        | student1@example.com | pass1    | 0       | 0         |
      | student2  | Student   | 2        | student2@example.com | pass2    | 0       | 0         |
      | suspender | Student   | 3        | student3@example.com | pass3    | 0       | 1         |
      | deleter   | Student   | 4        | student4@example.com | pass4    | 1       | 0         |
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
      | domain | otherdomain.net |
    And I press "Save changes"
    And I log out
    And I visit the local URL "/local/signin/index.php?nojs=1"

  @javascript
  Scenario: 01. Providing a username associated with existing domain advances to password form.
    Given I set the field "username" to "student1"
    And I press "Proceed"
    Then I should see "Username"
    And the field "username" matches value "student1"
    And I should see "Password"
    And I should not see "This username does not exist or it is not active"
    And the URL path should be "/local/signin/index.php"

  @javascript
  Scenario: 02. Providing a wrong password triggers a notification and does not advance the signin process.
    Given I set the field "username" to "student1"
    And I press "Proceed"
    And I set the field "password" to "huehuehue"
    And I press "Log In"
    Then I should see "Invalid login, please try again"
    And the URL path should be "/local/signin/index.php"

  @javascript
  Scenario: 03. Providing another user's password triggers a notification and does not advance the signin process.
    Given I set the field "username" to "student1"
    And I press "Proceed"
    And I set the field "password" to "pass2"
    And I press "Log In"
    Then I should see "Invalid login, please try again"
    And the URL path should be "/local/signin/index.php"

  @javascript
  Scenario: 04. Providing the correct password advances to homepage.
    Given I set the field "username" to "student1"
    And I press "Proceed"
    And I set the field "password" to "pass1"
    And I press "Log In"
    Then I should not see "Invalid login, please try again"
    And the URL path should be "/my/"

  @javascript
  Scenario: 05. Checking 'Remember username' leads straight to username + password on next login.
    Given I set the field "username" to "student1"
    And I click on "rememberme" "checkbox"
    And I press "Proceed"
    And I set the field "password" to "pass1"
    And I press "Log In"
    And I log out
    And I visit the local URL "/local/signin/index.php"
    Then I should see "Username"
    And "username" "field" should exist
    And the field "username" matches value "student1"
    And I should see "Password"
    And "password" "field" should exist
    And "rememberme" "checkbox" should not exist
    And "Change your username?" "link" should exist
    And "Forgotten your password?" "link" should exist
    And "Forgotten your username?" "link" should not exist

  @javascript
  Scenario: 06. User can return to change their username before providing a password.
    Given I set the field "username" to "student1"
    And I press "Proceed"
    And the field "username" matches value "student1"
    And I click on "Change your username?" "link"
    Then I should see "Username"
    And "submitusername" "button" should exist
    And the field "username" does not match value "student1"
    And I should not see "Password"
    And I should not see "Log In"

  @javascript
  Scenario: 07. Visiting the signin page while logged in redirects to logout confirmation.
    Given I set the field "username" to "student1"
    And I press "Proceed"
    And I set the field "password" to "pass1"
    And I press "Log In"
    And I visit the local URL "/local/signin/index.php"
    Then I should not see "Username"
    And "username" "field" should not exist
    And "Proceed" "button" should not exist
    And I should see "Do you really want to log out?"
    And "Continue" "button" should exist
    And "Cancel" "button" should exist
    And the URL path should be "/login/logout.php"

  @javascript
  Scenario: 08. If a redirect exists, user is taken there after successful login.
    Given I visit the local URL "/local/signin/index.php?returnurl=/user/profile.php"
    And I set the field "username" to "student1"
    And I press "Proceed"
    And I set the field "password" to "pass1"
    And I press "Log In"
    Then the URL path should be "/user/profile.php"


