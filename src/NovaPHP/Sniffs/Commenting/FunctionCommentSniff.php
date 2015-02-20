<?php
/**
 * This file is part of the NovaPHP standard for PHP_CodeSniffer
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

if (class_exists('PEAR_Sniffs_Commenting_FunctionCommentSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PEAR_Sniffs_Commenting_FunctionCommentSniff not found');
}

/**
 * Parses and verifies the doc comments for functions.
 *
 * Verifies that :
 * <ul>
 *  <li>the method doc is inherited from the parent class</li>
 *  <li>a return type exists if a return statement exists in the method</li>
 * </ul>
 *
 * @category  PHP
 * @package   Nova\Standards
 * @author    Guillaume Maïssa <g.maissa@novactive.com>
 * @copyright 2015 Novactive
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://www.novactive.com
 */
class NovaPHP_Sniffs_Commenting_FunctionCommentSniff extends PEAR_Sniffs_Commenting_FunctionCommentSniff
{

    /**
     * The name of the method that we are currently processing.
     *
     * @var string
     */
    private $_methodName = '';

    /**
     * The position in the stack where the function token was found.
     *
     * @var int
     */
    private $_functionToken = null;

    /**
     * The position in the stack where the class token was found.
     *
     * @var int
     */
    private $_classToken = null;


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $find = array(
                 T_COMMENT,
                 T_DOC_COMMENT,
                 T_CLASS,
                 T_FUNCTION,
                 T_OPEN_TAG,
                );

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1));

        if ($commentEnd === false) {
            return;
        }

        $this->currentFile = $phpcsFile;
        $tokens            = $phpcsFile->getTokens();

        // If the token that we found was a class or a function, then this
        // function has no doc comment.
        $code = $tokens[$commentEnd]['code'];

        if ($code === T_COMMENT) {
            $error = 'You must use "/**" style comments for a function comment';
            $phpcsFile->addError($error, $stackPtr, 'WrongStyle');
            return;
        } else if ($code !== T_DOC_COMMENT) {
            $phpcsFile->addError('Missing function doc comment', $stackPtr, 'Missing');
            return;
        }

        // If there is any code between the function keyword and the doc block
        // then the doc block is not for us.
        $ignore    = PHP_CodeSniffer_Tokens::$scopeModifiers;
        $ignore[]  = T_STATIC;
        $ignore[]  = T_WHITESPACE;
        $ignore[]  = T_ABSTRACT;
        $ignore[]  = T_FINAL;
        $prevToken = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
        if ($prevToken !== $commentEnd) {
            $phpcsFile->addError('Missing function doc comment', $stackPtr, 'Missing');
            return;
        }

        $this->_functionToken = $stackPtr;

        $this->_classToken = null;
        foreach ($tokens[$stackPtr]['conditions'] as $condPtr => $condition) {
            if ($condition === T_CLASS || $condition === T_INTERFACE) {
                $this->_classToken = $condPtr;
                break;
            }
        }

        // If the first T_OPEN_TAG is right before the comment, it is probably
        // a file comment.
        $commentStart = ($phpcsFile->findPrevious(T_DOC_COMMENT, ($commentEnd - 1), null, true) + 1);
        $prevToken    = $phpcsFile->findPrevious(T_WHITESPACE, ($commentStart - 1), null, true);
        if ($tokens[$prevToken]['code'] === T_OPEN_TAG) {
            // Is this the first open tag?
            if ($stackPtr === 0 || $phpcsFile->findPrevious(T_OPEN_TAG, ($prevToken - 1)) === false) {
                $phpcsFile->addError('Missing function doc comment', $stackPtr, 'Missing');
                return;
            }
        }

        $comment           = $phpcsFile->getTokensAsString($commentStart, ($commentEnd - $commentStart + 1));
        $this->_methodName = $phpcsFile->getDeclarationName($stackPtr);

        try {
            $this->commentParser = new PHP_CodeSniffer_CommentParser_FunctionCommentParser($comment, $phpcsFile);
            $this->commentParser->parse();
        } catch (PHP_CodeSniffer_CommentParser_ParserException $e) {
            $line = ($e->getLineWithinComment() + $commentStart);
            $phpcsFile->addError($e->getMessage(), $line, 'FailedParse');
            return;
        }

        $comment = $this->commentParser->getComment();
        if ($comment === null) {
            $error = 'Function doc comment is empty';
            $phpcsFile->addError($error, $commentStart, 'Empty');
            return;
        }

        $this->processParams($commentStart);
        $this->processReturn($commentStart, $commentEnd);
        $this->processThrows($commentStart);

        // No extra newline before short description.
        $short        = $comment->getShortComment();
        $newlineCount = 0;
        $newlineSpan  = strspn($short, $phpcsFile->eolChar);
        if ($short !== '' && $newlineSpan > 0) {
            $error = 'Extra newline(s) found before function comment short description';
            $phpcsFile->addError($error, ($commentStart + 1), 'SpacingBeforeShort');
        }

        $newlineCount = (substr_count($short, $phpcsFile->eolChar) + 1);

        // Exactly one blank line between short and long description.
        $long = $comment->getLongComment();
        if (empty($long) === false) {
            $between        = $comment->getWhiteSpaceBetween();
            $newlineBetween = substr_count($between, $phpcsFile->eolChar);
            if ($newlineBetween !== 2) {
                $error = 'There must be exactly one blank line between descriptions in function comment';
                $phpcsFile->addError($error, ($commentStart + $newlineCount + 1), 'SpacingAfterShort');
            }

            $newlineCount += $newlineBetween;
        }

        // Exactly one blank line before tags.
        $params = $this->commentParser->getTagOrders();
        if (count($params) > 1) {
            $newlineSpan = $comment->getNewlineAfter();
            if ($newlineSpan !== 2) {
                $error = 'There must be exactly one blank line before the tags in function comment';
                if ($long !== '') {
                    $newlineCount += (substr_count($long, $phpcsFile->eolChar) - $newlineSpan + 1);
                }

                $phpcsFile->addError($error, ($commentStart + $newlineCount), 'SpacingBeforeTags');
                $short = rtrim($short, $phpcsFile->eolChar.' ');
            }
        }

    }//end process()


    /**
     * Process the return comment of this function comment.
     *
     * @param int $commentStart The position in the stack where the comment started.
     * @param int $commentEnd   The position in the stack where the comment ended.
     *
     * @return void
     */
    protected function processReturn($commentStart, $commentEnd)
    {
        if ($this->isDocInherited($commentStart, $commentEnd) === true) {
            return;
        }

        $tokens = $this->currentFile->getTokens();
        // Only check for a return comment if a non-void return statement exists.
        if (isset($tokens[$this->_functionToken]['scope_opener']) === true) {
            $start = $tokens[$this->_functionToken]['scope_opener'];
            // Iterate over all return statements of this function,
            // run the check on the first which is not only 'return;'.
            while ($returnToken = $this->currentFile->findNext(
                T_RETURN,
                $start,
                $tokens[$this->_functionToken]['scope_closer']
            )) {
                if ($this->isMatchingReturn($tokens, $returnToken) === true) {
                    // Skip constructor and destructor.
                    $className = '';
                    if ($this->_classToken !== null) {
                        $className = $this->currentFile->getDeclarationName($this->_classToken);
                        $className = strtolower(ltrim($className, '_'));
                    }

                    $methodName = strtolower(ltrim($this->_methodName, '_'));
                    if ($methodName === '') {
                        $methodName = $this->_methodName;
                    }

                    $isSpecialMethod = false;
                    if ($this->_methodName === '__construct' || $this->_methodName === '__destruct') {
                        $isSpecialMethod = true;
                    }

                    if ($isSpecialMethod === false && $methodName !== $className) {
                        // Report missing return tag.
                        if ($this->commentParser->getReturn() === null) {
                            $error = 'Missing @return tag in function comment';
                            $this->currentFile->addError($error, $commentEnd, 'MissingReturn');
                        } else if (trim($this->commentParser->getReturn()->getRawContent()) === '') {
                            $error    = '@return tag is empty in function comment';
                            $errorPos = ($commentStart + $this->commentParser->getReturn()->getLine());
                            $this->currentFile->addError($error, $errorPos, 'EmptyReturn');
                        }
                    }

                    break;
                }//end if
                $start = ($returnToken + 1);
            }//end while
        }//end if

    }//end processReturn()


    /**
     * Process the function parameter comments.
     *
     * @param int $commentStart The position in the stack where
     *                          the comment started.
     *
     * @return void
     */
    protected function processParams($commentStart)
    {
        $commentEnd = ($this->currentFile->findNext(T_DOC_COMMENT, ($commentStart), null, true) - 1);
        if ($this->isDocInherited($commentStart, $commentEnd) === true) {
            return;
        }

        $realParams = $this->currentFile->getMethodParameters($this->_functionToken);

        $params      = $this->commentParser->getParams();
        $foundParams = array();

        if (empty($params) === false) {
            $lastParm = (count($params) - 1);
            if (substr_count($params[$lastParm]->getWhitespaceAfter(), $this->currentFile->eolChar) !== 2) {
                $error    = 'Last parameter comment requires a blank newline after it';
                $errorPos = ($params[$lastParm]->getLine() + $commentStart);
                $this->currentFile->addError($error, $errorPos, 'SpacingAfterParams');
            }

            // Parameters must appear immediately after the comment.
            if ($params[0]->getOrder() !== 2) {
                $error    = 'Parameters must appear immediately after the comment';
                $errorPos = ($params[0]->getLine() + $commentStart);
                $this->currentFile->addError($error, $errorPos, 'SpacingBeforeParams');
            }

            $previousParam      = null;
            $spaceBeforeVar     = 10000;
            $spaceBeforeComment = 10000;
            $longestType        = 0;
            $longestVar         = 0;

            foreach ($params as $param) {
                $paramComment = trim($param->getComment());
                $errorPos     = ($param->getLine() + $commentStart);

                // Make sure that there is only one space before the var type.
                if ($param->getWhitespaceBeforeType() !== ' ') {
                    $error = 'Expected 1 space before variable type';
                    $this->currentFile->addError($error, $errorPos, 'SpacingBeforeParamType');
                }

                $spaceCount = substr_count($param->getWhitespaceBeforeVarName(), ' ');
                if ($spaceCount < $spaceBeforeVar) {
                    $spaceBeforeVar = $spaceCount;
                    $longestType    = $errorPos;
                }

                $spaceCount = substr_count($param->getWhitespaceBeforeComment(), ' ');

                if ($spaceCount < $spaceBeforeComment && $paramComment !== '') {
                    $spaceBeforeComment = $spaceCount;
                    $longestVar         = $errorPos;
                }

                // Make sure they are in the correct order,
                // and have the correct name.
                $pos = $param->getPosition();

                $paramName = '[ UNKNOWN ]';
                if ($param->getVarName() !== '') {
                    $paramName = $param->getVarName();
                }

                if ($previousParam !== null) {
                    $previousName = 'UNKNOWN';
                    if ($previousParam->getVarName() !== '') {
                        $previousName = $previousParam->getVarName();
                    }

                    // Check to see if the parameters align properly.
                    if ($param->alignsVariableWith($previousParam) === false) {
                        $error = 'The variable names for parameters %s (%s) and %s (%s) do not align';
                        $data  = array(
                                  $previousName,
                                  ($pos - 1),
                                  $paramName,
                                  $pos,
                                 );
                        $this->currentFile->addError($error, $errorPos, 'ParameterNamesNotAligned', $data);
                    }

                    if ($param->alignsCommentWith($previousParam) === false) {
                        $error = 'The comments for parameters %s (%s) and %s (%s) do not align';
                        $data  = array(
                                  $previousName,
                                  ($pos - 1),
                                  $paramName,
                                  $pos,
                                 );
                        $this->currentFile->addError($error, $errorPos, 'ParameterCommentsNotAligned', $data);
                    }
                }//end if

                // Make sure the names of the parameter comment matches the
                // actual parameter.
                if (isset($realParams[($pos - 1)]) === true) {
                    $realName      = $realParams[($pos - 1)]['name'];
                    $foundParams[] = $realName;

                    if ($realName !== $paramName) {
                        $code = 'ParamNameNoMatch';
                        $data = array(
                                 $paramName,
                                 $realName,
                                 $pos,
                                );

                        $error = 'Doc comment for var %s does not match ';
                        if (strtolower($paramName) === strtolower($realName)) {
                            $error .= 'case of ';
                            $code   = 'ParamNameNoCaseMatch';
                        }

                        $error .= 'actual variable name %s at position %s';

                        $this->currentFile->addError($error, $errorPos, $code, $data);
                    }
                } else {
                    // We must have an extra parameter comment.
                    $error = 'Superfluous doc comment at position '.$pos;
                    $this->currentFile->addError($error, $errorPos, 'ExtraParamComment');
                }//end if

                if ($param->getVarName() === '') {
                    $error = 'Missing parameter name at position '.$pos;
                     $this->currentFile->addError($error, $errorPos, 'MissingParamName');
                }

                if ($param->getType() === '') {
                    $error = 'Missing type at position '.$pos;
                    $this->currentFile->addError($error, $errorPos, 'MissingParamType');
                }

                if ($paramComment === '') {
                    $error = 'Missing comment for param "%s" at position %s';
                    $data  = array(
                              $paramName,
                              $pos,
                             );
                    $this->currentFile->addError($error, $errorPos, 'MissingParamComment', $data);
                }

                $previousParam = $param;
            }//end foreach

            if ($spaceBeforeVar !== 1 && $spaceBeforeVar !== 10000 && $spaceBeforeComment !== 10000) {
                $error = 'Expected 1 space after the longest type';
                $this->currentFile->addError($error, $longestType, 'SpacingAfterLongType');
            }

            if ($spaceBeforeComment !== 1 && $spaceBeforeComment !== 10000) {
                $error = 'Expected 1 space after the longest variable name';
                $this->currentFile->addError($error, $longestVar, 'SpacingAfterLongName');
            }
        }//end if

        $realNames = array();
        foreach ($realParams as $realParam) {
            $realNames[] = $realParam['name'];
        }

        // Report and missing comments.
        $diff = array_diff($realNames, $foundParams);
        foreach ($diff as $neededParam) {
            if (count($params) !== 0) {
                $errorPos = ($params[(count($params) - 1)]->getLine() + $commentStart);
            } else {
                $errorPos = $commentStart;
            }

            $error = 'Doc comment for "%s" missing';
            $data  = array($neededParam);
            $this->currentFile->addError($error, $errorPos, 'MissingParamTag', $data);
        }

    }//end processParams()


    /**
     * Is the comment an inheritdoc?
     *
     * @param int $commentStart The position in the stack where the comment started.
     * @param int $commentEnd   The position in the stack where the comment ended.
     *
     * @return boolean True if the comment is an inheritdoc
     */
    protected function isDocInherited($commentStart, $commentEnd)
    {
        $phpcsFile = $this->currentFile;
        $content   = $phpcsFile->getTokensAsString($commentStart, ($commentEnd - $commentStart + 1));

        return preg_match('#{@inheritdoc}#i', $content) === 1;

    }//end isDocInherited()


    /**
     * Is the return statement matching?
     *
     * @param array $tokens    Array of tokens
     * @param int   $returnPos Stack position of the T_RETURN token to process
     *
     * @return boolean True if the return does not return anything
     */
    protected function isMatchingReturn($tokens, $returnPos)
    {
        do {
            $returnPos++;
        } while ($tokens[$returnPos]['code'] === T_WHITESPACE);

        return $tokens[$returnPos]['code'] !== T_SEMICOLON;

    }//end isMatchingReturn()


}//end class

?>
