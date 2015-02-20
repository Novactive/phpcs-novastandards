Feature: [NovaPHP][Commenting] Check PHP class header

  Scenario: The PHP class has no header
    Given there is a file named "features/test_file.php" with:
    """
      <?php
      class NoHeaderClass
      {
          public function noHeaderMethod()
          {

          }
      }
      """
    When I check the sniff "NovaPHP.Commenting.ClassComment" of the standard "./src/NovaPHP" on the file "features/test_file.php"
    Then the output should contain:
      """
       2 | ERROR | Missing class doc comment
      """

  Scenario: The PHP class header has only a contact name for author tag
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      /**
       * This file is part of the novactive/phpcs-novastandards package
       *
       * @package   Nova\Standards
       * @author    Guillaume Maïssa <g.maissa@novactive.com>
       * @copyright 2014 Novactive
       * @license   Proprietary
       */

      /**
       * Test class for tags values sniffs
       *
       * @author Guillaume Maïssa
       */
      class NoHeaderClass
      {
          public function noHeaderMethod()
          {

          }
      }
      """
    When I check the sniff "NovaPHP.Commenting.ClassComment" of the standard "./src/NovaPHP" on the file "features/test_file.php"
    Then the output should contain:
      """
       14 | ERROR | Content of the @author tag must be in the form "Display Name
          |       | <username@example.com>"
      """

  Scenario: The PHP class header has only an email address for author tag
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      /**
       * This file is part of the novactive/phpcs-novastandards package
       *
       * @package   Nova\Standards
       * @author    Guillaume Maïssa <g.maissa@novactive.com>
       * @copyright 2014 Novactive
       * @license   Proprietary
       */

      /**
       * Test class for tags values sniffs
       *
       * @author g.maissa@novactive.com
       */
      class NoHeaderClass
      {
          public function noHeaderMethod()
          {

          }
      }
      """
    When I check the sniff "NovaPHP.Commenting.ClassComment" of the standard "./src/NovaPHP" on the file "features/test_file.php"
    Then the output should contain:
      """
       14 | ERROR | Content of the @author tag must be in the form "Display Name
          |       | <username@example.com>"
      """
