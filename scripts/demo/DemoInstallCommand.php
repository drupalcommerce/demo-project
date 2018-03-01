<?php

namespace DemoCommerce;

use Drupal\Core\Database\Database;
use Drupal\Core\DrupalKernel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\HttpFoundation\Request;

define('MAINTENANCE_MODE', 'install');

/**
 * Installs a Drupal site for local testing/development.
 */
class DemoInstallCommand extends Command {

  /**
   * The class loader.
   *
   * @var \Composer\Autoload\ClassLoader
   */
  protected $classLoader;

  /**
   * Constructs a new DemoInstallCommand command.
   *
   * @param object $class_loader
   *   The class loader.
   */
  public function __construct($class_loader) {
    parent::__construct('install');
    $this->classLoader = $class_loader;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('install')
      ->setDescription('Installs a Drupal dev site. This is not meant for production or any custom development. It is a quick and easy way to get Drupal running.')
      ->addOption('langcode', NULL, InputOption::VALUE_OPTIONAL, 'The language to install the site in. Defaults to en', 'en')
      ->addOption('force', 'y', InputOption::VALUE_OPTIONAL, 'Force overriding the existing installation', FALSE);

    parent::configure();
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->changeRoot();

    if (!$input->getOption('force') && $this->isDrupalInstalled()) {
      $question_helper = new QuestionHelper();
      if (!$question_helper->ask($input, $output, new ConfirmationQuestion('There is already an existing installation. Confirm whether you want to override it. (y/N) ', FALSE))) {
        return;
      }
    }
    // Check whether there is already an installation.
    $this->install($this->classLoader, $output, 'demo_commerce', $input->getOption('langcode'));
  }

  /**
   * Returns whether there is already an existing Drupal installation.
   *
   * @return bool
   */
  protected function isDrupalInstalled() {
    $request = Request::createFromGlobals();
    DrupalKernel::createFromRequest($request, $this->classLoader, 'prod');

    return !empty(Database::getConnectionInfo());
  }

  /**
   * Changes the directory to the Drupal root.
   *
   * @return string
   *   Returns the path to the Drupal root.
   */
  protected function changeRoot() {
    $root = dirname(dirname(__DIR__)) . '/web';
    chdir($root);
    return $root;
  }

  /**
   * Installs Drupal with specified installation profile.
   *
   * @param object $class_loader
   *   The class loader.
   * @param \Symfony\Component\Console\Output\ConsoleOutputInterface $output
   *   The console output.
   * @param string $profile
   *   (optional) The installation profile to use.
   * @param string $langcode
   *   (optional) The language to install the site in.
   */
  protected function install($class_loader, ConsoleOutputInterface $output, $profile = 'standard', $langcode = 'en') {
    $parameters = [
      'interactive' => FALSE,
      'parameters' => [
        'profile' => $profile,
        'langcode' => $langcode,
      ],
      'forms' => [
        'install_settings_form' => [
          'driver' => 'sqlite',
          'sqlite' => [
            'database' => 'sites/default/files/.sqlite',
          ],
        ],
        'install_configure_form' => [
          'site_name' => 'Drupal',
          'site_mail' => 'simpletest@example.com',
          'account' => [
            'name' => 'admin',
            'mail' => 'admin@localhost',
            'pass' => [
              'pass1' => 'test',
              'pass2' => 'test',
            ],
          ],
          // form_type_checkboxes_value() requires NULL instead of FALSE values
          // for programmatic form submissions to disable a checkbox.
          'enable_update_status_module' => NULL,
          'enable_update_status_emails' => NULL,
        ],
      ],
    ];

    require_once __DIR__ . '/../../web/core/includes/install.core.inc';

    if (!extension_loaded('pdo_sqlite')) {
      $output->getErrorOutput()->writeln('You need to have sqlite installed.');
      return;
    }
    if (file_exists('sites/default/settings.php')) {
      $result = unlink('sites/default/settings.php');
      if ($result === FALSE) {
        $output->getErrorOutput()->writeln('Removing settings.php failed, please do it manually ...');
        return;
      }
    }
    if (file_exists('sites/default/files/.sqlite')) {
      $result = unlink('sites/default/files/.sqlite');
      if ($result === FALSE) {
        $output->getErrorOutput()->writeln('Removing .sqlite failed, please do it manually ...');
        return;
      }
    }

    $output->writeln('Drupal installation started.');
    install_drupal($class_loader, $parameters);
    $output->writeln('Drupal successfully installed.');
  }

}
