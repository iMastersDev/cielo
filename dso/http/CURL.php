<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas à conexão HTTP
 * @package		dso.http
 */

require_once 'dso/http/HTTPRequest.php';
require_once 'dso/http/HTTPRequestMethod.php';

/**
 * @brief		Implementação HTTPRequest para cURL
 * @class		CURL
 * @implements	HTTPRequest
 */
class CURL implements HTTPRequest {
	/**
	 * Recurso cURL
	 * @var resource
	 */
	private $curl;

	/**
	 * URL alvo da conexão
	 * @var string
	 */
	private $target;

	/**
	 * Destroi o objeto e fecha a conexão se estiver aberta
	 */
	public function __destruct() {
		if ( $this->testResource( false ) ) {
			curl_close( $this->curl );
		}
	}

	/**
	 * Fecha a conexão HTTP
	 * @see		HTTPRequest::close()
	 * @throws	BadMethodCallException Se o recurso CURL não estiver aberto
	 */
	public function close() {
		if ( $this->testResource() ) {
			curl_close( $this->curl );
		}
	}

	/**
	 * Executa a requisição HTTP
	 * @param	$data array Dados que serão enviados
	 * @param	string $method Método que será utilizado para enviar os dados
	 * @return	string Resposta da requisição HTTP
	 * @throws	BadMethodCallException Caso o recurso não esteja aberto.
	 */
	public function execute( array $data = array() , $method = HTTPRequestMethod::GET ) {
		if ( $this->testResource() ) {
			curl_setopt( $this->curl , CURLOPT_RETURNTRANSFER , 1 );

			switch ( $method ) {
				case HTTPRequestMethod::POST :
					curl_setopt( $this->curl , CURLOPT_POST , 1 );
					curl_setopt( $this->curl , CURLOPT_POSTFIELDS , http_build_query( $data ) );

					break;
				case HTTPRequestMethod::DELETE :
				case HTTPRequestMethod::PUT :
					curl_setopt( $this->curl , CURLOPT_CUSTOMREQUEST , $method );
				case HTTPRequestMethod::GET :
					curl_setopt( $this->curl , CURLOPT_URL , sprintf( '%s?%s' , $this->target , http_build_query( $data ) ) );
					break;
				default :
					throw new UnexpectedValueException( 'Método desconhecido' );
			}

			$resp = curl_exec( $this->curl );
			$error = curl_error( $this->curl );
			$errno = curl_errno( $this->curl );

			if ( (int) $errno != 0 ) {
				throw new RuntimeException( $error , $errno );
			}

			return $resp;
		}
	}

	/**
	 * Abre uma conexão HTTP
	 * @param	$target string URL que será utilizado na conexão
	 * @return	boolean <b>TRUE</b> Se for possível iniciar cURL
	 * @see		HTTPRequest::open()
	 * @throws	RuntimeException Se a extensão cURL não estiver instalada no sistema
	 * @throws	RuntimeException Se não for possível iniciar cURL
	 */
	public function open( $target ) {
		if ( function_exists( 'curl_init' ) ) {
			/**
			 * Fechamos uma conexão existente antes de abrir uma nova
			 */
			if ( is_resource( $this->curl ) ) {
				$this->close();
			}

			$curl = curl_init();

			/**
			 * Verificamos se o recurso CURL foi criado com êxito
			 */
			if ( is_resource( $curl ) ) {
				curl_setopt( $curl , CURLOPT_SSL_VERIFYPEER , 0 );
				curl_setopt( $curl , CURLOPT_HEADER , 0 );
				curl_setopt( $curl , CURLOPT_FOLLOWLOCATION , 1 );
				curl_setopt( $curl , CURLOPT_URL , $target );

				$this->curl = $curl;
				$this->target = $target;
			} else {
				throw new RuntimeException( 'Não foi possível iniciar cURL' );
			}
		} else {
			throw new RuntimeException( 'Extensão cURL não está instalada.' );
		}
	}

	/**
	 * Testa o recurso cURL e opcionalmente dispara uma exceção se ele não tiver aberto.
	 * @param	$throws boolean Indica se deverá ser disparada uma exceção caso o recurso
	 * não esteja aberto.
	 * @return	boolean Caso o recurso curl esteja aberto.
	 * @throws	BadMethodCallException Caso o recurso não esteja aberto.
	 */
	private function testResource( $throws = true ) {
		if (  !is_resource( $this->curl ) ) {
			if ( $throws ) {
				throw new BadMethodCallException( 'Recurso cURL não está aberto' );
			} else {
				return false;
			}
		}

		return true;
	}
}