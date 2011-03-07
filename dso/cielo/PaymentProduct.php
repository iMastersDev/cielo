<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo
 */

/**
 * Formas de pagamento para o webservice da Cielo
 * @ingroup		Cielo
 * @interface	PaymentProduct
 */
interface PaymentProduct {
	/**
	 * Crédito à Vista
	 */
	const ONE_TIME_PAYMENT = 1;

	/**
	 * Parcelado pela loja
	 */
	const INSTALLMENTS_BY_AFFILIATED_MERCHANTS = 2;

	/**
	 * Parcelado pela administradora
	 */
	const INSTALLMENTS_BY_CARD_ISSUERS = 3;

	/**
	 * Débito
	 */
	const DEBIT = 'A';
}