<?php
/**
 * This file is part of the NovaPHP standard for PHP_CodeSniffer
 *
 * PHP version 5
 *
 * @category   PHP
 * @package    Nova\Standards
 * @subpackage Tests
 * @author     Guillaume Maïssa <g.maissa@novactive.com>
 * @copyright  2015 Novactive
 * @license    https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link       http://www.novactive.com
 */

namespace Nova\Standards\Tests;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    /**
     * Test working directory
     * @var string
     */
    private $workingDir;

    /**
     * Cleans test folders in the temporary directory.
     *
     * @BeforeSuite
     * @AfterSuite
     */
    public static function cleanTestFolders()
    {
        if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpcs-novastandards')) {
            self::clearDirectory($dir);
        }
    }

    /**
     * Prepares test folders in the temporary directory.
     *
     * @BeforeScenario
     */
    public function prepareTestFolders()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpcs-novastandards' . DIRECTORY_SEPARATOR .
               md5(microtime() * rand(0, 10000));
        mkdir($dir . '/features/bootstrap', 0777, true);

        $this->workingDir = $dir;
    }

    /**
     * Creates a file with specified name and context in current workdir.
     *
     * @param string       $filename name of the file (relative path)
     * @param PyStringNode $content  PyString string instance
     *
     * @Given /^(?:there is )?a file named "([^"]*)" with:$/
     */
    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string)$content, array("'''" => '"""'));
        $this->createFile($this->workingDir . '/' . $filename, $content);
    }

    /**
     * Execute phpcs with a given standard and sniff on a file
     *
     * @param string $sniff    CodeSniffer sniff identifier
     * @param string $standard CodeSniffer standard name / path
     * @param string $file     File path to be checked
     *
     * @When /^I check the sniff "([^"]*)" of the standard "([^"]*)" on the file "([^"]*)"$/
     */
    public function iCheckTheSniffOfTheStandardOnTheFile($sniff, $standard, $file)
    {
        exec(
            sprintf(
                './vendor/bin/phpcs --standard=%s --sniffs=%s --report=full %s',
                $standard,
                $sniff,
                $this->workingDir . DIRECTORY_SEPARATOR . $file
            ),
            $output
        );
        $this->output = trim(implode("\n", $output));
    }

    /**
     * Execute phpcs on a file with a given standard
     *
     * @param string $file     File path to be checked
     * @param string $standard CodeSniffer standard name / path
     *
     * @When /^I test the file "([^"]*)" on the standard "([^"]*)"$/
     */
    public function iTestTheFileOnTheStandard($file, $standard)
    {
        exec(
            sprintf(
                './vendor/bin/phpcs --standard=%s --report=full %s',
                $standard,
                $this->workingDir . DIRECTORY_SEPARATOR . $file
            ),
            $output
        );
        $this->output = trim(implode("\n", $output));
    }

    /**
     * Checks whether last command output contains provided string.
     *
     * @param PyStringNode $text PyString text instance
     *
     * @Then the output should contain:
     */
    public function theOutputShouldContain(PyStringNode $text)
    {
        \PHPUnit_Framework_Assert::assertContains($text->getRaw(), $this->output);
    }

    /**
     * Checks whether last command output contains provided string.
     *
     * @param PyStringNode $text PyString text instance
     *
     * @Then the output should not contain:
     */
    public function theOutputShouldNotContain(PyStringNode $text)
    {
        \PHPUnit_Framework_Assert::assertNotContains($text->getRaw(), $this->output);
    }

    /**
     * Checks whether last command output is empty
     *
     * @Then the output should be empty
     */
    public function theOutputShouldBeEmpty()
    {
        \PHPUnit_Framework_Assert::assertEquals('', $this->output);
    }


    /**
     * Create a new file with its content
     *
     * @param string $filename new file full name
     * @param string $content  new file content
     */
    private function createFile($filename, $content)
    {
        $path = dirname($filename);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        file_put_contents($filename, $content);
    }

    /**
     * Cleanup a given subtree
     *
     * @param string $path subtree path to be cleaned
     */
    private static function clearDirectory($path)
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);
        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::clearDirectory($file);
            } else {
                unlink($file);
            }
        }
        rmdir($path);
    }
}
