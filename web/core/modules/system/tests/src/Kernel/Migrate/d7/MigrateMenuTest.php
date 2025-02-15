<?php

declare(strict_types=1);

namespace Drupal\Tests\system\Kernel\Migrate\d7;

use Drupal\Core\Database\Database;
use Drupal\Tests\migrate_drupal\Kernel\d7\MigrateDrupal7TestBase;
use Drupal\system\Entity\Menu;

/**
 * Upgrade menus to system.menu.*.yml.
 *
 * @group migrate_drupal_7
 */
class MigrateMenuTest extends MigrateDrupal7TestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->executeMigration('d7_menu');
  }

  /**
   * Asserts various aspects of a menu.
   *
   * @param string $id
   *   The menu ID.
   * @param string $language
   *   The menu language.
   * @param string $label
   *   The menu label.
   * @param string $description
   *   The menu description.
   *
   * @internal
   */
  protected function assertEntity(string $id, string $language, string $label, string $description): void {
    $navigation_menu = Menu::load($id);
    $this->assertSame($id, $navigation_menu->id());
    $this->assertSame($language, $navigation_menu->language()->getId());
    $this->assertSame($label, $navigation_menu->label());
    $this->assertSame($description, $navigation_menu->getDescription());
  }

  /**
   * Tests the Drupal 7 menu to Drupal 8 migration.
   */
  public function testMenu(): void {
    $this->assertEntity('main', 'und', 'Main menu', 'The <em>Main</em> menu is used on many sites to show the major sections of the site, often in a top navigation bar.');
    $this->assertEntity('admin', 'und', 'Management', 'The <em>Management</em> menu contains links for administrative tasks.');
    $this->assertEntity('menu-test-menu', 'und', 'Test Menu', 'Test menu description.');
    $this->assertEntity('tools', 'und', 'Navigation', 'The <em>Navigation</em> menu contains links intended for site visitors. Links are added to the <em>Navigation</em> menu automatically by some modules.');
    $this->assertEntity('account', 'und', 'User menu', 'The <em>User</em> menu contains links related to the user\'s account, as well as the \'Log out\' link.');
    $this->assertEntity('menu-fixedlang', 'is', 'FixedLang', '');

    // Test that we can re-import using the ConfigEntityBase destination.
    Database::getConnection('default', 'migrate')
      ->update('menu_custom')
      ->fields(['title' => 'Home Navigation'])
      ->condition('menu_name', 'navigation')
      ->execute();

    $migration = $this->getMigration('d7_menu');
    \Drupal::database()
      ->truncate($migration->getIdMap()->mapTableName())
      ->execute();
    $this->executeMigration($migration);

    $navigation_menu = Menu::load('tools');
    $this->assertSame('Home Navigation', $navigation_menu->label());
  }

}
