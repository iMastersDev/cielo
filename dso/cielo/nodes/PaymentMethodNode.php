<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.nodes
 */

require_once 'dso/cielo/nodes/XMLNode.php';
require_once 'dso/cielo/PaymentProduct.php';

/**
 * @brief		Nó forma-pagamento
 * @details		Implementação do nó forma-pagamento que contém os dados de forma de pagamento
 * @ingroup		Cielo
 * @class		PaymentMethodNode
 */
class PaymentMethodNode implements XMLNode {
	/**
	 * Bandeira do cartão
	 * @var		string
	 * @see		CreditCard
	 */
	private $creditCard;

	/**
	 * Código do produto:
	 * @li	1 - Crédito à Vista
	 * @li	2 - Parcelado loja
	 * @li	3 - Parcelado administradora
	 * @li	A - Débito
	 * @var		string
	 * @see		PaymentMethod
	 */
	private $product;

	/**
	 * Número de parcelas. Para crédito à vista ou débido, utilizar <b>1</b>
	 * @var		integer
	 */
	private $parcels;

	/**
	 * Constroi o objeto que representa a forma de pagamento
	 * @param	string $product Código do produto, pode ser um dos seguintes:
	 * @li 1 Crédito à vista
	 * @li 2 Parcelado Loja
	 * @li 3 Parcelado Administradora
	 * @li A Débito
	 * @param	integer $parcels Número de parcelas. Para crédito à vista ou Débito, utilizar 1.
	 * @param	string $creditCard Bandeira do cartão
	 * @see		PaymentMethod
	 * @see		CreditCard
	 * @throws	UnexpectedValueException Se $parcels não for um inteiro, maior ou igual a 1
	 * @throws	InvalidArgumentException Se o número de parcelas for diferente de 1 para crédito à vista ou débito
	 * @throws	InvalidArgumentException Se o valor de $produto não for válido
	 */
	public function __construct( $product , $parcels = 1 , $creditCard = null ) {
		if ( is_int( $parcels ) && ( $parcels >= 1 ) ) {
			switch ( $product ) {
				case PaymentProduct::ONE_TIME_PAYMENT :
				case PaymentProduct::DEBIT :
					if ( $parcels > 1 ) {
						throw new InvalidArgumentException( 'Para crédito à vista, o número de parcelas deve ser 1' );
					}
				case PaymentProduct::INSTALLMENTS_BY_CARD_ISSUERS :
				case PaymentProduct::INSTALLMENTS_BY_AFFILIATED_MERCHANTS :
					$this->product = $product;
					$this->parcels = $parcels;
					break;
				default :
					throw new InvalidArgumentException( 'Valor de $produto não é válido.' );
			}

			if (  !is_null( $creditCard ) ) {
				if ( $creditCard == CreditCard::VISA || $creditCard == CreditCard::MASTER_CARD ) {
					$this->creditCard = $creditCard;
				} else {
					throw new UnexpectedValueException( 'Bandeira não reconhecida.' );
				}
			}
		} else {
			throw new UnexpectedValueException( '$parcelas precisa ser um inteiro, maior ou igual a 1' );
		}
	}

	/**
	 * Cria o nó XML referente ao objeto.
	 * @return	string
	 * @see		XMLNode::createXMLNode()
	 */
	public function createXMLNode() {
		$node = '<forma-pagamento>';
		$node .= sprintf( '<bandeira>%s</bandeira>' , $this->creditCard );
		$node .= sprintf( '<produto>%s</produto>' , $this->product );
		$node .= sprintf( '<parcelas>%d</parcelas>' , $this->parcels );
		$node .= '</forma-pagamento>';

		return $node;
	}

	/**
	 * Recupera o número de parcelas
	 * @return	integer
	 */
	public function getParcels() {
		return $this->parcels;
	}

	/**
	 * Recupera o código do produto
	 * @li	1 - Crédito à Vista
	 * @li	2 - Parcelado loja
	 * @li	3 - Parcelado administradora
	 * @li	A - Débito
	 * @return	string
	 * @see		PaymentMethod
	 */
	public function getProduct() {
		return $this->product;
	}
}