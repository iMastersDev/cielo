<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.nodes
 */

require_once 'dso/cielo/nodes/XMLNode.php';

/**
 * @brief		Nó dados-cartao
 * @details		Implementação do nó dados-cartao que contém os dados do cartão de crédito
 * @ingroup		Cielo
 * @class		CardDataNode
 */
class CardDataNode implements XMLNode {
	/**
	 * Número do cartão de crédito
	 * @var	string
	 */
	private $cardNumber;

	/**
	 * Expiração do cartão no formato yyyymm.
	 * @var	integer
	 */
	private $cardExpiration;

	/**
	 * Código de segurança do cartão, <b>obrigatório se $indicator = 1</b>
	 * @var	integer
	 */
	private $securityCode;

	/**
	 * Indicador de segurança
	 * @see	ECI
	 * @var	integer
	 */
	private $indicator;

	/**
	 * Nome do portador do cartão
	 * @var	string
	 */
	private $holderName;

	/**
	 * Cria o objeto que representa o nó dados-portador
	 * @param string $cardNumber Número do cartão de crédito
	 * @param integer $cardExpiration Data de expiração do cartão no formato <b>yyyymm</b>
	 * @param integer $indicator Indicador do código de segurança
	 * @param integer $securityCode Código de segurança
	 * @param string $holderName Nome do titular do cartão
	 */
	public function __construct( $cardNumber , $cardExpiration , $indicator , $securityCode = null , $holderName = null ) {
		if ( $indicator == 1 && is_null( $securityCode ) ){
			throw new InvalidArgumentException( 'Quando o indicador do código de segurança for 1, o código de segurança deve ser informado' );
		}

		$this->cardNumber = $cardNumber;
		$this->cardExpiration = $cardExpiration;
		$this->indicator = $indicator;
		$this->securityCode = $securityCode;
		$this->holderName = $holderName;
	}

	/**
	 * Cria o nó XML referente ao objeto.
	 * @return	string
	 * @see		XMLNode::createXMLNode()
	 */
	public function createXMLNode() {
		$node = '<dados-cartao>';

		$node .= sprintf( '<numero>%s</numero>' , $this->cardNumber );
		$node .= sprintf( '<validade>%s</validade>' , $this->cardExpiration );
		$node .= sprintf( '<indicador>%s</indicador>' , $this->indicator );

		if (  !empty( $this->securityCode ) ) {
			$node .= sprintf( '<codigo-seguranca>%s</codigo-seguranca>' , $this->securityCode );
		}

		if (  !empty( $this->holderName ) ) {
			$node .= sprintf( '<nome-portador>%s</nome-portador>' , $this->holderName );
		}

		$node .= '</dados-cartao>';

		return $node;
	}

	/**
	 * Recupera o valor de $cardNumber
	 * @return	string
	 */
	public function getCardNumber() {
		return $this->cardNumber;
	}

	/**
	 * Recupera o valor de $cardExpiration
	 * @return	integer
	 */
	public function getCardExpiration() {
		return $this->cardExpiration;
	}

	/**
	 * Recupera o valor de $securityCode
	 * @return	integer
	 */
	public function getSecurityCode() {
		return $this->securityCode;
	}

	/**
	 * Recupera o valor de $indicator
	 * @return	integer
	 */
	public function getIndicator() {
		return $this->indicator;
	}

	/**
	 * Recupera o valor de $holderName
	 * @return	string
	 */
	public function getHolderName() {
		return $this->holderName;
	}
}