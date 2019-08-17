<?php

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Contains the name of the currently selected iframe.
   *
   * @var string
   */
  private $iframe = NULL;

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
  }

  /**
   * @When I switch to the frame :frame
   */
  public function iSwitchToTheFrame($frame) {
    $this->getSession()->switchToIFrame($frame);
    $this->iframe = $frame;
  }

  /**
   * @When I switch out of all frames
   */
  public function iSwitchOutOfAllFrames() {
    $this->getSession()->switchToIFrame();
    $this->iframe = NULL;
  }

  /**
   * @When I switch to the frame by selector :arg
   */
  public function switchToIFrameFromSelector($iframeSelector) {
    $function = <<<JS
    (function(){var iframe = document.querySelector("$iframeSelector");iframe.name = "iframeToSwitchTo";})()
JS;
    try {
        $this->getSession()->executeScript($function);
    }catch (Exception $e){
        print_r($e->getMessage());
        throw new \Exception("Element $iframeSelector was NOT found.".PHP_EOL . $e->getMessage());
    }
    $this->getSession()->getDriver()->switchToIFrame("iframeToSwitchTo");
  }

  /**
   * @When /^(?:|I )fill in the iframe field with "(?P<value>(?:[^"]|\\")*)"$/
   */
  public function iFillInTheIframeField($value)
  {
    $this->spin(function (FeatureContext $context) use ($value) {
      try {
        $session = $this->getSession();
        $field = $this->getSession()->getPage()->find('css', 'input:not(.StripeField--fake):first-child');

        if (!$field) {
          throw new \Exception(sprintf('No input found on the page %s.', $session->getCurrentUrl()));
        }
        $field->focus();
        usleep(550000);
        $field->setValue($value);
        return TRUE;
      }
      catch (\Exception $e) {
        return FALSE;
      }
    });
  }

  /**
   * Click on non-anchor element.
   *
   * @Then /^I click on "([^"]*)"$/
   */
  public function iClickOn($element) {
    $page = $this->getSession()->getPage();
    $findName = $page->find("css", $element);
    if (!$findName) {
      throw new Exception($element . " could not be found");
    }
    else {
      $findName->click();
    }
  }

  /**
   * Just wait.
   *
   * @Given I wait
   */
  public function iWait() {
    sleep(1);
  }

  /**
   * @Then I should see the :region
   */
  public function iShouldSeeRegion($region) {
    $session = $this->getSession();
    $region_obj = $session->getPage()->find('region', $region);
    if (!$region_obj) {
      throw new \Exception(sprintf('No region "%s" found on the page %s.', $region, $session->getCurrentUrl()));
    }
  }

  /**
   * @Given I wait for the :region region
   */
  public function iWaitForTheRegion($region) {
    $this->spin(function (FeatureContext $context) use ($region) {
      try {
        $session = $this->getSession();
        $region_obj = $session->getPage()->find('region', $region);
        if (!$region_obj) {
          throw new \Exception(sprintf('No region "%s" found on the page %s.', $region, $session->getCurrentUrl()));
        }
        return TRUE;
      }
      catch (\Exception $e) {
        return FALSE;
      }
    });
  }

  /**
   * @Then /^(?:|I )wait to see "(?P<text>(?:[^"]|\\")*)"$/
   */
  public function iWaitToSee($text) {
    $this->spin(function (FeatureContext $context) use ($text) {
      try {
        $this->assertSession()->pageTextContains($text);
        return TRUE;
      }
      catch (ElementNotFoundException $e) {
        return FALSE;
      }
    });
  }

  /**
   * Based on Behat's own example.
   *
   * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
   * @param $lambda
   * @param int $wait
   * @throws \Exception
   */
  public function spin($lambda, $wait = 60) {
    $time = time();
    $stopTime = $time + $wait;
    while (time() < $stopTime) {
      try {
        if ($lambda($this)) {
          return;
        }
      }
      catch (\Exception $e) {
        // do nothing.
      }

      usleep(250000);
    }

    throw new \Exception("Spin function timed out after {$wait} seconds");
  }

  /**
   * Wait for Drupal AJAX to finish.
   *
   * @Given I wait for Drupal's AJAX to finish
   */
  function iWaitForDrupalAjax() {
    $condition = <<<JS
      (function() {
        function isAjaxing(instance) {
          return instance && instance.ajaxing === true;
        }
        return (
          // Assert no AJAX request is running (via jQuery or Drupal) and no
          // animation is running.
          (typeof jQuery === 'undefined' || (jQuery.active === 0 && jQuery(':animated').length === 0)) &&
          (typeof Drupal === 'undefined' || typeof Drupal.ajax === 'undefined' || !Drupal.ajax.instances.some(isAjaxing))
        );
      }());
JS;
    $this->getSession()->wait(10000, $condition);
  }

  /**
   * @Then I switch to the modal window
   */
  public function iSwitchToTheModalWindow()
  {
    $windowNames = $this->getSession()->getWindowNames();
    if(count($windowNames) > 1) {
      $this->getSession()->switchToWindow($windowNames[1]);
    }
  }

  /**
   * @Then I wait for the modal window to close
   */
  public function iWaitForTheModalWindowToClose() {
    $this->spin(function (FeatureContext $context) {
      try {
        $windowNames = $this->getSession()->getWindowNames();
        return count($windowNames) === 1;
      }
      catch (ElementNotFoundException $e) {
        return FALSE;
      }
    });
  }

  /**
   * @Then I switch back to the main window
   */
  public function iSwitchBackToTheMainWindow() {
    $windowNames = $this->getSession()->getWindowNames();
    $this->getSession()->switchToWindow($windowNames[0]);
  }

}
