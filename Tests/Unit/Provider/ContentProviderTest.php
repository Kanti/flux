<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\ContentProvider;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * ContentProviderTest
 */
class ContentProviderTest extends AbstractProviderTest
{

    /**
     * @test
     */
    public function triggersContentManipulatorOnDatabaseOperationNew()
    {
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $form = Form::create();
        $provider = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\ContentProvider')->setMethods(array('loadRecordFromDatabase', 'getForm'))->getMock();
        $provider->expects($this->once())->method('loadRecordFromDatabase')->willReturn(array('foo' => 'bar'));
        $provider->expects($this->once())->method('getForm')->willReturn($form);

        /** @var DataHandler $tceMain */
        $tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
        $result = $provider->postProcessDatabaseOperation('new', $row['uid'], $row, $tceMain);
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function triggersContentManipulatorOnPasteCommandWithCallbackInUrl()
    {
        $_GET['CB'] = array('paste' => 'tt_content|0');
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $provider = $this->getConfigurationProviderInstance();
        /** @var DataHandler $tceMain */
        $tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
        $relativeUid = 0;
        $contentService = $this->getMockBuilder('FluidTYPO3\Flux\Service\ContentService')->setMethods(array('updateRecordInDatabase'))->getMock();
        $contentService->expects($this->once())->method('updateRecordInDatabase');
        ObjectAccess::setProperty($provider, 'contentService', $contentService, true);
        $result = $provider->postProcessCommand('move', 0, $row, $relativeUid, $tceMain);
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function canGetExtensionKey()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $extensionKey = $provider->getExtensionKey($record);
        $this->assertSame('flux', $extensionKey);
    }

    /**
     * @test
     */
    public function canGetControllerExtensionKey()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $result = $provider->getControllerExtensionKeyFromRecord($record);
        $this->assertEquals('flux', $result);
    }

    /**
     * @test
     */
    public function canGetTableName()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $tableName = $provider->getTableName($record);
        $this->assertSame('tt_content', $tableName);
    }

    /**
     * @test
     */
    public function canGetFieldName()
    {
        $provider = $this->getConfigurationProviderInstance();
        $record = $this->getBasicRecord();
        $result = $provider->getFieldName($record);
        $this->assertEquals('pi_flexform', $result);
    }

    /**
     * @test
     */
    public function canGetCallbackCommand()
    {
        $instance = $this->createInstance();
        $command = $this->callInaccessibleMethod($instance, 'getCallbackCommand');
        $this->assertIsArray($command);
    }

    /**
     * @test
     */
    public function postProcessCommandCallsExpectedMethodToMoveRecord()
    {
        $mock = $this->getMockBuilder(
            str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4))
        )->setMethods(
            array('getCallbackCommand', 'getMoveData')
        )->getMock();
        $mock->expects($this->once())->method('getCallbackCommand')->will($this->returnValue(array('move' => 1)));
        $mock->expects($this->once())->method('getMoveData')->willReturn(array());
        $mockContentService = $this->getMockBuilder('FluidTYPO3\Flux\Service\ContentService')->setMethods(array('pasteAfter', 'moveRecord'))->getMock();
        $mockContentService->expects($this->once())->method('moveRecord');
        ObjectAccess::setProperty($mock, 'contentService', $mockContentService, true);
        $command = 'move';
        $id = 0;
        $record = $this->getBasicRecord();
        $relativeTo = 0;
        $reference = new DataHandler();
        $mock->reset();
        $mock->postProcessCommand($command, $id, $record, $relativeTo, $reference);
    }

    /**
     * @test
     */
    public function postProcessCommandCallsExpectedMethodToCopyRecord()
    {
        $record = $this->getBasicRecord();
        $mock = $this->getMockBuilder(str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4)))->setMethods(array('getCallbackCommand'))->getMock();
        $mock->expects($this->once())->method('getCallbackCommand')->will($this->returnValue(array('paste' => 1)));
        $mockContentService = $this->getMockBuilder('FluidTYPO3\Flux\Service\ContentService')->setMethods(array('pasteAfter', 'moveRecord'))->getMock();
        $mockContentService->expects($this->once())->method('pasteAfter');
        ObjectAccess::setProperty($mock, 'contentService', $mockContentService, true);
        $command = 'copy';
        $id = 0;
        $relativeTo = 0;
        $reference = new DataHandler();
        $mock->reset();
        $mock->postProcessCommand($command, $id, $record, $relativeTo, $reference);
    }

    /**
     * @test
     */
    public function postProcessCommandCallsExpectedMethodToPasteRecord()
    {
        $record = $this->getBasicRecord();
        $mock = $this->getMockBuilder(str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4)))->setMethods(array('getCallbackCommand'))->getMock();
        $mock->expects($this->once())->method('getCallbackCommand')->will($this->returnValue(array('paste' => 1)));
        $mockContentService = $this->getMockBuilder('FluidTYPO3\Flux\Service\ContentService')->setMethods(array('pasteAfter', 'moveRecord'))->getMock();
        $mockContentService->expects($this->once())->method('pasteAfter');
        ObjectAccess::setProperty($mock, 'contentService', $mockContentService, true);
        $command = 'move';
        $id = 0;
        $relativeTo = 0;
        $reference = new DataHandler();
        $mock->reset();
        $mock->postProcessCommand($command, $id, $record, $relativeTo, $reference);
    }

    /**
     * @test
     * @dataProvider getPriorityTestValues
     * @param array $row
     * @param integer $expectedPriority
     */
    public function testGetPriority(array $row, $expectedPriority)
    {
        $provider = $this->objectManager->get($this->createInstanceClassName());
        $priority = $provider->getPriority($row);
        $this->assertEquals($expectedPriority, $priority);
    }

    /**
     * @return array
     */
    public function getPriorityTestValues()
    {
        return array(
            array(array('CType' => 'anyotherctype', 'list_type' => ''), 50),
            array(array('CType' => 'anyotherctype', 'list_type' => 'withlisttype'), 0),
        );
    }

    /**
     * @test
     * @dataProvider getTriggerTestValues
     * @param array $row
     * @param string $table
     * @param string $field
     * @param string $extensionKey
     * @param boolean $expectedResult
     */
    public function testTrigger($row, $table, $field, $extensionKey, $expectedResult)
    {
        $provider = $this->objectManager->get($this->createInstanceClassName());
        $result = $provider->trigger($row, $table, $field, $extensionKey);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getTriggerTestValues()
    {
        return array(
            array(array(), 'not_tt_content', 'pi_flexform', null, false),
            array(array(), 'not_tt_content', null, null, false),
            array(array(), 'tt_content', null, null, true),
            array(array('list_type' => '', 'CType' => 'any'), 'not_tt_content', 'pi_flexform', null, false),
            array(array('list_type' => '', 'CType' => 'any'), 'not_tt_content', 'pi_flexform', 'flux', false)
        );
    }

    /**
     * @test
     * @dataProvider getMoveDataTestvalues
     * @param mixed $postData
     * @param string|NULL $expected
     */
    public function getMoveDataReturnsExpectedValues($postData, $expected)
    {
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getRawPostData'))->getMock();
        $instance->expects($this->once())->method('getRawPostData')->willReturn($postData);
        $result = $this->callInaccessibleMethod($instance, 'getMoveData');
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getMoveDataTestvalues()
    {
        return array(
            array(null, null),
            array('{}', null),
            array('{"method": "test"}', null),
            array('{"method": "test", "data": []}', null),
            array('{"method": "moveContentElement", "data": "test"}', 'test'),
        );
    }
}
