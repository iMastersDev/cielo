<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.request
 */

require_once 'dso/cielo/nodes/AbstractCieloNode.php';
require_once 'dso/cielo/Transaction.php';

/**
 * @brief		Requisição de transação.
 * @details
 * <p>
 * A autenticação não é executada via Web Service. Não é efetuada somente com troca de mensagens entre
 * loja virtual e Cielo. Ela requer interação com o comprador. Interação tal que é iniciada no ambiente
 * da Cielo a partir do momento que a loja redireciona o browser do usuário.
 * </p>
 * <p>
 * Essa transferência de fluxo possui um destino, o qual é especificado pela URL retornada após a criação
 * da transação. Ela é pautada por algumas regras. Consulte “Redirecionamento à Cielo” para maiores detalhes.
 * Ou navegue pela loja exemplo para tornar mais claro esse entendimento.
 * </p>
 * <p>
 * Após o redirecionamento o portador fornece os dados do cartão no site de e-commerce da Cielo e então a
 * autenticação é de fato iniciada. Lembre-se: ela corre sempre no site do emissor. Para isso um segundo
 * redirecionamento é efetuado: da Cielo para o banco.
 * </p>
 *
 * @attention	Somente o primeiro redirecionamento é de responsabilidade da loja virtual.
 *
 * <p>
 * A tecnologia empregada para autenticação é de escolha do emissor. Pode ser cartão de bingo, token,
 * e-cpf entre outras. Entretanto o objetivo é sempre o mesmo: assegurar que o comprador é o portador legítimo.
 * Essa verificação é retornada à Cielo e o retorno dos fluxos tem início.
 * </p>
 * @ingroup		Cielo
 * @class		TransactionRequest
 */
class TransactionRequest extends AbstractCieloNode {
	/**
	 * Indicador de autorização automática:
	 * @li	0 (não autorizar)
	 * @li	1 (autorizar somente se autenticada)
	 * @li	2 (autorizar autenticada e não-autenticada)
	 * @li	3 (autorizar sem passar por autenticação – válido somente para crédito)
	 * @var		integer
	 */
	private $authorize = 2;

	/**
	 * Seis primeiros números do cartão
	 * @var		integer
	 */
	private $bin;

	/**
	 * Campo livre
	 * @var		string
	 */
	private $freeField;

	/**
	 * Define se a transação será automaticamente capturada caso seja autorizada.
	 * @var		boolean
	 */
	private $capture = false;

	/**
	 * @brief	URL da página de retorno.
	 * @details	É para essa tela que o fluxo será retornado ao fim da autenticação e/ou autorização.
	 * @var		string
	 */
	private $returnURL;

	/**
	 * Cria o nó XML que representa o objeto ou conjunto de objetos na composição
	 * @return	string
	 * @see		Cielo::createXMLNode()
	 * @throws	BadMethodCallException Se a URL de retorno não tiver sido especificada
	 * @throws	BadMethodCallException Se os dados do pedido não tiver sido especificado
	 */
	public function createXMLNode() {
		if (  !empty( $this->returnURL ) ) {
			$dom = new DOMDocument( '1.0' , 'UTF-8' );
			$dom->loadXML( parent::createXMLNode() );
			$dom->encoding = 'UTF-8';

			$orderData = $dom->getElementsByTagName( 'dados-pedido' )->item( 0 );

			if ( $orderData instanceof DOMElement ) {
				$orderNumber = $orderData->getElementsByTagName( 'numero' )->item( 0 );

				if ( $orderNumber instanceof DOMElement ) {
					$this->returnURL = preg_replace( '/\{pedido\}/' , $orderNumber->nodeValue , $this->returnURL );
				}

				$dom->childNodes->item( 0 )->appendChild( $dom->createElement( 'url-retorno' , $this->returnURL ) );
				$dom->childNodes->item( 0 )->appendChild( $dom->createElement( 'autorizar' , $this->authorize ) );
				$dom->childNodes->item( 0 )->appendChild( $dom->createElement( 'capturar' , $this->capture ? 'true' : 'false' ) );

				if (  !is_null( $this->freeField ) ) {
					$dom->childNodes->item( 0 )->appendChild( $dom->createElement( 'campo-livre' , $this->freeField ) );
				}

				return $dom->saveXML();
			} else {
				throw new BadMethodCallException( 'O nó contendo os dados do pedido ainda não foi especificado.' );
			}
		} else {
			throw new BadMethodCallException( 'A URL de retorno deve ser especificada.' );
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
		return '1';
	}

	/**
	 * Recupera o nome do nó raiz do XML que será enviado à Cielo
	 * @return	string
	 */
	protected function getRootNode() {
		return 'requisicao-transacao';
	}

	/**
	 * @brief	Indicador de autorização automática.
	 * @details	Define se será feita a autorização automática, seu valor pode ser um dos seguinte:
	 * @param	integer $authorize Indicador de autorização automática:
	 * @li	0 (não autorizar)
	 * @li	1 (autorizar somente se autenticada)
	 * @li	2 (autorizar autenticada e não-autenticada)
	 * @li	3 (autorizar sem passar por autenticação – válido somente para crédito)
	 * @throws	InvalidArgumentException Se o valor informado para $authorize não for um dos descritos acima
	 */
	public function setAuthorization( $authorize = 2 ) {
		if ( is_int( $authorize ) && ( $authorize >= 0 && $authorize <= 3 ) ) {
			$this->authorize = $authorize;
		} else {
			throw new InvalidArgumentException( 'Identificador de autorização inválido' );
		}
	}

	/**
	 * @brief	Seis primeiros números do cartão.
	 * @details	Define os seis primeiros números do cartão no caso de a autenticação estar sendo
	 * feita pela própria loja
	 * @param	integer $numero
	 * @throws	InvalidArgumentException Se $numero não for um inteiro
	 */
	public function setBin( $numero ) {
		if ( is_int( $numero ) ) {
			$this->bin = $numero;
		} else {
			throw new InvalidArgumentException( 'O valor de $numero deve ser um inteiro' );
		}
	}

	/**
	 * @brief	Campo livre.
	 * @details	Define um valor qualquer para o campo livre, esse valor poderá ser resgatado
	 * na hora que a Cielo redirecionar de volta para a loja
	 * @param	string $freeField Um valor qualquer
	 * @throws	InvalidArgumentException Se $freeField não for uma string
	 */
	public function setFreeField( $freeField ) {
		if ( is_string( $freeField ) ) {
			$this->freeField = $freeField;
		} else {
			throw new InvalidArgumentException( 'O conteúdo do campo livre deve ser uma string' );
		}
	}

	/**
	 * @brief	Define se será feita a captura automática.
	 * @details	Define se a transação será automaticamente capturada caso seja autorizada.
	 * @param	boolean $capture TRUE ou FALSE
	 * @throws	InvalidArgumentException Se $capturar não for um boolean
	 */
	public function setCapture( $capture = true ) {
		if ( is_bool( $capture ) ) {
			$this->capture = $capture;
		} else {
			throw new InvalidArgumentException( '$capture precisa ser um boolean' );
		}
	}

	/**
	 * @brief	Define a URL da página de retorno.
	 * @details	É para essa tela que o fluxo será retornado ao fim da autenticação e/ou autorização.
	 * @param	string $returnURL
	 */
	public function setReturnURL( $returnURL ) {
		$this->returnURL = $returnURL;
	}
}