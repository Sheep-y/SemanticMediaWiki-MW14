<?php

namespace SMW\Tests\DataValues;

use SMW\DataItemFactory;
use SMW\DataValues\ErrorMsgTextValue;
use SMW\Tests\PHPUnitCompat;

/**
 * @covers \SMW\DataValues\ErrorMsgTextValue
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 2.5
 *
 * @author mwjames
 */
class ErrorMsgTextValueTest extends \PHPUnit\Framework\TestCase {

	use PHPUnitCompat;

	private $dataItemFactory;

	protected function setUp(): void {
		$this->dataItemFactory = new DataItemFactory();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SMW\DataValues\ErrorMsgTextValue',
			new ErrorMsgTextValue()
		);
	}

	public function testErrorOnEmptyUserValue() {
		$instance = new ErrorMsgTextValue();
		$instance->setUserValue( '' );

		$this->assertNotEmpty(
			$instance->getErrors()
		);
	}

	public function testShortWikiText() {
		$instance = new ErrorMsgTextValue();
		$instance->setOption( ErrorMsgTextValue::OPT_USER_LANGUAGE, 'en' );
		$instance->setUserValue( '[2,"smw-constraint-violation-uniqueness","Has Url","http:\/\/loremipsum.org\/2","Lorem ipsum\/2"]' );

		$this->assertContains(
			"''http://loremipsum.org/2''",
			$instance->getShortWikiText( true )
		);

		$this->assertContains(
			"''http://loremipsum.org/2''",
			$instance->getShortWikiText( null )
		);

		$this->assertNotContains(
			'<a rel="nofollow" class="external free" href="http://loremipsum.org/2">http://loremipsum.org/2</a>',
			$instance->getShortWikiText( true )
		);

		$this->assertNotContains(
			'<a rel="nofollow" class="external free" href="http://loremipsum.org/2">http://loremipsum.org/2</a>',
			$instance->getShortWikiText( null )
		);
	}

	/**
	 * @dataProvider textProvider
	 */
	public function testValueOutput( $text, $expected ) {
		$dataItem = $this->dataItemFactory->newDIBlob( $text );

		$instance = new ErrorMsgTextValue();
		$instance->setDataItem( $dataItem );

		$this->assertEquals(
			$expected,
			$instance->getWikiValue()
		);

		$this->assertEquals(
			$expected,
			$instance->getShortWikiText()
		);

		$this->assertEquals(
			$expected,
			$instance->getShortHTMLText()
		);

		$this->assertEquals(
			$expected,
			$instance->getLongWikiText()
		);

		$this->assertEquals(
			$expected,
			$instance->getLongHTMLText()
		);
	}

	public function textProvider() {
		$provider[] = [
			'Foo',
			'Foo'
		];

		$provider[] = [
			'[2,"Foo"]',
			'Foo'
		];

		return $provider;
	}

}
