<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.request
 */

require_once 'dso/cielo/nodes/AbstractCieloNode.php';
require_once 'dso/cielo/Transaction.php';

/**
 * @brief		Requisição de Cancelamento.
 * @details		É empregado quando o lojista decide não efetivar um pedido de compra, seja por insuficiência
 * de estoque, desistência da compra, entre outros motivos. Seu uso faz-se necessário principalmente se a
 * transação estiver capturada, caso contrário haverá débito na fatura do cliente para um pedido de compra não efetivado.
 * @attention	se a transação estiver apenas autorizada e a loja queira cancelá-la, o pedido de cancelamento não é de
 * fato necessário: vencido o prazo de captura, ela é cancelada automaticamente.
 * @ingroup		Cielo
 * @class		CancellationRequest
 */
class CancellationRequest extends AbstractCieloNode {
	/**
	 * ID da transação
	 * @var		string
	 */
	private $tid;

	/**
	 * Cria o nó XML que representa o objeto ou conjunto de objetos na composição
	 * @return	string
	 * @see		Cielo::createXMLNode()
	 * @throws	BadMethodCallException Se a URL de retorno não tiver sido especificada
	 * @throws	BadMethodCallException Se os dados do pedido não tiver sido especificado
	 */
	public function createXMLNode() {
		if (  !empty( $this->tid ) ) {
			$dom = new DOMDocument( '1.0' , 'UTF-8' );
			$dom->loadXML( parent::createXMLNode() );
			$dom->encoding = 'UTF-8';

			$namespace = $this->getNamespace();
			$query = $dom->getElementsByTagNameNS( $namespace , $this->getRootNode() )->item( 0 );
			$EcData = $dom->getElementsByTagNameNS( $namespace , 'dados-ec' )->item( 0 );

			if ( $EcData instanceof DOMElement ) {
				$tid = $dom->createElement( 'tid' , $this->tid );
				$query->insertBefore( $tid , $EcData );
			} else {
				throw new BadMethodCallException( 'O nó dados-ec precisa ser informado.' );
			}

			return $dom->saveXML();
		} else {
			throw new BadMethodCallException( 'O ID da transação deve ser informado.' );
		}
	}

	/**
	 * Faz a chamada da requisição de autenticação no webservice da Cielo
	 * @return	Transaction
	 * @see		Cielo::call()
	 */
	public function call() {
		return new Transaction( parent::call() );
	}

	/**
	 * Define o identificador da transação
	 * @param	string $tid
	 */
	public function setTID( $tid ) {
		$this->tid = $tid;
	}

	/**
	 * Recupera o ID do nó raiz
	 * @return	string
	 */
	protected function getId() {
		return $this->tid;
	}

	/**
	 * Recupera o nome do nó raiz do XML que será enviado à Cielo
	 * @return	string
	 */
	protected function getRootNode() {
		return 'requisicao-cancelamento';
	}
}