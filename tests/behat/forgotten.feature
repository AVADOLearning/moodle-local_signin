@local_signin @no_javascript
Feature: Forgotten username
  In order to be able to retrieve a forgotten username
  As a user
  I need to access a page where I can use various username recovery methods.

  Background:
    Given the following "users" exist:
      | username  | firstname | lastname | email                | password | deleted | suspended |
      | student1  | Student   | 1        | student1@example.com | pass1    | 0       | 0         |
      | student2  | Student   | 2        | student2@example.com | pass2    | 0       | 0         |
      | suspender | Student   | 3        | student3@example.com | pass3    | 0       | 1         |
      | deleter   | Student   | 4        | student4@example.com | pass4    | 1       | 0         |
    And the following "cohorts" exist:
      | idnumber | name     |
      | cht1     | Cohort 1 |
    And the following "cohort members" exist:
      | user     | cohort |
      | student1 | cht1   |
    And the following "local_brandmanager" "brand" exist:
      | name   |
      | Brand1 |
    And the following "bmdisco_cohort" "brand_cohort" exist:
      | brand  | cohort |
      | Brand1 | cht1   |
    And the following "bmdisco_domain" "brand_domain" exist:
      | brand  | domain         | defaultdomain |
      | Brand1 | 192.168.120.50 | 1             |
    And I visit the local URL "/local/signin/index.php?nojs=1"

  @javascript
  Scenario: 01. Clicking on the 'Forgotten your username?' link leads to the retrieval page.
    Given I follow "Forgotten your username?"
    Then I should see "Forgotten username or password"
    And I should see "Search by username"
    And I should see "Search by email address"
    And "username" "field" should exist
    And "email" "field" should exist
    And the URL path should be "/local/signin/forgot.php"

  @javascript
  Scenario: 02. Failing to provide an email address triggers a notification and does not advance the retrieval process.
    Given I follow "Forgotten your username?"
    And I press "submitemail"
    Then I should see "An email address is required."
    And I should not see "If you supplied a correct username or email address then an email should have been sent to you."
    And "username" "field" should exist
    And "email" "field" should exist
    And the URL path should be "/local/signin/forgot.php"

  @javascript
  Scenario: 03. Providing an active user's email in the forgotten form leads to the confirmation page.
    Given I follow "Forgotten your username?"
    And I set the field "email" to "student2@example.com"
    And I press "submitemail"
    Then I should see "If you supplied a correct username or email address then an email should have been sent to you."
    And "Continue" "button" should exist
    And the URL path should be "/local/signin/forgot.php"

  @javascript
  Scenario: 04. Providing any other kind of email in the forgotten form leads to the same confirmation page.
    Given I follow "Forgotten your username?"
    And I set the field "email" to "student3@example.com"
    And I press "submitemail"
    Then I should see "If you supplied a correct username or email address then an email should have been sent to you."
    And "Continue" "button" should exist
    And the URL path should be "/local/signin/forgot.php"

  @javascript
  Scenario: 05. Failing to provide an username triggers a notification and does not advance the retrieval process.
    Given I follow "Forgotten your username?"
    And I press "submitusername"
    Then I should see "An username is required."
    And I should not see "If you supplied a correct username or email address then an email should have been sent to you."
    And "username" "field" should exist
    And "email" "field" should exist
    And the URL path should be "/local/signin/forgot.php"

  @javascript
  Scenario: 06. Providing an active user's username in the forgotten form leads to the confirmation page.
    Given I follow "Forgotten your username?"
    And I set the field "username" to "student2"
    And I press "submitusername"
    Then I should see "If you supplied a correct username or email address then an email should have been sent to you."
    And "Continue" "button" should exist
    And the URL path should be "/local/signin/forgot.php"

  @javascript
  Scenario: 07. Providing any other kind of username in the forgotten form leads to the same confirmation page.
    Given I follow "Forgotten your username?"
    And I set the field "username" to "deleter"
    And I press "submitusername"
    Then I should see "If you supplied a correct username or email address then an email should have been sent to you."
    And "Continue" "button" should exist
    And the URL path should be "/local/signin/forgot.php"

  @javascript
  Scenario: 08. Clicking on the 'Forgotten your password?' link leads to the retrieval page.
    Given I set the field "username" to "student1"
    And I press "Proceed"
    And I follow "Forgotten your password?"
    Then I should see "Forgotten username or password"
    And I should see "Search by username"
    And I should see "Search by email address"
    And "username" "field" should exist
    And "email" "field" should exist
    And the URL path should be "/local/signin/forgot.php"
