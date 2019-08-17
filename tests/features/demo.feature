@javascript @demo
Feature: Demo Walkthrough
  As a user
  I can use the demo
  To try out Commerce

  Scenario: Homepage test
    Given I am an anonymous user
    When I am on the homepage
    Then I should see "Featured products"
    Then I should see "Slow-carb paleo bicycle rights bushwick. Tote bag mustache man bun swag, tbh chartreuse synth stumptown portland cray."

  Scenario: Menu test
    Given I am an anonymous user
    And I am on the homepage
    When I click "Women" in the "Header Catalog Menu" region
      Then I should see the "Catalog"
    When I click "Men" in the "Header Catalog Menu" region
      Then I should see the "Catalog"
    When I click "Apothecary" in the "Header Catalog Menu" region
      Then I should see the "Catalog"
    When I click "Urban Living" in the "Header Catalog Menu" region
      Then I should see the "Catalog"
    When I click "Audio & Film" in the "Header Catalog Menu" region
      Then I should see the "Catalog"
    When I click "Print Shop" in the "Header Catalog Menu" region
      Then I should see the "Catalog"
