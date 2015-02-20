Feature: [NovaEZ] Check whitespaces before and after comma in eZ Publish PHP files

  Scenario: There are no whitespace after comma between paramaters in function declaration
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

      /**
       * Test class
       */
      class TestClass
      {
          /**
           * First test method for missing tag sniffs
           *
           * @param string $param1 first param
           * @param array  $param2 second param
           */
          public function testMethod( $param1,$param2 )
          {
          }
      }

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       22 | ERROR | No space found after comma in function definition
      """

  Scenario: There are no whitespace after comma between paramaters in function call
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

      $foo = FooProvider::factory( $config["bar"],true );

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       11 | ERROR | No space found after comma in function call
      """
