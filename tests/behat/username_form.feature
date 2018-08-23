@local_signin @no_javascript
Feature: Log in to platform
  In order to view my course
  As a user
  I need to log in to the platform, having had my domain and credentials validated.

  Background:
    Given the following "users" exist:
      | username   | firstname | lastname | email                | password | deleted | suspended | confirmed |
      | student1   | Student   | 1        | student1@example.com | pass1    | 0       | 0         | 1         |
      | student2   | Student   | 2        | student2@example.com | pass2    | 0       | 0         | 1         |
      | suspender  | Student   | 3        | student3@example.com | pass3    | 0       | 1         | 1         |
      | deleter    | Student   | 4        | student4@example.com | pass4    | 1       | 0         | 1         |
      | student5   | Student   | 5        | student5@example.com | pass5    | 0       | 0         | 0         |
      | cohortless | Student   | 6        | student6@example.com | pass6    | 0       | 0         | 1         |
      | samemail1  | Samemail  | 1        | samemail@example.com | pass7    | 0       | 0         | 1         |
      | samemail2  | Samemail  | 2        | samemail@example.com | pass8    | 0       | 0         | 1         |
    And the following "cohorts" exist:
      | idnumber | name     |
      | cht1     | Cohort 1 |
      | cht2     | Cohort 2 |
    And the following "cohort members" exist:
      | user     | cohort |
      | student1 | cht1   |
      | student2 | cht2   |
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
    And the URL path should be "/local/signin/index.php"

  @javascript
  Scenario: 03. Providing a non-existent username triggers a notification and does not advance the signin process.
    Given I set the field "username" to "ghost"
    And I press "Proceed"
    Then I should see "This username does not exist or it is not active"
    And I should see "Username"
    And I should not see "Password"
    And the URL path should be "/local/signin/index.php"

  @javascript
  Scenario: 04. Providing a suspended user's username triggers a notification and does not advance the signin process.
    Given I set the field "username" to "suspender"
    And I press "Proceed"
    Then I should see "This username does not exist or it is not active"
    And I should see "Username"
    And I should not see "Password"
    And the URL path should be "/local/signin/index.php"

  @javascript
  Scenario: 05. Providing a deleted user's username triggers a notification and does not advance the signin process.
    Given I set the field "username" to "deleter"
    And I press "Proceed"
    Then I should see "This username does not exist or it is not active"
    And I should see "Username"
    And I should not see "Password"
    And the URL path should be "/local/signin/index.php"

  @javascript
  Scenario: 06. Providing an unconfirmed username triggers a notification and does not advance the signin process.
    Given I set the field "username" to "student5"
    And I press "Proceed"
    Then I should see "You need to confirm your login"
    And I should not see "Username"
    And I should not see "Password"
    And the URL path should be "/local/signin/index.php"

  @javascript
  Scenario: 07. Providing a username associated with another domain redirects there.
    Given the following "core" configuration values are set:
      | local_signin_domainfinder | \local_signin\domainfinder\test_default_domain_finder |
    And I set the field "username" to "student2"
    And I press "Proceed"
    Then the URL domain should be "redirected.one"

  @javascript
  Scenario: 08. Providing 'guest' username triggers a notification and advances to homepage.
    Given I set the field "username" to "guest"
    And I press "Proceed"
    Then I should see "You are currently using guest access"
    And I should not see "This username does not exist or it is not active"
    And "username" "field" should not exist
    And "password" "field" should not exist
    And the URL path should be "/"

  @javascript
  Scenario: 09. Providing email adress associated to multiple users triggers a notification and does not advance the signin process.
    Given I set the field "username" to "samemail@example.com"
    When I press "Proceed"
    Then I should see "Field must be unique (if you're using your email, maybe try your username)."
    And I should see "Username"
    And I should not see "Password"
    And the URL path should be "/local/signin/index.php"

