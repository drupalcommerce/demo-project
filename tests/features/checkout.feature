@javascript @demo
Feature: Checkout
    In order to complete my order
    As a customer
    I want checkout and make payment

    Scenario: Complete checkout
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
        When I press "Checkout"
        Then I press "Continue as Guest"
        And I should not see "There are no payment gateways available for this order. Please try again later."
        Then I fill in the following:
            | contact_information[email]                                                | me@cg.com        |
            | shipping_information[shipping_profile][address][0][address][given_name]   | Matt             |
            | shipping_information[shipping_profile][address][0][address][family_name]  | Glaman           |
            | shipping_information[shipping_profile][address][0][address][address_line1]| 4039 80th Street |
            | shipping_information[shipping_profile][address][0][address][locality]     | Kenosha          |
            | shipping_information[shipping_profile][address][0][address][administrative_area] | WI        |
            | shipping_information[shipping_profile][address][0][address][postal_code]         | 53140     |
        Then I press "Recalculate shipping"
        And I wait for Drupal's AJAX to finish
        And I should see "Standard shipping: $8.00"
