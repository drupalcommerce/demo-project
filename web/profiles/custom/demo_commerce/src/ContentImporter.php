<?php

namespace Drupal\demo_commerce;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\taxonomy\TermInterface;

/**
 * Defines the content importer.
 *
 * @internal
 *   For internal usage by the Commerce Demo module.
 */
class ContentImporter {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The full path to the content directory.
   *
   * @var string
   */
  protected $contentPath;

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\Entity\StoreInterface
   */
  protected $store;

  /**
   * Constructs a new ContentImporter object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->contentPath = realpath(__DIR__ . '/../content');
  }

  /**
   * Reacts on the module being installed, imports all content.
   */
  public function onInstall() {
    // It is necessary to hardcode the available entity types/bundles to ensure
    // the right import order, because there is no dependency tracking.
    $available_content = [
      ['node', 'page'],
      ['menu_link_content', 'menu_link_content'],
    ];
    foreach ($available_content as $keys) {
      $this->importAll($keys[0], $keys[1]);
    }
  }

  /**
   * Imports all content for the given entity type and bundle.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The bundle.
   */
  public function importAll($entity_type_id, $bundle = '') {
    $filepath = $this->buildFilepath($entity_type_id, $bundle);
    if (!is_readable($filepath)) {
      throw new \InvalidArgumentException(sprintf('The %s file could not be found/read.', $filepath));
    }
    $data = Yaml::decode(file_get_contents($filepath));

    foreach ($data as $uuid => $values) {
      $values['uuid'] = $uuid;
      $this->importEntity($entity_type_id, $values);
    }
  }

  /**
   * Imports a given entity.
   *
   * If an entity with the given UUID already exists, it will be updated.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param array $values
   *   The entity values.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The created or updated entity.
   */
  public function importEntity($entity_type_id, array $values) {
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $wanted_keys = ['bundle', 'langcode', 'uuid'];
    $wanted_keys = array_combine($wanted_keys, $wanted_keys);
    $entity_keys = array_intersect_key($entity_type->getKeys(), $wanted_keys);
    $storage = $this->entityTypeManager->getStorage($entity_type_id);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->loadEntityByUuid($entity_type_id, $values['uuid']);
    if (!$entity) {
      // No existing entity found, create a new one.
      $initial_values = array_intersect_key($values, array_flip($entity_keys));
      $entity = $storage->create($initial_values);
    }
    // Process values.
    $values = array_diff_key($values, array_flip($entity_keys));
    foreach ($entity->getFieldDefinitions() as $field_name => $definition) {
      if (!isset($values[$field_name])) {
        continue;
      }

      $storage_definition = $definition->getFieldStorageDefinition();
      $items = $values[$field_name];
      // Re-add the wrapper array stripped by ContentExporter.
      if ($storage_definition->getCardinality() === 1) {
        $items = [$items];
      }
      foreach ($items as $delta => $item) {
        if ($definition->getType() == 'entity_reference' && is_string($item)) {
          $target_entity_type_id = $storage_definition->getSetting('target_type');
          $target_entity_type = $this->entityTypeManager->getDefinition($target_entity_type_id);
          if ($target_entity_type->entityClassImplements(ContentEntityInterface::class)) {
            $target_entity = $this->loadEntityByUuid($target_entity_type_id, $item);
            if ($target_entity) {
              $items[$delta] = $target_entity->id();
            }
            else {
              unset($items[$delta]);
            }
          }
        }
        elseif ($definition->getType() == 'image') {
          $file = $this->ensureFile($item['filename']);
          $items[$delta] = [
            'target_id' => $file->id(),
          ] + $item;
        }
        $values[$field_name] = $items;
      }
    }
    // Perform generic processing.
    if (substr($entity_type_id, 0, 9) == 'commerce_') {
      $values = $this->processCommerce($values, $entity);
    }
    // Process by entity type ID.
    if ($entity_type_id == 'commerce_product') {
      $values = $this->processProduct($values, $entity);
    }
    elseif ($entity_type_id == 'taxonomy_term') {
      $values = $this->processTerm($values, $entity);
    }

    foreach ($values as $field_name => $items) {
      $entity->set($field_name, $items);
    }
    $entity->save();

    return $entity;
  }

  /**
   * Processes Commerce entity values before importing.
   *
   * @param array $values
   *   The entity values.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The Commerce entity.
   *
   * @return array
   *   The processed entity values.
   */
  protected function processCommerce(array $values, ContentEntityInterface $entity) {
    $store = $this->ensureStore();
    if ($entity->hasField('stores')) {
      $values['stores'] = [$store];
    }
    elseif ($entity->hasField('store')) {
      $values['store'] = $store;
    }

    return $values;
  }

  /**
   * Processes product values before importing.
   *
   * @param array $values
   *   The product values.
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *   The product.
   *
   * @return array
   *   The processed product values.
   */
  protected function processProduct(array $values, ProductInterface $product) {
    $variation_ids = [];
    foreach ($values['variations'] as $uuid => $variation_values) {
      $variation_values['uuid'] = $uuid;
      $variation = $this->importEntity('commerce_product_variation', $variation_values);
      $variation_ids[] = $variation->id();
    }
    $values['variations'] = $variation_ids;

    return $values;
  }

  /**
   * Processes taxonomy term values before importing.
   *
   * @param array $values
   *   The taxonomy term values.
   * @param \Drupal\taxonomy\TermInterface $term
   *   The taxonomy term.
   *
   * @return array
   *   The processed taxonomy term values.
   */
  protected function processTerm(array $values, TermInterface $term) {
    if (!isset($values['parent'])) {
      $values['parent'] = [0];
    }
    return $values;
  }

  /**
   * Loads an entity by UUID.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param int $entity_uuid
   *   The entity UUID.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The loaded entity, or NULL if none found.
   */
  protected function loadEntityByUuid($entity_type_id, $entity_uuid) {
    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    $entities = $storage->loadByProperties(['uuid' => $entity_uuid]);

    return $entities ? reset($entities) : NULL;
  }

  /**
   * Builds the filepath for the given entity type's export file.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The bundle.
   *
   * @return string
   *   The filepath.
   */
  protected function buildFilepath($entity_type_id, $bundle = '') {
    $filepath = $this->contentPath . '/' . $entity_type_id;
    if ($bundle) {
      $filepath .= '.' . $bundle;
    }
    $filepath .= '.yml';

    return $filepath;
  }

  /**
   * Ensures the existence of a store.
   *
   * @return \Drupal\commerce_store\Entity\StoreInterface
   *   The store.
   */
  protected function ensureStore() {
    if (!$this->store) {
      $store_storage = $this->entityTypeManager->getStorage('commerce_store');
      $store = $store_storage->loadDefault();
      if (!$store) {
        $store = $store_storage->create([
          'type' => 'online',
          'name' => 'US Store',
          'mail' => 'admin@example.com',
          'default_currency' => 'USD',
          'address' => [
            'country_code' => 'US',
            'administrative_area' => 'SC',
            'locality' => 'Greenville',
            'postal_code' => '29616',
            'address_line1' => '12344 24th St',
          ],
          'billing_countries' => ['US'],
          'prices_include_tax' => FALSE,
        ]);
        $store->save();
        $store_storage->markAsDefault($store);
      }
      $this->store = $store;
    }

    return $this->store;
  }

  /**
   * Ensures the existence of a file.
   *
   * @param string $filename
   *   The filename. Assumed to exist in the content/files module subdirectory.
   *
   * @return \Drupal\file\FileInterface
   *   The file.
   */
  protected function ensureFile($filename) {
    $file_storage = $this->entityTypeManager->getStorage('file');
    $files = $file_storage->loadByProperties(['filename' => $filename]);
    $file = reset($files);
    if (!$file) {
      $path = $this->contentPath . '/files/' . $filename;
      $uri = file_unmanaged_copy($path, 'public://' . $filename, FILE_EXISTS_REPLACE);
      $file = $file_storage->create([
        'filename' => $filename,
        'uri' => $uri,
        'status' => 1,
      ]);
      $file->save();
    }

    return $file;
  }

}
