<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\big_pipe\Render\Placeholder\BigPipeStrategy;
use Behat\Mink\Exception\UnsupportedDriverActionException;

/**
 * Big Pipe context.
 */
class BigPipeContext extends RawDrupalContext {

    /**
     * Prepares Big Pipe NOJS cookie if needed.
     *
     * TODO: breaking change, this executes before wdSession connected.
     * // @BeforeScenario
     */
    public function prepareBigPipeNoJsCookie()
    {
        try {
            // Check if JavaScript can be executed by Driver.
            $this->getSession()->getDriver()->executeScript('true');
        }
        catch (UnsupportedDriverActionException $e) {
            // Set NOJS cookie.
            // Skip `BigPipeStrategy::NOJS_COOKIE` to avoid bootstrapping.
            $this
              ->getSession()
              ->setCookie('big_pipe_nojs', true);

        }
        catch (\Exception $e) {
            // Mute exceptions.
        }
    }

}
