<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.nodes
 */

require_once 'dso/cielo/nodes/TransactionNode.php';

/**
 * @brief		Nó cancelamento
 * @details		Nó com dados do cancelamento caso tenha passado por essa etapa
 * @ingroup		Cielo
 * @class		CancellationNode
 */
class CancellationNode extends TransactionNode {
	/**
	 * Cria o nó XML referente ao objeto.
	 * @return	string
	 * @see		XMLNode::createXMLNode()
	 */
	public function createXMLNode() {
		$node = '<cancelamento />';

		return $node;
	}
}