@block @block_gamification
Feature: A teacher can change the settings for their instance
  In order to customise the behaviour of the plugin
  As a teacher
  I can use the settings page

  Scenario: A teacher can changes various settings
    Given the following "users" exist:
      | username | firstname | lastname | email          |
      | t1       | Teacher   | One      | t1@example.com |
    And the following "courses" exist:
      | fullname  | shortname |
      | Course 1  | c1        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | t1       | c1     | editingteacher |
    And I log in as "t1"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add the "Level Up gamification" block
    When I am on "Course 1" course homepage
    And I click on "Settings" "link" in the "Level up!" "block"
    And the field "Enable the leaderboard" matches value "Yes"
    And the field "Anonymity" matches value "Display participants identity"
    And the field "Title" matches value "Level up!"
    And I set the field "Enable the leaderboard" to "No"
    And I set the field "Anonymity" to "Hide participants identity"
    And I set the field "Title" to "New level up title"
    And I press "Save changes"
    Then the field "Enable the leaderboard" matches value "No"
    And the field "Anonymity" matches value "Hide participants identity"
    And the field "Title" matches value "New level up title"
    And I am on "Course 1" course homepage
    And I should see "New level up title"
