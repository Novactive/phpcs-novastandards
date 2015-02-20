Feature: [NovaEZ] Check brackets positions in eZ Publish PHP files

  Scenario: The opening bracket of a control structure is at the end of the first line
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
      if ( $test == 'test' ) {
          echo "true";
      }
      else {
          echo "false";
      }
      foreach ( $array as $entry ) {
          echo $entry;
      }
      for ($i=0; $i<10; $i++) {
          echo $i;
      }
      while ( $i < 10 ) {
          echo $i;
          $i++;
      }
      $i = 0;
      do {
          echo $i;
          $i++;
      }
      while ($i < 10);

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       10 | ERROR | Expected "if (...)\n...{\n"; found "if (...) ...{\n"
      """
    And the output should contain:
      """
       13 | ERROR | Expected "}\nelse\n...{\n"; found "}\nelse ...{\n"
      """
    And the output should contain:
      """
       16 | ERROR | Expected "foreach (...)\n...{\n"; found "foreach (...) ...{\n"
      """
    And the output should contain:
      """
       19 | ERROR | Expected "for (...)\n...{\n"; found "for (...) ...{\n"
      """
    And the output should contain:
      """
       22 | ERROR | Expected "while (...)\n...{\n"; found "while (...) ...{\n"
      """
    And the output should contain:
      """
       27 | ERROR | Expected "do\n...{\n...}\n...while (...);\n"; found "do
          |       | ...{\n...}\n...while (...);\n"
      """

  Scenario: The opening bracket of a class is at the end of the first line
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
      class eZTestClass {
      }

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       14 | ERROR | Opening brace of a class must be on the line after the definition
      """

  Scenario: The opening bracket of a method is at the end of the first line
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
          public function testMethod() {
          }
      }

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       19 | ERROR | Opening brace should be on a new line
      """

  Scenario: The opening bracket of a multi ligne declatation method is on the same line as the closing parenthesis
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
           * @param string $param1 first param
           * @param array  $param2 second param
           * @param array  $param3 third param
           */
          public function testMethod(
              $param1,
              $param2,
              $param3
          ) {

          }
      }

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should contain:
      """
       27 | ERROR | Opening brace should be on a new line
      """

  Scenario: The opening bracket of a multi ligne declatation method is on the line after the closing parenthesis
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
           * @param string $param1 first param
           * @param array  $param2 second param
           * @param array  $param3 third param
           */
          public function testMethod(
              $param1,
              $param2,
              $param3
          )
          {

          }
      }

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should be empty
