<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.request
 */

require_once 'dso/cielo/nodes/AbstractCieloNode.php';
require_once 'dso/cielo/Transaction.php';

/**
 * @brief		Requisição de Captura.
 * @details		Uma transação autorizada somente gera crédito para o estabelecimento comercial caso
 * ela seja capturada. Por isso, todo pedido de compra que o lojista queira efetivar, deve ter a transação capturada.
 * <p>
 * Para venda na modalidade de Crédito, essa confirmação pode ocorrer
 *
 * @li Logo após a autorização
 * @li Ou num momento posterior
 *
 * Essa definição é feita através do parâmetro capturar. Consulte o tópico “Criação”.
 * Já na modalidade de Débito não existe essa abertura: toda transação de débito autorizada é automaticamente capturada.
 * </p>
 * @ingroup		Cielo
 * @class		CaptureRequest
 */
class CaptureRequest extends AbstractCieloNode {
	/**
	 * ID da transação
	 * @var		string
	 */
	private $tid;

	/**
	 * Valor da captura. Caso não fornecido, o valor atribuído é o valor da autorização.
	 * @var		integer
	 */
	private $value;

	/**
	 * Informação adicional para detalhamento da captura
	 * @var		string
	 */
	private $annex;

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

				if (  !is_null( $this->value ) ) {
					$value = $dom->createElement( 'valor' , $this->value );
					$query->insertBefore( $value , $EcData );
				}

				if (  !is_null( $this->annex ) ) {
					$annex = $dom->createElement( 'anexo' );
					$query->insertBefore( $annex , $EcData );
				}
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
	 * Recupera o ID do nó raiz
	 * @return	string
	 */
	protected function getId() {
		return 5;
	}

	/**
	 * Recupera o nome do nó raiz do XML que será enviado à Cielo
	 * @return	string
	 */
	protected function getRootNode() {
		return 'requisicao-captura';
	}

	/**
	 * Define uma informação adicional para detalhamento da captura
	 * @param	string $annex
	 */
	public function setAnnex( $annex ) {
		if ( is_string( $annex ) ) {
			$this->annex = $annex;
		} else {
			throw new InvalidArgumentException( sprintf( 'Anexo deve ser uma string, %s foi dado' , gettype( $annex ) ) );
		}
	}

	/**
	 * Define o identificador da transação
	 * @param	string $tid
	 */
	public function setTID( $tid ) {
		$this->tid = $tid;
	}

	/**
	 * @brief	Define valor da captura.
	 * @details	Caso não fornecido, o valor atribuído é o valor da autorização.
	 * @param	float $value
	 * @throws	InvalidArgumentException
	 */
	public function setValue( $value ) {
		if ( is_float( $value ) || is_int( $value ) ) {
			$this->value = $value;
		} else {
			throw new InvalidArgumentException( sprintf( 'O valor deve ser um inteiro ou float, %s foi dado.' , gettype( $value ) ) );
		}
	}
}