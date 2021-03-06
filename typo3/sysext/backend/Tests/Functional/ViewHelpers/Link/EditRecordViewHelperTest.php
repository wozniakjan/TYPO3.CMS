<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Backend\Tests\Functional\ViewHelpers\Link;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case for \TYPO3\CMS\Backend\ViewHelpers\Link\EditRecordViewHelper
 */
class EditRecordViewHelperTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function renderReturnsValidLinkInExplicitFormat()
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:backend/Tests/Functional/ViewHelpers/Fixtures/Link/EditRecordViewHelper/WithUidAndTable.html');
        $result = urldecode($view->render());

        $this->assertStringContainsString('route=/record/edit', $result);
        $this->assertStringContainsString('edit[a_table][42]=edit', $result);
    }

    /**
     * @test
     */
    public function renderReturnsValidLinkInInlineFormat()
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:backend/Tests/Functional/ViewHelpers/Fixtures/Link/EditRecordViewHelper/InlineWithUidAndTable.html');
        $result = urldecode($view->render());

        $this->assertStringContainsString('route=/record/edit', $result);
        $this->assertStringContainsString('edit[b_table][21]=edit', $result);
    }

    /**
     * @test
     */
    public function renderReturnsValidLinkWithReturnUrl()
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:backend/Tests/Functional/ViewHelpers/Fixtures/Link/EditRecordViewHelper/WithUidTableAndReturnUrl.html');
        $result = urldecode($view->render());

        $this->assertStringContainsString('route=/record/edit', $result);
        $this->assertStringContainsString('edit[c_table][43]=edit', $result);
        $this->assertStringContainsString('returnUrl=foo/bar', $result);
    }

    /**
     * @test
     */
    public function renderThrowsExceptionForInvalidUidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1526127158);

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:backend/Tests/Functional/ViewHelpers/Fixtures/Link/EditRecordViewHelper/WithNegativeUid.html');
        $view->render();
    }
}
