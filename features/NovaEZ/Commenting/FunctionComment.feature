Feature: [NovaEZ][Commenting] Check eZ Publish PHP function header

  Scenario: The eZ Publish PHP function has no header
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      class TestClass
      {
          public function testMethod()
          {

          }
      }
      """
    When I check the sniff "NovaPHP.Commenting.FunctionComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should contain:
      """
       4 | ERROR | Missing function doc comment
      """

  Scenario: The eZ Publish PHP function has a parameter but the header does not have a param tag
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      class TestClass
      {
          /**
           * First test method for missing tag sniffs
           */
          public function testMethod($param1)
          {
          }
      }
      """
    When I check the sniff "NovaPHP.Commenting.FunctionComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should contain:
      """
       4 | ERROR | Doc comment for "$param1" missing
      """

  Scenario: The eZ Publish PHP function has a return instruction but its header does not have a return tag
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      class TestClass
      {
          /**
           * First test method for missing tag sniffs
           */
          public function testMethod()
          {
              return 1;
          }
      }
      """
    When I check the sniff "NovaPHP.Commenting.FunctionComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should contain:
      """
       6 | ERROR | Missing @return tag in function comment
      """

  Scenario: The eZ Publish PHP function header has unaligned param tags
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      class TestClass
      {
          /**
           * First test method for missing tag sniffs
           *
           * @param string $param1 first param
           * @param array $param2 second param
           */
          public function testMethod($param1, $param2)
          {
          }
      }
      """
    When I check the sniff "NovaPHP.Commenting.FunctionComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should contain:
      """
       8 | ERROR | The variable names for parameters $param1 (1) and $param2 (2) do
         |       | not align
      """

  Scenario: The eZ Publish PHP function header has missing comments for param tags
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      class TestClass
      {
          /**
           * First test method for missing tag sniffs
           *
           * @param string $param1
           * @param array  $param2 second param
           */
          public function testMethod($param1, $param2)
          {
          }
      }
      """
    When I check the sniff "NovaPHP.Commenting.FunctionComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should not contain:
      """
       7 | ERROR | Missing comment for param "$param1" at position 1
      """

  Scenario: The eZ Publish PHP function header has no empty line after last param tag
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      class TestClass
      {
          /**
           * First test method for missing tag sniffs
           *
           * @param string $param1 first param
           * @Route("/hello/{name}")
           * @Template
           * @return array
           */
          public function testMethod($param1)
          {
          }
      }
      """
    When I check the sniff "NovaPHP.Commenting.FunctionComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should not contain:
      """
       7 | ERROR | Last parameter comment requires a blank newline after it
      """

  Scenario: The eZ Publish PHP function header has no empty line before first param tag
    Given there is a file named "features/test_file.php" with:
      """
      <?php
      class TestClass
      {
          /**
           * First test method for missing tag sniffs
           *
           * @Route("/hello/{name}")
           * @Template
           * @param string $param1 first param
           * @return array
           */
          public function testMethod($param1)
          {
          }
      }
      """
    When I check the sniff "NovaPHP.Commenting.FunctionComment" of the standard "./src/NovaEZ" on the file "features/test_file.php"
    Then the output should not contain:
      """
       9 | ERROR | Parameters must appear immediately after the comment
      """
