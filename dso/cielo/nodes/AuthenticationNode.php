<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.nodes
 */

require_once 'dso/cielo/nodes/TransactionNode.php';

/**
 * @brief		Nó autenticacao
 * @details		Nó com dados da autenticação caso tenha passado por essa etapa.
 * @ingroup		Cielo
 * @class		AuthenticationNode
 */
class AuthenticationNode extends TransactionNode {
	/**
	 * Nível de segurança.
	 * @var		integer
	 */
	private $eci;

	/**
	 * Constroi o objeto que representa o nó autenticacao
	 * @attention <b>Os dois últimos dígitos são os centavos.</b>
	 * @param	integer $code Código do processamento.
	 * @param	string $message Detalhe do processamento.
	 * @param	string $dateTime Data hora do processamento.
	 * @param	integer $value Valor do processamento sem pontuação.
	 * @param	integer $eci Nível de segurança.
	 */
	public function __construct( $code , $message , $dateTime , $value , $eci ) {
		parent::__construct( $code , $message , $dateTime , $value );

		$this->eci = $eci;
	}

	/**
	 * Cria o nó XML referente ao objeto.
	 * @return	string
	 * @see		XMLNode::createXMLNode()
	 */
	public function createXMLNode() {
		$node = '<autenticacao />';

		return $node;
	}

	/**
	 * Recupera o nível de segurança da transação
	 * @return	integer
	 */
	public function getECI() {
		return $this->eci;
	}
}