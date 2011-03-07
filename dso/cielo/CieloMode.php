<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo
 */

/**
 * @brief	Modo de integração
 * @details	Define o modo de integração da loja<br /><p>
 * Durante os testes, o modo DESENVOlVIMENTO deve ser utilizado,
 * nessa situação, a URL do serviço será https://qasecommerce.cielo.com.br/servicos/ecommwsec.do e quando
 * estiver em produção a URL do serviço será https://ecommerce.cbmp.com.br/servicos/ecommwsec.do</p>
 * @ingroup		Cielo
 * @interface	CieloMode
 */
interface CieloMode {
	/**
	 * @brief	Ambiente de testes
	 * @details	Define que está em ambiente de testes, deve ser utilizado antes da homologação
	 */
	const DEPLOYMENT = 1;

	/**
	 * @brief	Ambiente de produção
	 * @details	Define que está em ambiente de produção, deve ser utilizado <b>após</b> a homologação
	 */
	const PRODUCTION = 2;
}