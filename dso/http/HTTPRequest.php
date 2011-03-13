<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas à conexão HTTP
 * @package		dso.http
 */

require_once 'dso/http/HTTPRequestMethod.php';

/**
 * @brief		Requisição HTTP
 * @details		Interface para criação de objetos que farão a requisição HTTP
 * @interface	HTTPRequest
 */
interface HTTPRequest {
	/**
	 * Fecha a conexão HTTP
	 */
	public function close();

	/**
	 * Abre uma conexão HTTP
	 * @param	$target string URL que será utilizado na conexão
	 * @return	boolean
	 */
	public function open( $target );

	/**
	 * Envia dados e recupera a resposta utilizando um método específico
	 * @param	$data array Dados que serão enviados
	 * @param	$method string Método que será utilizado para enviar os dados
	 * @return	string Resposta HTTP
	 */
	public function execute( array $data = array() , $method = HTTPRequestMethod::GET );
}