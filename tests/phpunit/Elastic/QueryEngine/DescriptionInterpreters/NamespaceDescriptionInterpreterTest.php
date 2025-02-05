<?php

namespace SMW\Tests\Elastic\QueryEngine\DescriptionInterpreters;

use SMW\Elastic\QueryEngine\DescriptionInterpreters\NamespaceDescriptionInterpreter;
use SMW\Query\DescriptionFactory;

/**
 * @covers \SMW\Elastic\QueryEngine\DescriptionInterpreters\NamespaceDescriptionInterpreter
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 3.0
 *
 * @author mwjames
 */
class NamespaceDescriptionInterpreterTest extends \PHPUnit\Framework\TestCase {

	private DescriptionFactory $descriptionFactory;
	private $conditionBuilder;

	public function setUp(): void {
		$this->descriptionFactory = new DescriptionFactory();

		$this->conditionBuilder = $this->getMockBuilder( '\SMW\Elastic\QueryEngine\ConditionBuilder' )
			->disableOriginalConstructor()
			->setMethods( null )
			->getMock();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			NamespaceDescriptionInterpreter::class,
			new NamespaceDescriptionInterpreter( $this->conditionBuilder )
		);
	}

	public function testInterpretDescription_NotPartOfAConjunction() {
		$instance = new NamespaceDescriptionInterpreter(
			$this->conditionBuilder
		);

		$condition = $instance->interpretDescription(
			$this->descriptionFactory->newNamespaceDescription( NS_MAIN ),
			false
		);

		$this->assertEquals(
			'{"bool":{"filter":{"term":{"subject.namespace":0}}}}',
			$condition
		);
	}

	public function testInterpretDescription_IsPartOfAConjunction() {
		$instance = new NamespaceDescriptionInterpreter(
			$this->conditionBuilder
		);

		$condition = $instance->interpretDescription(
			$this->descriptionFactory->newNamespaceDescription( NS_MAIN ),
			true
		);

		$this->assertEquals(
			'{"term":{"subject.namespace":0}}',
			$condition
		);
	}

}
