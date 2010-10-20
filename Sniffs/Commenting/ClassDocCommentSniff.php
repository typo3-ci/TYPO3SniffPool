<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2010 Andy Grunwald <andreas.grunwald@wmdb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * TYPO3_Sniffs_Commenting_ClassDocCommentSniff.
 *
 * PHP version 5
 * TYPO3 version 4
 *
 * @category	Commenting
 * @package		TYPO3_PHPCS_Pool
 * @author		Greg Sherwood <gsherwood@squiz.net>
 * @author		Marc McIntyre <mmcintyre@squiz.net>
 * @author		Andy Grunwald <andreas.grunwald@wmdb.de>
 * @copyright	2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license		http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version		SVN: $ID$
 * @link		pear.typo3.org
 */
if (class_exists('PHP_CodeSniffer_CommentParser_ClassCommentParser', TRUE) === FALSE) {
    $error = 'Class PHP_CodeSniffer_CommentParser_ClassCommentParser not found';
    throw new PHP_CodeSniffer_Exception($error);
}
/**
 * Parses and verifies the doc comments for classes and interfaces.
 *
 * This sniff is copied and modified from PEAR_Sniffs_Commenting_ClassCommentSniff.
 * Thanks for this guys!
 *
 * Verifies that :
 * <ul>
 *  <li>A doc comment exists.</li>
 *  <li>A doc comment is made by "/**"-Comments.</li>
 *  <li>A doc comment is not empty.</li>
 *  <li>There is no blank newline before the short description.</li>
 *  <li>There is a blank newline after the short description.</li>
 *  <li>There is a blank newline between the long and short description.</li>
 *  <li>There is a blank newline between the long description and tags.</li>
 * </ul>
 *
 * @category	Commenting
 * @package		TYPO3_PHPCS_Pool
 * @author		Greg Sherwood <gsherwood@squiz.net>
 * @author		Marc McIntyre <mmcintyre@squiz.net>
 * @author		Andy Grunwald <andreas.grunwald@wmdb.de>
 * @copyright	2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license		http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version		Release: @package_version@
 * @link		pear.typo3.org
 */
class TYPO3_Sniffs_Commenting_ClassDocCommentSniff implements PHP_CodeSniffer_Sniff {
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register() {
        return array(T_CLASS, T_INTERFACE,);
    }
    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        $this->currentFile = $phpcsFile;
        $tokens = $phpcsFile->getTokens();
        $type = strtolower($tokens[$stackPtr]['content']);
        $errorData = array($type);
        $find = array(T_ABSTRACT, T_WHITESPACE, T_FINAL,);
        // Extract the class comment docblock.
        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), NULL, TRUE);
        if ($commentEnd !== FALSE && $tokens[$commentEnd]['code'] === T_COMMENT) {
            $error = 'You must use "/**" style comments for a %s comment';
            $phpcsFile->addError($error, $stackPtr, 'WrongStyle', $errorData);
            return;
        } elseif ($commentEnd === FALSE || $tokens[$commentEnd]['code'] !== T_DOC_COMMENT) {
            $phpcsFile->addError('Missing %s doc comment', $stackPtr, 'Missing', $errorData);
            return;
        }
        $commentStart = ($phpcsFile->findPrevious(T_DOC_COMMENT, ($commentEnd - 1), NULL, TRUE) + 1);
        $commentNext = $phpcsFile->findPrevious(T_WHITESPACE, ($commentEnd + 1), $stackPtr, FALSE, $phpcsFile->eolChar);
        // Distinguish file and class comment.
        $prevClassToken = $phpcsFile->findPrevious(T_CLASS, ($stackPtr - 1));
        if ($prevClassToken === FALSE) {
            // This is the first class token in this file, need extra checks.
            $prevNonComment = $phpcsFile->findPrevious(T_DOC_COMMENT, ($commentStart - 1), NULL, TRUE);
            if ($prevNonComment !== FALSE) {
                $prevComment = $phpcsFile->findPrevious(T_DOC_COMMENT, ($prevNonComment - 1));
                if ($prevComment === FALSE) {
                    // There is only 1 doc comment between open tag and class token.
                    $newlineToken = $phpcsFile->findNext(T_WHITESPACE, ($commentEnd + 1), $stackPtr, FALSE, $phpcsFile->eolChar);
                    if ($newlineToken !== FALSE) {
                        $newlineToken = $phpcsFile->findNext(T_WHITESPACE, ($newlineToken + 1), $stackPtr, FALSE, $phpcsFile->eolChar);
                        if ($newlineToken !== FALSE) {
                            // Blank line between the class and the doc block.
                            // The doc block is most likely a file comment.
                            $error = 'Missing %s doc comment';
                            $phpcsFile->addError($error, ($stackPtr + 1), 'Missing', $errorData);
                            return;
                        }
                    }
                }
            }
        }
        $comment = $phpcsFile->getTokensAsString($commentStart, ($commentEnd - $commentStart + 1));
        // Parse the class comment.docblock.
        try {
            $this->commentParser = new PHP_CodeSniffer_CommentParser_ClassCommentParser($comment, $phpcsFile);
            $this->commentParser->parse();
        }
        catch(PHP_CodeSniffer_CommentParser_ParserException $e) {
            $line = ($e->getLineWithinComment() + $commentStart);
            $phpcsFile->addError($e->getMessage(), $line, 'FailedParse');
            return;
        }
        $comment = $this->commentParser->getComment();
        if (is_null($comment) === TRUE) {
            $error = 'Doc comment is empty for %s';
            $phpcsFile->addError($error, $commentStart, 'Empty', $errorData);
            return;
        }
        // No extra newline before short description.
        $short = $comment->getShortComment();
        $newlineCount = 0;
        $newlineSpan = strspn($short, $phpcsFile->eolChar);
        if ($short !== '' && $newlineSpan > 0) {
            $error = 'Extra newline(s) found before %s comment short description';
            $phpcsFile->addError($error, ($commentStart + 1), 'SpacingBeforeShort', $errorData);
        }
        $newlineCount = (substr_count($short, $phpcsFile->eolChar) + 1);
        // Exactly one blank line between short and long description.
        $long = $comment->getLongComment();
        if (empty($long) === FALSE) {
            $between = $comment->getWhiteSpaceBetween();
            $newlineBetween = substr_count($between, $phpcsFile->eolChar);
            if ($newlineBetween !== 2) {
                $error = 'There must be exactly one blank line between descriptions in %s comments';
                $phpcsFile->addError($error, ($commentStart + $newlineCount + 1), 'SpacingAfterShort', $errorData);
            }
            $newlineCount+= $newlineBetween;
        }
        // Exactly one blank line before tags.
        $tags = $this->commentParser->getTagOrders();
        if (count($tags) > 1) {
            $newlineSpan = $comment->getNewlineAfter();
            if ($newlineSpan !== 2) {
                $error = 'There must be exactly one blank line before the tags in %s comments';
                if ($long !== '') {
                    $newlineCount+= (substr_count($long, $phpcsFile->eolChar) - $newlineSpan + 1);
                }
                $phpcsFile->addError($error, ($commentStart + $newlineCount), 'SpacingBeforeTags', $errorData);
                $short = rtrim($short, $phpcsFile->eolChar . ' ');
            }
        }
    }
}
?>