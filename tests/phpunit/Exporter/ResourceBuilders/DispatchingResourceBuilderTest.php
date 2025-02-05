<?php

namespace SMW\Tests\Exporter\ResourceBuilders;

use SMW\DataItemFactory;
use SMW\Exporter\ResourceBuilder;
use SMW\Exporter\ResourceBuilders\DispatchingResourceBuilder;

/**
 * @covers \SMW\Exporter\ResourceBuilders\DispatchingResourceBuilder
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 2.5
 *
 * @author mwjames
 */
class DispatchingResourceBuilderTest extends \PHPUnit\Framework\TestCase {

	private $dataItemFactory;

	protected function setUp(): void {
		parent::setUp();
		$this->dataItemFactory = new DataItemFactory();
	}

	public function testCanConstruct() {
		$this->assertInstanceof(
			DispatchingResourceBuilder::class,
			new DispatchingResourceBuilder()
		);
	}

	public function testIsResourceBuilderForValidMatch() {
		$property = $this->dataItemFactory->newDIProperty( 'Foo' );

		$resourceBuilder = $this->getMockBuilder( ResourceBuilder::class )
			->disableOriginalConstructor()
			->getMock();

		$resourceBuilder->expects( $this->once() )
			->method( 'isResourceBuilderFor' )
			->with( $property )
			->willReturn( true );

		$instance = new DispatchingResourceBuilder();
		$instance->addResourceBuilder( $resourceBuilder );

		$this->assertTrue(
			$instance->isResourceBuilderFor( $property )
		);
	}

	public function testIsResourceBuilderForInvalidMatch() {
		$property = $this->dataItemFactory->newDIProperty( 'Foo' );

		$instance = new DispatchingResourceBuilder();

		$this->assertFalse(
			$instance->isResourceBuilderFor( $property )
		);
	}

	public function testAddResourceValueOnValidMatchedResourceBuilder() {
		$expData = $this->getMockBuilder( '\SMWExpData' )
			->disableOriginalConstructor()
			->getMock();

		$property = $this->dataItemFactory->newDIProperty( 'Foo' );
		$dataItem = $this->dataItemFactory->newDIBlob( 'Bar' );

		$resourceBuilder = $this->getMockBuilder( ResourceBuilder::class )
			->disableOriginalConstructor()
			->getMock();

		$resourceBuilder->expects( $this->once() )
			->method( 'isResourceBuilderFor' )
			->willReturn( true );

		$resourceBuilder->expects( $this->once() )
			->method( 'addResourceValue' );

		$instance = new DispatchingResourceBuilder();
		$instance->addResourceBuilder( $resourceBuilder );

		$instance->addResourceValue( $expData, $property, $dataItem );
	}

	public function testAddResourceValueOnDefaultResourceBuilderWhenOthersCannotMatch() {
		$expData = $this->getMockBuilder( '\SMWExpData' )
			->disableOriginalConstructor()
			->getMock();

		$property = $this->dataItemFactory->newDIProperty( 'Foo' );
		$dataItem = $this->dataItemFactory->newDIBlob( 'Bar' );

		$resourceBuilder = $this->getMockBuilder( ResourceBuilder::class )
			->disableOriginalConstructor()
			->getMock();

		$resourceBuilder->expects( $this->once() )
			->method( 'isResourceBuilderFor' )
			->willReturn( false );

		$defaultResourceBuilder = $this->getMockBuilder( ResourceBuilder::class )
			->disableOriginalConstructor()
			->getMock();

		$defaultResourceBuilder->expects( $this->once() )
			->method( 'addResourceValue' );

		$instance = new DispatchingResourceBuilder();

		$instance->addResourceBuilder( $resourceBuilder );
		$instance->addDefaultResourceBuilder( $defaultResourceBuilder );

		$instance->addResourceValue( $expData, $property, $dataItem );
	}

}
