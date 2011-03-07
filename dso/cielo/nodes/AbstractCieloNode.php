<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo.nodes
 */

require_once 'dso/cielo/nodes/XMLNode.php';
require_once 'dso/http/HTTPRequestMethod.php';

/**
 * @brief		Nó abstrato de requisições e consultas
 * @details		Base para criação de objetos da composição do XML de requisição
 * e resposta.
 * @class		AbstractCieloNode
 */
abstract class AbstractCieloNode implements XMLNode {
	/**
	 * Objeto que será utilizado para conexão
	 * @var		HTTPRequest
	 */
	private $httpRequester;

	/**
	 * Nós que serão utilizados nas requisições
	 * @var		ArrayObject
	 */
	private $nodes;

	/**
	 * Versão do webservice da Cielo
	 * @var		string
	 */
	private $version = '1.1.0';

	/**
	 * XML de requisição (para debug)
	 * @var		string
	 */
	private $requestXML;

	/**
	 * XML de resposta (para debug)
	 * @var		string
	 */
	private $responseXML;

	/**
	 * URL da requisição
	 * @var		string
	 */
	private $url;

	/**
	 * Constroi o objeto de integração com o webservice da Cielo
	 * @param	HTTPRequest $httpRequester Objeto que será utilizado para a conexão
	 * @param	string $version Versão do webservice
	 */
	public function __construct( HTTPRequest $httpRequester , $version = '1.1.0' ) {
		$this->httpRequester = $httpRequester;
		$this->nodes = new ArrayObject();
		$this->version = $version;
	}

	/**
	 * Adiciona um objeto que será representado como um nó no XML que será enviado à Cielo
	 * @param	XMLNode $node
	 */
	public function addNode( XMLNode $node ) {
		$this->nodes->append( $node );
	}

	/**
	 * Faz a requisição no webservice
	 * @return	string XML de retorno
	 */
	public function call() {
		$this->httpRequester->open( $this->url );

		$this->requestXML = $this->createXMLNode();
		$this->responseXML = $this->httpRequester->execute( array( 'mensagem' => $this->requestXML ) , HTTPRequestMethod::POST );

		return $this->responseXML;
	}

	/**
	 * Cria o nó XML referente ao objeto.
	 * @return	string
	 * @see		XMLNode::createXMLNode()
	 */
	public function createXMLNode() {
		$root = $this->getRootNode();
		$xml = '';

		foreach ( $this->nodes->getIterator() as $node ) {
			$xml .= $node->createXMLNode();
		}

		return sprintf( '<?xml version="1.0" encoding="UTF-8"?>%s<%s id="%s" versao="%s" xmlns="%s">%s</%s>' , PHP_EOL , $root , $this->getId() , $this->version , $this->getNamespace() , $xml , $root );
	}

	/**
	 * Recupera o ID do nó raiz
	 * @return	string
	 */
	abstract protected function getId();

	/**
	 * Recupera o namespace do XML que será enviado para o webservice
	 * @return	string
	 */
	protected function getNamespace() {
		return 'http://ecommerce.cbmp.com.br';
	}

	/**
	 * Recupera o nome do nó raiz do XML que será enviado à Cielo
	 * @return	string
	 */
	abstract protected function getRootNode();

	/**
	 * Recupera o XML de requisição
	 * @param	boolean $format Indica se o retorno deve ser formatado
	 * @return	string
	 */
	public function getRequestXML( $format = false ) {
		return $format ? highlight_string( $this->requestXML , true ) : $this->requestXML;
	}

	/**
	 * Recupera o XML de resposta
	 * @param	boolean $format Indica se o retorno deve ser formatado
	 * @return	string
	 */
	public function getResponseXML( $format = false ) {
		return $format ? highlight_string( $this->responseXML , true ) : $this->responseXML;
	}

	/**
	 * Define a URL da requisição
	 * @param	string $url
	 * @throws	InvalidArgumentException Se a URL for inválida
	 */
	public function setURL( $url ) {
		if ( filter_var( $url , FILTER_VALIDATE_URL ) ) {
			$this->url = $url;
		} else {
			throw new InvalidArgumentException( 'URL inválida' );
		}
	}
}