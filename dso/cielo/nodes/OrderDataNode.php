<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.nodes
 */

require_once 'dso/cielo/nodes/XMLNode.php';

/**
 * @brief		Nó dados-pedido
 * @details		Implementação do nó dados-pedido que contém os dados da afiliação, nome da empresa, chave
 * e todas as informações referentes à empresa.
 * @ingroup		Cielo
 * @class		OrderDataNode
 */
class OrderDataNode implements XMLNode {
	/**
	 * Data hora do pedido.
	 * @var		string
	 */
	private $dateTime;

	/**
	 * Idioma do pedido:
	 * @li PT - português
	 * @li EN - inglês
	 * @li ES - espanhol
	 * @var		string
	 */
	private $language = 'PT';

	/**
	 * Código numérico da moeda na ISO 4217. Para o Real, o código é 986.
	 * @var		integer
	 */
	private $currency;

	/**
	 * Número do pedido da loja.
	 * @var		string
	 */
	private $orderNumber;

	/**
	 * Valor do pedido
	 * @var		integer
	 */
	private $orderValue;

	/**
	 * Constroi o objeto que representa os dados do pedido
	 * @param	integer $orderNumber Número do pedido
	 * @param	float $orderValue Valor do pedido
	 * @param	integer $currency Moeda usada no pedido (<b>986</b> para Real R$)
	 * @param	string $dateTime Data e hora do pedido
	 * @param	string $language Idioma do pedido:
	 * @li PT - português
	 * @li EN - inglês
	 * @li ES - espanhol
	 * Com base nessa informação é definida a língua a ser utilizada nas telas da Cielo. <b>Caso não preenchido, assume-se PT</b>.
	 */
	public function __construct( $orderNumber , $orderValue , $currency = 986 , $dateTime = null , $language = 'PT' ) {
		$this->orderNumber = $orderNumber;
		$this->orderValue = (int) $orderValue;
		$this->currency = $currency;

		if ( is_null( $dateTime ) ) {
			$dateTime = strftime( '%Y-%m-%dT%H:%M:%S' );
		} else {
			$dateTime = strftime( '%Y-%m-%d %H:%M:%S' , strtotime( $dateTime ) );
		}

		$this->language = $language;
		$this->dateTime = $dateTime;
	}

	/**
	 * Cria o nó XML referente ao objeto.
	 * @return	string
	 * @see		XMLNode::createXMLNode()
	 */
	public function createXMLNode() {
		$node = '<dados-pedido>';

		if (  !empty( $this->orderNumber ) ) {
			$node .= sprintf( '<numero>%s</numero>' , $this->orderNumber );
		}

		if (  !empty( $this->orderValue ) ) {
			$node .= sprintf( '<valor>%s</valor>' , $this->orderValue );
		}

		if (  !empty( $this->currency ) ) {
			$node .= sprintf( '<moeda>%s</moeda>' , $this->currency );
		}

		$node .= sprintf( '<data-hora>%s</data-hora>' , $this->dateTime );

		if (  !empty( $this->language ) ) {
			$node .= sprintf( '<idioma>%s</idioma>' , $this->language );
		}

		$node .= '</dados-pedido>';

		return $node;
	}

	/**
	 * Recupera a data e hora do pedido.
	 * @return	string
	 */
	public function getDateTime() {
		return $this->dateTime;
	}

	/**
	 * Recupera o idioma do pedido:
	 * @li PT - português
	 * @li EN - inglês
	 * @li ES - espanhol
	 * @return	string
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * Código numérico da moeda na ISO 4217. Para o Real, o código é 986.
	 * @return	integer
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * Recupera o número do pedido da loja
	 * @return	string
	 */
	public function getOrderNumber() {
		return $this->orderNumber;
	}

	/**
	 * Recupera o valor do pedido
	 * @return	float
	 */
	public function getOrderValue() {
		return $this->orderValue;
	}
}