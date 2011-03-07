<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.nodes
 */

require_once 'dso/cielo/nodes/TransactionNode.php';

/**
 * @brief		Nó autorizacao
 * @details		Nó com dados da autorização caso tenha passado por essa etapa.
 * @ingroup		Cielo
 * @class		AuthorizationNode
 */
class AuthorizationNode extends TransactionNode {
	/**
	 * @attention Quando negada, é o motivo da negação.
	 * @brief	Retorno da autorização.
	 * @var		integer
	 */
	private $lr;

	/**
	 * Código da autorização caso a transação tenha sido autorizada com sucesso.
	 * @var		string
	 */
	private $arp;

	/**
	 * Constroi o objeto que representa o nó autenticacao
	 * @param	integer $code Código do processamento.
	 * @param	string $message Detalhe do processamento.
	 * @param	string $dateTime Data hora do processamento.
	 * @param	integer $value Valor do processamento sem pontuação.
	 * @attention <b>Os dois últimos dígitos são os centavos.</b>
	 * @param	integer $lr Retorno da autorização.
	 * @attention Quando negada, é o motivo da negação.
	 * @param	string $arp Código da autorização caso a transação tenha sido autorizada com sucesso.
	 */
	public function __construct( $code , $message , $dateTime , $value , $lr , $arp ) {
		parent::__construct( $code , $message , $dateTime , $value );
		$this->lr = $lr;
		$this->arp = $arp;
	}

	/**
	 * Cria o nó XML referente ao objeto.
	 * @return	string
	 * @see		XMLNode::createXMLNode()
	 */
	public function createXMLNode() {
		$node = '<autorizacao />';

		return $node;
	}

	/**
	 * Recupera o retorno da autorização.
	 * @attention Quando negada, é o motivo da negação.
	 * @return	integer
	 */
	public function getLR() {
		return $this->lr;
	}

	/**
	 * Código da autorização caso a transação tenha sido autorizada com sucesso.
	 * @return	string
	 */
	public function getArp() {
		return $this->arp;
	}
}