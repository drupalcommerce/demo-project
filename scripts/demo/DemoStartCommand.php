<?php

namespace DemoCommerce;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Starts up a Drupal site for local testing/development.
 */
class DemoStartCommand extends Command {

  /**
   * The class loader.
   *
   * @var object
   */
  protected $classLoader;

  /**
   * Constructs a new DemoStartCommand command.
   *
   * @param object $class_loader
   *   The class loader.
   */
  public function __construct($class_loader) {
    parent::__construct('start');
    $this->classLoader = $class_loader;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Starts up a webserver for the dev site.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $root = $this->boot();
    $this->start($root, $output);
  }

  /**
   * Boots up a Drupal environment.
   *
   * @return string
   *   Returns the path to the Drupal root.
   */
  protected function boot() {
    $root = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
    chdir($root);
    return $root;
  }

  /**
   * Finds an available port.
   *
   * @return int
   */
  protected function getAvailablePort() {
    $address = '0.0.0.0';
    $port = 0;
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_bind($socket, $address, $port);
    socket_listen($socket, 5);
    socket_getsockname($socket, $address, $port);
    return $port;
  }

  /**
   * Starts up a webserver with a running Drupal.
   *
   * @param string $root
   *   The Drupal root.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The console output.
   */
  protected function start($root, OutputInterface $output) {
    $finder = new PhpExecutableFinder();
    $port = $this->getAvailablePort();
    if (($binary = $finder->find()) && $binary === FALSE) {
      throw new \RuntimeException('Unable to find the PHP binary.');
    }

    $root = dirname(dirname(__DIR__)) . '/web';
    chdir($root);

    $process = new Process(implode(' ', [
      $binary,
      '-S',
      'localhost:' . $port,
      '.ht.router.php',
    ]), $root, NULL, NULL, NULL);
    $output->writeln('Starting webserver on http://localhost:' . $port);
    $process->run();
  }

}
