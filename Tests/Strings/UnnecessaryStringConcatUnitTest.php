<?php
/**
 * Unit test class for the UnnecessaryStringConcat sniff.
 *
 * PHP version 5
 *
 * @category  Strings
 * @package   TYPO3SniffPool
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Stefano Kowalke <blueduck@mailbox.org>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @link      https://github.com/typo3-ci/TYPO3SniffPool
 */
/**
 * Unit test class for the UnnecessaryStringConcat sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 *
 * @category  Strings
 * @package   TYPO3SniffPool
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Stefano Kowalke <blueduck@mailbox.org>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @link      https://github.com/typo3-ci/TYPO3SniffPool
 */
class TYPO3SniffPool_Tests_Strings_UnnecessaryStringConcatUnitTest extends AbstractSniffUnitTest
{
    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @param string $testFile The name of the file being tested.
     *
     * @return array(int => int)
     */
    public function getErrorList($testFile = 'UnnecessaryStringConcatUnitTest.inc')
    {
        switch ($testFile) {
        case 'UnnecessaryStringConcatUnitTest.inc':
            return array(
                        2 => 1,
                        6 => 1,
                        9 => 1,
                        12 => 1,
                        14 => 0,
                        17 => 1,
                    );
            break;
        case 'UnnecessaryStringConcatUnitTest.js':
            return array(
                        1 => 1,
                        8 => 1,
                        11 => 1,
                    );
        break;
        default:
            return array();
            break;
        }
    }

    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of warnings that should occur on that line.
     *
     * @return array(int => int)
     */
    public function getWarningList()
    {
        return array();
    }
}
