Feature: [NovaEZ] Check whitespaces in square brackets in eZ Publish PHP files

  Scenario: There are whitespace after opening and before closing square brackets
    Given there is a file named "features/testFile.php" with:
      """
      <?php
      /**
       * This file is part of the novactive/phpcs-novastandards package
       *
       * @package   Nova\Standards
       * @author    Guillaume Maïssa <g.maissa@novactive.com>
       * @copyright 2015 Novactive
       * @license   Proprietary
       */

      $foo = FooProvider::factory( $config[ "bar" ] );

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       11 | ERROR | Space found after square bracket; expected "["bar"" but found "[
          |       | "bar""
      """
    And the output should contain:
      """
       11 | ERROR | Space found before square bracket; expected ""bar"]" but found
          |       | ""bar" ]"
      """
