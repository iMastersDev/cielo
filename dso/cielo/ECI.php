<?php
/**
 * @author		João Batista Neto
 * @brief		Classes relacionadas ao webservice da Cielo
 * @package		dso.cielo
 */

/**
 * @brief	Nível de segurança
 * @details	Toda transação possui um indicador, chamado ECI (Eletronic Commerce Indicator),
 * que diz quão segura uma transação é.  Seus valores variam de acordo com o resultado da autenticação:
 * @li	5 - Quando o portador autenticou-se com sucesso.
 * @li	6 - Quando o emissor não forneceu mecanismos de autenticação.
 * @li	7 - Quando o portador não se autenticou ou a loja optou por não submeter à autenticação
 * @ingroup		Cielo
 * @class		ECI
 */
final class ECI {
	/**
	 * O portador autenticou-se com sucesso.
	 */
	const AUTHENTICATED = 1;

	/**
	 * O emissor não forneceu mecanismos de autenticação.
	 */
	const WITHOUT_AUTHENTICATION = 2;

	/**
	 * O portador não se autenticou ou a loja optou por não submeter à autenticação.
	 */
	const UNAUTHENTICATED = 4;

	/**
	 * O portador não se autenticou ou a loja optou por não submeter à autenticação.
	 */
	const AFFILIATED_DID_NOT_SEND_AUTHENTICATION = 8;

	/**
	 * Recupera o valor do indicador de segurança
	 * @param	integer $indicator Tipo do indicador de segurança
	 * @param	string $flag Bandeira do cartão de crédito
	 * @see		CreditCard::MASTER_CARD
	 * @see		CreditCard::VISA
	 * @throws	UnexpectedValueException Se a bandeira não for conhecida
	 * @throws	UnexpectedValueException Se o ECI não for conhecido
	 */
	public static function value( $indicator , $flag ) {
		if ( $flag == CreditCard::VISA || $flag == CreditCard::MASTER_CARD ) {
			switch ( $indicator ) {
				case ECI::AUTHENTICATED :
					return $flag == CreditCard::VISA ? 5 : 2;
				case ECI::WITHOUT_AUTHENTICATION :
					return $flag == CreditCard::VISA ? 6 : 1;
				case ECI::UNAUTHENTICATED :
					return $flag == CreditCard::VISA ? 7 : 0;
				case ECI::AFFILIATED_DID_NOT_SEND_AUTHENTICATION :
					return $flag == CreditCard::VISA ? 7 : 0;
				default :
					throw new UnexpectedValueException( 'Indicador de segurança desconhecido' );
			}
		} else {
			throw new UnexpectedValueException( 'Indicador de segurança desconhecido' );
		}
	}

	/**
	 * Interpreta o valor do ECI
	 * @param	$eci integer
	 * @return	integer
	 */
	public static function parse( $eci ) {
		switch ( $eci ) {
			case 2 :
			case 5 :
				return ECI::AUTHENTICATED;
				break;
			case 1 :
			case 6 :
				return ECI::WITHOUT_AUTHENTICATION;
				break;
			case 0 :
			case 7 :
				return ECI::UNAUTHENTICATED | ECI::AFFILIATED_DID_NOT_SEND_AUTHENTICATION;
				break;
		}
	}
}