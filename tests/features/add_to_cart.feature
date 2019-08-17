@javascript @demo
Feature: Add to cart
    In order to purchase a product
    As a customer
    I want to add it to my cart

  Scenario: Add to cart
    Given I am an anonymous user
    And I am on the homepage
    When I click "Men" in the "Header Catalog Menu" region
    Then I click "Drupal Commerce Hoodie"
    And I should see "Urban Hipster"
    And I should see "$52.00"
    When I press "Add to cart"
    Then I should see "Drupal Commerce Hoodie - Blue, Small" in the "Cart Flyout"
    And I should see "1 item" in the "Cart block"
    When I click "View cart" in the "Cart Flyout"
    Then I should see "Shopping cart"
