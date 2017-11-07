@local_signin @no_javascript
Feature: Log in to platform
  In order to view my course
  As a user
  I need to log in to the platform, having had my domain and credentials validated.

  Background:
    Given I log in as "admin"
    And the following "users" exist:
      | username  | firstname | lastname | email                | password | deleted | suspended | confirmed |
      | student1  | Student   | 1        | student1@example.com | pass1    | 0       | 0         | 1         |
      | student2  | Student   | 2        | student2@example.com | pass2    | 0       | 0         | 1         |
      | suspender | Student   | 3        | student3@example.com | pass3    | 0       | 1         | 1         |
      | deleter   | Student   | 4        | student4@example.com | pass4    | 1       | 0         | 1         |
      | student5  | Student   | 5        | student5@example.com | pass5    | 0       | 0         | 0         |
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
  Scenario: 01. The login page first displays all the elements of the username form.
    Given I should see "Username"
    And I should not see "Password"
    And "username" "field" should exist
    And "password" "field" should exist
    And I should see "Remember username"
    And "rememberme" "checkbox" should exist
    And "submitusername" "button" should exist
    And "submitpassword" "button" should exist
    And "Forgotten your username?" "link" should exist

  @javascript
  Scenario: 02. Failing to provide a username triggers a notification and does not advance the signin process.
    Given I press "Proceed"
    Then I should see "This username does not exist or it is not active"
    And I should see "Username"
    And I should not see "Password"
    And the URL path should be "/behat/local/signin/index.php"

  @javascript
  Scenario: 03. Providing a non-existent username triggers a notification and does not advance the signin process.
    Given I set the field "username" to "ghost"
    And I press "Proceed"
    Then I should see "This username does not exist or it is not active"
    And I should see "Username"
    And I should not see "Password"
    And the URL path should be "/behat/local/signin/index.php"

  @javascript
  Scenario: 04. Providing a suspended user's username triggers a notification and does not advance the signin process.
    Given I set the field "username" to "suspender"
    And I press "Proceed"
    Then I should see "This username does not exist or it is not active"
    And I should see "Username"
    And I should not see "Password"
    And the URL path should be "/behat/local/signin/index.php"

  @javascript
  Scenario: 05. Providing a deleted user's username triggers a notification and does not advance the signin process.
    Given I set the field "username" to "deleter"
    And I press "Proceed"
    Then I should see "This username does not exist or it is not active"
    And I should see "Username"
    And I should not see "Password"
    And the URL path should be "/behat/local/signin/index.php"

  @javascript
  Scenario: 06. Providing an unconfirmed username triggers a notification and does not advance the signin process.
    Given I set the field "username" to "student5"
    And I press "Proceed"
    Then I should see "You need to confirm your login"
    And I should not see "Username"
    And I should not see "Password"
    And the URL path should be "/behat/local/signin/index.php"

  @javascript
  Scenario: 07. Providing a username associated with another domain redirects there.
    Given I set the field "username" to "student2"
    And I press "Proceed"
    Then the full URL should be "http://otherdomain.net/behat/local/signin/index.php?username=student2"

  @javascript
  Scenario: 08. Providing 'guest' username triggers a notification and advances to homepage.
    Given I set the field "username" to "guest"
    And I press "Proceed"
    Then I should see "You are currently using guest access"
    And I should not see "This username does not exist or it is not active"
    And "username" "field" should not exist
    And "password" "field" should not exist
    And the URL path should be "/behat/"
