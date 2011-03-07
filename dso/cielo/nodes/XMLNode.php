<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.nodes
 */

/**
 * @brief		Objeto que será trafegado como XML
 * @details		Interface para um objeto que será trafegado via HTTP na forma de XML
 * @ingroup		Cielo
 * @interface	XMLNode
 */
interface XMLNode {
	/**
	 * Cria o nó XML referente ao objeto
	 * @return	string
	 */
	public function createXMLNode();
}