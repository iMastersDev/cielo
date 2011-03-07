<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas à conexão HTTP
 * @package		dso.http
 */

/**
 * @brief		Métodos de requisição HTTP
 * @details		Constantes para identificar o método de requisição HTTP
 * @author		João Batista Neto
 */
interface HTTPRequestMethod {
	const DELETE = 'DELETE';
	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
}