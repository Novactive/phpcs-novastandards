Feature: [NovaEZ] Check parenthesis positions in eZ Publish PHP files

  Scenario: There are no whitespace after the opening and before the closing parenthesis of a function definition that has parameters
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
      class eZTestClass
      {
          /**
           * Test method
           *
           * @param string $param1
           */
          public function testMethod($param1)
          {
              echo $param1;
          }
      }

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       21 | ERROR | Expected 1 space after opening bracket; 0 found
      """
    And the output should contain:
      """
       21 | ERROR | Expected 1 space before closing bracket; 0 found
      """

  Scenario: There are whitespaces after the opening and before the closing parenthesis of a function definition that has no parameters
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
      class eZTestClass
      {
          /**
           * Test method
           */
          public function testMethod( )
          {
          }
      }

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       19 | ERROR | Without parameters, space are forbidden in function declaration
      """

  Scenario: There are no whitespace after the opening and before the closing parenthesis of a function call that has arguments
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

      $foo = FooProvider::factory($config["bar"], $config["baz"], true);

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       11 | ERROR | A space is required after opening parenthesis of function call
      """
    And the output should contain:
      """
       11 | ERROR | A space is required before closing parenthesis of function call
      """

  Scenario: There are whitespace after the opening and before the closing parenthesis of a function call that does not have arguments
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

      $foo = FooProvider::factory( );

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       11 | ERROR | Without arguments, space are forbidden in function call
      """

  Scenario: There are whitespace after the opening and before the closing parenthesis of a cast statement
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

      $bar = ( string )FooProvider::baz();

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       11 | ERROR | Cast statements must not contain whitespace; expected "(string)"
          |       | but found "( string )"
      """
