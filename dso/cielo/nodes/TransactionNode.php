<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.nodes
 */

require_once 'dso/cielo/nodes/XMLNode.php';

/**
 * @brief		Nós da transação
 * @details		Base para implementação dos nós da transação
 * @ingroup		Cielo
 * @class		TransactionNode
 */
abstract class TransactionNode implements XMLNode {
	/**
	 * Código do processamento.
	 * @var		integer
	 */
	private $code;

	/**
	 * Detalhe do processamento.
	 * @var		string
	 */
	private $message;

	/**
	 * Data hora do processamento.
	 * @var		string
	 */
	private $dateTime;

	/**
	 * Valor do processamento sem pontuação.
	 * @attention <b>Os dois últimos dígitos são os centavos.</b>
	 * @var		integer
	 */
	private $value;

	/**
	 * Constroi o objeto que representa o nó captura
	 * @param	integer $code Código do processamento.
	 * @param	string $message Detalhe do processamento.
	 * @param	string $dateTime Data hora do processamento.
	 * @param	integer $value Valor do processamento sem pontuação.
	 * @attention <b>Os dois últimos dígitos são os centavos.</b>
	 */
	public function __construct( $code , $message , $dateTime , $value ) {
		$this->code = $code;
		$this->message = $message;
		$this->dateTime = strftime( '%Y-%m-%d %H:%M:%S' , strtotime( $dateTime ) );
		$this->value = (int) $value;
	}

	/**
	 * Recupera o código do processamento.
	 * @return	integer
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Recupera os detalhes do processamento.
	 * @return	string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Recupera a data e hora do processamento.
	 * @return	string
	 */
	public function getDateTime() {
		return $this->dateTime;
	}

	/**
	 * Recupera o valor do processamento
	 * @return	float
	 */
	public function getValue() {
		return (float) $this->value / 100;
	}
}