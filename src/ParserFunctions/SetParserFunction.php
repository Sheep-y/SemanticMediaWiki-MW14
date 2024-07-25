<?php

namespace SMW\ParserFunctions;

use Parser;
use SMW\DataValueFactory;
use SMW\MediaWiki\Renderer\WikitextTemplateRenderer;
use SMW\MediaWiki\StripMarkerDecoder;
use SMW\MessageFormatter;
use SMW\ParserData;
use SMW\SemanticData;
use SMW\ParserParameterProcessor;
use SMW\Parser\AnnotationProcessor;

/**
 * Class that provides the {{#set}} parser function
 *
 * @see http://semantic-mediawiki.org/wiki/Help:Properties_and_types#Silent_annotations_using_.23set
 * @see http://www.semantic-mediawiki.org/wiki/Help:Setting_values
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author Markus Krötzsch
 * @author Jeroen De Dauw
 * @author mwjames
 */
class SetParserFunction {

	/**
	 * @var ParserData
	 */
	private $parserData;

	/**
	 * @var MessageFormatter
	 */
	private $messageFormatter;

	/**
	 * @var WikitextTemplateRenderer
	 */
	private $templateRenderer;

	/**
	 * @var InTextAnnotationParser
	 */
	private $inTextParser;

	/**
	 * @var StripMarkerDecoder
	 */
	private $stripMarkerDecoder;

	/**
	 * @since 1.9
	 *
	 * @param ParserData $parserData
	 * @param MessageFormatter $messageFormatter
	 * @param WikitextTemplateRenderer $templateRenderer
	 */
	public function __construct( ParserData $parserData, MessageFormatter $messageFormatter, WikitextTemplateRenderer $templateRenderer ) {
		$this->parserData = $parserData;
		$this->messageFormatter = $messageFormatter;
		$this->templateRenderer = $templateRenderer;
	}

	/**
	 * @since 3.0
	 *
	 * @param StripMarkerDecoder $stripMarkerDecoder
	 */
	public function setStripMarkerDecoder( StripMarkerDecoder $stripMarkerDecoder ) {
		$this->stripMarkerDecoder = $stripMarkerDecoder;
	}

	/**
	 * @since 3.1
	 *
	 * @return SemanticData
	 */
	public function getSemanticData() {
		return $this->parserData->getSemanticData();
	}

	/**
	 * @since  1.9
	 *
	 * @param ParserParameterProcessor $parameters
	 *
	 * @return string|null
	 */
	public function parse( ParserParameterProcessor $parameters ) {
		$count = 0;
		$template = '';
		$html = '';
		$glue = '';
		$subject = $this->parserData->getSemanticData()->getSubject();

		$parametersToArray = $parameters->toArray();

		if ( isset( $parametersToArray['template'] ) ) {
			$template = $parametersToArray['template'][0];
			unset( $parametersToArray['template'] );
			if ( !empty( $template ) && $template[0] === '#' && mb_strlen( $template ) > 1 ) {
				$glue = mb_substr( $template, 1 );
				$template = '#';
			}
		}

		$annotationProcessor = new AnnotationProcessor(
			$this->parserData->getSemanticData(),
			DataValueFactory::getInstance()
		);

		$lastProperty = array_key_last( $parametersToArray );

		foreach ( $parametersToArray as $property => $values ) {

			$last = count( $values ) - 1; // -1 because the key starts with 0

			foreach ( $values as $key => $value ) {

				if ( $this->stripMarkerDecoder !== null ) {
					$value = $this->stripMarkerDecoder->decode( $value );
				}

				$dataValue = $annotationProcessor->newDataValueByText(
						$property,
						$value,
						false,
						$subject
					);

				if ( $this->parserData->canUse() ) {
					$this->parserData->addDataValue( $dataValue );
				}

				$this->messageFormatter->addFromArray( $dataValue->getErrors() );

				if ( $dataValue->isValid() ) {
					if ( $template === '#' ) {
						$html .= $dataValue->getShortWikitext( true );
						if ( $last !== $key || $property !== $lastProperty ) {
							$html .= $glue;
						}
					} else {
						$this->addFieldsToTemplate(
							$template,
							$dataValue,
							$property,
							$value,
							$last == $key,
							$count
						);
					}
				}
			}
		}

		$this->parserData->copyToParserOutput();

		$annotationProcessor->release();

		if ( $template !== '#' ) {
			$html = $this->templateRenderer->render();
		}
		$error = $this->messageFormatter
				->addFromArray( $parameters->getErrors() )
				->getHtml();

		return [ $html . $error, 'noparse' => $template === '', 'isHTML' => false ];
	}

	private function addFieldsToTemplate( $template, $dataValue, $property, $value, $isLastElement, &$count ) {
		if ( $template === '' ) {
			return '';
		}

		$this->templateRenderer->addField( 'property', $property );
		$this->templateRenderer->addField( 'value', $value );
		$this->templateRenderer->addField( 'last-element', $isLastElement );
		$this->templateRenderer->addField( '#', $count++ );
		$this->templateRenderer->packFieldsForTemplate( $template );
	}
}
