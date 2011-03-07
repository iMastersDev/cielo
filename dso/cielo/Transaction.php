<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo
 */

require_once 'dso/cielo/nodes/AuthenticationNode.php';
require_once 'dso/cielo/nodes/AuthorizationNode.php';
require_once 'dso/cielo/nodes/CaptureNode.php';
require_once 'dso/cielo/nodes/CancellationNode.php';
require_once 'dso/cielo/nodes/OrderDataNode.php';
require_once 'dso/cielo/nodes/PaymentMethodNode.php';

/**
 * Retorno de uma Requisição ao webservice da Cielo
 * @ingroup		Cielo
 * @class		Transaction
 */
class Transaction {
	/**
	 * Dados de autenticação
	 * @var AuthenticationNode
	 */
	private $authentication;

	/**
	 * Dados de autorização
	 * @var AuthorizationNode
	 */
	private $authorization;

	/**
	 * Dados de cancelamento
	 * @var CancellationNode
	 */
	private $cancellation;

	/**
	 * Dados de captura
	 * @var CaptureNode
	 */
	private $capture;

	/**
	 * Dados do Pedido
	 * @var OrderDataNode
	 */
	private $orderData;

	/**
	 * Dados da forma de pagamento
	 * @var PaymentMethodNode
	 */
	private $paymentMethod;

	/**
	 * Status da transação
	 * @var int
	 */
	private $status;

	/**
	 * ID da transação
	 * @var string
	 */
	private $tid;

	/**
	 * URL de autenticação
	 * @var string
	 */
	private $url;

	/**
	 * Hash do número do cartão do portador.
	 * @var string
	 */
	private $pan;

	/**
	 * @brief	Constroi o objeto de transação segundo o XML retornado pela Cielo
	 * @details	O XML de resposta da Cielo pode vir de duas formas: &lt;transacao /&gt; ou &lt;erro /&gt;
	 * Caso o retorno seja um &lt;erro /&gt; uma exceção Exception será disparada com o código e a mensagem.
	 * <p>
	 * @li <b>Exemplo de retorno para uma transação</b>
	 * <code><pre>
	 * &lt;?xml version="1.0" encoding="ISO-8859-1"?&gt;
	 * &lt;transacao id="1" versao="1.0.0" xmlns="http://ecommerce.cbmp.com.br"&gt;
	 * &lt;tid&gt;100173489800B2F81001&lt;/tid&gt;
	 * &lt;dados-pedido&gt;
	 * &lt;numero&gt;123&lt;/numero&gt;
	 * &lt;valor&gt;100&lt;/valor&gt;
	 * &lt;moeda&gt;986&lt;/moeda&gt;
	 * &lt;data-hora&gt;2010-08-09T11:21:29.305-03:00&lt;/data-hora&gt;
	 * &lt;idioma&gt;PT&lt;/idioma&gt;
	 * &lt;/dados-pedido&gt;
	 * &lt;forma-pagamento&gt;
	 * &lt;produto&gt;1&lt;/produto&gt;
	 * &lt;parcelas&gt;1&lt;/parcelas&gt;
	 * &lt;/forma-pagamento&gt;
	 * &lt;status&gt;0&lt;/status&gt;
	 * &lt;url-autenticacao&gt;https://qasecommerce.cielo.com.br/web/index.cbmp?id=bf9b310513668bdf92797518eb249c03&lt;/url-autenticacao&gt;
	 * &lt;/transacao&gt;
	 * </pre></code>
	 * @li <b>Exemplo de um retorno de erro</b>
	 * <code><pre>
	 * &lt;?xml version="1.0" encoding="UTF-8"?&gt;
	 * &lt;erro xmlns="http://ecommerce.cbmp.com.br"&gt;
	 * &lt;codigo&gt;032&lt;/codigo&gt;
	 * &lt;mensagem&gt;Valor de captura inválido&lt;/mensagem&gt;
	 * &lt;/erro&gt;
	 * </pre></code>
	 * </p>
	 * @param	$xml string XML Retornado por uma chamada RequisicaoAutenticacao
	 * @throws	Exception Se o nó raiz da resposta da Cielo for um &lt;erro /&gt;
	 */
	public function __construct( $xml ) {
		libxml_use_internal_errors( true );

		$dom = new DOMDocument();
		$dom->loadXML( $xml );

		if ( $dom->getElementsByTagName( 'erro' )->item( 0 ) instanceof DOMElement ) {
			$codigo = $dom->getElementsByTagName( 'codigo' )->item( 0 )->nodeValue;
			$mensagem = $dom->getElementsByTagName( 'mensagem' )->item( 0 )->nodeValue;

			throw new Exception( $mensagem , $codigo );
		} else if ( ( $retorno = $dom->getElementsByTagName( 'retorno-tid' )->item( 0 ) ) instanceof DOMElement ){
			$this->tid = $this->getNodeValue( 'tid' , $retorno );
		} else {
			$transacao = $dom->getElementsByTagName( 'transacao' )->item( 0 );

			if ( $transacao instanceof DOMElement ) {
				$this->tid = $this->getNodeValue( 'tid' , $transacao );
				$this->pan = $this->getNodeValue( 'pan' , $transacao );

				$this->parseOrderData( $dom->getElementsByTagName( 'dados-pedido' )->item( 0 ) );
				$this->parsePaymentMethod( $dom->getElementsByTagName( 'forma-pagamento' )->item( 0 ) );
				$this->parseAuthentication( $dom->getElementsByTagName( 'autenticacao' )->item( 0 ) );
				$this->parseAuthorization( $dom->getElementsByTagName( 'autorizacao' )->item( 0 ) );
				$this->parseCapture( $dom->getElementsByTagName( 'captura' )->item( 0 ) );
				$this->parseCancellation( $dom->getElementsByTagName( 'cancelamento' )->item( 0 ) );

				$this->status = $this->getNodeValue( 'status' , $transacao );
				$this->status = is_null( $this->status ) ?  -1 : (int) $this->status;
				$this->url = $this->getNodeValue( 'url-autenticacao' , $transacao );
			} else {
				throw new RuntimeException( 'Um erro inesperado ocorreu, não existe um nó transação no retorno' );
			}
		}
	}

	/**
	 * @brief	Recupera os dados de autenticação.
	 * @details	Dados da autenticação caso tenha passado por essa etapa.
	 * @return	AuthenticationNode
	 */
	public function getAuthentication() {
		return $this->authentication;
	}

	/**
	 * @brief	Recupera os dados de autorização.
	 * @details	Dados da autorização caso tenha passado por essa etapa.
	 * @return	AuthorizationNode
	 */
	public function getAuthorization() {
		return $this->authorization;
	}

	/**
	 * @brief	Recupera os dados de cancelamento.
	 * @details	Dados do cancelamento caso tenha passado por essa etapa.
	 * @return	CancellationNode
	 */
	public function getCancellation() {
		return $this->capture;
	}

	/**
	 * @brief	Recupera os dados de captura.
	 * @details	Dados da captura caso tenha passado por essa etapa.
	 * @return	CaptureNode
	 */
	public function getCapture() {
		return $this->capture;
	}

	/**
	 * @brief	Recupera os dados do pedido.
	 * @details	Idêntico ao enviado pela loja na criação da transação.
	 * @return	OrderDataNode
	 */
	public function getOrderData() {
		return $this->orderData;
	}

	/**
	 * @brief	Recupera a forma de pagamento.
	 * @details	Idêntico ao enviado pela loja na criação da transação.
	 * @return	PaymentMethodNode
	 */
	public function getPaymentMethod() {
		return $this->paymentMethod;
	}

	/**
	 * Recupera o valor de um nó
	 * @param	$name string Nome do nó que se deseja recuperar
	 * @param	$node DOMElement Nó pai que contém o nó desejado
	 * @return	string
	 */
	private function getNodeValue( $name , DOMElement $node ) {
		$element = $node->getElementsByTagName( $name )->item( 0 );

		if (  !is_null( $element ) ) {
			return $element->nodeValue;
		}

		return null;
	}

	/**
	 * Recupera o Hash do número do cartão do portador.
	 * @return	string
	 */
	public function getPan() {
		return $this->pan;
	}

	/**
	 * @brief	Recupera o status da transação
	 * @details	O código de status, pode ser um dos seguintes:
	 * @li 0 - Criada
	 * @li 1 - Em andamento
	 * @li 2 - Autenticada
	 * @li 3 - Não autenticada
	 * @li 4 - Autorizada ou pendente de captura
	 * @li 5 - Não autorizada
	 * @li 6 - Capturada
	 * @li 8 - Não capturada
	 * @li 9 - Cancelada
	 * @return	integer
	 * @see		TransacaoStatus
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Recupera o ID da transação
	 * @return	string
	 */
	public function getTID() {
		return $this->tid;
	}

	/**
	 * Recupera a URL de redirecionamento à Cielo.
	 * @return	string
	 */
	public function getAuthenticationURL() {
		return $this->url;
	}

	/**
	 * Interpreta a autenticação caso tenha passado por essa etapa.
	 * @param	$element DOMElement Nó autenticacao
	 */
	private function parseAuthentication( DOMElement $element = null ) {
		if (  !is_null( $element ) ) {
			$codigo = $this->getNodeValue( 'codigo' , $element );
			$mensagem = $this->getNodeValue( 'mensagem' , $element );
			$dataHora = $this->getNodeValue( 'data-hora' , $element );
			$valor = $this->getNodeValue( 'valor' , $element );
			$eci = $this->getNodeValue( 'eci' , $element );

			$this->authentication = new AuthenticationNode( $codigo , $mensagem , $dataHora , $valor , $eci );
		}
	}

	/**
	 * Interpreta a autorização caso tenha passado por essa etapa.
	 * @param	$element DOMElement Nó autorizacao
	 */
	private function parseAuthorization( DOMElement $element = null ) {
		if (  !is_null( $element ) ) {
			$codigo = $this->getNodeValue( 'codigo' , $element );
			$mensagem = $this->getNodeValue( 'mensagem' , $element );
			$dataHora = $this->getNodeValue( 'data-hora' , $element );
			$valor = $this->getNodeValue( 'valor' , $element );
			$lr = $this->getNodeValue( 'lr' , $element );
			$arp = $this->getNodeValue( 'arp' , $element );

			$this->authorization = new AuthorizationNode( $codigo , $mensagem , $dataHora , $valor , $lr , $arp );
		}
	}

	/**
	 * Interpreta os dados do pedido anexados à transação
	 * @param	$element DOMElement Nó dados-pedido
	 */
	private function parseOrderData( DOMElement $element = null ) {
		if (  !is_null( $element ) ) {
			$numero = $this->getNodeValue( 'numero' , $element );
			$valor = $this->getNodeValue( 'valor' , $element );
			$moeda = $this->getNodeValue( 'moeda' , $element );
			$dataHora = $this->getNodeValue( 'data-hora' , $element );
			$idioma = $this->getNodeValue( 'idioma' , $element );

			$this->orderData = new OrderDataNode( $numero , $valor , $moeda , $dataHora , $idioma );
		}
	}

	/**
	 * Interpreta a forma de pagamento anexada à transação
	 * @param	$element DOMElement Nó forma-pagamento
	 */
	private function parsePaymentMethod( DOMElement $element = null ) {
		if (  !is_null( $element ) ) {
			$produto = $this->getNodeValue( 'produto' , $element );
			$parcelas = $this->getNodeValue( 'parcelas' , $element );
			$bandeira = $this->getNodeValue( 'bandeira' , $element );

			$this->paymentMethod = new PaymentMethodNode( $produto , (int) $parcelas , $bandeira );
		}
	}

	/**
	 * Interpreta a cancelamento caso tenha passado por essa etapa.
	 * @param	$element DOMElement Nó cancelamento
	 */
	private function parseCancellation( DOMElement $element = null ) {
		if (  !is_null( $element ) ) {
			$codigo = $this->getNodeValue( 'codigo' , $element );
			$mensagem = $this->getNodeValue( 'mensagem' , $element );
			$dataHora = $this->getNodeValue( 'data-hora' , $element );
			$valor = $this->getNodeValue( 'valor' , $element );

			$this->cancellation = new CancellationNode( $codigo , $mensagem , $dataHora , $valor );
		}
	}

	/**
	 * Interpreta a captura caso tenha passado por essa etapa.
	 * @param	$element DOMElement Nó captura
	 */
	private function parseCapture( DOMElement $element = null ) {
		if (  !is_null( $element ) ) {
			$codigo = $this->getNodeValue( 'codigo' , $element );
			$mensagem = $this->getNodeValue( 'mensagem' , $element );
			$dataHora = $this->getNodeValue( 'data-hora' , $element );
			$valor = $this->getNodeValue( 'valor' , $element );

			$this->capture = new CaptureNode( $codigo , $mensagem , $dataHora , $valor );
		}
	}
}