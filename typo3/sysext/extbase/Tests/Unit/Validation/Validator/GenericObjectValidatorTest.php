<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Extbase\Tests\Unit\Validation\Validator;

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

use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase
 */
class GenericObjectValidatorTest extends UnitTestCase
{
    /**
     * @test
     */
    public function validatorShouldReturnErrorsIfTheValueIsNoObjectAndNotNull()
    {
        $this->assertTrue((new GenericObjectValidator())->validate('foo')->hasErrors());
    }

    /**
     * @test
     */
    public function validatorShouldReturnNoErrorsIfTheValueIsNull()
    {
        $this->assertFalse((new GenericObjectValidator())->validate(null)->hasErrors());
    }

    /**
     * @return array
     */
    public function dataProviderForValidator(): array
    {
        $error1 = new Error('error1', 1);
        $error2 = new Error('error2', 2);
        $emptyResult1 = new Result();
        $emptyResult2 = new Result();
        $resultWithError1 = new Result();
        $resultWithError1->addError($error1);
        $resultWithError2 = new Result();
        $resultWithError2->addError($error2);
        $objectWithPrivateProperties = new class() {
            protected $foo = 'foovalue';
            protected $bar = 'barvalue';

            public function getFoo()
            {
                return $this->foo;
            }

            public function getBar()
            {
                return $this->bar;
            }
        };

        return [
            // If no errors happened, this is shown
            [$objectWithPrivateProperties, $emptyResult1, $emptyResult2, []],
            // If errors on two properties happened, they are merged together.
            [$objectWithPrivateProperties, $resultWithError1, $resultWithError2, ['foo' => [$error1], 'bar' => [$error2]]]
        ];
    }

    /**
     * @test
     * @dataProvider dataProviderForValidator
     *
     * @param mixed $objectToBeValidated
     * @param mixed $validationResultForFoo
     * @param mixed $validationResultForBar
     * @param mixed $errors
     */
    public function validateChecksAllPropertiesForWhichAPropertyValidatorExists($objectToBeValidated, $validationResultForFoo, $validationResultForBar, $errors)
    {
        $validator = new GenericObjectValidator();

        /** @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $validatorForFoo */
        $validatorForFoo = $this->getMockBuilder(ValidatorInterface::class)
            ->setMethods(['validate', 'getOptions'])
            ->getMock();
        $validatorForFoo->expects($this->once())->method('validate')->with('foovalue')->will($this->returnValue($validationResultForFoo));

        /** @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $validatorForBar */
        $validatorForBar = $this->getMockBuilder(ValidatorInterface::class)
            ->setMethods(['validate', 'getOptions'])
            ->getMock();
        $validatorForBar->expects($this->once())->method('validate')->with('barvalue')->will($this->returnValue($validationResultForBar));

        $validator->addPropertyValidator('foo', $validatorForFoo);
        $validator->addPropertyValidator('bar', $validatorForBar);

        $this->assertEquals($errors, $validator->validate($objectToBeValidated)->getFlattenedErrors());
    }

    /**
     * @test
     */
    public function validateCanHandleRecursiveTargetsWithoutEndlessLooping()
    {
        $A = new class() {
            public $b;
        };

        $B = new class() {
            public $a;
        };

        $A->b = $B;
        $B->a = $A;

        $aValidator = new GenericObjectValidator();
        $bValidator = new GenericObjectValidator();

        $aValidator->addPropertyValidator('b', $bValidator);
        $bValidator->addPropertyValidator('a', $aValidator);

        $this->assertFalse($aValidator->validate($A)->hasErrors());
    }

    /**
     * @test
     */
    public function validateDetectsFailuresInRecursiveTargetsI()
    {
        $A = new class() {
            public $b;
        };

        $B = new class() {
            public $a;
            public $uuid = 0xF;
        };

        $A->b = $B;
        $B->a = $A;
        $aValidator = new GenericObjectValidator();
        $bValidator = new GenericObjectValidator();

        $aValidator->addPropertyValidator('b', $bValidator);
        $bValidator->addPropertyValidator('a', $aValidator);
        $error = new Error('error1', 123);
        $result = new Result();
        $result->addError($error);

        /** @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $mockUuidValidator */
        $mockUuidValidator = $this->getMockBuilder(ValidatorInterface::class)
            ->setMethods(['validate', 'getOptions'])
            ->getMock();
        $mockUuidValidator->expects($this->any())->method('validate')->with(15)->will($this->returnValue($result));
        $bValidator->addPropertyValidator('uuid', $mockUuidValidator);

        $this->assertSame(['b.uuid' => [$error]], $aValidator->validate($A)->getFlattenedErrors());
    }

    /**
     * @test
     */
    public function validateDetectsFailuresInRecursiveTargetsII()
    {
        $A = new class() {
            public $b;
            public $uuid = 0xF;
        };

        $B = new class() {
            public $a;
            public $uuid = 0xF;
        };

        $A->b = $B;
        $B->a = $A;
        $aValidator = new GenericObjectValidator();
        $bValidator = new GenericObjectValidator();

        $aValidator->addPropertyValidator('b', $bValidator);
        $bValidator->addPropertyValidator('a', $aValidator);
        $error1 = new Error('error1', 123);
        $result1 = new Result();
        $result1->addError($error1);

        /** @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $mockUuidValidator */
        $mockUuidValidator = $this->getMockBuilder(ValidatorInterface::class)
            ->setMethods(['validate', 'getOptions'])
            ->getMock();
        $mockUuidValidator->expects($this->any())->method('validate')->with(15)->will($this->returnValue($result1));
        $aValidator->addPropertyValidator('uuid', $mockUuidValidator);
        $bValidator->addPropertyValidator('uuid', $mockUuidValidator);

        $this->assertSame(['b.uuid' => [$error1], 'uuid' => [$error1]], $aValidator->validate($A)->getFlattenedErrors());
    }

    /**
     * @test
     */
    public function validateDetectsFailuresInRecursiveTargetsIII()
    {
        // Create to test-entities. Use the same uuid to make the same validator trigger on both objects
        $A = new class() {
            public $b;
            public $uuid = 0xF;
        };

        $B = new class() {
            public $a;
            public $uuid = 0xF;
        };

        $A->b = $B;
        $B->a = $A;
        $aValidator = new GenericObjectValidator();
        $bValidator = new GenericObjectValidator();

        $error1 = new Error('error1', 123);
        $result1 = new Result();
        $result1->addError($error1);

        /** @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $mockValidatorUuidNot0xF */
        $mockValidatorUuidNot0xF = $this->getMockBuilder(ValidatorInterface::class)
            ->setMethods(['validate', 'getOptions'])
            ->getMock();
        $mockValidatorUuidNot0xF->expects($this->any())
            ->method('validate')->with(0xF)->will($this->returnValue($result1));

        $aValidator->addPropertyValidator('uuid', $mockValidatorUuidNot0xF);
        $bValidator->addPropertyValidator('uuid', $mockValidatorUuidNot0xF);
        $aValidator->addPropertyValidator('b', $bValidator);
        $bValidator->addPropertyValidator('a', $aValidator);

        // assert that the validation error is being reported for both objects
        $this->assertSame(
            ['uuid' => [$error1], 'b.uuid' => [$error1], 'b.a.uuid' => [$error1]],
            $aValidator->validate($A)->getFlattenedErrors()
        );
    }
}
