Feature: [NovaEZ][Commenting] Check eZ Publish PHP file header
  The eZ Publish PHP file header should have mandatory information, with specific format for some of them.

  Scenario: The eZ Publish PHP file has no header
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
    When I check the sniff "NovaPHP.Commenting.FileComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should contain:
      """
       2 | ERROR | Missing file doc comment
      """

  Scenario: The eZ Publish PHP file header is missing some tags
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      /**
       * This file is part of the novactive/phpcs-novastandards package
       */

      /**
       * Test class for missing tags sniffs
       */
      class MissingTagsClass
      {
      }
      """
    When I check the sniff "NovaPHP.Commenting.FileComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should contain:
      """
       4 | ERROR | Missing @package tag in class comment
       4 | ERROR | Missing @author tag in class comment
       4 | ERROR | Missing @copyright tag in class comment
      """

  Scenario: The eZ Publish PHP file header has unaligned tags
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      /**
       * This file is part of the novactive/phpcs-novastandards package
       *
       * @package Nova\Standards
       * @author Guillaume Maïssa <g.maissa@novactive.com>
       * @copyright 2014 Novactive
       * @license Proprietary
       */

      /**
       * Test class for tags alignment sniffs
       */
      class UnalignedTagsClass
      {
      }
      """
    When I check the sniff "NovaPHP.Commenting.FileComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should contain:
      """
       5 | ERROR | @package tag comment indented incorrectly; expected 3 spaces but
         |       | found 1
       6 | ERROR | @author tag comment indented incorrectly; expected 4 spaces but
         |       | found 1
       8 | ERROR | @license tag comment indented incorrectly; expected 3 spaces but
         |       | found 1
      """

  Scenario: The eZ Publish PHP file header has no year in copyright tag
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      /**
       * This file is part of the novactive/phpcs-novastandards package
       *
       * @package   Nova\Standards
       * @author    Guillaume Maïssa <g.maissa@novactive.com>
       * @copyright Novactive
       * @license   Proprietary
       */

      /**
       * Test class for tags values sniffs
       */
      class TagValueErrorsClass
      {
      }
      """
    When I check the sniff "NovaPHP.Commenting.FileComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should contain:
      """
       7 | ERROR | @copyright tag must contain a year and the name of the copyright
         |       | holder
      """

  Scenario: The eZ Publish PHP file header has only a contact name for author tag
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      /**
       * This file is part of the novactive/phpcs-novastandards package
       *
       * @package   Nova\Standards
       * @author    Guillaume Maïssa
       * @copyright 2014 Novactive
       * @license   Proprietary
       */

      /**
       * Test class for tags values sniffs
       */
      class TagValueErrorsClass
      {
      }
      """
    When I check the sniff "NovaPHP.Commenting.FileComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should contain:
      """
       6 | ERROR | Content of the @author tag must be in the form "Display Name
         |       | <username@example.com>"
      """

  Scenario: The eZ Publish PHP file header has only an email address for author tag
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      /**
       * This file is part of the novactive/phpcs-novastandards package
       *
       * @package   Nova\Standards
       * @author    g.maissa@novactive.com
       * @copyright 2014 Novactive
       * @license   Proprietary
       */

      /**
       * Test class for tags values sniffs
       */
      class TagValueErrorsClass
      {
      }
      """
    When I check the sniff "NovaPHP.Commenting.FileComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should contain:
      """
       6 | ERROR | Content of the @author tag must be in the form "Display Name
         |       | <username@example.com>"
      """
