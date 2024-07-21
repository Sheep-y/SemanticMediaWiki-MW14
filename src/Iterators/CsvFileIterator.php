<?php

namespace SMW\Iterators;

use Exception;
use Iterator;
use Countable;
use SMW\Exception\FileNotFoundException;
use RuntimeException;
use SplFileObject;

/**
 * @see http://php.net/manual/en/function.fgetcsv.php
 *
 * @license GNU GPL v2+
 * @since 3.0
 */
class CsvFileIterator implements Iterator, Countable {

	/**
	 * @var SplFileObject
	 */
	private $file;

	/**
	 * @var Resource
	 */
	private $handle;

	/**
	 * @var boolean
	 */
	private $parseHeader;

	/**
	 * @var array|null
	 */
	private $header = null;

	/**
	 * @since 4.2
	 * @var array|null
	 */
	private $currentElement = null;

	/**
	 * @var string
	 */
	private $delimiter;

	/**
	 * @var integer
	 */
	private $length;

	/**
	 * @var int
	 */
	private $key = 0;

	/**
	 * @var boolean
	 */
	private $count = false;

	/**
	 * @since 3.0
	 *
	 * @param string $file
	 * @param boolean $parseHeader
	 * @param string $delimiter
	 * @param integer $length
	 */
	public function __construct( $file, $parseHeader = false, $delimiter = ",", $length = 8000 ) {
		try {
			$this->file = new SplFileObject( $file, 'r' );
		} catch ( RuntimeException $e ) {
			throw new FileNotFoundException( 'File "' . $file . '" is not accessible.' );
		}

		$this->parseHeader = $parseHeader;
		$this->delimiter = $delimiter;
		$this->length = $length;

		$this->rewind();
	}

	/**
	 * @since 3.0
	 */
	public function __destruct() {
		$this->handle = null;
	}

	/**
	 * @see Countable::count
	 * @since 2.5
	 *
	 * {@inheritDoc}
	 */
	public function count() : int {
		if ( $this->count ) {
			return $this->count;
		}


		return $this->count;
	}

	/**
	 * @since 3.0
	 *
	 * @return []
	 */
	public function getHeader() {
		return $this->header ?? [];
	}

	/**
	 * Resets the file handle
	 *
	 * @since 3.0
	 *
	 * {@inheritDoc}
	 */
	public function rewind() : void {
		$this->key = 0;
		// Can't rewind when iterating, have to get count earlier.
		// https://stackoverflow.com/questions/21447329/how-can-i-get-the-total-number-of-rows-in-a-csv-file-with-php
		$this->file->seek( PHP_INT_MAX );
		$this->count = $this->file->key() + ( $this->parseHeader ? 0 : 1 );
		$this->file->rewind();
		if ( $this->parseHeader ) {
			$this->header = $this->file->fgetcsv( $this->delimiter );
		}
		$this->currentElement = $this->file->fgetcsv( $this->delimiter );
	}

	/**
	 * Returns the current CSV row.  False if reading past EOF.
	 *
	 * @since 3.0
	 *
	 * {@inheritDoc}
	 */
	public function current() : mixed {
		return $this->currentElement;
	}

	/**
	 * Returns the current row number.
	 * First row (after header) is row 0.
	 *
	 * @since 3.0
	 *
	 * {@inheritDoc}
	 */
	public function key() : int {
		return $this->key;
	}

	/**
	 * Read next line
	 *
	 * @since 3.0
	 *
	 * {@inheritDoc}
	 */
	public function next() : void {
		$this->key++;
		$this->currentElement = $this->file->fgetcsv( $this->delimiter );
	}

	/**
	 * Checks if there are more rows.
	 *
	 * @since 3.0
	 *
	 * {@inheritDoc}
	 */
	public function valid() : bool {
		return !$this->file->eof();
	}

}