<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.nodes
 */

require_once 'dso/cielo/nodes/XMLNode.php';

/**
 * @brief		Nó dados-ec
 * @details		Implementação do nó dados-ec que contém os dados da afiliação, nome da empresa, chave
 * e todas as informações referentes à empresa.
 * @ingroup		Cielo
 * @class		EcDataNode
 */
class EcDataNode implements XMLNode {
	/**
	 * Número de afiliação da loja com a Cielo.
	 * @var		integer
	 */
	private $affiliationCode;

	/**
	 * Chave de acesso da loja atribuída pela Cielo.
	 * @var		string
	 */
	private $affiliationKey;

	/**
	 * Constroi o objeto que representa o nó dados-ec
	 * @param	integer $affiliationCode Número de afiliação da loja com a Cielo.
	 * @param	string $affiliationKey Chave de acesso da loja atribuída pela Cielo.
	 */
	public function __construct( $affiliationCode , $affiliationKey ) {
		$this->affiliationCode = $affiliationCode;
		$this->affiliationKey = $affiliationKey;
	}

	/**
	 * Cria o nó XML referente ao objeto.
	 * @return	string
	 * @see		XMLNode::createXMLNode()
	 */
	public function createXMLNode() {
		$node = '<dados-ec>';

		if (  !empty( $this->affiliationCode ) ) {
			$node .= sprintf( '<numero>%s</numero>' , $this->affiliationCode );
		}

		if (  !empty( $this->affiliationKey ) ) {
			$node .= sprintf( '<chave>%s</chave>' , $this->affiliationKey );
		}

		$node .= '</dados-ec>';

		return $node;
	}
}