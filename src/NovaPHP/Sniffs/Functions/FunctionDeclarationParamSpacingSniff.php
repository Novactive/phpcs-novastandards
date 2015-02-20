<?php
/**
 * This file is part of the NovaEZ standard for PHP_CodeSniffer
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   Nova\Standards
 * @author    Guillaume Maïssa <g.maissa@novactive.com>
 * @copyright 2015 Novactive
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://www.novactive.com
 */

/**
 * NovaPHP_Sniffs_Functions_FunctionDeclarationParamSpacingSniff.
 *
 * Checks that declaration of methods and functions are spaced correctly.
 *
 * @category  PHP
 * @package   Nova\Standards
 * @author    Guillaume Maïssa <g.maissa@novactive.com>
 * @copyright 2015 Novactive
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://www.novactive.com
 */
class NovaPHP_Sniffs_Functions_FunctionDeclarationParamSpacingSniff implements PHP_CodeSniffer_Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_FUNCTION,
                T_CLOSURE,
               );

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens        = $phpcsFile->getTokens();
        $openBracket   = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket  = $tokens[$openBracket]['parenthesis_closer'];
        $nextSeparator = $openBracket;

        $find = array(
                 T_COMMA,
                 T_VARIABLE,
                 T_CLOSURE,
                 T_OPEN_SHORT_ARRAY,
                );

        while ($nextSeparator = $phpcsFile->findNext($find, ($nextSeparator + 1), $closeBracket)) {
            if ($tokens[$nextSeparator]['code'] === T_CLOSURE) {
                // Skip closures.
                $nextSeparator = $tokens[$nextSeparator]['scope_closer'];
                continue;
            } else if ($tokens[$nextSeparator]['code'] === T_OPEN_SHORT_ARRAY) {
                // Skips arrays using short notation.
                $nextSeparator = $tokens[$nextSeparator]['bracket_closer'];
                continue;
            }

            // Make sure the comma or variable belongs directly to this function call,
            // and is not inside a nested function call or array.
            $brackets    = $tokens[$nextSeparator]['nested_parenthesis'];
            $lastBracket = array_pop($brackets);
            if ($lastBracket !== $closeBracket) {
                continue;
            }

            if ($tokens[$nextSeparator]['code'] === T_COMMA) {
                if ($tokens[($nextSeparator - 1)]['code'] === T_WHITESPACE) {
                    if (in_array(
                        $tokens[($nextSeparator - 2)]['code'],
                        PHP_CodeSniffer_Tokens::$heredocTokens
                    ) === false) {
                        $error = 'Space found before comma in function definition';
                        $phpcsFile->addError($error, $nextSeparator, 'SpaceBeforeComma');
                    }
                }

                if ($tokens[($nextSeparator + 1)]['code'] !== T_WHITESPACE) {
                    $error = 'No space found after comma in function definition';
                    $phpcsFile->addError($error, $nextSeparator, 'NoSpaceAfterComma');
                } else {
                    // If there is a newline in the space, then the must be formatting
                    // each argument on a newline, which is valid, so ignore it.
                    if (strpos($tokens[($nextSeparator + 1)]['content'], $phpcsFile->eolChar) === false) {
                        $space = strlen($tokens[($nextSeparator + 1)]['content']);
                        if ($space > 1) {
                            $error = 'Expected 1 space after comma in function definition; %s found';
                            $data  = array($space);
                            $phpcsFile->addError($error, $nextSeparator, 'TooMuchSpaceAfterComma', $data);
                        }
                    }
                }
            } else {
                // Token is a variable.
                $nextToken = $phpcsFile->findNext(
                    PHP_CodeSniffer_Tokens::$emptyTokens,
                    ($nextSeparator + 1),
                    $closeBracket,
                    true
                );
                if ($nextToken !== false) {
                    if ($tokens[$nextToken]['code'] === T_EQUAL) {
                        if (($tokens[($nextToken - 1)]['code']) !== T_WHITESPACE) {
                            $error = 'Expected 1 space before = sign of default value';
                            $phpcsFile->addError($error, $nextToken, 'NoSpaceBeforeEquals');
                        }

                        if ($tokens[($nextToken + 1)]['code'] !== T_WHITESPACE) {
                            $error = 'Expected 1 space after = sign of default value';
                            $phpcsFile->addError($error, $nextToken, 'NoSpaceAfterEquals');
                        }
                    }
                }
            }//end if
        }//end while

    }//end process()


}//end class

?>
