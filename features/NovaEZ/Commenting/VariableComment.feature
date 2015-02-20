Feature: [NovaEZ][Commenting] PHP class header

  Scenario: The variable comment short description does not start with a capital letter
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
           * my custom variable
           * @var boolean
           */
          public $myVar = false;
      }

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should be empty

  Scenario: The variable comment long description does not start with a capital letter
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
           * my custom variable
           *
           * this is a long description
           *
           * @var boolean
           */
          public $myVar = false;
      }

      """
    When I test the file "features/testFile.php" on the standard "./src/NovaEZ"
    Then the output should be empty
