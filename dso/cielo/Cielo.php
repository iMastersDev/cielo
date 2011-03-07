<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo
 */

require_once 'dso/cielo/PaymentProduct.php';
require_once 'dso/cielo/nodes/PaymentMethodNode.php';
require_once 'dso/cielo/nodes/OrderDataNode.php';
require_once 'dso/cielo/nodes/EcDataNode.php';
require_once 'dso/cielo/nodes/CardDataNode.php';
require_once 'dso/cielo/request/AuthorizationRequest.php';
require_once 'dso/cielo/request/TransactionRequest.php';
require_once 'dso/cielo/request/CancellationRequest.php';
require_once 'dso/cielo/request/CaptureRequest.php';
require_once 'dso/cielo/request/QueryRequest.php';
require_once 'dso/cielo/request/TIDRequest.php';
require_once 'dso/http/CURL.php';

/**
 * Builder para criação dos objetos da integração com a Cielo
 * @ingroup		Cielo
 * @class		Cielo
 */
class Cielo {
	/**
	 * @var	boolean
	 */
	private $automaticCapture = false;

	/**
	 * @var	HTTPRequest
	 */
	private $httpRequester;

	/**
	 * URL do webservice
	 * @var string
	 */
	private $cieloURL;

	/**
	 * URL de retorno
	 * @var string
	 */
	private $returnURL;

	/**
	 * Código de afiliação do cliente
	 * @var string
	 */
	private $affiliationCode;

	/**
	 * Chave de afiliação do cliente
	 * @var string
	 */
	private $affiliationKey;

	/**
	 * @var	AbstractCieloNode
	 */
	private $transaction;

	/**
	 * @brief	Constroi o builder
	 * @details	Constroi o builder para integração com o webservice da Cielo
	 * @param	integer $mode Define o modo da integração, os valores possíveis são:
	 * @li		<b>CieloMode::DEPLOYMENT</b> Para o ambiente de testes
	 * @li		<b>CieloMode::PRODUCTION</b> Para o ambiente de produção
	 * @param	string $returnURL URL de retorno
	 * @param	string $affiliationCode Código de afiliação da loja
	 * @param	string $affiliationKey	Chave de afiliação
	 * @see		CieloMode::DEPLOYMENT
	 * @see		CieloMode::PRODUCTION
	 * @throws	InvalidArgumentException Se o modo não for um dos especificados acima.
	 * @throws	InvalidArgumentException Se a URL de retorno for inválida.
	 * @throws	InvalidArgumentException Se o código de afiliação for inválido.
	 * @throws	InvalidArgumentException Se a chave de afiliação for inválida.
	 */
	final public function __construct( $mode = CieloMode::PRODUCTION , $returnURL = null , $affiliationCode = null , $affiliationKey = null ) {
		switch ( $mode ) {
			case CieloMode::DEPLOYMENT :
				$this->cieloURL = 'https://qasecommerce.cielo.com.br/servicos/ecommwsec.do';
				break;
			case CieloMode::PRODUCTION :
				$this->cieloURL = 'https://ecommerce.cbmp.com.br/servicos/ecommwsec.do';
				break;
			default :
				throw new InvalidArgumentException( 'Modo inválido' );
		}

		if (  !is_null( $returnURL ) ) {
			$this->setReturnURL( $returnURL );
		}

		if (  !is_null( $affiliationCode ) ) {
			$this->setAffiliationCode( $affiliationCode );
		}

		if (  !is_null( $affiliationKey ) ) {
			$this->setAffiliationKey( $affiliationKey );
		}
	}

	/**
	 * Recupera o XML da última requisição
	 * @param	boolean $highlight Indica se o retorno deverá ser formatado
	 * @return	string
	 * @throws	BadMethodCallException Se nenhuma transação tiver sido efetuada
	 */
	public function __getLastRequest( $highlight = false ) {
		if ( !is_null( $this->transaction ) ) {
			return $this->transaction->getRequestXML( $highlight );
		} else {
			throw new BadMethodCallException( 'Nenhuma transação foi feita ainda' );
		}
	}

	/**
	 * Recupera o XML da última resposta
	 * @param	boolean $highlight Indica se o retorno deverá ser formatado
	 * @return	string
	 * @throws	BadMethodCallException Se nenhuma transação tiver sido efetuada
	 */
	public function __getLastResponse( $highlight = false ) {
		if ( !is_null( $this->transaction ) ) {
			return $this->transaction->getResponseXML( $highlight );
		} else {
			throw new BadMethodCallException( 'Nenhuma transação foi feita ainda' );
		}
	}

	/**
	 * Define que a captura será feita automaticamente, por padrão a captura é manual.
	 * @return	Cielo
	 */
	public function automaticCapture() {
		$this->automaticCapture = true;

		return $this;
	}

	/**
	 * Cria um objeto de requisição de autorização da transacao
	 * @param	string $tid ID da transação
	 * @param	string $creditCard Tipo do cartão
	 * @param	string $cardNumber Número do cartão de crédito
	 * @param	integer $cardExpiration Data de expiração do cartão no formato <b>yyyymm</b>
	 * @param	integer $indicator Indicador do código de segurança
	 * @param	integer $securityCode Código de segurança do cartão
	 * @param	string $orderNumber Número identificador do pedido
	 * @param	integer $orderValue Valor do pedido
	 * @param	string $paymentProduct Forma de pagamento do pedido, pode ser uma das seguintes:
	 * @li	PaymentMethod::ONE_TIME_PAYMENT - <b>1</b> - Crédito à Vista
	 * @li	PaymentMethod::INSTALLMENTS_BY_AFFILIATED_MERCHANTS - <b>2</b> - Parcelado pela loja
	 * @li	PaymentMethod::INSTALLMENTS_BY_CARD_ISSUERS - <b>3</b> - Parcelado pela administradora
	 * @li	PaymentMethod::DEBIT - <b>A</b> - Débito
	 * @param $parcels integer Número de parcelas do pedido.
	 * @attention Se $formaPagamento for 1 (Crédito à Vista) ou A (Débito), $parcelas precisa, <b>necessariamente</b>
	 * ser igual a <b>1</b>
	 * @param	string $freeField Um valor qualquer que poderá ser enviado à Cielo para ser resgatado posteriormente
	 * @return	AuthorizationRequest
	 * @throws	UnexpectedValueException Se $formaPagamento for 1 (Crédito à Vista) ou A (Débito) e o número de parcelas
	 * for diferente de 1
	 */
	final public function buildAuthorizationRequest( $tid , $creditCard , $cardNumber , $cardExpiration , $indicator , $securityCode , $orderNumber , $orderValue , $paymentProduct , $parcels = 1 , $freeField = null ) {
		if ( ( ( $paymentProduct == PaymentProduct::ONE_TIME_PAYMENT ) || ( $paymentProduct == PaymentProduct::DEBIT ) ) && ( $parcels != 1 ) ) {
			throw new UnexpectedValueException( 'Quando a forma de pagamento é Crédito à vista ou Débito, o número de parcelas deve ser 1' );
		} else {
			if ( is_int( $orderValue ) || is_float( $orderValue ) ) {
				$this->transaction = new AuthorizationRequest( $this->getHTTPRequester() );
				$this->transaction->addNode( new EcDataNode( $this->getAffiliationCode() , $this->getAffiliationKey() ) );
				$this->transaction->addNode( new CardDataNode( $cardNumber , $cardExpiration , $indicator , $securityCode ) );
				$this->transaction->addNode( new OrderDataNode( $orderNumber , $orderValue ) );
				$this->transaction->addNode( new PaymentMethodNode( $paymentProduct , $parcels , $creditCard ) );
				$this->transaction->setCapture( $this->automaticCapture );
				$this->transaction->setURL( $this->cieloURL );
				$this->transaction->setTID( $tid );

				if (  !is_null( $freeField ) ) {
					$this->transaction->setFreeField( $freeField );
				}

				return $this->transaction;
			} else {
				throw new UnexpectedValueException( sprintf( 'O valor do pedido deve ser numérico, %s foi dado.' , gettype( $orderValue ) ) );
			}
		}
	}

	/**
	 * @brief	Cria um objeto de requisição de cancelamento de transacao
	 * @details	Constroi o objeto de transação a partir de uma consulta para cancelamento, utilizando o TID (<i>Transaction ID</i>).
	 * @param	string $tid TID da transação que será utilizado para fazer a consulta
	 * @return	CancellationRequest
	 */
	final public function buildCancellationTransaction( $tid ) {
		$this->transaction = new CancellationRequest( $this->getHTTPRequester() );
		$this->transaction->addNode( new EcDataNode( $this->getAffiliationCode() , $this->getAffiliationKey() ) );
		$this->transaction->setTID( $tid );
		$this->transaction->setURL( $this->cieloURL );

		return $this->transaction;
	}

	/**
	 * @brief	Cria um objeto Transacao
	 * @details	Constroi o objeto de transação a partir de uma captura, utilizando o TID (<i>Transaction ID</i>).
	 * @param	string $tid TID da transação que será utilizado para fazer a captura
	 * @param	float $value Valor que será capturado
	 * @return	CaptureRequest
	 * @throws	InvalidArgumentException Se o valor for definido mas não for numérico
	 */
	final public function buildCaptureTransaction( $tid , $value = null ) {
		$nullValue = is_null( $value );

		if ( $nullValue || is_float( $value ) || is_int( $value ) ) {
			$this->transaction = new CaptureRequest( $this->getHTTPRequester() );
			$this->transaction->addNode( new EcDataNode( $this->getAffiliationCode() , $this->getAffiliationKey() ) );
			$this->transaction->setTID( $tid );
			$this->transaction->setURL( $this->cieloURL );

			if (  !$nullValue ) {
				$this->transaction->setValue( $value );
			}

			return $this->transaction;
		} else {
			throw new InvalidArgumentException( sprintf( 'O valor deve ser um inteiro ou float, %s foi dado' , gettype( $value ) ) );
		}
	}

	/**
	 * @brief	Cria um objeto Transacao
	 * @details	Constroi o objeto de transação a partir de uma consulta, utilizando o TID (<i>Transaction ID</i>).
	 * @param	string $tid TID da transação que será utilizado para fazer a consulta
	 * @return	QueryRequest
	 */
	final public function buildQueryTransaction( $tid ) {
		$this->transaction = new QueryRequest( $this->getHTTPRequester() );
		$this->transaction->addNode( new EcDataNode( $this->getAffiliationCode() , $this->getAffiliationKey() ) );
		$this->transaction->setTID( $tid );
		$this->transaction->setURL( $this->cieloURL );

		return $this->transaction;
	}

	/**
	 * @brief	Cria um objeto de requisição de TID
	 * @param	string $creditCard Tipo do cartão
	 * @param	string $paymentProduct Forma de pagamento do pedido, pode ser uma das seguintes:
	 * @li	PaymentMethod::ONE_TIME_PAYMENT - <b>1</b> - Crédito à Vista
	 * @li	PaymentMethod::INSTALLMENTS_BY_AFFILIATED_MERCHANTS - <b>2</b> - Parcelado pela loja
	 * @li	PaymentMethod::INSTALLMENTS_BY_CARD_ISSUERS - <b>3</b> - Parcelado pela administradora
	 * @li	PaymentMethod::DEBIT - <b>A</b> - Débito
	 * @param $parcels integer Número de parcelas do pedido.
	 * @attention Se $formaPagamento for 1 (Crédito à Vista) ou A (Débito), $parcelas precisa, <b>necessariamente</b>
	 * ser igual a <b>1</b>
	 * @return	TIDRequest
	 * @throws	UnexpectedValueException Se $formaPagamento for 1 (Crédito à Vista) ou A (Débito) e o número de parcelas
	 * for diferente de 1
	 */
	final public function buildTIDRequest( $creditCard , $paymentProduct , $parcels = 1 ) {
		if ( ( ( $paymentProduct == PaymentProduct::ONE_TIME_PAYMENT ) || ( $paymentProduct == PaymentProduct::DEBIT ) ) && ( $parcels != 1 ) ) {
			throw new UnexpectedValueException( 'Quando a forma de pagamento é Crédito à vista ou Débito, o número de parcelas deve ser 1' );
		} else {
			$this->transaction = new TIDRequest( $this->getHTTPRequester() );
			$this->transaction->addNode( new EcDataNode( $this->getAffiliationCode() , $this->getAffiliationKey() ) );
			$this->transaction->addNode( new PaymentMethodNode( $paymentProduct , $parcels , $creditCard ) );
			$this->transaction->setURL( $this->cieloURL );

			return $this->transaction;
		}
	}

	/**
	 * @brief	Cria um objeto de requisição de transacao
	 * @details Constroi um objeto de requisição de transação para autenticação
	 * @param	string $creditCard Tipo do cartão
	 * @param	string $orderNumber Número identificador do pedido
	 * @param	integer $orderValue Valor do pedido
	 * @param	string $paymentProduct Forma de pagamento do pedido, pode ser uma das seguintes:
	 * @li	PaymentMethod::ONE_TIME_PAYMENT - <b>1</b> - Crédito à Vista
	 * @li	PaymentMethod::INSTALLMENTS_BY_AFFILIATED_MERCHANTS - <b>2</b> - Parcelado pela loja
	 * @li	PaymentMethod::INSTALLMENTS_BY_CARD_ISSUERS - <b>3</b> - Parcelado pela administradora
	 * @li	PaymentMethod::DEBIT - <b>A</b> - Débito
	 * @param $parcels integer Número de parcelas do pedido.
	 * @attention Se $formaPagamento for 1 (Crédito à Vista) ou A (Débito), $parcelas precisa, <b>necessariamente</b>
	 * ser igual a <b>1</b>
	 * @param	string $freeField Um valor qualquer que poderá ser enviado à Cielo para ser resgatado posteriormente
	 * @return	TransactionRequest
	 * @throws	UnexpectedValueException Se $formaPagamento for 1 (Crédito à Vista) ou A (Débito) e o número de parcelas
	 * for diferente de 1
	 */
	final public function buildTransactionRequest( $creditCard , $orderNumber , $orderValue , $paymentProduct , $parcels = 1 , $freeField = null ) {
		if ( ( ( $paymentProduct == PaymentProduct::ONE_TIME_PAYMENT ) || ( $paymentProduct == PaymentProduct::DEBIT ) ) && ( $parcels != 1 ) ) {
			throw new UnexpectedValueException( 'Quando a forma de pagamento é Crédito à vista ou Débito, o número de parcelas deve ser 1' );
		} else {
			if ( is_int( $orderValue ) || is_float( $orderValue ) ) {
				$this->transaction = new TransactionRequest( $this->getHTTPRequester() );
				$this->transaction->addNode( new EcDataNode( $this->getAffiliationCode() , $this->getAffiliationKey() ) );
				$this->transaction->addNode( new OrderDataNode( $orderNumber , $orderValue ) );
				$this->transaction->addNode( new PaymentMethodNode( $paymentProduct , $parcels , $creditCard ) );
				$this->transaction->setReturnURL( $this->getReturnURL() );
				$this->transaction->setCapture( $this->automaticCapture );

				if (  !is_null( $freeField ) ) {
					$this->transaction->setFreeField( $freeField );
				}

				$this->transaction->setURL( $this->cieloURL );

				return $this->transaction;
			} else {
				throw new UnexpectedValueException( sprintf( 'O valor do pedido deve ser numérico, %s foi dado.' , gettype( $orderValue ) ) );
			}
		}
	}

	/**
	 * Recupera o número de afiliação da loja junto à Cielo
	 * @return	string O código de afiliação
	 * @throws	BadMethodCallException Se não tivermos um código de afiliação
	 */
	public function getAffiliationCode() {
		if ( is_null( $this->affiliationCode ) ) {
			throw new BadMethodCallException( 'Código de afiliação não definido' );
		} else {
			return $this->affiliationCode;
		}
	}

	/**
	 * Recupera a chave da afiliação da loja junto à Cielo
	 * @return	string A chave de afiliação
	 * @throws	BadMethodCallException Se não tivermos uma chave de afiliação
	 */
	public function getAffiliationKey() {
		if ( is_null( $this->affiliationKey ) ) {
			throw new BadMethodCallException( 'Chave de afiliação não definido' );
		} else {
			return $this->affiliationKey;
		}
	}

	/**
	 * Recupera o objeto de requisição HTTP
	 * @return	HTTPRequest
	 */
	public function getHTTPRequester() {
		if ( is_null( $this->httpRequester ) ) {
			return new CURL();
		}

		return $this->httpRequester;
	}

	/**
	 * @brief	Recupera a URL de retorno que será utilizado pela Cielo para retornar à loja
	 * @details	O valor retornado pode utilizar o template <b>{pedido}</b> para compor a URL
	 * de retorno, esse valor será substituído pelo número do pedido informado.
	 * @return	string
	 */
	public function getReturnURL() {
		if (  !is_null( $this->returnURL ) ) {
			return $this->returnURL;
		} else {
			throw new BadMethodCallException( 'Ainda não foi definido a URL de retorno' );
		}
	}

	/**
	 * Define o código de afiliação
	 * @param	string $affiliationCode Código de afiliação
	 * @throws	InvalidArgumentException Se o código de afiliação não for uma string
	 */
	public function setAffiliationCode( $affiliationCode ) {
		if ( is_string( $affiliationCode ) ) {
			$this->affiliationCode = & $affiliationCode;
		} else {
			throw new InvalidArgumentException( 'Código de afiliação inválido' );
		}
	}

	/**
	 * Define a chave de afiliação
	 * @param	string $affiliationKey Chave de afiliação
	 * @throws	InvalidArgumentException Se a chave de afiliação não for uma string
	 */
	public function setAffiliationKey( $affiliationKey ) {
		if ( is_string( $affiliationKey ) ) {
			$this->affiliationKey = & $affiliationKey;
		} else {
			throw new InvalidArgumentException( 'Chave de afiliação inválida' );
		}
	}

	/**
	 * Define a URL de retorno
	 * @param	string $url
	 * @throws	InvalidArgumentException Se a URL de retorno for inválida
	 */
	public function setReturnURL( $url ) {
		if ( filter_var( $url , FILTER_VALIDATE_URL ) ) {
			$this->returnURL = & $url;
		} else {
			throw new InvalidArgumentException( 'URL de retorno inválida' );
		}
	}

	/**
	 * Define o objeto de requisição HTTP
	 * @param	HTTPRequest $httpRequester
	 * @return	CieloBuilder
	 */
	public function useHttpRequester( HTTPRequest $httpRequester ) {
		$this->httpRequester = $httpRequester;

		return $this;
	}
}